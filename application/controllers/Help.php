<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Help Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Help
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Help extends CI_Controller {

    var $user_id;
    public function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('help_model');
    }


    /** default function of the controller */
    public function index()
    {
        $data['videos'] = $this->help_model->get_all();
        $data['page_title'] = 'Help';
        $data['view'] = 'help/videos';
        $this->load->view('template', $data);
    }


}

/* End of file Help.php */
/* Location: ./application/controllers/Help.php */
