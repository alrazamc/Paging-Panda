<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Insights Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Insights
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Insights extends CI_Controller {

    var $user_id;
    public function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('insights_model');
    }


    /** default function of the controller */
    public function index()
    {
        $this->favorites();
    }


    /**
    * Show favorite metrics
    */
    public function favorites()
    {
        $data['page_title'] = 'Insights';
        $data['view'] = 'insights/favorites';
        $data['favorites'] = $this->insights_model->get_favorite_metric_details($this->user_id);
        $this->load->model('accounts_model');
        $data['accounts'] = $this->accounts_model->get_all_pages($this->user_id);
        $this->load->view('template', $data);
    }

    /**
    * show All Metrics
    */
    public function all()
    {
        $data['page_title'] = 'Insights';
        $data['view'] = 'insights/all';
        $data['metrics'] = $this->insights_model->get_all_metrics();
        $this->load->model('accounts_model');
        $data['accounts'] = $this->accounts_model->get_all_pages($this->user_id);
        $this->load->view('template', $data);
    }


    /**
    * update favorite metrics
    */
    public function update_favorites($page_id = 0)
    {
        $data['page_title'] = 'Insights';
        $data['view'] = 'insights/update_favorites';
        $data['favorites'] = $this->insights_model->get_favorite_metrics($this->user_id);
        $data['metrics'] = $this->insights_model->get_all_metrics();
        $this->load->view('template', $data);
    }



}

/* End of file Insights.php */
/* Location: ./application/controllers/Insights.php */
