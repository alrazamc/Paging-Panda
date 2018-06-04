<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Import Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Import
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Import extends CI_Controller {

    var $user_id;
    public function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('import_model');
    }

    public function index()
    {
        redirect('import/feeds');
    }

    /** Feeds List */
    public function feeds()
    {
        $data['page_title'] = 'RSS Feeds';
        $data['feeds'] = $this->import_model->get_feeds($this->user_id);
        $this->load->model('categories_model');
        $categories = $this->categories_model->get_list($this->user_id);
        $cat_by_ids = [];
        foreach ($categories as $category)
            $cat_by_ids[$category->category_id] = $category;
        $data['categories'] = $cat_by_ids;
        $data['view'] = 'import/feeds';
        $this->load->view('template', $data);
    }

    /** CSV file */
    public function csv()
    {
        $data['page_title'] = 'Upload CSV file';
        $this->load->model('accounts_model');
        $this->load->model('categories_model');
        $data['pages'] = $this->accounts_model->get_all($this->user_id, YES); // pages only with post permissions
        $data['categories'] = $this->categories_model->get_list($this->user_id);
        $data['view'] = 'import/import_from_csv';
        $this->load->view('template', $data);
    }

    /**
    * add category
    */
    public function add()
    {
        $data['page_title'] = 'Add RSS Feed';
        $this->load->model('accounts_model');
        $this->load->model('categories_model');
        $data['pages'] = $this->accounts_model->get_all($this->user_id, YES); // pages only with post permissions
        $data['categories'] = $this->categories_model->get_list($this->user_id);
        $data['view'] = 'import/add_feed';
        $this->load->view('template', $data);
    }

    /**
    * edit Import
    */
    public function edit($feed_id = 0)
    {
        $feed = $this->_verify_feed_id($feed_id);
        $data['page_title'] = 'Edit RSS Feed';
        $data['feed'] = $feed;
        $this->load->model('accounts_model');
        $this->load->model('categories_model');
        $data['pages'] = $this->accounts_model->get_all($this->user_id, YES); // pages only with post permissions
        $data['categories'] = $this->categories_model->get_list($this->user_id);
        $data['view'] = 'import/edit_feed';
        $this->load->view('template', $data);
    }

    /**
    * delete Import
    */
    public function delete($feed_id = 0)
    {
        $feed = $this->_verify_feed_id($feed_id);
        $this->import_model->delete($feed_id, $this->user_id);
        $this->session->set_flashdata('alert', get_alert_html("RSS feed deleted successfully", ALERT_TYPE_SUCCESS));
        redirect("import/feeds");
    }

    /**
    * delete Import
    */
    public function refresh($feed_id = 0)
    {
        $feed = $this->_verify_feed_id($feed_id);
        read_rss_feed($feed->rss_feed_id); //background Job
        $this->session->set_flashdata('alert', get_alert_html("Reading RSS feed.... Please check library in a minute for updates", ALERT_TYPE_SUCCESS));
        redirect("import/feeds");
    }


    private function _verify_feed_id($rss_feed_id = 0)
    {
        if(!$rss_feed_id || !is_numeric($rss_feed_id))
            show_404();
        $feed = $this->import_model->get_record($rss_feed_id);
        if(!isset($feed->rss_feed_id) || $feed->user_id != $this->user_id)
            show_404();
        $feed->accounts = json_decode($feed->accounts);
        return $feed;
    }

}

/* End of file Import.php */
/* Location: ./application/controllers/Import.php */
