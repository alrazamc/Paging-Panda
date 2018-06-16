<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Accounts extends REST_Controller {

    var $user_id;
    function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('accounts_model');
    }

    function import_get()
    {
        $access_token = $this->session->userdata('profile_token');
        if(empty($access_token))
        {
             $response = array(
                'type' => AJAX_RESPONSE_TYPE_ERROR,
                'message' => get_alert_html('Something went wrong, Please go back and import pages again', ALERT_TYPE_ERROR)
            );
            $this->response($response);
        }
        
        $before = $this->get('before', TRUE);
        $after = $this->get('after', TRUE);
        
        $this->load->library('myfacebook');
        $this->myfacebook->set_token($access_token);
        
        $fb_response = $this->myfacebook->get_manage_pages($before, $after);
        $nodes = is_object($fb_response) ? $fb_response->asArray() : $fb_response;
             
        if(isset($nodes['error']))
        {
            $response = array(
                'type' => AJAX_RESPONSE_TYPE_ERROR,
                'message' => get_alert_html(get_fb_request_error_string($nodes), ALERT_TYPE_ERROR)
            );
        }else if(empty($nodes))
        {
            $message = "No pages found, Please check the following points <br>
                        1. You allowed our app all the permissions asked, If not, Please go back to accounts menu and Import pages again and grant all the permissions <br>
                        2. You do not manage any page in this Facebook account, Please logout from Facebook and login with different Facebook account then go back to accounts page and import pages again";
            $response = array(
                'type' => AJAX_RESPONSE_TYPE_ERROR,
                'message' => get_alert_html($message, ALERT_TYPE_ERROR)
            );
        }else
        {
            $length = count($nodes);
            for($key = 0; $key < $length; $key++) 
                $nodes[$key]['selected'] = false;
            $response = array('nodes' => array_values($nodes));
            $metaData = $fb_response->getMetaData();
            if(isset($metaData['paging']['next'])) // next page url
                $response['next'] = $fb_response->getNextCursor();
            if(isset($metaData['paging']['previous'])) // previous page url
                $response['previous'] = $fb_response->getPreviousCursor();
        }
        $this->response($response,200);
    }

    function save_post()
    {
        $data = $this->post('data');
        $inserted = 0;    
        $pages = $this->accounts_model->get_all($this->user_id);
        $this->load->model('payments_model');
        $plan = $this->payments_model->get_plan( $this->session->userdata('plan_id') );
        $remain_pages = $plan->page_limit - count($pages) ; 
        $remain_pages = $remain_pages < 0 ? 0 : $remain_pages;
    
        foreach ($data as $node) {
           if($node['selected']){
                if( ( $this->accounts_model->is_page_exist($node['id'], $this->user_id) ) )
                {
                    $this->accounts_model->update_page($node, $this->user_id);
                }else if( $remain_pages > 0 )
                {
                    $this->accounts_model->insert_page($node, $this->user_id);
                    $inserted ++;
                    $remain_pages--;
                }   
           }
        }
        $this->session->set_flashdata('alert', get_alert_html("$inserted new pages added successfully", ALERT_TYPE_SUCCESS));
        $response['type'] = AJAX_RESPONSE_TYPE_REDIRECT;
        $response['message'] = site_url('accounts');
        $this->response($response);
    }

    function all_get()
    {
        $records = $this->accounts_model->get_all_pages($this->user_id);
        $this->response($records,200);
    }

}
?>