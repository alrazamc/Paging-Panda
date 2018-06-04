<?php
class AppLoader
{
    var $ci;
    function initialize()
    {
        $this->ci =& get_instance();
        $this->check_user_status();
        $this->remember_me();
        $this->set_global_alerts();
    }

    function check_user_status()
    {
        if($this->ci->session->userdata('user_id'))
        {
            $this->ci->db->select('*')->from("users");
            $this->ci->db->where('user_id', $this->ci->session->userdata('user_id'));
            $user = $this->ci->db->get()->row();
            if(!isset($user->user_id))
            {
                $this->ci->session->sess_destroy();
                delete_cookie('rememberme');
                redirect('users/login');
            }
            $this->ci->session->set_userdata('user_login', TRUE);
            $this->ci->session->set_userdata('user_id', $user->user_id);
            $this->ci->session->set_userdata('first_name', $user->first_name);
            $this->ci->session->set_userdata('last_name', $user->last_name);
            $this->ci->session->set_userdata('time_zone', $user->time_zone ? $user->time_zone : 'UTC');
            $this->ci->session->set_userdata('email', $user->email);

            $this->ci->session->set_userdata('general_pointer', $user->general_pointer);
            $this->ci->session->set_userdata('use_once_pointer', $user->use_once_pointer);
            $this->ci->session->set_userdata('random_pointer', $user->random_pointer);
            $this->ci->session->set_userdata('queue_paused', $user->queue_paused);
            $this->ci->session->set_userdata('accounts_alert', $user->accounts_alert);


            $this->ci->db->where('plan_id', $user->plan_id);
            $plan = $this->ci->db->get('plans')->row();
            $this->ci->session->set_userdata('plan_id', $plan->plan_id);
            $this->ci->session->set_userdata('plan_name', $plan->name);
            $this->ci->session->set_userdata('page_limit', $plan->page_limit);
        }
    }

    function remember_me()
    {
        $this->ci =& get_instance();
        if(!$this->ci->session->userdata('user_id')) //session not set
        {
            $email = get_cookie('rememberme');
            if(is_null($email) || empty($email)) return false;
            $this->ci->load->library('encryption');
            $email = $this->ci->encryption->decrypt($email);
            $this->ci->db->select('*')->from("users");
            $this->ci->db->where('email', $email);
            $user = $this->ci->db->get()->row();
            if(!isset($user->user_id))
            {
                return false;
            }
            $this->ci->session->set_userdata('user_login', TRUE);
            $this->ci->session->set_userdata('user_id', $user->user_id);
            $this->ci->session->set_userdata('first_name', $user->first_name);
            $this->ci->session->set_userdata('last_name', $user->last_name);
            $this->ci->session->set_userdata('time_zone', $user->time_zone ? $user->time_zone : 'UTC');
            $this->ci->session->set_userdata('email', $user->email);

            $this->ci->session->set_userdata('general_pointer', $user->general_pointer);
            $this->ci->session->set_userdata('use_once_pointer', $user->use_once_pointer);
            $this->ci->session->set_userdata('random_pointer', $user->random_pointer);
            $this->ci->session->set_userdata('queue_paused', $user->queue_paused);
            $this->ci->session->set_userdata('accounts_alert', $user->accounts_alert);

            $this->ci->db->where('plan_id', $user->plan_id);
            $plan = $this->ci->db->get('plans')->row();
            $this->ci->session->set_userdata('plan_id', $plan->plan_id);
            $this->ci->session->set_userdata('plan_name', $plan->name);
            $this->ci->session->set_userdata('page_limit', $plan->page_limit);
        }
    }


    function set_global_alerts()
    {
        $data = array();
        $global_alerts = "";
        if($this->ci->session->userdata('queue_paused'))
        {
            $text = 'Queue is paused and no posts will be published until you unpause it in <a class="text-success font-weight-500" href="'.site_url('posts').'">Posts</a> section';
            $global_alerts .= get_alert_html($text, ALERT_TYPE_INFO, NO); //NO=>NO dissmiss option
        }

        if($this->ci->session->userdata('accounts_alert'))
        {
            $text = 'Some of your pages might need reconnection with our app. Please head to the <a class="text-success font-weight-500" href="'.site_url('accounts').'">Pages</a> section for details';
            $global_alerts .= get_alert_html($text, ALERT_TYPE_INFO, NO); //NO=>NO dissmiss option
        }

        if(!empty($global_alerts))
            $data['global_alerts'] = $global_alerts;
        $this->ci->load->vars($data);
    }
}
