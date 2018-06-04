<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Categories Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Categories
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Categories extends CI_Controller {

    var $user_id;
    public function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('categories_model');
    }


    /** default function of the controller */
    public function index()
    {
        $data['page_title'] = 'Categories';
        $categories = $this->categories_model->get_list($this->user_id);
        $category_counts = $this->categories_model->get_content_count($this->user_id);
        $content_counts = [];
        foreach($category_counts as $count)
            $content_counts[$count->category_id][$count->use_once] = $count->content_count;
        foreach($categories as $category)
            $category->content_count = isset($content_counts[$category->category_id][0]) ? $content_counts[$category->category_id][0] : 0;
        $data['categories'] = $categories;
        $data['general_count'] = isset($content_counts[0][0]) ? $content_counts[0][0] : 0;
        $data['use_once_count'] = isset($content_counts[0][1]) ? $content_counts[0][1] : 0;
        $data['view'] = 'categories/all_categories';
        $this->load->view('template', $data);
    }


    /**
    * add category
    */
    public function add()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('category', 'Category name', 'trim|required|max_length[256]');
        if(!$this->form_validation->run())
        {
            $data['page_title'] = 'Add Category';
            $data['view'] = 'categories/add_category';
            $this->load->view('template', $data);
        }else
        {
            $this->categories_model->insert($this->user_id);
            $this->session->set_flashdata('alert', get_alert_html('Category added successfully', ALERT_TYPE_SUCCESS));
            redirect('categories');
        }
    }

    /**
    * edit category
    */
    public function edit($category_id = 0)
    {
        if(!$category_id || !is_numeric($category_id))
            show_404();
        $category = $this->categories_model->get_record($category_id);
        if(!isset($category->category_id) || $category->user_id != $this->user_id)
            show_404();
        $this->load->library('form_validation');
        $this->form_validation->set_rules('category', 'Category name', 'trim|required|max_length[256]');
        if(!$this->form_validation->run())
        {
            $data['page_title'] = 'Edit Category';
            $data['view'] = 'categories/edit_category';
            $data['category'] = $category;
            $this->load->view('template', $data);
        }else
        {
            $this->categories_model->update($category_id);
            $new_include_in_random = $this->input->post('include') ? YES : NO;
            if($category->include_in_random != $new_include_in_random)
                rebuild_random_queue($this->user_id);
            $this->session->set_flashdata('alert', get_alert_html('Category updated successfully', ALERT_TYPE_SUCCESS));
            redirect('categories');
        }
    }

    /**
    * delete category
    */
    public function delete($category_id = 0)
    {
        if(!$category_id || !is_numeric($category_id))
            show_404();
        $category = $this->categories_model->get_record($category_id);
        if(!isset($category->category_id) || $category->user_id != $this->user_id)
            show_404();
        $this->categories_model->delete($category_id, $this->user_id);
        delete_s3_media($this->user_id, $category->category_id); //background process, delete category cotent after that and rebuild random queue
        $this->session->set_flashdata('alert', get_alert_html('Category deleted successfully', ALERT_TYPE_SUCCESS));
        redirect('categories');
    }

    /**
    * Shuffle category contents
    */
    public function shuffle($category_id = 0)
    {
        if(!is_numeric($category_id) || $category_id < CATEGORY_USE_ONCE)
            show_404();
        if($category_id > 0)
        {
            $category = $this->categories_model->get_record($category_id);
            if(!isset($category->category_id) || $category->user_id != $this->user_id)
                show_404();
        }
        $this->categories_model->shuffle($category_id, $this->user_id);
        rebuild_category_queue($this->user_id, $category_id);
        $this->session->set_flashdata('alert', get_alert_html('Content shuffled successfully, Updating Queue...', ALERT_TYPE_SUCCESS));
        redirect('categories');
    }

    /**
    * Shuffle category contents
    */
    public function reset($category_id = 0)
    {
        if(!is_numeric($category_id) || $category_id < CATEGORY_USE_ONCE)
            show_404();
        if($category_id > 0)
        {
            $category = $this->categories_model->get_record($category_id);
            if(!isset($category->category_id) || $category->user_id != $this->user_id)
                show_404();
        }
        $this->categories_model->reset($category_id, $this->user_id);
        rebuild_category_queue($this->user_id, $category_id);
        $this->session->set_flashdata('alert', get_alert_html('Content order reset successfully. Content recently added will appear first in the queue. Updating Queue...', ALERT_TYPE_SUCCESS));
        redirect('categories');
    }

    public function test($category_id = 0)
    {

        $current_pointer = 0;
        if($category_id == CATEGORY_RANDOM)
        {
            $current_pointer = $this->session->userdata('random_pointer');
            if(empty($current_pointer))
            {
                $this->db->where('user_id', $this->user_id);
                $this->db->where('moderation_status', CONTENT_MODERATION_APPROVED);
                $this->db->order_by('random_order ASC');
                $this->db->limit(1);
                $current_pointer = $this->db->get('contents')->row()->random_order;
            }
        }
        else if($category_id == CATEGORY_USE_ONCE)
            $current_pointer = $this->session->userdata('use_once_pointer');
        else if($category_id == CATEGORY_GENERAL)
            $current_pointer = $this->session->userdata('general_pointer');
        else
            $current_pointer = $this->db->get_where('categories', array('category_id' => $category_id))->row()->queue_pointer;

        $this->db->select('content_id, post_order, message, link_title, random_order')->from('contents');
        $this->db->where('user_id', $this->user_id);
        $this->db->where('moderation_status', CONTENT_MODERATION_APPROVED);
        if($category_id == -1)
            $this->db->where('use_once', YES);
        else if($category_id >= 0)
        {
            $this->db->where('use_once', NO);
            $this->db->where('category_id', $category_id);
        }
        if($category_id == CATEGORY_RANDOM)
            $this->db->order_by('random_order ASC');
        else
            $this->db->order_by('post_order ASC');
        $contents = $this->db->get()->result();
        $orders = array();
        foreach($contents as $item)
        {
            if($category_id == CATEGORY_RANDOM)
                echo "$item->random_order == ";
            else
                echo "$item->post_order == ";
            echo "$item->content_id || ";
            if(!empty($item->link_title))
                echo substr($item->link_title, 0, 100);
            else if(!empty($item->message))
                echo substr($item->message, 0, 100);
            if(($category_id == CATEGORY_RANDOM && $current_pointer == $item->random_order) || $current_pointer == $item->post_order)
                echo " <===========";
            echo "<br>";
        }
    }

}

/* End of file Categories.php */
/* Location: ./application/controllers/Categories.php */
