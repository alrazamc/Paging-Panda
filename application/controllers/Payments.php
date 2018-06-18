<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Payments Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Payments
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Payments extends CI_Controller {

    var $user_id;
    public function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('payments_model');
        $this->load->model('users_model');
    }


    /** default function of the controller */
    public function pay()
    {
        $data['page_title'] = 'Subscribe Plan';
        $plans = $this->payments_model->get_all_plans();
        $data['plans'] = $plans;
        $user = $this->users_model->get_record($this->user_id);
        $plan = $this->payments_model->get_plan($user->plan_id);
        $trial_days_remaining = 0;
        if($user->on_trial)
        {
            $expiry_date = date('Y-m-d H:i:s', strtotime("$user->date_registered +$plan->trial_period days"));
            if(strtotime($expiry_date) > time())
            {
                $diff  = strtotime($expiry_date) - time();
                $days = $diff/86400; //to days
                $trial_days_remaining = floor($days);
            }
        }
        $data['due_date_valid'] = $user->next_due_date ? strtotime($user->next_due_date) > time() : true;
        $this->load->model('accounts_model');
        $pages = $this->accounts_model->get_all($this->user_id);
        $data['total_pages'] = count($pages);
        $data['trial_days_remaining'] = $trial_days_remaining;
        $data['user'] = $user;
        $data['countries'] = $this->users_model->get_countries();
        $data['view'] = 'payments/pay';
        $this->load->view('template', $data);
    }

    private function _verify_sale()
    {
        $secret = getenv('2CO_SECRET');
        $seller_id = getenv('2CO_SELLER_ID');
        $order = $this->input->get_post('order_number');
        $total = $this->input->get_post('total');
        $key = strtoupper(md5("$secret$seller_id$order$total"));
        if($key != $this->input->get_post('key'))
            show_404();
    }
    
    public function approve()
    {
        $this->_verify_sale();
        $data['page_title'] = 'Processing';
        $data['view'] = 'payments/processing';
        $this->load->view('template', $data);
    }

    public function process()
    {   
        $this->_verify_sale();

        $user = $this->users_model->get_record($this->user_id);
        $plan = $this->payments_model->get_plan($this->input->get_post('plan_id'));
        if(is_null($plan)) show_404();
        $discount = 0.00;
        $trial_days_remaining = 0;
        if($user->on_trial)
        {
            $expiry_date = date('Y-m-d H:i:s', strtotime("$user->date_registered +$plan->trial_period days"));
            if(strtotime($expiry_date) > time())
            {
                $diff  = strtotime($expiry_date) - time();
                $days = $diff/86400; //to days
                $trial_days_remaining = floor($days);
                $ds = ($plan->price/30)*$trial_days_remaining;
                $discount = ceil($ds);
            }
        }
        $this->load->model('accounts_model');
        $pages = $this->accounts_model->get_all($this->user_id);
        $is_order_exist = $this->payments_model->is_order_exist( $this->input->get_post('order_number') );
        if( $this->input->get_post('total') != ($plan->price - $discount) || 
            $this->input->get_post('li_0_price') != $plan->price || 
            $plan->page_limit - count($pages) < 0 || 
            $is_order_exist)
        {
            $this->session->set_flashdata('alert', get_alert_html('we are sorry to inform you that there was some malfunctioning with checkout process. Please contact our support. ', ALERT_TYPE_ERROR));
            echo 'ok'; return;
        }
        $refund_msg = '';
        if($user->on_trial == NO && $user->status == USER_STATUS_ACTIVE)
        {
            $previous_plan = $this->payments_model->get_plan($user->plan_id);
            $last_invoice = $this->payments_model->get_user_last_invoice($user->user_id);
            $refund_msg = $this->_refund($previous_plan, $last_invoice, $user, $plan); //refund remaining amount of last plan
            $this->_stop_recurring($last_invoice, RECUR_STOP_REASON_CHANGE_OF_PLAN); //stop last recurring plan
        }
        $this->payments_model->approve_payment($user, $plan, $trial_days_remaining);
        $this->session->set_flashdata('alert', get_alert_html("$plan->name plan activated successfully. $refund_msg Thank you for choosing ".getenv("SITE_NAME"), ALERT_TYPE_SUCCESS));
        echo 'ok'; return;
    }

    public function close()
    {
        $user = $this->users_model->get_record($this->user_id);
        if($user->on_trial == YES || $user->status == USER_STATUS_CANCELLED) show_404();
        $this->load->library('form_validation');
        $this->form_validation->set_rules('cancel', '', 'trim|required');
        if(!$this->form_validation->run())
        {
            $data['page_title'] = 'Close Account';
            $data['view'] = 'payments/close_account';
            $this->load->view('template', $data);
        }else
        {
            $previous_plan = $this->payments_model->get_plan($user->plan_id);
            $last_invoice = $this->payments_model->get_user_last_invoice($user->user_id);

            $refund_msg = $this->_refund($previous_plan, $last_invoice, $user); //refund remaining amount of last plan
            $this->_stop_recurring($last_invoice, RECUR_STOP_REASON_ACCOUNT_CLOSED); //stop recurring plan
            $this->payments_model->close_account($this->user_id);
            if(!empty($refund_msg))
                $this->session->set_flashdata('alert', get_alert_html($refund_msg, ALERT_TYPE_SUCCESS));
            redirect('payments/closed');
        }
    }

    public function closed()
    {
        $user = $this->users_model->get_record($this->user_id);
        if($user->status != USER_STATUS_CANCELLED) show_404();
        $data['page_title'] = 'Account Closed';
        $data['view'] = 'payments/account_closed';
        $this->load->view('template', $data);
    }

    private function _refund($previous_plan, $last_invoice, $user, $new_plan = false)
    {
        if(empty($last_invoice)) return;
        if($user->on_trial == YES || $user->status != USER_STATUS_ACTIVE) return;
        if(empty($user->next_due_date) || strtotime($user->next_due_date) < time()) return;

        Twocheckout::username(getenv('2CO_ADMIN_USER'));
        Twocheckout::password(getenv('2CO_ADMIN_PASSWORD'));
        if(getenv('2CO_MODE') == 'sandbox')
            Twocheckout::sandbox(true);

        $diff  = strtotime($user->next_due_date) - time();
        $days = $diff/86400; //to days
        $current_plan_days_remaining = floor($days);
        $ds = ($last_invoice->total/30)*$current_plan_days_remaining;
        $amount = round($ds, 2);
        $amount = $amount > $last_invoice->total ? $last_invoice->total : $amount;
        if( empty($amount) ) return;
        if(!empty($new_plan) )
            $comment = "Plan changed from $previous_plan->name to $new_plan->name, Refunding $".$amount." for $current_plan_days_remaining days remaining in last plan";
        else
            $comment = 'Account closed';
        $args = array(
            'invoice_id' => $last_invoice->invoice_id,
            'category' => empty($new_plan) ? 10 : 5, //10=>Cancellation , 5=>other
            'amount' => $amount,
            'currency' => 'usd',
            'comment' => $comment
        );
        try {
            $result = Twocheckout_Sale::refund($args);
            if(isset($result['response_code']) && $result['response_code'] == 'OK')
            {
                $this->payments_model->log_refund($user, $previous_plan, $last_invoice, $amount, empty($new_plan) ? PAYMENT_TYPE_CLOSED_REACTIVATION : PAYMENT_TYPE_CHANGE_OF_PLAN);
                $refund_msg = '$'."$amount refunded from previous plan for $current_plan_days_remaining remaining days.";
                return $refund_msg;
            }
        } catch (Twocheckout_Error $e) 
        {
            $msg = $e->getMessage();
        }
        return '';
    }
    //refund remaining amount and stop recurring
    private function _stop_recurring($last_invoice, $reason)
    {
        if(empty($last_invoice) || $last_invoice->is_stopped == YES) return;
        Twocheckout::username(getenv('2CO_ADMIN_USER'));
        Twocheckout::password(getenv('2CO_ADMIN_PASSWORD'));
        if(getenv('2CO_MODE') == 'sandbox')
            Twocheckout::sandbox(true);
        //stop recurring
        $args = array(
            'sale_id' => $last_invoice->order_number
        );
        try {
            $result = Twocheckout_Sale::stop($args);
            if(isset($result['response_code']) && $result['response_code'] == 'OK')
                $this->payments_model->stop_recurring($last_invoice->order_number, $reason);
        } catch (Twocheckout_Error $e) {
            $e->getMessage();
        }
    }

    public function invoices()
    {
        $data['invoices'] = $this->payments_model->get_all_transactions($this->user_id);
        $data['page_title'] = 'Invoices';
        $data['view'] = 'payments/invoices';
        $this->load->view('template', $data);
    }

}

/* End of file Payments.php */
/* Location: ./application/controllers/Payments.php */