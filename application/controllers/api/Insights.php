<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Insights extends REST_Controller {

    var $user_id;
    function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('insights_model');
    }

    function metrics_get()
    {
        $metrics = $this->insights_model->get_all_metrics();
        $this->response($metrics);
    }

    function fav_metrics_get()
    {
        $metrics = $this->insights_model->get_favorite_metric_details($this->user_id);
        $this->response($metrics);
    }

    function save_post()
    {
        $metrics = $this->post('metrics');
        $this->insights_model->update_favorite_metrics($this->user_id, $metrics);
        $response['type'] = AJAX_RESPONSE_TYPE_SUCCESS;
        $response['message'] = get_alert_html("Favorite metrics saved successfully", ALERT_TYPE_SUCCESS);
        $this->response($response);
    }

}
?>