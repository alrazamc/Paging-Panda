<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Posts extends REST_Controller {

    var $user_id;
    function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('posts_model');
    }

    public function list_post()
    {
        $filters = $this->post('filters');
        $items = $this->posts_model->get_list($this->user_id, $filters);
        $count = $this->posts_model->get_list($this->user_id, $filters, true);
        $this->load->library('aws/Myaws');
        foreach ($items as $item)
        {
            $item->attachments = json_decode($item->attachments);
            if(!empty($item->attachments))
            {
                $bucket = $item->post_type == POST_TYPE_VIDEO ? getenv('AWS_VIDEO_BUCKET') : getenv('AWS_IMG_BUCKET');
                foreach($item->attachments as $key => $file_name)
                    $item->attachments[$key] = $this->myaws->get_s3_obj_url($bucket, "user-$this->user_id/$file_name");
            }
        }
        $response['posts'] = $items;
        $response['total'] = $count;
        $this->response($response);
    }


    public function skip_get($content_id = 0, $is_random = 0)
    {
        if(!$content_id || !is_numeric($content_id))
            show_404();
        $this->load->model('content_model');
        $content = $this->content_model->get_record($content_id);
        if(!isset($content->content_id) || $content->user_id != $this->user_id)
            show_404();
        $this->load->model('posts_model');
        $this->posts_model->skip_post($content, $is_random);
        if($is_random)
            rebuild_random_queue($this->user_id);
        else
            rebuild_category_queue($this->user_id, $content->use_once ? CATEGORY_USE_ONCE : $content->category_id);
        $this->response('true');
    }

    

}
?>