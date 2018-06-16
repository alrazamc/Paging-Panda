<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Ins Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Ins
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Ins extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('payments_model');
        $this->load->model('users_model');
    }


    public function handle()
    {
        $this->load->library('myaws');
        $this->myaws->send_email('alrazamc@gmail.com', $_POST['message_type'], get_fb_response_as_html_table($_POST));
    }

}

/* End of file Ins.php */
/* Location: ./application/controllers/Ins.php */