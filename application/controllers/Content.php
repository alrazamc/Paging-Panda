<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Content Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Content
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Content extends CI_Controller {

    var $user_id;
    public function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('content_model');
        $this->load->model('accounts_model');
        $this->load->model('categories_model');
    }


    /** default function of the controller */
    public function index()
    {
        $data['page_title'] = 'Library';
        $counts = $this->content_model->get_moderation_status_counts($this->user_id);
        $data['totals'][CONTENT_MODERATION_PENDING] = 0;
        $data['totals'][CONTENT_MODERATION_DECLINED] = 0;
        foreach($counts as $record)
            $data['totals'][$record->moderation_status] = $record->total;
        $data['pages'] = $this->accounts_model->get_all($this->user_id);
        $data['categories'] = $this->categories_model->get_list($this->user_id);
        $this->load->model('import_model');
        $data['rss_feeds'] = $this->import_model->get_feeds($this->user_id);
        $data['view'] = 'content/content';
        $this->load->view('template', $data);
    }

    /** Bulk Edit */
    public function bulk_edit()
    {
        $data['page_title'] = 'Bulk Edit';
        $data['pages'] = $this->accounts_model->get_all($this->user_id);
        $data['categories'] = $this->categories_model->get_list($this->user_id);
        $this->load->model('import_model');
        $data['rss_feeds'] = $this->import_model->get_feeds($this->user_id);
        $data['view'] = 'content/bulk_edit';
        $this->load->view('template', $data);
    }

    /**
    * add category
    */
    public function add()
    {
        $data['page_title'] = 'Add Content';
        $data['pages'] = $this->accounts_model->get_all($this->user_id, YES); // pages only with post permissions
        $data['categories'] = $this->categories_model->get_list($this->user_id);
        $this->load->config('time_zones');
        date_default_timezone_set( $this->session->userdata('time_zone') );
        $data['view'] = 'content/add_content';
        $this->load->view('template', $data);
    }

    /**
    * edit content
    */
    public function edit($content_id = 0)
    {
        $content = $this->_verify_content_id($content_id);
        $data['page_title'] = 'Edit Content';
        $data['content'] = $content;
        $data['pages'] = $this->accounts_model->get_all($this->user_id, YES); // pages only with post permissions
        $data['categories'] = $this->categories_model->get_list($this->user_id);
        $this->load->config('time_zones');
        date_default_timezone_set( $this->session->userdata('time_zone') );
        $data['view'] = 'content/edit_content';
        $this->load->view('template', $data);
    }

    /**
    * delete content
    */
    public function delete($content_id = 0)
    {
        $content = $this->_verify_content_id($content_id);
        if(in_array($content->post_type, [POST_TYPE_PHOTO, POST_TYPE_VIDEO]))
        {
            $to_be_deleted = [];
            foreach (json_decode($content->attachments) as $file_name)
            {
                if(empty($file_name)) continue;
                $to_be_deleted[] = "user-$this->user_id/$file_name";
            }
            if(!empty($to_be_deleted))
            {
                $bucket = $content->post_type == POST_TYPE_PHOTO ? getenv('AWS_IMG_BUCKET') : getenv('AWS_VIDEO_BUCKET');
                $this->load->library('myaws');
                $this->myaws->delete_multi_objects($bucket, $to_be_deleted);
            }
        }
        if($content->moderation_status == CONTENT_MODERATION_APPROVED)
        {
            $this->load->model('posts_model');
            $this->posts_model->delete_content_posts($this->user_id, $content_id);
            $this->content_model->remove_from_category_queue($content);
        }
        $this->content_model->delete($content);
        if($content->moderation_status == CONTENT_MODERATION_APPROVED)
            rebuild_category_queue($this->user_id, $content->use_once ? CATEGORY_USE_ONCE : $content->category_id, REBUILD_RANDOM_YES);
        $this->session->set_flashdata('alert', get_alert_html("Content deleted successfully", ALERT_TYPE_SUCCESS));
        redirect("content");
    }

    /** approve all pending  posts */
    public function approve($content_id = 0)
    {
        $content = $this->_verify_content_id($content_id);
        $this->content_model->change_status($content, CONTENT_MODERATION_APPROVED);
        $this->load->model('posts_model');
        $content->moderation_status = CONTENT_MODERATION_APPROVED;
        $is_scheduled = $this->posts_model->create_manual_posts($content);
        if(!$is_scheduled)
            rebuild_category_queue($this->user_id, $content->use_once ? CATEGORY_USE_ONCE : $content->category_id, REBUILD_RANDOM_YES);
        $this->session->set_flashdata('alert', get_alert_html("Content approved successfully, Updating Queue...", ALERT_TYPE_SUCCESS));
        redirect("content");
    }

    /** decline all pending  posts */
    public function decline($content_id = 0)
    {
        $content = $this->_verify_content_id($content_id);
        $this->content_model->change_status($content, CONTENT_MODERATION_DECLINED);
        $this->session->set_flashdata('alert', get_alert_html("Content declined successfully", ALERT_TYPE_SUCCESS));
        redirect("content");
    }

    private function _verify_content_id($content_id = 0)
    {
        if(!$content_id || !is_numeric($content_id))
            show_404();
        $content = $this->content_model->get_record($content_id);
        if(!isset($content->content_id) || $content->user_id != $this->user_id)
            show_404();
        return $content;
    }

}

/* End of file Content.php */
/* Location: ./application/controllers/Content.php */
