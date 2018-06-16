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
        $this->db->order_by('transaction_time', DESC);
        $this->db->limit(1);
        return $this->db->get('transactions')->row();
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
            'transaction_type' => TRANS_TYPE_CREDIT
        );
        $this->db->insert('transactions', $transaction);
        //update user
        $record = array(
            'status' => USER_STATUS_ACTIVE,
            'on_trial' => NO,
            'plan_id' => $plan->plan_id,
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
      
}

/* End of file Payments_model.php */
/* Location: ./application/models/Payments_model.php */