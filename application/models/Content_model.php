<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Content_model Class
 * 
 * @package   PHP_FMT
 * @subpackage  Models
 * @category  Content_model
 */
class Content_model extends CI_Model{
    
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
        $this->db->select('content_id, contents.category_id, contents.use_once, array_to_json(accounts) as accounts, post_type, message, link_url, rss_id, link_image,link_title,link_description,link_caption,array_to_json(attachments) as attachments, should_expire, expiry_time');
        $this->db->from('contents');
        $this->db->where('contents.user_id', $user_id);
        $this->apply_filters($filters);
        if($get_count)
            return $this->db->count_all_results();
        $this->db->order_by('time_created DESC, content_id ASC');
        if($all_records) // approve all/bulk, get all to check manual scheduling
            return $this->db->get()->result();
        $offset = ($filters['page'] - 1) * getenv('LIBRARY_PER_PAGE');
        $this->db->limit(getenv('LIBRARY_PER_PAGE'), $offset);
        return $this->db->get()->result();
    }

    /**
    * Specific for bulk approve, get all scheduled posts to create manual posts
    * @return object
    */
    public function get_scheduled_list($user_id = 0, $filters = array())
    {
        $this->db->select('content_id, contents.user_id, contents.category_id, contents.use_once, array_to_json(accounts) as accounts, post_type, message, link_url, rss_id, link_image,link_title,link_description,link_caption,array_to_json(attachments) as attachments, moderation_status, is_scheduled, scheduled_time, is_published, should_expire, expiry_time, array_to_json(published_on) as published_on');
        $this->db->from('contents');
        $this->db->where('contents.user_id', $user_id);
        $this->apply_filters($filters);
        $this->db->order_by('time_created DESC, content_id ASC');
        return $this->db->get()->result();
    }

    /**
    * get all available insights metrics
    * @return object
    */
    public function get_bulk_list($user_id = 0, $filters = array(), $get_count = false)
    {
        $this->db->select('content_id, contents.category_id, contents.use_once, array_to_json(accounts) as accounts, post_type, message, link_title');
        $this->db->from('contents');
        $this->db->where('contents.user_id', $user_id);
        $this->apply_filters($filters);
        if($get_count)
            return $this->db->count_all_results();
        $this->db->order_by('time_created DESC, content_id ASC');
        return $this->db->get()->result();
    }

    private function apply_filters($filters = array())
    {
        if(isset($filters['moderation']))
            $this->db->where('contents.moderation_status', $filters['moderation']);

        if($filters['use_once'] !== '')
            $this->db->where('contents.use_once', YES);
        else if($filters['category_id'] !== ''){
            if($filters['category_id'] == '0')
                $this->db->where('contents.use_once', NO);
            $this->db->where('contents.category_id', $filters['category_id']);
        }

        if($filters['account_id'] !== '')
            $this->db->where($this->db->escape($filters['account_id']), 'ANY(contents.accounts)', false);
        if($filters['rss_feed_id'] !== '')
            $this->db->where('contents.rss_id', $filters['rss_feed_id']);
        if($filters['post_type'] !== '')
            $this->db->where('contents.post_type', $filters['post_type']);
        if($filters['is_published'] !== '')
            $this->db->where('contents.is_published', $filters['is_published']);
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
        if($filters['is_expired'] !== '')
        {
            $this->db->where('should_expire', YES);
            $current_time = date('Y-m-d H:i:s');
            if($filters['is_expired'] == '1')
                $this->db->where("expiry_time <", $current_time);
            else if($filters['is_expired'] == '0')
                $this->db->where("expiry_time >=", $current_time);
        }
        if($filters['start_date'] !== '')
        {
            $start_date = date('Y-m-d 00:00:00', strtotime($filters['start_date']));
            $this->db->where("time_created $operator '$hours $interval' >=", $start_date);
        }
        if($filters['end_date'] !== '')
        {
            $end_date = date('Y-m-d 23:59:59', strtotime($filters['end_date']));
            $this->db->where("time_created $operator '$hours $interval' <=", $end_date);
        }
    }

    /**
    * insert new category
    * @return object
    */
    public function create($user_id = 0, $post = array())
    {
        $post_type = POST_TYPE_TEXT;
        if(!empty($post['link_url']))
        {
            $post_type = POST_TYPE_LINK;
        }else if(!empty($post['attachments']))
        {
            $img_extensions = ['png', 'gif', 'jpeg', 'jpg', 'jpe'];
            $ext = pathinfo($post['attachments'][0], PATHINFO_EXTENSION);
            $post_type = in_array(strtolower($ext), $img_extensions) ? POST_TYPE_PHOTO : POST_TYPE_VIDEO;
        }
        $record = array(
            'user_id' => $user_id,
            'category_id' => $post['category_id'] == '-1' ? 0 : $post['category_id'],
            'use_once' => $post['category_id'] == '-1' ? YES : NO,
            'is_published' => NO,
            'accounts' => '{'.implode(',', $post['pages']).'}',
            'published_on' => '{}',
            'post_type' => $post_type,
            'message' => $post['message'],
            'link_url' => $post['link_url'],
            'rss_id' => 0,
            'shortener_id' => 0,
            'shorten_url' => '',
            'link_image' => isset($post['link_preview']['image']) ? $post['link_preview']['image'] : '',
            'link_title' => isset($post['link_preview']['title']) ? $post['link_preview']['title'] : '',
            'link_description' => isset($post['link_preview']['description']) ? $post['link_preview']['description'] : '',
            'link_caption' => isset($post['link_preview']['url']) ? $post['link_preview']['url'] : '',
            'attachments' => '{'.implode(',', $post['attachments']).'}',
            'is_scheduled' => empty($post['is_scheduled']) ? NO : YES,
            'scheduled_time' => date('Y-m-d H:i:s', convert_to_utc($post['scheduled_time'])),
            'should_expire' => empty($post['should_expire']) ? NO : YES,
            'expiry_time' => date('Y-m-d H:i:s', convert_to_utc($post['expiry_time'])),
            'moderation_status' => CONTENT_MODERATION_APPROVED,
            'post_order' => $this->get_post_order($user_id, $post['category_id']),
            'random_order' => get_random_order(),
            'time_created' => date('Y-m-d H:i:s')
        );
        $this->db->insert('contents', $record);
        return $this->db->insert_id();
    }

    /**
    * make space for new content, add new content at current pointer, and move other content one up
    */
    public function get_post_order($user_id, $category_id)
    {
        if($category_id == '-1')
        {
            $current_pointer = $this->session->userdata('use_once_pointer');
            $this->db->set('post_order','post_order + 1', false);
            $this->db->where('user_id', $user_id);
            $this->db->where('use_once', YES);
            $this->db->where('post_order >=', $current_pointer);
            $this->db->update('contents');
            return $current_pointer;
        }else if($category_id == '0')
        {
            $current_pointer = $this->session->userdata('general_pointer');
            $this->db->set('post_order','post_order + 1', false);
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
            $this->db->set('post_order','post_order + 1', false);
            $this->db->where('user_id', $user_id);
            $this->db->where('category_id', $category_id);
            $this->db->where('post_order >=', $current_pointer);
            $this->db->update('contents');
            return $current_pointer;
        }
    }

    /**
    * remove from category queue, post either deleted or moved to other category
    */
    public function remove_from_category_queue($content)
    {    
        $this->db->set('post_order','post_order - 1', false);
        $this->db->where('user_id', $content->user_id);
        if($content->use_once == YES)
            $this->db->where('use_once', YES);
        else
        {
            $this->db->where('use_once', NO);
            $this->db->where('category_id', $content->category_id);
        }
        $this->db->where("post_order > (SELECT post_order FROM contents WHERE content_id = $content->content_id)");
        $this->db->update('contents');
        //backward pointer 1 step if item exist before pointer, because above query will set pointer to next item in this case
        if($content->use_once == YES)
        {
            $query = "UPDATE users set use_once_pointer = (CASE WHEN EXISTS
                      (select post_order from contents where content_id = $content->content_id AND post_order < (SELECT use_once_pointer FROM users WHERE user_id = $content->user_id))  
                      THEN users.use_once_pointer - 1
                      ELSE use_once_pointer
                      END)
                      WHERE users.user_id = $content->user_id";
            $this->db->query($query);
        }
        else if($content->category_id == '0')
        {
            $query = "UPDATE users set general_pointer = (CASE WHEN EXISTS
                      (select post_order from contents where content_id = $content->content_id AND post_order < (SELECT general_pointer FROM users WHERE user_id = $content->user_id))  
                      THEN users.general_pointer - 1
                      ELSE general_pointer
                      END)
                      WHERE users.user_id = $content->user_id";
            $this->db->query($query);
        }
        else
        {
            $query = "UPDATE categories set queue_pointer = (CASE WHEN EXISTS
                      (select post_order from contents where content_id = $content->content_id AND post_order < (SELECT queue_pointer FROM categories WHERE category_id = $content->category_id))  
                      THEN categories.queue_pointer - 1
                      ELSE queue_pointer
                      END)
                      WHERE categories.category_id = $content->category_id";
            $this->db->query($query);
        }
    }

    /**
    * Bulk Approve - add posts to current pointer as latest content
    */
    public function add_bulk_content_to_queue($user_id, $content)
    {
        $records = [];
        foreach ($content as $item)
        {
            $category_id = $item->use_once == YES ? '-1' : $item->category_id;
            $records[$category_id][] = $item;
        }
        $category_count = count($records);
        if(!empty($records))
        {
            $this->load->model('import_model');
            foreach($records as $category_id => $items)
            {
                $current_pointer = (int) $this->import_model->get_post_order($user_id, $category_id, count($items));
                for($index = 0; $index < count($items); $index++)
                {
                    $this->db->where('content_id', $records[$category_id][$index]->content_id);
                    $this->db->update('contents', array('post_order' => $current_pointer + $index));
                }
            }
        }
        return $category_count;
    }

    /**
    * get max sort order of post in a category
    */
    public function get_last_order_category($user_id, $category_id)
    {
        $this->db->select('MAX(post_order) as max_order')->from('contents');
        $this->db->where('user_id', $user_id);
        if($category_id == '-1')
        {
            $this->db->where('use_once', YES);
        }
        else
        {
            $this->db->where('use_once', NO);
            $this->db->where('category_id', $category_id);
        }
        $res = $this->db->get();
        if($res->num_rows())
        {
            $row = $res->row();
            return is_null($row->max_order) ? 0 : $row->max_order + 1;
        }
        return 0;
    }

    /**
    * update category
    * @return object
    */
    public function update($content_id = 0, $post = array(), $old_content)
    {
        $category_changed = false;
        $post_type = POST_TYPE_TEXT;
        if(!empty($post['link_url']))
        {
            $post_type = POST_TYPE_LINK;
        }else if(!empty($post['attachments']))
        {
            $img_extensions = ['png', 'gif', 'jpeg', 'jpg', 'jpe'];
            $ext = pathinfo($post['attachments'][0], PATHINFO_EXTENSION);
            $post_type = in_array(strtolower($ext), $img_extensions) ? POST_TYPE_PHOTO : POST_TYPE_VIDEO;
        }
        $record = array(
            'category_id' => $post['category_id'] == '-1' ? 0 : $post['category_id'],
            'use_once' => $post['category_id'] == '-1' ? YES : NO,
            'accounts' => '{'.implode(',', $post['pages']).'}',
            'post_type' => $post_type,
            'message' => $post['message'],
            'link_url' => $post['link_url'],
            'link_image' => isset($post['link_preview']['image']) ? $post['link_preview']['image'] : '',
            'link_title' => isset($post['link_preview']['title']) ? $post['link_preview']['title'] : '',
            'link_description' => isset($post['link_preview']['description']) ? $post['link_preview']['description'] : '',
            'link_caption' => isset($post['link_preview']['url']) ? $post['link_preview']['url'] : '',
            'attachments' => '{'.implode(',', $post['attachments']).'}',
            'is_scheduled' => empty($post['is_scheduled']) ? NO : YES,
            'scheduled_time' => date('Y-m-d H:i:s', convert_to_utc($post['scheduled_time'])),
            'should_expire' => empty($post['should_expire']) ? NO : YES,
            'expiry_time' => date('Y-m-d H:i:s', convert_to_utc($post['expiry_time']))
        );
        if(isset($post['approve']) && $post['approve']) // new placement
        {
            $record['moderation_status'] = CONTENT_MODERATION_APPROVED;
            $record['post_order'] = $this->get_post_order($old_content->user_id, $post['category_id']); // place in category Queue, prefer as new content
        }else if( $old_content->moderation_status == CONTENT_MODERATION_APPROVED && 
            ($record['category_id'] != $old_content->category_id || $record['use_once'] != $old_content->use_once) ) // content approved and category is changed
        {
            $this->remove_from_category_queue($old_content); // category changed, remove from old category queue
            $record['post_order'] = $this->get_last_order_category($old_content->user_id, $post['category_id']); //put to the end of queue in new category
            $category_changed = true;
        }
        $this->db->where('content_id', $content_id);
        $this->db->update('contents', $record);
        return $category_changed;
    }

    /**
    * delete category
    * @return object
    */
    public function delete($content = 0)
    {
        $this->db->where('content_id', $content->content_id);
        $this->db->delete('contents');
    }

    /**
    * get single record
    * @return object
    */
    public function get_record($content_id = 0)
    {
        $this->db->select('contents.*, array_to_json(accounts) as accounts, array_to_json(published_on) as published_on, array_to_json(attachments) as attachments');
        return $this->db->get_where('contents', array('content_id' => $content_id))->row();
    }

    /**
    * get single record
    * @return object
    */
    public function get_moderation_status_counts($user_id = 0)
    {
        $this->db->select('moderation_status, count(*) as total')->from('contents');
        $this->db->where('user_id', $user_id);
        $this->db->group_by('moderation_status');
        return $this->db->get()->result();
    }

    /**
    * get single record
    * @return object
    */
    public function change_status($content, $new_status)
    {
        $record = array('moderation_status' => $new_status);
        if($new_status == CONTENT_MODERATION_APPROVED)
        {
            $record['post_order'] = $this->get_post_order($content->user_id, $content->use_once == YES ? '-1' : $content->category_id);
        }
        $this->db->where('content_id', $content->content_id);
        $this->db->update('contents', $record);

    }

    /**
    * get single record
    * @return object
    */
    public function change_status_bulk($user_id, $new_status, $filters = array())
    {
        $this->db->where('user_id', $user_id);
        $this->apply_filters($filters);
        $this->db->update('contents', array('moderation_status' => $new_status));
    }

    /**
    * get single record
    * @return object
    */
    public function delete_bulk($user_id, $filters = array())
    {
        $this->db->where('user_id', $user_id);
        $this->apply_filters($filters);
        $this->db->delete('contents');
    }

    /**
    * get content in bulk by ids
    * @return object
    */
    public function get_content_bulk($user_id, $ids = array())
    {
        $this->db->select('content_id, user_id, use_once, category_id, post_order, moderation_status, is_scheduled, scheduled_time, is_published, should_expire, expiry_time, post_type, array_to_json(attachments) as attachments, array_to_json(accounts) as accounts, array_to_json(published_on) as published_on')->from('contents');
        $this->db->where('user_id', $user_id);
        $this->db->where_in('content_id', $ids);
        return $this->db->get()->result();
    }

    /**
    * get content in bulk by ids
    * @return object
    */
    public function delete_content_bulk($user_id, $ids = array())
    {
        $this->db->where('user_id', $user_id);
        $this->db->where_in('content_id', $ids);
        $this->db->delete('contents');
    }

    /**
    * update approved content in bulk
    * @return object
    */
    public function update_content_bulk($user_id, $items = array(), $allowed_page_ids)
    {
        $category_changed = false;
        foreach ($items as $item)
        {
            foreach($item['accounts'] as $page_id)
            {
                if(!in_array($page_id, $allowed_page_ids))
                    die('invalid Request');
            }
            $record = array(
                'category_id' => $item['category_id'],
                'use_once' =>    $item['use_once'],
                'accounts' => '{'.implode(',', $item['accounts']).'}'
            );
            if(  $record['category_id'] != $item['old_content']->category_id || $record['use_once'] != $item['old_content']->use_once ) // content approved and category is changed
            {
                $this->remove_from_category_queue($item['old_content']); // category changed, remove from old category queue
                $record['post_order'] = $this->get_last_order_category($user_id, $record['use_once'] == 1 ? '-1' : $record['category_id']); //put to the end of queue in new category
                $category_changed = true;
            }
            $this->db->where('user_id', $user_id);
            $this->db->where('content_id', $item['content_id']);
            $this->db->update('contents', $record);   
        }
        return $category_changed;
    }
}

/* End of file Content_model.php */
/* Location: ./application/models/Content_model.php */