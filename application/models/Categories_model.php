<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Categories_model Class
 * 
 * @package   PHP_FMT
 * @subpackage  Models
 * @category  Categories_model
 */
class Categories_model extends CI_Model{
    
    function __construct()
    {
        parent :: __construct();
    }

    /**
    * get all available insights metrics
    * @return object
    */
    public function get_content_count($user_id = 0)
    {
        $this->db->select('category_id, use_once, count(content_id) as content_count')->from('contents');
        $this->db->where('contents.user_id', $user_id);
        $this->db->group_by('contents.category_id, contents.use_once');
        return $this->db->get()->result();
    }

    /**
    * get all available insights metrics
    * @return object
    */
    public function get_list($user_id = 0)
    {
        $this->db->select('*')->from('categories');
        $this->db->where('categories.user_id', $user_id);
        $this->db->order_by('categories.category_name ASC');
        return $this->db->get()->result();
    }

    /**
    * default categories
    * @return object
    */
    public function create_default($user_id = 0)
    {
        $default = ['Quotes', 'Funny', 'Blog Posts', 'News'];
        $batch = [];
        foreach ($default as $key => $name)
        {
            $batch[] = array(
                'user_id' => $user_id,
                'category_name' => $name,
                'category_color' => $this->random_color(),
                'include_in_random' => YES,
                'queue_pointer' => 0
            );
        }
        $this->db->insert_batch('categories', $batch);
    }

    /**
    * insert new category
    * @return object
    */
    public function insert($user_id = 0)
    {
        $record = array(
            'user_id' => $user_id,
            'category_name' => $this->input->post('category'),
            'category_color' => $this->random_color(),
            'include_in_random' => $this->input->post('include') ? YES : NO,
            'queue_pointer' => 0
        );
        $this->db->insert('categories', $record);
    }


    /**
    * insert new category
    * @return object
    */
    public function add_category($user_id = 0, $category, $include)
    {
        $record = array(
            'user_id' => $user_id,
            'category_name' => $category,
            'category_color' => $this->random_color(),
            'include_in_random' => $include ? YES : NO,
            'queue_pointer' => 0
        );
        $this->db->insert('categories', $record);
    }

    /**
    * update category
    * @return object
    */
    public function update($category_id = 0)
    {
        $record = array(
            'category_name' => $this->input->post('category'),
            'include_in_random' => $this->input->post('include') ? YES : NO
        );
        $this->db->where('category_id', $category_id);
        $this->db->update('categories', $record);
    }

    /**
    * delete category
    * @return object
    */
    public function delete($category_id = 0, $user_id = 0)
    {
        $query = "DELETE FROM posts USING contents where posts.user_id = $user_id AND contents.content_id = posts.content_id AND contents.category_id = $category_id";
        $this->db->query($query);

        $this->db->where('category_id', $category_id);
        $this->db->delete('schedule');

        $this->db->where('category_id', $category_id);
        $this->db->delete('rss_feeds');

        $this->db->where('category_id', $category_id);
        $this->db->delete('categories');
    }

    /**
    * get single record
    * @return object
    */
    public function get_record($category_id = 0)
    {
        return $this->db->get_where('categories', array('category_id' => $category_id))->row();
    }

    private function random_color_part() {
        return str_pad( dechex( mt_rand( 0, 127 ) ), 2, '0', STR_PAD_LEFT);
    }

    private function random_color() {
        return '#'.$this->random_color_part() . $this->random_color_part() . $this->random_color_part();
    }

    public function shuffle($category_id, $user_id)
    {
        $this->db->select('content_id, post_order')->from('contents');
        $this->db->where('user_id', $user_id);
        $this->db->where('moderation_status', CONTENT_MODERATION_APPROVED);
        if($category_id == -1)
            $this->db->where('use_once', YES);
        else
        {
            $this->db->where('use_once', NO);
            $this->db->where('category_id', $category_id);
        }
        $this->db->order_by('post_order ASC');
        $contents = $this->db->get()->result();
        if(empty($contents)) return;
        $orders = array();
        foreach($contents as $item)
            $orders[$item->post_order] = $item->content_id;
        shuffle($orders);
        $pairs = "";
        foreach($orders  as $post_order => $content_id)
            $pairs .= "($content_id, $post_order),";
        $pairs = substr($pairs, 0, -1);

        $query = "UPDATE contents set post_order = orders.new_order FROM(VALUES $pairs) as orders(content_id, new_order) 
                  WHERE contents.content_id = orders.content_id AND contents.user_id = $user_id";
        $this->db->query($query);
        $this->reset_pointer($category_id, $user_id);
    }

    public function reset($category_id, $user_id)
    {
        $this->db->select('content_id, post_order')->from('contents');
        $this->db->where('user_id', $user_id);
        $this->db->where('moderation_status', CONTENT_MODERATION_APPROVED);
        if($category_id == -1)
            $this->db->where('use_once', YES);
        else
        {
            $this->db->where('use_once', NO);
            $this->db->where('category_id', $category_id);
        }
        $this->db->order_by('time_created DESC, content_id ASC');
        $contents = $this->db->get()->result();
        if(empty($contents)) return;
        $total = count($contents);
        $orders = array();
        for($i=0; $i<$total; $i++)
            $orders[$i] = $contents[$i]->content_id;
        $pairs = "";
        foreach($orders  as $post_order => $content_id)
            $pairs .= "($content_id, $post_order),";
        $pairs = substr($pairs, 0, -1);

        $query = "UPDATE contents set post_order = orders.new_order FROM(VALUES $pairs) as orders(content_id, new_order) 
                  WHERE contents.content_id = orders.content_id AND contents.user_id = $user_id";
        $this->db->query($query);
        $this->reset_pointer($category_id, $user_id);
    }

    public function reset_pointer($category_id, $user_id)
    {
        if($category_id > 0)
        {
            $this->db->set('queue_pointer', 0);
            $this->db->where('category_id', $category_id);
            $this->db->where('user_id', $user_id);
            $this->db->update('categories');       
        }else
        {
            if($category_id == 0)
                $this->db->set('general_pointer', 0);
            else if($category_id == -1)
                $this->db->set('use_once_pointer', 0);
            $this->db->where('user_id', $user_id);
            $this->db->update('users');
        }
    }
}

/* End of file Categories_model.php */
/* Location: ./application/models/Categories_model.php */