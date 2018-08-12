<?php
class AppLoader
{
    var $ci;
    function initialize()
    {
        $this->ci =& get_instance();
        $this->update_session();
        $this->check_account_status();
        $this->set_global_alerts();
    }

    function update_session()
    {
        
        if($this->ci->session->userdata('user_id'))
        {
            $this->ci->db->select('*')->from("users");
            $this->ci->db->where('user_id', $this->ci->session->userdata('user_id'));
            $user = $this->ci->db->get()->row();
            if(!isset($user->user_id))
            {
                $this->ci->session->sess_destroy();
                delete_cookie('remme');
                redirect('users/login');
            }
            $this->ci->session->set_userdata('user_login', TRUE);
            $this->ci->session->set_userdata('user_id', $user->user_id);
            $this->ci->session->set_userdata('first_name', $user->first_name);
            $this->ci->session->set_userdata('last_name', $user->last_name);
            $this->ci->session->set_userdata('time_zone', $user->time_zone ? $user->time_zone : 'UTC');
            $this->ci->session->set_userdata('email', $user->email);
            $this->ci->session->set_userdata('user_status', $user->status);
            $this->ci->session->set_userdata('on_trial', $user->on_trial);
            $this->ci->session->set_userdata('next_due_date', $user->next_due_date);
            $this->ci->session->set_userdata('date_registered', $user->date_registered);

            $this->ci->session->set_userdata('general_pointer', $user->general_pointer);
            $this->ci->session->set_userdata('use_once_pointer', $user->use_once_pointer);
            $this->ci->session->set_userdata('random_pointer', $user->random_pointer);
            $this->ci->session->set_userdata('queue_paused', $user->queue_paused);
            $this->ci->session->set_userdata('accounts_alert', $user->accounts_alert);

            $this->ci->session->set_userdata('plan_id', $user->plan_id);
        }
    }

    function check_account_status()
    {
        if($this->ci->session->userdata('user_id'))
        {
            $status = $this->ci->session->userdata('user_status');
            $rs_controllers = array('insights', 'categories', 'schedule', 'posts', 'content', 'import');//Restricted controllers
            $controller = strtolower($this->ci->router->fetch_class());
            if($status != USER_STATUS_ACTIVE && in_array($controller, $rs_controllers))
                redirect('users/settings');
        }
    }

    function set_global_alerts()
    {
        if($this->ci->input->is_ajax_request()) return;
        $data = array();
        $global_alerts = "";
        $user_status = $this->ci->session->userdata('user_status');
        $on_trial = $this->ci->session->userdata('on_trial');
        $next_due_date = $this->ci->session->userdata('next_due_date');
        $due_date_formatted = date('d M, Y', strtotime($next_due_date));

        if($user_status == USER_STATUS_ACTIVE)
        {
            $alerts = $this->ci->db->select('*')->from('alerts')->where('user_id', 0)->get()->result();
            foreach ($alerts as $alert)
                $global_alerts .= get_alert_html($alert->text, $alert->type, $alert->dismiss);
        }


        if($this->ci->session->userdata('queue_paused') && $user_status == USER_STATUS_ACTIVE )
        {
            $text = 'Queue is paused and no posts will be published until you un-pause it in <a class="text-success font-weight-500" href="'.site_url('posts').'">Posts</a> section';
            $global_alerts .= get_alert_html($text, ALERT_TYPE_INFO, NO); //NO=>NO dissmiss option
        }

        if($this->ci->session->userdata('accounts_alert') && $user_status == USER_STATUS_ACTIVE )
        {
            $text = 'Some of your pages might need re-connection with our app. Please head to the <a class="text-success font-weight-500" href="'.site_url('accounts').'">Pages</a> section for details';
            $global_alerts .= get_alert_html($text, ALERT_TYPE_INFO, NO); //NO=>NO dissmiss option
        }

        // Last day of trial
        if($user_status == USER_STATUS_ACTIVE && $on_trial == YES && strtotime( $this->ci->session->userdata('date_registered')." +29 days" ) < time())
        {
            $text = 'Trial period is about to expire. Please <a class="text-success font-weight-500" href="'.site_url('payments/pay').'">Buy a Plan</a> to continue using '.getenv('SITE_NAME');
            $global_alerts .= get_alert_html($text, ALERT_TYPE_WARNING, NO); //NO=>NO dissmiss option
        }
        //Trial expired, account suspended
        else if($user_status == USER_STATUS_SUSPENDED && $on_trial == YES)
        {
            $text = 'Trial period has expired. Please <a class="text-success font-weight-500" href="'.site_url('payments/pay').'">Buy a Plan</a> to continue using '.getenv('SITE_NAME');
            $global_alerts .= get_alert_html($text, ALERT_TYPE_INFO, NO); //NO=>NO dissmiss option
        }
        //Monthly Billing Failed, on wait period for 3 days
        else if($user_status == USER_STATUS_ACTIVE && $on_trial == NO && strtotime( $next_due_date ) < time())
        {
            $text = 'We were unable to charge your account on due date('.$due_date_formatted.'). Please <a class="text-success font-weight-500" href="'.site_url('payments/pay').'">update your payment method</a> to continue using '.getenv('SITE_NAME');
            $global_alerts .= get_alert_html($text, ALERT_TYPE_WARNING, NO); //NO=>NO dissmiss option
        }
        //Monthly Billing Failed, user didn't responded in 3 days
        else if($user_status == USER_STATUS_SUSPENDED && $on_trial == NO)
        {
            $text = 'Account suspended. We were unable to charge your account on due date('.$due_date_formatted.'). Please <a class="text-success font-weight-500" href="'.site_url('payments/pay').'">Buy a Plan</a> to continue using '.getenv('SITE_NAME');
            $global_alerts .= get_alert_html($text, ALERT_TYPE_INFO, NO); //NO=>NO dissmiss option
        }
        //Acount closed by user
        else if($user_status == USER_STATUS_CANCELLED)
        {
            $text = 'Account closed. Please <a class="text-success font-weight-500" href="'.site_url('payments/pay').'">Buy a Plan</a> to reactivate';
            $global_alerts .= get_alert_html($text, ALERT_TYPE_INFO, NO); //NO=>NO dissmiss option
        }

        if(!empty($global_alerts))
            $data['global_alerts'] = $global_alerts;
        $this->ci->load->vars($data);
    }
}
