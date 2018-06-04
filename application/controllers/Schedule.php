<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Schedule Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Schedule
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Schedule extends CI_Controller {

    var $user_id;
    public function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('categories_model');
        $this->load->model('accounts_model');
        $this->load->model('schedule_model');
    }


    /** default function of the controller */
    public function index()
    {
        $data['page_title'] = 'Schedule';
        $data['pages'] = $this->accounts_model->get_all($this->user_id);
        $data['categories'] = $this->categories_model->get_list($this->user_id);
        $schedules = $this->schedule_model->get_list($this->user_id);
        foreach($schedules as $item)
            $item->accounts = json_decode($item->accounts);
        $data['schedules'] = $schedules;
        $this->load->config('time_zones');
        date_default_timezone_set( $this->session->userdata('time_zone') );
        $data['view'] = 'schedule/calender';
        $this->load->view('template', $data);
    }
}

/* End of file Schedule.php */
/* Location: ./application/controllers/Schedule.php */
