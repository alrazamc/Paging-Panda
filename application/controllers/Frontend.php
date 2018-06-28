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
        $data['page_title'] = getenv("SITE_NAME");
        $this->load->model('payments_model');
        $data['view'] = "home";
        $data['plans'] = $this->payments_model->get_all_plans();
        $this->load->view('frontend/front_template', $data);
    }


}

/* End of file Frontend.php */
/* Location: ./application/controllers/Frontend.php */
