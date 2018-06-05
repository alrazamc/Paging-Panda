<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Worker Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Worker
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Worker extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        // if(!is_cli()) 
        //     exit(0);
    }

    //Delete S3 media of category and delete content after that
    public function delete_s3_media($user_id, $category_id)
    {
        $this->load->model('worker_model');
        $items = $this->worker_model->get_content_with_media($user_id, $category_id);

        $images = [];
        $videos = [];
        foreach ($items as $item)
        {
            $item->attachments = json_decode($item->attachments);
            if($item->post_type == POST_TYPE_PHOTO)
                $images = array_merge($images, $item->attachments);
            elseif ($item->post_type == POST_TYPE_VIDEO)
                $videos = array_merge($videos, $item->attachments);
        }
        $this->load->library('myaws');
        if(!empty($images))
        {
            $keys = [];
            foreach ($images as $key => $value)
                $keys[] = "user-$user_id/$value";
            $this->myaws->delete_multi_objects(getenv('AWS_IMG_BUCKET'), $keys);
        }
        if(!empty($videos))
        {
            $keys = [];
            foreach ($videos as $key => $value)
                $keys[] = "user-$user_id/$value";
            $this->myaws->delete_multi_objects(getenv('AWS_VIDEO_BUCKET'), $keys);
        }

        $this->worker_model->delete_category_content($user_id, $category_id);
        $this->rebuild_random_queue($user_id);
    }

    //Read single Rss feed and update queue if given
    public function read_rss($rss_feed_id = 0, $update_queue = true)
    {
        if(!$rss_feed_id || !is_numeric($rss_feed_id))
            return false;
        $this->load->model('import_model');
        $feed = $this->import_model->get_record($rss_feed_id);
        if(!isset($feed->rss_feed_id))
            return false;
        $feed->accounts = json_decode($feed->accounts);
        
        $this->load->library('rssparser');
        $this->rssparser->set_feed_url($feed->rss_feed_url);
        $this->rssparser->parse();
        $items = $this->rssparser->getFeed(getenv('FEED_ITEMS_LIMIT'));
        if(!empty($items))
        {
            $imported = $this->import_model->save_content($feed, $items);
            if($update_queue && $imported && $feed->to_pending == NO) // favor new content in queue
                $this->rebuild_category_queue($feed->user_id, $feed->use_once ? CATEGORY_USE_ONCE : $feed->category_id, REBUILD_RANDOM_YES);
        }
    }

    //Daily cron to read feeds, update queues, delete 30 days old post
    public function update_content_queues()
    {
        $start_time = time();
        $this->load->model('worker_model');
        $users = $this->worker_model->get_active_users();
        $feeds_count = 0;
        $max_num_of_feeds = 0;
        $max_feeds_user_id = 0;
        foreach ($users as $user)
        {
            $feeds = $this->worker_model->get_user_feeds($user->user_id);
            $feeds_count += count($feeds);
            if(count($feeds) > $max_num_of_feeds)
            {
                $max_num_of_feeds = count($feeds);
                $max_feeds_user_id = $user->user_id;
            }
            foreach($feeds as $feed)
                $this->read_rss($feed->rss_feed_id, false); //false=> Do not update queue
            $this->rebuild_queue($user->user_id); //rebuild user queue after importing all user's feeds
        }
        $this->worker_model->delete_one_month_old_posts();

        $stats = array(
            'total_users' => count($users),
            'total_feeds' => $feeds_count,
            'max_num_of_feeds' => $max_num_of_feeds,
            'max_feeds_user_id' => $max_feeds_user_id,
            'execution_time' => time() - $start_time
        );
        $this->worker_model->log_cron(CRON_DAILY_UPDATE, $stats);
    }

    //Rebuild whole queue of a user
    public function rebuild_queue($user_id)
    {
        $this->load->library('queue');
        $this->queue->rebuild($user_id);
        //echo "User-$user_id queue reconstructed";
    }

    //Rebuild a specific category queue of a user
    public function rebuild_category_queue($user_id, $category_id = 0, $rebuild_random = 0)
    {
        $this->load->library('queue');
        $this->queue->rebuild_category($user_id, $category_id);
        if($rebuild_random)
            $this->queue->rebuild_random($user_id);
        echo "User-$user_id category $category_id queue reconstructed";
    }

    //Rebuild only random queue of user
    public function rebuild_random_queue($user_id)
    {
        $this->load->library('queue');
        $this->queue->rebuild_random($user_id);
        echo "User-$user_id queue reconstructed ";
    }

    //child cron, publish single batch of around 50 posts
    public function publish_batch($start_user_id = 0, $end_user_id = 0)
    {
        date_default_timezone_set('UTC');
        $scheduled_time = date('Y-m-d H:i:00'); 
        $this->load->model('worker_model');
        $posts = $this->worker_model->get_pending_posts($start_user_id, $end_user_id,  $scheduled_time);
        $final_batch = [];
        $batch = [];
        $api_request_count = 0;
        $total_batches = 0; 
        $total_api_requests=0;
        $this->load->library('myaws');
        $this->load->library('myfacebook');
        foreach($posts as $key => $post)
        {
            $publish_buffer = false;
            $post->attachments = json_decode($post->attachments);
            foreach($post->attachments as $index => $file_name)
                $post->attachments[$index] = $this->myaws->get_s3_obj_url($post->post_type == POST_TYPE_VIDEO ? getenv('AWS_VIDEO_BUCKET'): getenv('AWS_IMG_BUCKET'), "user-$post->user_id/$file_name");
            
            $attachment_count = count($post->attachments);
            $total_api_requests++;//only for cron log
            if($attachment_count > 1)
                $total_api_requests += $attachment_count;

            if($attachment_count <= 1)
                $api_request_count++;
            else if(( $attachment_count + $api_request_count) < 50) //more than one photos
                $api_request_count += $attachment_count + 1; //attachements calls + 1 post call for multiple photos
            else 
                $publish_buffer = true;

            if($publish_buffer === false) // skip post for next batch if attachment + count >= 50
                $batch[$post->post_id] = $post;

            if($publish_buffer === false && $api_request_count < 50 && $key+1 != count($posts) ) // not last post
                continue;
            //batch ready for publishing
            $batch = $this->myfacebook->publish_batch($batch);
            $final_batch = array_merge($final_batch, $batch);
            $total_batches++; //only for cron log
            $batch = []; //empty batch move to next posts
            if($publish_buffer === true)
                $batch[$post->post_id] = $post;
            if($key+1 == count($posts) && !empty($batch)) //no iteration after this, last post multiple photos
            {
                $batch = $this->myfacebook->publish_batch($batch);
                $final_batch = array_merge($final_batch, $batch);
                $total_batches++; //only for cron log
            }
            $api_request_count = 0;
        }
        //everything published, Update DB data
        $this->load->model('posts_model');
        foreach ($final_batch as $post)
        {
            $this->worker_model->update_post( $post ); //status, error, Post ID
            $this->worker_model->update_content( $post ); //is_published, use once tracking
            $this->worker_model->update_account( $post ); //update page, token expired
            $this->posts_model->point_to_next_item($post, $post->is_random); //move queue pointers
        }

        $stats = array(
            'start_user_id' => $start_user_id,
            'end_user_id' => $end_user_id,
            'total_posts' => count($posts),
            'batch_count' => $total_batches,
            'api_requests' => $total_api_requests
        );
        $this->worker_model->log_cron(CRON_QUEUE_BATCH_PROCESSOR, $stats);
    }

    //Cron Schedule queue Batches
    public function process_queues()
    {
        date_default_timezone_set('UTC');
        $scheduled_time = date('Y-m-d H:i:00');
        $this->load->model('worker_model'); 
        $users = $this->worker_model->get_queue_load($scheduled_time); //desc user ids
        $total_users = count($users);
        $total_posts = 0;
        $total_batches = 0;
        $batch_post_count = 0;
        $end_user_id = $total_users > 0 ? $users[0]->user_id : 0;
        foreach ($users as $key => $user)
        {
            $total_posts += $user->post_count;
            if( ($batch_post_count + $user->post_count) > 50  || $key+1 == $total_users )
            {
                if($key+1 == $total_users)
                    $start_user_id = $user->user_id;
                //$this->publish_batch($start_user_id, $end_user_id);
                start_background_process("worker publish_batch $start_user_id $end_user_id");
                $total_batches++;
                $batch_post_count = 0;
                $end_user_id = $user->user_id;
                continue;
            }
            $batch_post_count += $user->post_count;
            $start_user_id = $user->user_id;
        }
        $this->worker_model->set_post_error_with_expired_tokens($scheduled_time);
        if($total_posts == 0) return;
        $stats = array(
            'total_users' => $total_users,
            'total_posts' => $total_posts,
            'total_batches' => $total_batches
        );
        $this->worker_model->log_cron(CRON_QUEUE_PROCESSOR, $stats);
    }
    
    //run in browser for syntax errors
    public function poke()
    {
        start_background_process("worker process_queues");
        echo 'I am Ok';
    }

    public function send()
    {
        $this->load->library('myrabbit');
        $test = array(
            'start_user_id' => mt_rand(1, 10),
            'end_user_id' => mt_rand(11, 20)
        );
        $this->myrabbit->basic_publish(getenv('FACEBOOK_QUEUE'), json_encode($test));
        $this->myrabbit->close();
        echo "DOne";
    }

    public function receive()
    {
        $this->load->library('myrabbit');
        $this->myrabbit->basic_consume(getenv('FACEBOOK_QUEUE'));
        $this->myrabbit->close();
    }

}

/* End of file Worker.php */
/* Location: ./application/controllers/Worker.php */
