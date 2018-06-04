<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Posts Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Posts
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Posts extends CI_Controller {

    var $user_id;
    public function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
    }


    /** default function of the controller */
    public function index()
    {
        $data['page_title'] = 'Posts';

        $this->load->model('categories_model');
        $this->load->model('accounts_model');
        $this->load->model('import_model');

        $data['pages'] = $this->accounts_model->get_all($this->user_id);
        $data['categories'] = $this->categories_model->get_list($this->user_id);
        $data['rss_feeds'] = $this->import_model->get_feeds($this->user_id);

        $data['view'] = 'posts/posts';
        $this->load->view('template', $data);
    }

    /**
    * delete post
    */
    public function delete($post_id = 0)
    {
        if(!$post_id || !is_numeric($post_id))
            show_404();
        $this->load->model('posts_model');
        $post = $this->posts_model->get_record($post_id);
        if(!isset($post->post_id) || $post->user_id != $this->user_id)
            show_404();
        $this->posts_model->delete($post_id);
        $this->session->set_flashdata('alert', get_alert_html('Post removed successfully', ALERT_TYPE_SUCCESS));
        redirect('posts');
    }

    public function skip($content_id = 0, $is_random = 0)
    {
        if(!$content_id || !is_numeric($content_id))
            show_404();
        $this->load->model('content_model');
        $content = $this->content_model->get_record($content_id);
        if(!isset($content->content_id) || $content->user_id != $this->user_id)
            show_404();
        $this->load->model('posts_model');
        $this->posts_model->skip_post($content, $is_random);
        rebuild_queue($this->user_id);
        $this->session->set_flashdata('alert', get_alert_html('Post skipped successfully, Updating Queue... check in a second', ALERT_TYPE_SUCCESS));
        redirect('posts');
    }

    public function pause()
    {
        $this->load->model('posts_model');
        $this->posts_model->change_queue_status($this->user_id, YES); //Queue Paused => YES
        $this->session->set_flashdata('alert', get_alert_html('Queue paused successfully. Posts will NOT be published', ALERT_TYPE_SUCCESS));
        redirect('posts');
    }

    public function unpause()
    {
        $this->load->model('posts_model');
        $this->posts_model->change_queue_status($this->user_id, NO); //Queue Paused => NO
        rebuild_queue($this->user_id);
        $this->session->set_flashdata('alert', get_alert_html('Queue unpaused successfully. Updating queue... Refresh in a minute. Next Post will be published on scheduled time', ALERT_TYPE_SUCCESS));
        redirect('posts');
    }

}

/* End of file Posts.php */
/* Location: ./application/controllers/Posts.php */
