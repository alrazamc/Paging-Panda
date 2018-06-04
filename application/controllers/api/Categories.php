<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Categories extends REST_Controller {

    var $user_id;
    function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('categories_model');
    }

    function add_post()
    {
        $category = $this->post('category');
        if(empty($category))
            die('invalied request');
        $include = $this->post('include');
        $this->categories_model->add_category($this->user_id, $category, $include);
        $categories = $this->categories_model->get_list($this->user_id);
        $this->response($categories);
    }

    

}
?>