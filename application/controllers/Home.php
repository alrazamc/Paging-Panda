<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Home Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Home
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Home extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        session_check();
    }


    /** default function of the controller */
    public function index()
    {
        $this->dashboard();
    }


    /**
    * Login page for application
    */
    public function dashboard()
    {
        $data['page_title'] = 'Dashboard';
        $data['view'] = 'dashboard';
        $this->load->view('template', $data);
    }


}

/* End of file Home.php */
/* Location: ./application/controllers/Home.php */
