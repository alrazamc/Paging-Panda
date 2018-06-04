<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Content extends REST_Controller {

    var $user_id;
    function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('content_model');
    }

    //get signed URLs to upload to S3
    function upload_attachments_post()
    {
        $files = $this->post('files');
        $response = [];
        $image_types = ['image/jpeg', 'image/gif', 'image/png'];
        $video_types = ['video/mp4', 'video/quicktime'];
        $this->load->library('myaws');
        $time = time();
        foreach($files as $file)
        {
            if(in_array($file['type'], $image_types))
                $bucket = getenv('AWS_IMG_BUCKET');
            else if(in_array($file['type'], $video_types))
                $bucket = getenv('AWS_VIDEO_BUCKET');
            else
                continue;
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $file_name = "$time-".uniqid().".$ext"; //90202392-2sdkseu.jpg
            $url = $this->myaws->create_obj_put_url($bucket, "user-$this->user_id/$file_name" , $file['type']);
            $response[] = array(
                'index' => $file['index'],
                'file_name' => $file_name,
                'sgnurl' => $url
            );
        }

        $this->response($response);
    }

    //Delete attachment from S3
    function delete_attachment_post()
    {
        $files = $this->post('files');
        $image_types = ['image/jpeg', 'image/gif', 'image/png'];
        $video_types = ['video/mp4', 'video/quicktime'];
        $this->load->library('myaws');
        foreach($files as $file)
        {
            if(empty($file['key'])) continue;
            if(in_array($file['type'], $image_types))
                $bucket = getenv('AWS_IMG_BUCKET');
            else if(in_array($file['type'], $video_types))
                $bucket = getenv('AWS_VIDEO_BUCKET');
            else
                continue;
            $this->myaws->delete_object($bucket, "user-$this->user_id/".$file['key']);
        }
        $this->response('true');
    }

    //Get Link Preview --- should move to frontend for better performance
    function preview_post()
    {
        $this->response('true');
        $target = urlencode($this->post('url'));
        $response = file_get_contents(getenv('LINK_PREVIEW_API')."?key=".getenv('LINK_PREVIEW_KEY')."&q=$target");
        $this->response( json_decode($response) );
    }

    //create new content manually
    function create_post()
    {
        $post = $this->post('post');
        $this->load->model('accounts_model');
        $user_pages = $this->accounts_model->get_all($this->user_id);
        $allowed_page_ids = [];
        foreach($user_pages as $page)
            $allowed_page_ids[] = $page->account_id;
        foreach($post['pages'] as $page_id)
        {
            if(!in_array($page_id, $allowed_page_ids))
                die('invalid Request');
        }
        $content_id = $this->content_model->create($this->user_id, $post);
        $content = $this->content_model->get_record($content_id);
        $this->load->model('posts_model');
        $is_scheduled = $this->posts_model->create_manual_posts($content);
        if(!$is_scheduled)
            rebuild_category_queue($this->user_id, $content->use_once ? CATEGORY_USE_ONCE : $content->category_id, REBUILD_RANDOM_YES);
        $this->session->set_flashdata('alert', get_alert_html("Content added successfully", ALERT_TYPE_SUCCESS));
        $response['message'] = site_url('content/add');
        $response['type'] = AJAX_RESPONSE_TYPE_REDIRECT;
        $this->response($response);
    }

    //get content from edit content page
    function details_get($content_id = 0)
    {
        if(!$content_id || !is_numeric($content_id))
            show_404();
        $content = $this->content_model->get_record($content_id);
        if(!isset($content->content_id) || $content->user_id != $this->user_id)
            show_404();
        $post = array(
            'category_id' => $content->use_once ? -1 : $content->category_id,
            'accounts' => json_decode($content->accounts),
            'published_on' => json_decode($content->published_on),
            'message' => $content->message,
            'link_url' => $content->link_url,
            'show_link_preview' => $content->post_type == POST_TYPE_LINK ? true : false,
            'preview' => array(
                'title' => $content->link_title,
                'description' => $content->link_description,
                'image' => $content->link_image,
                'url' => $content->link_caption
            ),
            'is_scheduled' => $content->is_scheduled ? true : false,
            'scheduled_time' => date('d M, Y h:i A', convert_to_user_timezone($content->scheduled_time)),
            'should_expire' => $content->should_expire ? true : false,
            'expiry_time' => date('d M, Y h:i A', convert_to_user_timezone($content->expiry_time)),
            'moderation_status' => $content->moderation_status,
            'rss_id' => $content->rss_id,
            'images' => array(),
            'videos' => array()
        );
        if(in_array($content->post_type, [POST_TYPE_PHOTO, POST_TYPE_VIDEO]))
        {
            $this->load->library('myaws');
        }
        if($content->post_type == POST_TYPE_PHOTO)
        {
            foreach (json_decode($content->attachments) as $file_name)
            {
                $post['images'][] = array(
                    'key' => $file_name,
                    'size' => 22,
                    'type' => get_mime($file_name),
                    'preview' => $this->myaws->get_s3_obj_url(getenv('AWS_IMG_BUCKET'), "user-$this->user_id/$file_name"),
                    'uploaded' => true,
                    'percent' => 0,
                    'orign' => true
                );
            }
        }
        if($content->post_type == POST_TYPE_VIDEO)
        {
            foreach (json_decode($content->attachments) as $file_name)
            {
                $post['videos'][] = array(
                    'key' => $file_name,
                    'size' => 22,
                    'type' => get_mime($file_name),
                    'object_url' => $this->myaws->get_s3_obj_url(getenv('AWS_VIDEO_BUCKET'), "user-$this->user_id/$file_name"),
                    'uploaded' => true,
                    'orign' => true
                );
            }
        }
        $this->response($post);
    }

    //update content from edit content page
    function update_post($content_id = 0)
    {
        if(!$content_id || !is_numeric($content_id))
            show_404();
        $content = $this->content_model->get_record($content_id);
        if(!isset($content->content_id) || $content->user_id != $this->user_id)
            show_404();
        $old_attachments = json_decode($content->attachments);
        $to_be_deleted = [];
        $post = $this->post('post');
        foreach ($old_attachments as $file_name)
        {
            if(!empty($file_name) && !in_array($file_name, $post['attachments']))
                $to_be_deleted[] = "user-$this->user_id/$file_name";
        }
        if(!empty($to_be_deleted))
        {
            $bucket = $content->post_type == POST_TYPE_PHOTO ? getenv('AWS_IMG_BUCKET') : getenv('AWS_VIDEO_BUCKET');
            $this->load->library('myaws');
            $this->myaws->delete_multi_objects($bucket, $to_be_deleted);
        }
        $this->load->model('accounts_model');
        $user_pages = $this->accounts_model->get_all($this->user_id);
        $allowed_page_ids = [];
        foreach($user_pages as $page)
            $allowed_page_ids[] = $page->account_id;
        foreach($post['pages'] as $page_id)
        {
            if(!in_array($page_id, $allowed_page_ids))
                die('invalid Request');
        }
        $category_changed = $this->content_model->update($content_id, $post, $content);

        $content = $this->content_model->get_record($content_id);
        
        $this->load->model('posts_model');
        $this->posts_model->delete_content_posts($this->user_id, $content_id, POST_STATUS_PENDING);//delete pending posts
        $this->posts_model->create_manual_posts($content);
        
        if($content->moderation_status == CONTENT_MODERATION_APPROVED && $category_changed) //rebuild whole queue if category of content is changed
            rebuild_queue($this->user_id);
        else if($content->moderation_status == CONTENT_MODERATION_APPROVED)
            rebuild_category_queue($this->user_id, $content->use_once ? CATEGORY_USE_ONCE : $content->category_id, REBUILD_RANDOM_YES);
        
        $this->session->set_flashdata('alert', get_alert_html("Content updated successfully", ALERT_TYPE_SUCCESS));
        $response['message'] = site_url('content');
        $response['type'] = AJAX_RESPONSE_TYPE_REDIRECT;
        $this->response($response);
    }

    //Library
    function list_post()
    {
        $filters = $this->post('filters');
        $items = $this->content_model->get_list($this->user_id, $filters);
        $count = $this->content_model->get_list($this->user_id, $filters, true);
        $this->load->library('myaws');
        foreach ($items as $item)
        {
            $item->accounts = json_decode($item->accounts);
            $item->attachments = json_decode($item->attachments);
            if(!empty($item->attachments))
            {
                $bucket = $item->post_type == POST_TYPE_VIDEO ? getenv('AWS_VIDEO_BUCKET') : getenv('AWS_IMG_BUCKET');
                foreach($item->attachments as $key => $file_name)
                    $item->attachments[$key] = $this->myaws->get_s3_obj_url($bucket, "user-$this->user_id/$file_name");
            }
        }
        $response['content'] = $items;
        $response['total'] = $count;
        $this->response($response);
    }

    //Content List for Bulk Edit Page
    function bulk_post()
    {
        $filters = $this->post('filters');
        $items = $this->content_model->get_bulk_list($this->user_id, $filters);
        $count = $this->content_model->get_bulk_list($this->user_id, $filters, true);
        foreach ($items as $item)
            $item->accounts = json_decode($item->accounts);
        $response['content'] = $items;
        $response['total'] = $count;
        $this->response($response);
    }

    //Bulk Approve form library
    function approve_post()
    {
        $filters = $this->post('filters');
        $total = $this->post('total');
        $items = $this->content_model->get_scheduled_list($this->user_id, $filters); //false=>no count, true=>all records
        $this->content_model->change_status_bulk($this->user_id, CONTENT_MODERATION_APPROVED, $filters);
        $this->load->model('posts_model');
        foreach ($items as $content)
        {
            $content->moderation_status = CONTENT_MODERATION_APPROVED;
            $this->posts_model->create_manual_posts($content); //manual scheduling
        }
        $num_of_categories = $this->content_model->add_bulk_content_to_queue($this->user_id, $items); // set posts order in queue
        
        if($num_of_categories > 1) // content added in multiple categories
            rebuild_queue($this->user_id);
        else if(isset($items[0])) // content added in single category
            rebuild_category_queue($this->user_id, $items[0]->use_once ? CATEGORY_USE_ONCE : $items[0]->category_id, REBUILD_RANDOM_YES);

        if(count($items))
            rebuild_queue($this->user_id);
        $this->session->set_flashdata('alert', get_alert_html("$total items approved successfully", ALERT_TYPE_SUCCESS));
        $this->response('true');
    }

    //bulk Decline from Library
    function decline_post()
    {
        $filters = $this->post('filters');
        $total = $this->post('total');
        $items = $this->content_model->change_status_bulk($this->user_id, CONTENT_MODERATION_DECLINED, $filters);
        $this->session->set_flashdata('alert', get_alert_html("$total items declined successfully", ALERT_TYPE_SUCCESS));
        $this->response('true');
    }

    //Bulk Delete from library for declined content
    function delete_post()
    {
        $filters = $this->post('filters');
        $total = $this->post('total');
        $items = $this->content_model->delete_bulk($this->user_id, $filters);
        $this->session->set_flashdata('alert', get_alert_html("$total items deleted successfully", ALERT_TYPE_SUCCESS));
        $this->response('true');
    }

    //Bulk delete from edit bulk Page
    function delete_bulk_post()
    {
        $content_ids = $this->post('ids');
        $items = $this->content_model->get_content_bulk($this->user_id, $content_ids);
        $images = [];
        $videos = [];
        $categories = [];
        foreach ($items as $item)
        {
            $category_id = $item->use_once == YES ? '-1' : $item->category_id;
            $categories[$category_id] = YES; //just found how many categories content is spreaded over to rebuild entire queue or just one category

            if($item->post_type == POST_TYPE_PHOTO)
            {
                $item->attachments = json_decode($item->attachments);
                $images = array_merge($images, $item->attachments);
            }elseif ($item->post_type == POST_TYPE_VIDEO)
            {
                $item->attachments = json_decode($item->attachments);
                $videos = array_merge($videos, $item->attachments);
            }
        }
        $this->load->library('myaws');
        if(!empty($images))
        {
            $keys = [];
            foreach ($images as $key => $value)
                $keys[] = "user-$this->user_id/$value";
            $this->myaws->delete_multi_objects(getenv('AWS_IMG_BUCKET'), $keys);
        }
        if(!empty($videos))
        {
            $keys = [];
            foreach ($videos as $key => $value)
                $keys[] = "user-$this->user_id/$value";
            $this->myaws->delete_multi_objects(getenv('AWS_VIDEO_BUCKET'), $keys);
        }
        foreach ($items as $item)
            $this->content_model->remove_from_category_queue($item); //reset queue index where items need to be removed
        
        $this->content_model->delete_content_bulk($this->user_id, $content_ids);
        $this->load->model('posts_model');
        $this->posts_model->delete_content_posts_bulk($this->user_id, $content_ids);

        if(count($categories) > 1) // content deleted from multiple categories
            rebuild_queue($this->user_id);
        else if(isset($items[0])) // content deleted from in single category
            rebuild_category_queue($this->user_id, $items[0]->use_once ? CATEGORY_USE_ONCE : $items[0]->category_id, REBUILD_RANDOM_YES);

        rebuild_queue($this->user_id);
        $total = count($content_ids);
        $this->session->set_flashdata('alert', get_alert_html("$total items deleted successfully, Updating Queue...", ALERT_TYPE_SUCCESS));
        $response['url'] = site_url('content');
        $this->response($response);
    }

    //Bulk edit submit
    function update_bulk_post()
    {
        $items = $this->post('items');

        $this->load->model('accounts_model');
        $user_pages = $this->accounts_model->get_all($this->user_id);
        $allowed_page_ids = [];
        foreach($user_pages as $page)
            $allowed_page_ids[] = $page->account_id;
        
        $content_ids = array();
        foreach ($items as $item)
            $content_ids[] = $item['content_id'];

        $records_by_ids = [];
        $items_by_ids = [];

        $records = $this->content_model->get_content_bulk($this->user_id, $content_ids);
        foreach($records as $record)
            $records_by_ids[$record->content_id] = $record;

        for($i=0; $i<count($items); $i++)
        {
            $items[$i]['old_content'] = $records_by_ids[ $items[$i]['content_id'] ];
            $items_by_ids[ $items[$i]['content_id'] ] = $items[$i];
        }

        $category_changed = $this->content_model->update_content_bulk($this->user_id, $items, $allowed_page_ids);
        $total = count($items);

        foreach($records_by_ids as $content_id => $record)
        {
            $record->category_id = $items_by_ids[$content_id]['category_id'];
            $record->use_once = $items_by_ids[$content_id]['use_once'];
            $record->accounts = $items_by_ids[$content_id]['accounts'];
        }
                
        $this->load->model('posts_model');
        foreach ($records_by_ids as $content)
        {
            $this->posts_model->delete_content_posts($this->user_id, $content->content_id, POST_STATUS_PENDING);//delete pending posts
            $this->posts_model->create_manual_posts($content);
        }
        if($category_changed) // if at least one item's category changed
            rebuild_queue($this->user_id);
        else
            rebuild_category_queue($this->user_id, $content->use_once ? CATEGORY_USE_ONCE : $content->category_id, REBUILD_RANDOM_YES);
        
        $this->session->set_flashdata('alert', get_alert_html("$total items updated successfully", ALERT_TYPE_SUCCESS));
        $response['url'] = site_url('content');
        $this->response($response);
    }



}
?>