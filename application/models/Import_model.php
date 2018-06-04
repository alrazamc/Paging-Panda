<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Import_model Class
 * 
 * @package   PHP_FMT
 * @subpackage  Models
 * @category  Import_model
 */
class Import_model extends CI_Model{
    
    function __construct()
    {
        parent :: __construct();
    }

    /**
    * get all available insights metrics
    * @return object
    */
    public function get_feeds($user_id = 0)
    {
        $this->db->select('rss_feeds.*, array_to_json(accounts) as accounts');
        $this->db->from('rss_feeds');
        $this->db->where('rss_feeds.user_id', $user_id);
        $this->db->order_by('time_created', "DESC");
        return $this->db->get()->result();
    }

    /**
    * insert new category
    * @return object
    */
    public function create($user_id = 0, $feed = array())
    {
        $record = array(
            'user_id' => $user_id,
            'category_id' => $feed['category_id'] == '-1' ? 0 : $feed['category_id'],
            'use_once' => $feed['category_id'] == '-1' ? YES : NO,
            'accounts' => '{'.implode(',', $feed['pages']).'}',
            'rss_feed_url' => $feed['feed_url'],
            'rss_feed_name' => $feed['feed_title'],
            'to_pending' => $feed['to_pending'],
            'time_created' => date('Y-m-d H:i:s')
        );
        $this->db->insert('rss_feeds', $record);
    }

