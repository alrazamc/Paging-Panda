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
        $this->load->model('accounts_model');
        $pages = $this->accounts_model->get_all($this->user_id);
        $data['total_pages'] = count($pages);
        $data['trial_days_remaining'] = $trial_days_remaining;
        $data['user'] = $user;
        $data['countries'] = $this->users_model->get_countries();
        $data['view'] = 'payments/pay';
        $this->load->view('template', $data);
    }

    public function approve()
    {   
        $secret = getenv('2CO_SECRET');
        $seller_id = getenv('2CO_SELLER_ID');
        $order = $this->input->get_post('order_number');
        $total = $this->input->get_post('total');

        $key = strtoupper(md5("$secret$seller_id$order$total"));
        if($key != $this->input->get_post('key'))
            show_404();

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
        $is_order_exist = $this->payments_model->is_order_exist( $order );
        if( $total != ($plan->price - $discount) || 
            $this->input->get_post('li_0_price') != $plan->price || 
            $plan->page_limit - count($pages) < 0 || 
            $is_order_exist)
        {
            $this->session->set_flashdata('alert', get_alert_html('we are sorry to inform you that there was some malfunctioning with checkout process. Please contact our support. ', ALERT_TYPE_ERROR));
            redirect('users/settings');
        }
        $refund_msg = '';
        if($user->on_trial == NO && $user->status == USER_STATUS_ACTIVE)
        {
            $previous_plan = $this->payments_model->get_plan($user->plan_id);
            $last_invoice = $this->payments_model->get_user_last_invoice($user->user_id);
            if($last_invoice)
            {
                Twocheckout::username(getenv('2CO_ADMIN_USER'));
                Twocheckout::password(getenv('2CO_ADMIN_PASSWORD'));
                if(getenv('2CO_MODE') == 'sandbox')
                    Twocheckout::sandbox(true);
                if($user->next_due_date && strtotime($user->next_due_date) > time())
                {
                    $diff  = strtotime($user->next_due_date) - time();
                    $days = $diff/86400; //to days
                    $current_plan_days_remaining = floor($days);
                    $ds = ($last_invoice->total/30)*$current_plan_days_remaining;
                    $amount = round($ds, 2);
                    $amount = $amount > $last_invoice->total ? $last_invoice->total : $amount;
                    if($amount)
                    {
                        $args = array(
                            'invoice_id' => $last_invoice->invoice_id,
                            'category' => 13,
                            'amount' => $amount,
                            'currency' => 'usd',
                            'comment' => "Plan changed from $previous_plan->name to $plan->name, Refunding $ $amount for $current_plan_days_remaining days remaining in last plan"
                        );
                        try {
                            $result = Twocheckout_Sale::refund($args);
                            if(isset($result['response_code']) && $result['response_code'] == 'OK')
                                $refund_msg = '$'."$amount refunded from previous plan for $current_plan_days_remaining remaining days.";
                        } catch (Twocheckout_Error $e) {
                            $msg = $e->getMessage();
                        }
                    }
                } //end refund amount

                //stop recurring
                $args = array(
                    'sale_id' => $last_invoice->order_number
                );
                try {
                    $result = Twocheckout_Sale::stop($args);
                } catch (Twocheckout_Error $e) {
                    $e->getMessage();
                }
            }//if last invoice
        }//active user changing plan

        $this->payments_model->approve_payment($user, $plan, $trial_days_remaining);
        $this->session->set_flashdata('alert', get_alert_html("$plan->name plan activated successfully. $refund_msg Thank you for choosing ".getenv("SITE_NAME"), ALERT_TYPE_SUCCESS));
        redirect('users/settings');
    }

}

/* End of file Payments.php */
/* Location: ./application/controllers/Payments.php */