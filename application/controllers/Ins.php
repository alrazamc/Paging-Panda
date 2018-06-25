<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Ins Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Ins
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Ins extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('payments_model');
        $this->load->model('users_model');
    }


    public function handle()
    {
        $this->_verify();
        $type = strtolower( $this->input->post('message_type') );
        if(method_exists($this, "_$type"))
            $this->{ "_$type" }();
        else
            $this->_notify_admin();
        echo $this->input->post('message_type');
    }

    private function _order_created()
    {

        $user = $this->users_model->get_user_by_email( $this->input->post('customer_email') );
        //update due date, make user active(optional)
        $this->payments_model->update_user_due_date($user->user_id, $this->input->post('item_rec_date_next_1'));
        //handled in payment/pay, transaction logged
        //send email to user
        $plan = $this->payments_model->get_plan( $this->input->post('item_id_1') );
        $this->load->library('myaws');
        $data['name'] = $user->first_name;
        $data['plan'] = $plan;
        $data['next_due_date'] = $this->input->post('item_rec_date_next_1');
        $data['total'] = $this->input->post('invoice_usd_amount');
        $message = $this->load->view('emails/payments/new_order', $data, true);
        $this->myaws->send_email($user->email, "Payment confirmed for $plan->name plan", $message);
        //create pdf invoice
    }

    private function _fraud_status_changed()
    {
        $fraud_status = $this->input->post('fraud_status');
        $this->payments_model->set_fraud_status( $this->input->post('sale_id'), $this->input->post('invoice_id'), $fraud_status );
        if($fraud_status == 'fail')
            $this->_notify_admin();
    }

    private function _invoice_status_changed()
    {
        $invoice_status = $this->input->post('invoice_status');
        $this->payments_model->set_invoice_status( $this->input->post('sale_id'), $this->input->post('invoice_id'),  $invoice_status );
        if($invoice_status == 'declined')
            $this->_notify_admin();
    }

    private function _refund_issued()
    {
        //do nothing, transaction logged manually
        //create PDF invoice
    }


    private function _recurring_installment_success()
    {
        //log transaction, update due date, make user active(optional)
        $this->payments_model->log_installment();
        $invoice = $this->payments_model->get_invoice_by_id( $this->input->post('sale_id'), $this->input->post('invoice_id') );
        //Send email receipt to user
        $user = $this->users_model->get_record($invoice->user_id);
        $this->load->library('myaws');
        $data['name'] = $user->first_name;
        $data['invoice'] = $invoice;
        $data['next_due_date'] = $this->input->post('item_rec_date_next_1');
        $message = $this->load->view('emails/payments/installment', $data, true);
        $this->myaws->send_email($user->email, "Monthly Payment Received", $message);
        //create pdf invoice  
    }

    private function _recurring_installment_failed()
    {
        //send email to user, suspend after 3 days via cron
        $invoice = $this->payments_model->get_invoice_by_id( $this->input->post('sale_id'), $this->input->post('invoice_id') );
        $user = $this->users_model->get_record($invoice->user_id);
        if( date('Y-m-d', strtotime("$user->next_due_date +1 day")) == date('Y-m-d') )//2nd try
        {
            $this->load->library('myaws');
            $data['name'] = $user->first_name;
            $data['invoice'] = $invoice;
            $data['user'] = $user;
            $message = $this->load->view('emails/payments/installment_failed', $data, true);
            $this->myaws->send_email($user->email, "Renewal Payment Failed", $message);
        }
        //notify admin, manually notify user from 2checkout panel
        $this->_notify_admin();
    }

    private function _recurring_stopped()
    {
        //case 1: plan changed, previous plan stopped, Do Nothing
        //case 2: user closed account, Do nothing 
        //case 3: stopped by cron after 3 days of due date, failed billing, DO Nothing, user already suspended by cron
        //case 4: user stopped manually, Suspend user account, send email to admin and user
        $order = $this->payments_model->get_invoice_by_id( $this->input->post('sale_id') );
        if($order->is_stopped == NO) //case 4
        {
            $this->payments_model->stop_recurring( $this->input->post('sale_id'), RECUR_STOP_REASON_EXTERNAL );
            $this->_notify_admin();
        }
    }



    private function _verify()
    {
        $sale_id = $this->input->post('sale_id');
        $invoice = $this->input->post('invoice_id');
        $vendor = getenv('2CO_SELLER_ID');
        $secret = getenv('2CO_SECRET');
        $hash = strtoupper(md5($sale_id . $vendor . $invoice . $secret));
        if($this->input->post('md5_hash') != $hash)
            show_404();
    }

    private function _notify_admin()
    {
        $this->load->library('myaws');
        $this->myaws->send_email(getenv('ADMIN_EMAIL'), $_POST['message_type'], get_fb_response_as_html_table($_POST));
    }

    //Facebook messenger hook
    public function messenger()
    {
        $verify_token = getenv('FB_MSG_VERIFY_TOKEN');
        $hub_verify_token = null;
        if( $this->input->get_post('hub_challenge')) {
         $challenge = $this->input->get_post('hub_challenge');
         $hub_verify_token = $this->input->get_post('hub_verify_token');
        }
        if ($hub_verify_token === $verify_token) {
         echo $challenge;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        //Label facebook converstion with user ID
        if(isset($input['entry'][0]['messaging'][0]['postback']['referral']['ref']))
        {
            $label = $input['entry'][0]['messaging'][0]['postback']['referral']['ref'];
            $uri = $input['entry'][0]['messaging'][0]['postback']['referral']['referer_uri'];
            $this->load->library('myfacebook');
            $this->myfacebook->set_token( getenv('FB_PAGE_TOKEN') );
            $user_psid = $input['entry'][0]['messaging'][0]['sender']['id']; //page scope user facebook ID
            $response = $this->myfacebook->post_request('/me/admin_notes', array('body' => "$label \n $uri", 'user_id' => $user_psid));
        }
    }

}

/* End of file Ins.php */
/* Location: ./application/controllers/Ins.php */