    /**
    * insert new category
    * @return object
    */
    public function is_feed_exist($user_id = 0, $feed = array(), $rss_feed_id = false)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('category_id', $feed['category_id'] == '-1' ? 0 : $feed['category_id']);
        $this->db->where('use_once', $feed['category_id'] == '-1' ? YES : NO);
        $this->db->where('rss_feed_url', $feed['feed_url']);
        if($rss_feed_id)
            $this->db->where('rss_feed_id !='. $rss_feed_id);
        return $this->db->count_all_results('rss_feeds');
    }

    /**
    * insert new category
    * @return object
    */
    public function update($rss_feed_id = 0, $feed = array())
    {
        $record = array(
            'category_id' => $feed['category_id'] == '-1' ? 0 : $feed['category_id'],
            'use_once' => $feed['category_id'] == '-1' ? YES : NO,
            'accounts' => '{'.implode(',', $feed['pages']).'}',
            'rss_feed_url' => $feed['feed_url'],
            'rss_feed_name' => $feed['feed_title'],
            'to_pending' => $feed['to_pending']
        );
        $this->db->where('rss_feed_id', $rss_feed_id);
        $this->db->update('rss_feeds', $record);
    }

    /**
    * delete category
    * @return object
    */
    public function delete($rss_feed_id = 0, $user_id = 0)
    {
        $this->db->where('rss_feed_id', $rss_feed_id);
        $this->db->delete('rss_feeds');

        $this->db->where('rss_id', $rss_feed_id);
        $this->db->where('user_id', $user_id);
        $this->db->update('contents', array('rss_id' => 0));
    }

    /**
    * get single record
    * @return object
    */
    public function get_record($rss_feed_id = 0)
    {
        $this->db->select('rss_feeds.*, array_to_json(accounts) as accounts');
        return $this->db->get_where('rss_feeds', array('rss_feed_id' => $rss_feed_id))->row();
    }

    public function save_content($feed, $items)
    {
        $records = [];
        foreach ($items as $item)
        {
            $this->db->where('user_id', $feed->user_id);
            $this->db->where('rss_id', $feed->rss_feed_id);
            $this->db->where('link_url', $item['link']);
            if($this->db->get('contents')->num_rows()) continue;

            $records[] = array(
                'user_id' => $feed->user_id,
                'category_id' => $feed->category_id,
                'use_once' => $feed->use_once,
                'is_published' => NO,
                'accounts' => '{'.implode(',', $feed->accounts).'}',
                'published_on' => '{}',
                'post_type' => POST_TYPE_LINK,
                'message' => $item['link'],
                'link_url' => $item['link'],
                'rss_id' => $feed->rss_feed_id,
                'shortener_id' => 0,
                'shorten_url' => '',
                'link_image' => isset($item['image_url']) ? $item['image_url'] : '',
                'link_title' => isset($item['title']) ? $item['title'] : '',
                'link_description' => isset($item['description']) ? substr(strip_tags($item['description']), 0, 512)  : '',
                'link_caption' => $item['link'],
                'attachments' => '{}',
                'is_scheduled' => NO,
                'scheduled_time' => date('Y-m-d H:i:s'),
                'should_expire' => NO,
                'expiry_time' => date('Y-m-d H:i:s'),
                'moderation_status' => $feed->to_pending == YES ? CONTENT_MODERATION_PENDING : CONTENT_MODERATION_APPROVED,
                'post_order' => $feed->to_pending == YES ? -1 : 0,
                'random_order' => get_random_order(),
                'time_created' => date('Y-m-d H:i:s')
            );
        }
        $count = count($records);
        if($count)
        {
            if($feed->to_pending == NO) // not pending add to queue to prefer this
            {
                $current_pointer = (int) $this->get_post_order($feed->user_id, $feed->use_once ? -1 : $feed->category_id, $count);
                for($index = 0; $index < $count; $index++)
                    $records[$index]['post_order'] = $current_pointer + $index;
            }
            $this->db->insert_batch('contents', $records);
        }
        return $count;
    }


    /**
    * make space for new content items, add new items at current pointer, and move other content one up
    */
    public function get_post_order($user_id, $category_id, $item_count)
    {
        if($category_id == '-1')
        {
            $user = $this->db->get_where('users', array('user_id' => $user_id))->row();
            $current_pointer = $user->use_once_pointer;
            $this->db->set('post_order',"post_order + $item_count", false);
            $this->db->where('user_id', $user_id);
            $this->db->where('use_once', YES);
            $this->db->where('post_order >=', $current_pointer);
            $this->db->update('contents');
            return $current_pointer;
        }else if($category_id == '0')
        {
            $user = $this->db->get_where('users', array('user_id' => $user_id))->row();
            $current_pointer = $user->general_pointer;
            $this->db->set('post_order',"post_order + $item_count", false);
            $this->db->where('user_id', $user_id);
            $this->db->where('use_once', NO);
            $this->db->where('category_id', 0);
            $this->db->where('post_order >=', $current_pointer);
            $this->db->update('contents');
            return $current_pointer;
        }else
        {
            $this->db->where('user_id', $user_id);
            $this->db->where('category_id', $category_id);
            $category = $this->db->get('categories')->row();
            $current_pointer = $category->queue_pointer;
            $this->db->set('post_order',"post_order + $item_count", false);
            $this->db->where('user_id', $user_id);
            $this->db->where('category_id', $category_id);
            $this->db->where('post_order >=', $current_pointer);
            $this->db->update('contents');
            return $current_pointer;
        }
    }

    /**
    * update content bulk
    * @return object
    */
    public function save_csv_content($user_id, $items = array(), $allowed_page_ids)
    {
        $records = [];
        foreach ($items as $item)
        {
            foreach($item['accounts'] as $page_id)
            {
                if(!in_array($page_id, $allowed_page_ids))
                    die('invalid Request');
            }
            $category_id = $item['use_once'] ? '-1' : $item['category_id'];
            $records[$category_id][] = array(
                'user_id' => $user_id,
                'category_id' => $item['category_id'],
                'use_once' => $item['use_once'],
                'is_published' => NO,
                'accounts' => '{'.implode(',', $item['accounts']).'}',
                'published_on' => '{}',
                'post_type' => POST_TYPE_TEXT,
                'message' => $item['message'],
                'link_url' => '',
                'rss_id' => 0,
                'shortener_id' => 0,
                'shorten_url' => '',
                'link_image' => '',
                'link_title' => '',
                'link_description' => '',
                'link_caption' => '',
                'attachments' => '{}',
                'is_scheduled' => NO,
                'scheduled_time' => date('Y-m-d H:i:s'),
                'should_expire' => NO,
                'expiry_time' => date('Y-m-d H:i:s'),
                'moderation_status' => CONTENT_MODERATION_APPROVED,
                'post_order' => 0,
                'random_order' => get_random_order(),
                'time_created' => date('Y-m-d H:i:s')
            );
        }
        $categories_count = count($records);
        if(!empty($records))
        {
            $final_batch = [];
            foreach($records as $category_id => $items)
            {
                $current_pointer = (int) $this->get_post_order($user_id, $category_id, count($items));
                for($index = 0; $index < count($items); $index++)
                    $records[$category_id][$index]['post_order'] = $current_pointer + $index;
                $final_batch = array_merge($final_batch, $records[$category_id]);
            }
            if(!empty($final_batch))
                $this->db->insert_batch('contents', $final_batch);
        }
        return $categories_count;
    }

}

/* End of file Import_model.php */
/* Location: ./application/models/Import_model.php */