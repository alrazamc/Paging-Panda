<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Frontend Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Frontend
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Frontend extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
    }


    /** Landing Page */
    public function index()
    {
        if($this->session->userdata('user_login') === TRUE )
            redirect('content');
        $this->load->model('payments_model');
        $data['page_title'] = getenv('SITE_NAME') .' | Scheduling and Analytics Tool for Facebook Pages';
        $data['view'] = "home";
        $data['plans'] = $this->payments_model->get_all_plans();
        $this->load->view('frontend/front_template', $data);
    }

    //Contact Us
    public function contact()
    {
        $data['page_title'] = 'Contact Us | '.getenv('SITE_NAME');
        $data['view'] = "contact";
        $this->load->view('frontend/front_template', $data);
    }

    //privacy policy
    public function privacy()
    {
        $data['page_title'] = 'Privacy Policy | '.getenv('SITE_NAME');
        $data['view'] = "privacy";
        $this->load->view('frontend/front_template', $data);
    }

    //Terms and conditions
    public function terms()
    {
        $data['page_title'] = 'Terms & Conditions | '.getenv('SITE_NAME');
        $data['view'] = "terms";
        $this->load->view('frontend/front_template', $data);
    }


}

/* End of file Frontend.php */
/* Location: ./application/controllers/Frontend.php */
