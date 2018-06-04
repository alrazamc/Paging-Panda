<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Schedule extends REST_Controller {

    var $user_id;
    function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('schedule_model');
    }

    function save_post()
    {
        $slot = $this->post('slot');
        $this->load->model('accounts_model');
        $user_pages = $this->accounts_model->get_all($this->user_id);
        $allowed_page_ids = [];
        foreach($user_pages as $page)
            $allowed_page_ids[] = $page->account_id;
        foreach($slot['accounts'] as $page_id)
        {
            if(!in_array($page_id, $allowed_page_ids))
                die('invalid Request');
        }
        
        if(isset($slot['schedule_id']))
        {
            $db_slot = $this->schedule_model->get_record($slot['schedule_id']);
            if($db_slot->user_id != $this->user_id)
                die('invalid Request');
            $this->schedule_model->update($slot['schedule_id'], $slot);
            if($db_slot->use_random != $slot['use_random'] || $db_slot->use_once != $slot['use_once'] || $db_slot->category_id != $slot['category_id']) //category of slot changed
                rebuild_queue($this->user_id);
            else // slot day, timing or pages changed
            {
                if($slot['use_random'] == YES)
                    rebuild_random_queue($this->user_id);
                else 
                    rebuild_category_queue($this->user_id, $slot['use_once'] ? CATEGORY_USE_ONCE : $slot['category_id']);
            }
            $this->response('true');
        }else
        {
            $schedule_id = $this->schedule_model->insert($this->user_id, $slot);

            if($slot['use_random'] == YES)
                rebuild_random_queue($this->user_id);
            else 
                rebuild_category_queue($this->user_id, $slot['use_once'] ? CATEGORY_USE_ONCE : $slot['category_id']);

            $this->response($schedule_id);
        }
    }

    function remove_post()
    {
        $schedule_id = $this->post('schedule_id');
        if(!$schedule_id || !is_numeric($schedule_id))
            show_404();
        $slot = $this->schedule_model->get_record($schedule_id);
        if(!isset($slot->schedule_id) || $slot->user_id != $this->user_id)
            show_404();
        $this->schedule_model->delete($schedule_id);
        if($slot->use_random == YES)
            rebuild_random_queue($this->user_id);
        else 
            rebuild_category_queue($this->user_id, $slot->use_once ? CATEGORY_USE_ONCE : $slot->category_id);
        $this->response('true');
    }

    

}
?>