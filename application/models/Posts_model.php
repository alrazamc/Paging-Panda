<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Posts_model Class
 * 
 * @package   PHP_FMT
 * @subpackage  Models
 * @category  Posts_model
 */
class Posts_model extends CI_Model{
    
    function __construct()
    {
        parent :: __construct();
    }

    /**
    * get all available insights metrics
    * @return object
    */
    public function get_list($user_id = 0, $filters = array(), $get_count = false, $all_records = false)
    {
        $this->db->select('contents.content_id, contents.user_id, contents.category_id, contents.use_once, post_type, message, link_url, rss_id, link_image,link_title,link_description,link_caption,array_to_json(contents.attachments) as attachments');
        $this->db->select('post_id, account_id,schedule_id,status, posts.scheduled_time, post_fb_id, is_random');
        $this->db->from('posts');
        $this->db->join('contents', 'contents.content_id=posts.content_id', 'inner');
        $this->db->where('contents.user_id', $user_id);
        $this->apply_filters($filters);
        if($get_count)
            return $this->db->count_all_results();
        if(!isset($filters['status']) || $filters['status'] == POST_STATUS_PENDING)
            $this->db->order_by('posts.scheduled_time ASC, post_id ASC');
        else if(isset($filters['status']) && $filters['status'] == POST_STATUS_PUBLISHED)
            $this->db->order_by('posts.scheduled_time DESC, post_id ASC');
        if($all_records)
            return $this->db->get()->result();
        $offset = ($filters['page'] - 1) * getenv('POSTS_PER_PAGE');
        $this->db->limit(getenv('POSTS_PER_PAGE'), $offset);
        return $this->db->get()->result();
    }

    private function apply_filters($filters = array())
    {
        if(isset($filters['status']))
            $this->db->where('posts.status', $filters['status']);

        if($filters['use_once'] !== '')
            $this->db->where('contents.use_once', YES);
        else if($filters['category_id'] !== ''){
            if($filters['category_id'] == '0')
                $this->db->where('contents.use_once', NO);
            $this->db->where('contents.category_id', $filters['category_id']);
        }

        if($filters['account_id'] !== '')
            $this->db->where('posts.account_id', $filters['account_id']);
        if($filters['rss_feed_id'] !== '')
            $this->db->where('contents.rss_id', $filters['rss_feed_id']);
        if($filters['post_type'] !== '')
            $this->db->where('contents.post_type', $filters['post_type']);
        
        if($filters['query'] !== ''){
            $this->db->group_start();
            $this->db->where('contents.message ILIKE', "%".$filters['query']."%");
            $this->db->or_where('contents.link_url ILIKE', "%".$filters['query']."%");
            $this->db->group_end();
        }
        if($filters['start_date'] !== '' || $filters['end_date'] !== '')
        {
            date_default_timezone_set($this->session->userdata('time_zone'));
            $hours = date('Z')/3600;
            $operator = $hours < 0 ? '-' : '+';
            $hours = abs($hours);
            $interval = $hours > 1 ? 'hours' : 'hour';
        }
        
        if($filters['start_date'] !== '')
        {
            $start_date = date('Y-m-d 00:00:00', strtotime($filters['start_date']));
            $this->db->where("posts.scheduled_time $operator '$hours $interval' >=", $start_date);
        }
        if($filters['end_date'] !== '')
        {
            $end_date = date('Y-m-d 23:59:59', strtotime($filters['end_date']));
            $this->db->where("posts.scheduled_time $operator '$hours $interval' <=", $end_date);
        }
    }

    /**
    * update category
    * @return object
    */
    public function create_manual_posts($content)
    {
        if($content->moderation_status != CONTENT_MODERATION_APPROVED || $content->is_scheduled == NO) return false;
        if($content->use_once == YES)
        {
            $content->accounts = !is_array($content->accounts) ? json_decode($content->accounts) : $content->accounts;
            $content->published_on = !is_array($content->published_on) ? json_decode($content->published_on) : $content->published_on; //already published on these pages
            $not_published_on = array_diff($content->accounts, $content->published_on);
            if( empty( $not_published_on ) ) //already published on all connected accountss
                return false;
            $content->accounts = $not_published_on; // only post on pages that haven't used this content
        } 
        if($content->should_expire == YES && strtotime($content->expiry_time) < strtotime("now")) return false;
        if( strtotime($content->scheduled_time) < strtotime("now")) //scheduled time passed
        {
            $this->db->where('content_id', $content->content_id);
            $this->db->update('contents', array('is_scheduled' => NO));
            return false;
        }else
        {
            $pages = !is_array($content->accounts) ? json_decode($content->accounts) : $content->accounts;
            $batch = [];
            foreach ($pages as $key => $page_id)
            {
                $batch[] = array(
                    'user_id' => $content->user_id,
                    'content_id' => $content->content_id,
                    'account_id' => $page_id,
                    'schedule_id' => 0,
                    'status' => POST_STATUS_PENDING,
                    'error_message' => '',
                    'scheduled_time' => $content->scheduled_time,
                    'post_fb_id' => ''
                );
            }
            if(!empty($batch))
                $this->db->insert_batch('posts', $batch);
            return true;
        }
    }

