<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Payments_model Class
 * 
 * @package   PHP_FMT
 * @subpackage  Models
 * @category  Payments_model
 */
class Payments_model extends CI_Model{
    
    function __construct()
    {
        parent :: __construct();
    }

    /**
    * get all available insights metrics
    * @return object
    */
    public function get_plan($plan_id = 0)
    {
        return $this->db->get_where('plans', array('plan_id' => $plan_id))->row();
    }

    /**
    * get all available insights metrics
    * @return object
    */
    public function get_transaction($transaction_id = 0)
    {
        return $this->db->get_where('transactions', array('transaction_id' => $transaction_id))->row();
    }

    /**
    * get All plans
    */
    public function get_all_plans()
    {
        $this->db->order_by('price', 'ASC');
        return $this->db->get('plans')->result();
    }

    /**
    * get all available insights metrics
    * @return object
    */
    public function get_default_plan()
    {
        $this->db->order_by('price', 'ASC');
        $this->db->limit(1);
        return $this->db->get('plans')->row();
    }

    public function is_order_exist($order_number)
    {
        $this->db->where('order_number', $order_number);
        return $this->db->get('transactions')->num_rows();
    }

    public function get_user_last_invoice($user_id = 0)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('transaction_type', TRANS_TYPE_CREDIT);
        $this->db->order_by('transaction_time', 'DESC');
        $this->db->limit(1);
        return $this->db->get('transactions')->row();
    }

    public function get_invoice_by_id($order_number = 0, $invoice_id = 0)
    {
        $this->db->where('order_number', $order_number);
        if($invoice_id)
            $this->db->where('invoice_id', $invoice_id);
        $this->db->where('transaction_type', TRANS_TYPE_CREDIT);
        $this->db->limit(1);
        return $this->db->get('transactions')->row();
    }

    public function set_fraud_status($order_number, $invoice_id, $status)
    {
        $this->db->set('fraud_status', $status);
        $this->db->where('order_number', $order_number);
        $this->db->where('invoice_id', $invoice_id);
        $this->db->update('transactions');
    }

    public function set_invoice_status($order_number, $invoice_id, $status)
    {
        $this->db->set('invoice_status', $status);
        $this->db->where('order_number', $order_number);
        $this->db->where('invoice_id', $invoice_id);
        $this->db->update('transactions');
    }

    public function update_user_due_date($user_id, $date)
    {
        $record = array(
            'next_due_date' => "$date 23:59:59",
            'date_updated' => date('Y-m-d H:i:s')
        );
        $this->db->where('user_id', $user_id);
        $this->db->update('users', $record);
    }

    public function approve_payment($user, $plan, $trial_days_left = 0)
    {
        if($user->on_trial)
            $type = PAYMENT_TYPE_NEW_SUBSCRIPTION;
        else if($user->status == USER_STATUS_ACTIVE)
            $type = PAYMENT_TYPE_CHANGE_OF_PLAN;
        else if($user->status == USER_STATUS_SUSPENDED)
            $type = PAYMENT_TYPE_SUSPENDED_REACTIVATION;
        else if($user->status == USER_STATUS_CANCELLED)
            $type = PAYMENT_TYPE_CLOSED_REACTIVATION;

        $transaction = array(
            'user_id' => $user->user_id,
            'type' => isset($type) ? $type : PAYMENT_TYPE_NEW_SUBSCRIPTION,
            'plan_id' => $plan->plan_id,
            'plan_name' => $plan->name,
            'plan_price' => $plan->price,
            'payment_interval' => 'Monthly',
            'order_number' => $this->input->get_post('order_number'),
            'invoice_id' => $this->input->get_post('invoice_id'),
            'currency_code' => $this->input->get_post('currency_code'),
            'total' => $this->input->get_post('total'),
            'discount' => $this->input->get_post('li_0_startup_fee') ? $this->input->get_post('li_0_startup_fee') : 0.00,
            'trial_days_left' => $trial_days_left,
            'cc_processed' => $this->input->get_post('credit_card_processed'),
            'pay_method' => $this->input->get_post('pay_method'),
            'card_holder_name' => $this->input->get_post('card_holder_name'),
            'email_address' => $this->input->get_post('email'),
            'address_1' => $this->input->get_post('street_address'),
            'address_2' => $this->input->get_post('street_address2'),
            'city' => $this->input->get_post('city'),
            'state' => $this->input->get_post('state'),
            'zip' => $this->input->get_post('zip'),
            'country' => $this->input->get_post('country'),
            'phone' => $this->input->get_post('phone'),
            'transaction_time' => date('Y-m-d H:i:s'),
            'transaction_type' => TRANS_TYPE_CREDIT,
            'is_stopped' => NO,
            'stop_reason' => NO
        );
        $this->db->insert('transactions', $transaction);
        //update user
        $record = array(
            'status' => USER_STATUS_ACTIVE,
            'on_trial' => NO,
            'plan_id' => $plan->plan_id,
            'next_due_date' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'date_updated' => date('Y-m-d H:i:s')
        );
        if(empty($user->address))
            $record['address'] = $this->input->get_post('street_address');
        if(empty($user->city))
            $record['city'] = $this->input->get_post('city');
        if(empty($user->state))
            $record['state'] = $this->input->get_post('state');
        if(empty($user->zip_code))
            $record['zip_code'] = $this->input->get_post('zip');
        if(empty($user->phone))
            $record['phone'] = $this->input->get_post('phone');
        if(empty($user->country))
        {
            $country = $this->db->get_where('countries', array('iso3' => $this->input->get_post('country') ) )->row();
            $record['country'] = !is_null($country) ? $country->country_id : 0;
        }

        $this->db->where('user_id', $user->user_id);
        $this->db->update('users', $record);
    }

    public function log_installment()
    {
        $this->db->where('order_number', $this->input->post('sale_id'));
        $this->db->limit(1);
        $order = $this->db->get('transactions')->row();

        $transaction = array(
            'user_id' => $order->user_id,
            'type' => PAYMENT_TYPE_INSTALLMENT,
            'plan_id' => $order->plan_id,
            'plan_name' => $order->plan_name,
            'plan_price' => $order->plan_price,
            'payment_interval' => 'Monthly',
            'order_number' => $this->input->post('sale_id'),
            'invoice_id' => $this->input->post('invoice_id'),
            'currency_code' => $this->input->post('list_currency'),
            'total' => $this->input->post('item_usd_amount_1'),
            'discount' => 0.00,
            'trial_days_left' => 0,
            'cc_processed' => 'Y',
            'pay_method' => $this->input->post('payment_type'),
            'card_holder_name' => $this->input->post('customer_name'),
            'email_address' => $this->input->post('customer_email'),
            'address_1' => $this->input->post('bill_street_address'),
            'address_2' => $this->input->post('bill_street_address2'),
            'city' => $this->input->post('bill_city'),
            'state' => $this->input->post('bill_state'),
            'zip' => $this->input->post('bill_postal_code'),
            'country' => $this->input->post('bill_country'),
            'phone' => $this->input->post('customer_phone'),
            'transaction_time' => date('Y-m-d H:i:s'),
            'transaction_type' => TRANS_TYPE_CREDIT,
            'is_stopped' => NO,
            'stop_reason' => NO
        );
        $this->db->insert('transactions', $transaction);

        $this->update_user_due_date($order->user_id, $this->input->post('item_rec_date_next_1'));
    }

    public function log_refund($user, $plan, $invoice, $amount, $type = PAYMENT_TYPE_CHANGE_OF_PLAN)
    {
        $transaction = array(
            'user_id' => $user->user_id,
            'type' => $type,
            'plan_id' => $plan->plan_id,
            'plan_name' => $plan->name,
            'plan_price' => $plan->price,
            'payment_interval' => 'Monthly',
            'order_number' => $invoice->order_number,
            'invoice_id' => $invoice->invoice_id,
            'currency_code' => $invoice->currency_code,
            'total' => "-$amount",
            'discount' => 0.00,
            'trial_days_left' => 0,
            'cc_processed' => $invoice->cc_processed,
            'pay_method' => $invoice->pay_method,
            'card_holder_name' => $invoice->card_holder_name,
            'email_address' => $invoice->email_address,
            'address_1' => $invoice->address_1,
            'address_2' => $invoice->address_2,
            'city' => $invoice->city,
            'state' => $invoice->state,
            'zip' => $invoice->zip,
            'country' => $invoice->country,
            'phone' => $invoice->phone,
            'transaction_time' => date('Y-m-d H:i:s'),
            'invoice_status' => $invoice->invoice_status,
            'fraud_status' => $invoice->fraud_status,
            'transaction_type' => $amount == $invoice->total ? TRANS_TYPE_FULL_REFUND :  TRANS_TYPE_PARTIAL_REFUND,
            'is_stopped' => $invoice->is_stopped,
            'stop_reason' => $invoice->stop_reason
        );
        $this->db->insert('transactions', $transaction);
    }

    public function stop_recurring($order_number, $reaason = 0)
    {
        $this->db->set('is_stopped', YES);
        $this->db->set('stop_reason', $reaason);
        $this->db->where('order_number', $order_number);
        $this->db->update('transactions');
    }

    public function suspend_account($user_id = 0)
    {
        $record = array(
            'status' => USER_STATUS_SUSPENDED,
            'date_updated' => date('Y-m-d H:i:s')
        );
        $this->db->where('user_id', $user_id);
        $this->db->update('users', $record);
    }

    public function close_account($user_id = 0)
    {
        $record = array(
            'status' => USER_STATUS_CANCELLED,
            'date_updated' => date('Y-m-d H:i:s')
        );
        $this->db->where('user_id', $user_id);
        $this->db->update('users', $record);
    }

    public function get_all_transactions($user_id = 0)
    {
        $this->db->where('user_id', $user_id);
        $this->db->order_by('transaction_time', 'DESC');
        return $this->db->get('transactions')->result();
    }
      
}

/* End of file Payments_model.php */
/* Location: ./application/models/Payments_model.php */