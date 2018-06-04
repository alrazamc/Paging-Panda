<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Queue_model Class
 * 
 * @package   PHP_FMT
 * @subpackage  Models
 * @category  Queue_model
 */
class Queue_model extends CI_Model{
    
    function __construct()
    {
        parent :: __construct();
    }

    public function remove_auto_scheduled_pending_posts($user_id, $category_id = false)
    {
        if($category_id === FALSE || $category_id == CATEGORY_RANDOM)
        {
            $this->db->where('user_id', $user_id);
            $this->db->where('status', POST_STATUS_PENDING);
            $this->db->where('schedule_id !=', 0);
            if($category_id == CATEGORY_RANDOM)
                $this->db->where('is_random', YES); //delete only randomly selected posts
            $this->db->delete('posts');
        }else 
        {
            $query = "DELETE FROM posts USING contents where posts.user_id = $user_id AND posts.schedule_id != 0 AND posts.is_random = 0 AND  posts.status = ".POST_STATUS_PENDING;
            $query .= " AND contents.content_id = posts.content_id AND ";
            if($category_id == CATEGORY_USE_ONCE)
                $query .= "contents.use_once = 1";
            else
                $query .= "contents.use_once = 0 AND contents.category_id = $category_id";
            $this->db->query($query);
        }
    }

    public function get_categories($user_id)
    {
        $this->db->where('user_id', $user_id);
        return $this->db->get('categories')->result();
    }

    public function get_user($user_id = 0)
    {
        $this->db->select('general_pointer, use_once_pointer, random_pointer')->from('users');
        $this->db->where('user_id', $user_id);
        return $this->db->get()->row();
    }

    public function get_slots($user_id, $category_id = false)
    {
        $this->db->select('schedule.*, array_to_json(accounts) as accounts')->from('schedule');
        $this->db->where('user_id', $user_id);
        if($category_id !== FALSE)
        {
            if($category_id == CATEGORY_RANDOM)
            {
                $this->db->where('use_random', YES);
                $this->db->where('use_once', NO);
            }
            else if($category_id == CATEGORY_USE_ONCE)
            {
                $this->db->where('use_once', YES);
                $this->db->where('use_random', NO);
            }
            else
            {
                $this->db->where('use_random', NO);
                $this->db->where('use_once', NO);
                $this->db->where('category_id', $category_id);
            }
        }
        $this->db->order_by('day ASC, time ASC');
        return $this->db->get()->result();
    }

    public function get_content($user_id, $category_id = false)
    {
        $this->db->select('content_id, category_id, use_once, is_scheduled, scheduled_time, should_expire, expiry_time, post_order, array_to_json(accounts) as accounts, array_to_json(published_on) as published_on');
        $this->db->from('contents');
        $this->db->where('user_id', $user_id);
        $this->db->where('moderation_status', CONTENT_MODERATION_APPROVED);
        if($category_id !== FALSE)
        {
            if($category_id == CATEGORY_USE_ONCE)
                $this->db->where('use_once', YES);
            else
            {
                $this->db->where('use_once', NO);
                $this->db->where('category_id', $category_id);
            }
        }        
        $this->db->order_by('category_id ASC, post_order ASC');
        return $this->db->get()->result();
    }

    public function get_random_content($user_id)
    {
        $this->db->select('content_id, category_id, use_once, is_scheduled, scheduled_time, should_expire, expiry_time, post_order, random_order, array_to_json(accounts) as accounts, array_to_json(published_on) as published_on');
        $this->db->from('contents');
        $this->db->where('user_id', $user_id);
        $this->db->where('moderation_status', CONTENT_MODERATION_APPROVED);
        $this->db->order_by('random_order ASC');
        return $this->db->get()->result();
    }

    /**
    * get single category
    * @return object
    */
    public function get_category($category_id = 0)
    {
        return $this->db->get_where('categories', array('category_id' => $category_id))->row();
    }
}

/* End of file Queue_model.php */
/* Location: ./application/models/Queue_model.php */