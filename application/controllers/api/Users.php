<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Users extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model("users_model");
    }

    function subscribe_post()
    {
    	$user_id = $this->users_model->subscribe( $this->post('email') );
        if($user_id)
            mailchimp($user_id);
		$this->response('true',200);
    }
}
?>