<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Import extends REST_Controller {

    var $user_id;
    function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('import_model');
    }

    function find_post()
    {
        $url = $this->post('url');
        if (!filter_var($url, FILTER_VALIDATE_URL))
        { 
          $response['message'] = get_alert_html('Invalid URL, Please enter a RSS feed URL', ALERT_TYPE_ERROR);
          $response['error'] = true;
          $this->response($response);
        }
        $this->load->library('rssparser');
        $this->rssparser->set_feed_url($url);
        $this->rssparser->parse();
        $data = $this->rssparser->getChannelData();
        if($data === FALSE)
        {
            $response['message'] = get_alert_html("Couldn't find the RSS info on this URL. Please make sure its RSS feed url", ALERT_TYPE_ERROR);
            $response['error'] = true;
            $this->response($response);
        }
        $response['feed_title'] = (string) $data['title'];
        $response['success'] = true;
        $this->response($response);
    }

    function add_post()
    {
        $feed = $this->post('feed');
        $this->load->model('accounts_model');
        $user_pages = $this->accounts_model->get_all($this->user_id);
        $allowed_page_ids = [];
        foreach($user_pages as $page)
            $allowed_page_ids[] = $page->account_id;
        foreach($feed['pages'] as $page_id)
        {
            if(!in_array($page_id, $allowed_page_ids))
                die('invalid Request');
        }
        if($this->import_model->is_feed_exist($this->user_id, $feed))
        {
            $response['message'] = get_alert_html('This RSS feed already exists in this category. Please change category or try a different RSS feed', ALERT_TYPE_ERROR);
            $response['error'] = true;
            $this->response($response);
        }
        $this->import_model->create($this->user_id, $feed);
        $this->session->set_flashdata('alert', get_alert_html("RSS feed added successfully", ALERT_TYPE_SUCCESS));
        $response['message'] = site_url('import/feeds');
        $response['success'] = true;
        $this->response($response);
    }

    function update_post($rss_feed_id = 0)
    {
        if(!$rss_feed_id || !is_numeric($rss_feed_id))
            show_404();
        $feed = $this->import_model->get_record($rss_feed_id);
        if(!isset($feed->rss_feed_id) || $feed->user_id != $this->user_id)
            show_404();
        $feed = $this->post('feed');
        $this->load->model('accounts_model');
        $user_pages = $this->accounts_model->get_all($this->user_id);
        $allowed_page_ids = [];
        foreach($user_pages as $page)
            $allowed_page_ids[] = $page->account_id;
        foreach($feed['pages'] as $page_id)
        {
            if(!in_array($page_id, $allowed_page_ids))
                die('invalid Request');
        }
        if($this->import_model->is_feed_exist($this->user_id, $feed, $rss_feed_id))
        {
            $response['message'] = get_alert_html('This RSS feed already exists in this category. Please change category or try a different RSS feed', ALERT_TYPE_ERROR);
            $response['error'] = true;
            $this->response($response);
        }
        $this->import_model->update($rss_feed_id, $feed);
        $this->session->set_flashdata('alert', get_alert_html("RSS feed updated successfully", ALERT_TYPE_SUCCESS));
        $response['message'] = site_url('import/feeds');
        $response['success'] = true;
        $this->response($response);
    }

    function csv_bulk_post()
    {
        $items = $this->post('items');
        $total = count($items);
        $this->load->model('accounts_model');
        $user_pages = $this->accounts_model->get_all($this->user_id);
        $allowed_page_ids = [];
        foreach($user_pages as $page)
            $allowed_page_ids[] = $page->account_id;

        $num_of_categories = $this->import_model->save_csv_content($this->user_id, $items, $allowed_page_ids);
        if($total && $num_of_categories > 1) // content added in multiple categories
            rebuild_queue($this->user_id);
        else if($total) // content added in single category
            rebuild_category_queue($this->user_id, $items[0]['use_once'] ? CATEGORY_USE_ONCE : $items[0]['category_id'], REBUILD_RANDOM_YES);
        $this->session->set_flashdata('alert', get_alert_html("$total items added successfully", ALERT_TYPE_SUCCESS));
        $response['url'] = site_url('content');
        $this->response($response);
    }


}
?>