    public function remove_from_queue($content, $is_random)
    {
        if($is_random)
            $this->db->set('random_order','random_order - 1', false); //remove from random Queue
        else
            $this->db->set('post_order','post_order - 1', false); //remove from category queue
        $this->db->where('user_id', $content->user_id);
        $this->db->where('moderation_status', CONTENT_MODERATION_APPROVED);
        if($is_random)
        {
            $this->db->where("random_order > ", $content->random_order);
        }
        else
        {
            if($content->use_once == YES)
                $this->db->where('use_once', YES);
            else
            {
                $this->db->where('use_once', NO);
                $this->db->where('category_id', $content->category_id);
            }
            $this->db->where("post_order > ", $content->post_order);
        }
        $this->db->update('contents');
    }

    //skip post
    public function skip_post($content, $is_random)
    {
        //find current pointer location
        $current_pointer = 0;
        if($is_random)
        {
            $current_pointer = $this->session->userdata('random_pointer');
            if(empty($current_pointer))
            {
                $this->db->where('user_id', $content->user_id);
                $this->db->where('moderation_status', CONTENT_MODERATION_APPROVED);
                $this->db->order_by('random_order ASC');
                $this->db->limit(1);
                $current_pointer = $this->db->get('contents')->row()->random_order;
            }
        }
        else if($content->use_once == YES)
            $current_pointer = $this->session->userdata('use_once_pointer');
        else if($content->category_id == '0')
            $current_pointer = $this->session->userdata('general_pointer');
        else
            $current_pointer = $this->db->get_where('categories', array('category_id' => $content->category_id))->row()->queue_pointer;

        //Case 1 => item at pointer location, Just forward the pointer to skip
        if(($is_random && $content->random_order == $current_pointer ) || $content->post_order == $current_pointer )
            return $this->point_to_next_item($content, $is_random);

        //move all items one up from pointer location, pointers doesn't move
        if($is_random)
            $this->db->set('random_order','random_order + 1', false); //from current pointer move all plus one
        else
            $this->db->set('post_order','post_order + 1', false); //from current pointer move all plus one

        $this->db->where('user_id', $content->user_id);
        $this->db->where('moderation_status', CONTENT_MODERATION_APPROVED);
        if($is_random)
        {
            $this->db->where("random_order >= $current_pointer");
        }
        else
        {
            if($content->use_once == YES)
                $this->db->where('use_once', YES);
            else
            {
                $this->db->where('use_once', NO);
                $this->db->where('category_id', $content->category_id);
            }
            $this->db->where("post_order >= $current_pointer");
        }
        $this->db->update('contents');
        //place item at pointer location
        if($is_random)
            $this->db->set('random_order', $current_pointer);
        else
            $this->db->set('post_order', $current_pointer);
        $this->db->where('content_id', $content->content_id);
        $this->db->update('contents');

        $this->remove_from_queue($content, $is_random); //remove from queue, move all next items 1 step down from content old index

        //move pointer to next item
        if(($is_random && $content->random_order > $current_pointer ) || $content->post_order > $current_pointer )
        {
            $is_random ? $content->random_order = $current_pointer : $content->post_order = $current_pointer;
            return $this->point_to_next_item($content, $is_random);
        }
    }

    public function point_to_next_item($content, $is_random)
    {
        if($is_random)
        {
            $query = "UPDATE users set random_pointer = 
                      (select random_order from contents where user_id = $content->user_id AND moderation_status = ".CONTENT_MODERATION_APPROVED." AND random_order > $content->random_order ORDER BY random_order ASC LIMIT 1) 
                      WHERE users.user_id = $content->user_id";
            $this->db->query($query);
        }else if($content->use_once == YES)
        {
            $this->db->set('use_once_pointer', $content->post_order + 1, false);
            $this->db->where('user_id', $content->user_id);
            $this->db->update('users');
        }else if($content->category_id == CATEGORY_GENERAL)
        {
            $this->db->set('general_pointer', $content->post_order + 1, false);
            $this->db->where('user_id', $content->user_id);
            $this->db->update('users');
        }else
        {
            $this->db->set('queue_pointer', $content->post_order + 1, false);
            $this->db->where('user_id', $content->user_id);
            $this->db->where('category_id', $content->category_id);
            $this->db->update('categories');
        }
    }
    /**
    * delete single post
    * @return object
    */
    public function delete($post_id = 0)
    {
        $this->db->where('post_id', $post_id);
        $this->db->delete('posts');
    }

    /**
    * Delete content posts
    * @return object
    */
    public function delete_content_posts($user_id, $content_id = 0, $status = false)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('content_id', $content_id);
        if($status)
            $this->db->where('status', $status);
        $this->db->delete('posts');
    }

    /**
    * Delete content posts
    * @return object
    */
    public function delete_content_posts_bulk($user_id ,$content_ids = 0, $status = false)
    {
        if($status)
            $this->db->where('status', $status);
        $this->db->where('user_id', $user_id);
        $this->db->where_in('content_id', $content_ids);
        $this->db->delete('posts');
    }

    /**
    * get single record
    * @return object
    */
    public function get_record($post_id = 0)
    {
        return $this->db->get_where('posts', array('post_id' => $post_id))->row();
    }

    public function change_queue_status($user_id, $status = YES)
    {
        $this->db->set('queue_paused', $status);
        $this->db->where('user_id', $user_id);
        $this->db->update('users');
    }

    public function get_content_history($content_id = 0, $post_fb_id = '')
    {
        $this->db->where('content_id', $content_id);
        $this->db->where('status', POST_STATUS_PUBLISHED);
        if(!empty($post_fb_id))
            $this->db->where('post_fb_id', $post_fb_id);
        $this->db->order_by('scheduled_time', 'DESC');
        return $this->db->get('posts')->result();
    }
}

/* End of file Posts_model.php */
/* Location: ./application/models/Posts_model.php */