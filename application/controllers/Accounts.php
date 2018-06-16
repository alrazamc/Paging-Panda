<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Accounts Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Accounts
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Accounts extends CI_Controller {

    var $user_id;
    public function __construct()
    {
        parent::__construct();
        session_check();
        $this->user_id = $this->session->userdata('user_id');
        $this->load->model('accounts_model');
    }


    /** default function of the controller */
    public function index()
    {
        $this->all();
    }


    /**
    * Show pages list
    */
    public function all()
    {
        $data['page_title'] = 'Pages';
        $data['view'] = 'accounts/accounts';
        $data['pages'] = $this->accounts_model->get_all($this->user_id);
        $this->load->model('payments_model');
        $plan = $this->payments_model->get_plan( $this->session->userdata('plan_id') );
        $data['page_limit'] = $plan->page_limit;
        $expired_tokens = 0;
        foreach ($data['pages'] as $page){
            if($page->token_expired == YES)
                $expired_tokens++;
        }
        if($expired_tokens == 0)
            $this->accounts_model->set_accounts_alert($this->user_id, NO);
        $data['expired_tokens'] = $expired_tokens;
        $this->load->library('myfacebook');
        $data['fb_login_url'] = $this->myfacebook->get_login_url( site_url('accounts/token') );
        $this->load->view('template', $data);
    }

    /**
    * Save Profile Token
    */
    public function token()
    {
        $this->load->library('myfacebook');
        $token = $this->myfacebook->get_access_token();
        if($token !== true || $this->input->get('error') || $this->input->get('error_code'))
        {
            $message = $token ? $token : get_fb_response_as_html_table($_GET);  
            $this->session->set_flashdata('alert', get_alert_html($message, ALERT_TYPE_ERROR));
            redirect('accounts/all');            
        }
        $access_token =  $this->myfacebook->get_token();
        $this->session->set_userdata('profile_token', $access_token);
        redirect('accounts/import');
    }

    /**
    * Auth user to refresh tokens
    */
    public function auth()
    {
        $this->load->library('myfacebook');
        $fb_login_url = $this->myfacebook->get_login_url( site_url('accounts/refresh') );
        redirect( $fb_login_url );
    }

    /**
    * Refresh Page tokens
    */
    public function refresh()
    {
        $this->load->library('myfacebook');
        $token = $this->myfacebook->get_access_token();
        if($token !== true || $this->input->get('error') || $this->input->get('error_code'))
        {
            $message = $token ? $token : get_fb_response_as_html_table($_GET);  
            $this->session->set_flashdata('alert', get_alert_html($message, ALERT_TYPE_ERROR));
            redirect('accounts/all');            
        }
        $pages = $this->accounts_model->get_all_pages($this->user_id);
        $all_pages = array();
        $update_count =  0;
        foreach($pages as $page)
            $all_pages[$page->account_fb_id] = $page;
        $fb_pages = $this->myfacebook->get_manage_pages();
        do
        {
            foreach($fb_pages->all() as $node)
            {
                $node = $node->asArray();
                if( isset($all_pages[ $node['id'] ]) ){
                    $update_count++;
                    $this->accounts_model->update_page( $node, $this->user_id );
                }
            }
            $fb_pages = $this->myfacebook->next($fb_pages);
        }while( !is_null($fb_pages) && $fb_pages->count() );
        rebuild_queue($this->user_id);
        $message = "$update_count page(s) updated successfully";
        $expired_pages = $this->accounts_model->get_expired_pages($this->user_id);
        if(count($expired_pages))
            $message .= ". ".count($expired_pages)." page(s) below still needs to be refreshed. Probably these pages are managed in different Facebook account. Please login to that Facebook account and click \"Reconnect Pages\" button again";
        else
            $this->accounts_model->set_accounts_alert($this->user_id, NO);
        $this->session->set_flashdata('alert', get_alert_html($message, ALERT_TYPE_SUCCESS));
        redirect('accounts');
    }

    /**
    * Show pages list
    */
    public function import()
    {
        $access_token = $this->session->userdata('profile_token');
        if(empty($access_token))
        {
            $this->load->library('myfacebook');
            $fb_login_url = $this->myfacebook->get_login_url( site_url('accounts/token') );
            redirect( $fb_login_url );
        }
        $data['page_title'] = 'Import Pages';
        $data['view'] = 'accounts/import';
        $pages = $this->accounts_model->get_all($this->user_id);
        $this->load->model('payments_model');
        $plan = $this->payments_model->get_plan( $this->session->userdata('plan_id') );
        $data['page_limit'] = $plan->page_limit;
        $ids = array();
        foreach($pages as $page)
            $ids[] = $page->account_fb_id;
        $data['pages'] = $ids;
        $this->load->view('template', $data);
    }

    /**
    * Remove page
    */
    public function remove($page_id = 0)
    {
        if(!$page_id || !is_numeric($page_id))
            show_404();
        $page = $this->accounts_model->get_record($page_id);
        if(!isset($page->account_id) || $page->user_id != $this->user_id)
            show_404();
        $this->accounts_model->delete($page_id, $this->user_id);
        rebuild_queue($this->user_id);
        $this->session->set_flashdata('alert', get_alert_html('Page removed successfully', ALERT_TYPE_SUCCESS));
        redirect('/accounts');
    }



}

/* End of file Accounts.php */
/* Location: ./application/controllers/Accounts.php */
