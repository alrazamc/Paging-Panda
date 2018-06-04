<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Worker_model Class
 * 
 * @package   PHP_FMT
 * @subpackage  Models
 * @category  Worker_model
 */
class Worker_model extends CI_Model{
    
    function __construct()
    {
        parent :: __construct();
    }


    //get content to delete attachments from S3
    public function get_content_with_media($user_id, $category_id = false)
    {
        if(empty($category_id)) return [];
        $this->db->select('content_id, category_id, post_type, array_to_json(attachments) as attachments');
        $this->db->from('contents');
        $this->db->where('user_id', $user_id);
        $this->db->where('category_id', $category_id);
        $this->db->group_start();
            $this->db->where('post_type', POST_TYPE_PHOTO);
            $this->db->or_where('post_type', POST_TYPE_VIDEO);
        $this->db->group_end();
        return $this->db->get()->result();
    }

    public function delete_category_content($user_id, $category_id)
    {
        if(empty($category_id)) return;
        $this->db->where('user_id', $user_id);
        $this->db->where('category_id', $category_id);
        $this->db->delete('contents');
    }

    public function get_active_users()
    {
        $this->db->where('status', USER_STATUS_ACTIVE);
        $this->db->order_by('user_id', 'DESC');
        return $this->db->get('users')->result();
    }

    public function get_user_feeds($user_id)
    {
        $this->db->where('user_id', $user_id);
        return $this->db->get('rss_feeds')->result();
    }

    public function delete_one_month_old_posts()
    {
        $one_month_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
        $this->db->where('scheduled_time < ',$one_month_ago);
        $this->db->delete('posts');
    }


    public function get_queue_load($scheduled_time)
    {
        $this->db->select('posts.user_id, count(posts.post_id) as post_count');
        $this->db->from('posts');
        $this->db->join('accounts', 'accounts.account_id=posts.account_id', 'inner');
        $this->db->join('users', 'users.user_id=posts.user_id', 'inner');
        $this->db->where('posts.status', POST_STATUS_PENDING);
        $this->db->where('users.status', USER_STATUS_ACTIVE);
        $this->db->where('users.queue_paused', NO);
        $this->db->where('accounts.token_expired', NO);
        $this->db->where('accounts.post_perms', YES);
        //$this->db->where('posts.scheduled_time < ', $scheduled_time);
        $this->db->where('posts.scheduled_time < ', '2018-06-10 04:00:00');
        $this->db->group_by('posts.user_id');
        $this->db->order_by('posts.user_id', 'DESC'); //important, change algo in worker if changed
        return $this->db->get()->result();
    }

    public function get_pending_posts($start_user_id, $end_user_id, $scheduled_time)
    {
        $this->db->select('posts.post_id, posts.user_id, posts.content_id, posts.account_id, posts.schedule_id, posts.status, posts.scheduled_time, posts.is_random');
        $this->db->select('contents.category_id, contents.use_once, contents.is_published, contents.post_type, contents.post_order, contents.random_order');
        $this->db->select('contents.message, contents.link_url, array_to_json(contents.attachments) as attachments, array_to_json(published_on) as published_on');
        $this->db->select('accounts.account_fb_id, accounts.access_token');
        $this->db->from('posts');
        $this->db->join('contents', 'contents.content_id=posts.content_id', 'inner');
        $this->db->join('accounts', 'accounts.account_id=posts.account_id', 'inner');
        $this->db->join('users', 'users.user_id=posts.user_id', 'inner');
        $this->db->where('posts.status', POST_STATUS_PENDING);
        $this->db->where('users.status', USER_STATUS_ACTIVE);
        $this->db->where('users.queue_paused', NO);
        $this->db->where('accounts.token_expired', NO);
        $this->db->where('accounts.post_perms', YES);
        $this->db->where('posts.user_id >=', $start_user_id);
        $this->db->where('posts.user_id <=', $end_user_id);
        //$this->db->where('posts.scheduled_time < ', $scheduled_time);
        $this->db->where('posts.scheduled_time < ', '2018-06-10 04:00:00');
        $this->db->order_by('posts.user_id DESC, posts.post_id ASC'); //sort order important to synchronize queue pointers
        return $this->db->get()->result();
    }

    public function update_post($post)
    {
        $record = array(
            'status' => $post->status
        );
        if(isset($post->error_message))
            $record['error_message'] = $post->error_message;
        if(isset($post->published_time))
            $record['published_time'] = $post->published_time;
        if(isset($post->post_fb_id))
            $record['post_fb_id'] = $post->post_fb_id;

        $this->db->where('user_id', $post->user_id);
        $this->db->where('status', POST_STATUS_PENDING);
        $this->db->where('content_id', $post->content_id);
        $this->db->where('account_id', $post->account_id);
        $this->db->where('schedule_id', $post->schedule_id);
        $this->db->where('scheduled_time', $post->scheduled_time);
        $this->db->update('posts', $record);
    }

    public function update_content($post)
    {
        $record = array();
        if($post->status == POST_STATUS_PUBLISHED && $post->is_published == NO)
            $record['is_published'] = YES;
        if($post->use_once == YES)
        {
            if(!is_array($post->published_on))
                $post->published_on = json_decode($post->published_on);
            if(!in_array($post->account_id, $post->published_on))
            {
                $post->published_on[] = $post->account_id;
                $record['published_on'] = '{'.implode(',', $post->published_on).'}';
            }
        }
        if(empty($record)) return;
        $this->db->where('content_id', $post->content_id);
        $this->db->where('user_id', $post->user_id);
        $this->db->update('contents', $record);
    }

    public function update_account($post)
    {
        if(!isset($post->token_expired) || $post->token_expired == NO) return;
        $record['token_expired'] = YES;
        $this->db->where('account_id', $post->account_id);
        $this->db->where('user_id', $post->user_id);
        $this->db->update('accounts', $record);

        $this->db->where('user_id', $post->user_id);
        $this->db->update('users', array('accounts_alert' => YES));
    }

    public function set_post_error_with_expired_tokens($scheduled_time)
    {
        $message = json_encode(array('message' => 'Access token expired'));
        $query = "UPDATE posts SET status = ".POST_STATUS_ERROR.", error_message = '$message' FROM accounts 
                 WHERE posts.account_id = accounts.account_id AND (accounts.token_expired = 1 OR accounts.post_perms = 0) AND posts.scheduled_time = '$scheduled_time' AND posts.status = ".POST_STATUS_PENDING;
        $this->db->query($query);
    }

    public function log_cron($cron_type, $data)
    {
        $record = array(
            'cron_type' => $cron_type,
            'log_text' => is_array($data) ? json_encode($data) : $data,
            'log_time' => date('Y-m-d H:i:s')
        );
        $this->db->insert('cron_logs', $record);
    }
}

/* End of file Worker_model.php */
/* Location: ./application/models/Worker_model.php */