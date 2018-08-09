<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Users Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Users
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Users extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        check_remember_me();
        $this->load->model('users_model');
    }


    /** default function of the controller */
    public function index()
    {
        $this->login();
    }


    /**
    * Login page for application
    */
    public function login()
    {
        if($this->session->userdata('user_login') === TRUE )
            redirect('content');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', "Email", 'trim|required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');
        if(!$this->form_validation->run())
        {
            $data['page_title'] = 'Login';
            $data['view'] = 'login';
            $this->load->view('users/layout', $data);
        }else
        {
            $user = $this->users_model->validate_user();
            if(isset($user->user_id))
            {
                if(($this->input->post('remember', TRUE) == 1))
                {
                    $this->load->library('encryption');
                    set_cookie('remme', $this->encryption->encrypt($user->email), 10 * 365 * 24 * 60 * 60); // 10 years expiry time
                }

                $this->session->set_userdata('user_login', TRUE);
                $this->session->set_userdata('user_id', $user->user_id);
                $this->users_model->last_login($user->user_id);
                if($this->session->userdata('redirect_after_login'))
                {
                    $url = $this->session->userdata('redirect_after_login');
                    $this->session->unset_userdata('redirect_after_login');
                    redirect($url);
                }else
                {
                    redirect('content');
                }
            }else
            {
                $this->session->set_flashdata('alert', get_alert_html('Invalid email or password', ALERT_TYPE_ERROR));
                redirect('users/login');
            }
        }
    }

    /**
    * Logout from application link
    */
    public function logout()
    {
        $this->session->sess_destroy();
        delete_cookie('remme');
        redirect('users/login');
    }

    /**
    * Signup page for application
    */
    public function signup($plan_id = 0)
    {
        if(!is_numeric($plan_id))
            show_404();
        if($this->session->userdata('user_login') === TRUE )
            redirect('content');
        if($plan_id == 0)
            redirect( site_url('#pricing') );
        $this->load->library('form_validation');
        $this->form_validation->set_rules('first_name', "First name", 'trim|required|max_length[100]');
        $this->form_validation->set_rules('last_name', 'Last name', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|callback_email_check');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        if(!$this->form_validation->run())
        {
            $data['page_title'] = 'Signup';
            $data['view'] = 'signup';
            $this->load->view('users/layout', $data);
        }else
        {
            $this->load->model('payments_model');
            $plan = $this->payments_model->get_plan($plan_id);
            if(!isset($plan->plan_id))
                $plan = $this->payments_model->get_default_plan();
            $user = $this->users_model->signup($plan->plan_id);
            $this->load->model('categories_model');
            $this->categories_model->create_default($user->user_id); //create default categories
            $this->session->set_userdata('user_login', TRUE);
            $this->session->set_userdata('user_id', $user->user_id);
            $this->load->library('myaws');
            $data['name'] = $user->first_name;
            $message = $this->load->view('emails/welcome', $data, true);
            $this->myaws->send_email($user->email, 'Welcome to '.getenv('SITE_NAME'), $message);
            mailchimp($user->user_id);
            $this->session->set_flashdata('signup', true);
            redirect('accounts/welcome');
        }
    }

    /**
    * Subscribe
    */
    public function subscribe()
    {
        if($this->session->userdata('user_login') === TRUE )
            redirect('content');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        if(!$this->form_validation->run())
        {
            redirect( getenv('BASE_URL') );
        }else
        {
            $email = $this->input->post('email');
            $user = $this->users_model->get_user_by_email($email);
            if(!isset($user->user_id))
                $this->users_model->subscribe($email); //add user to db as subscribed user
            redirect('demo');
        }
    }

    /**
    * Forgot password page to enter email address
    */
    public function forgot_password()
    {
        if($this->session->userdata('user_login') === TRUE )
            redirect('content');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        if(!$this->form_validation->run())
        {
            $data['page_title'] = 'Forgot Password';
            $data['view'] = 'forgot_password';
            $this->load->view('users/layout', $data);
        }else
        {
            $user = $this->users_model->get_user_by_email($this->input->post('email', TRUE));
            if(!isset($user->user_id) || $user->status == USER_STATUS_SUBSCRIBED)
            {
                $this->session->set_flashdata('alert', get_alert_html('This email is not registered', ALERT_TYPE_ERROR));
                redirect('users/forgot_password');
            }
            $data['name'] = $user->first_name;
            $data['hash'] = md5(uniqid().uniqid());
            $this->users_model->save_hash($user->user_id, $data['hash']);
            $this->load->library('myaws');
            $message = $this->load->view('emails/forgot_password', $data, true);
            $sent = $this->myaws->send_email($user->email, 'Reset Password', $message);
            if($sent)
                $this->session->set_flashdata('alert', get_alert_html('An email has been sent to your email address, with a link to reset password', ALERT_TYPE_SUCCESS));
            else
                $this->session->set_flashdata('alert', get_alert_html('Something went wrong, Please try again', ALERT_TYPE_ERROR));
            redirect('users/forgot_password');
        }
    }
    /**
    * Reset password link sent in email
    * @param $hash string, The hash string used to validate a user
    */
    public function reset_password($hash = '')
    {
        if($this->session->userdata('user_login') === TRUE )
            redirect('content');
        if(empty($hash))
            redirect('users/login');
        $user = $this->users_model->validate_hash($hash);
        if(!isset($user->user_id) || $user->status == USER_STATUS_SUBSCRIBED)
            redirect('users/login');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]|callback_password_check');
        $this->form_validation->set_rules('confirm_password', 'Password', 'required|min_length[6]');
         if(!$this->form_validation->run())
         {
            $data['page_title'] = 'Reset Password';
            $data['view'] = 'reset_password';
            $this->load->view('users/layout', $data);
         }else
         {
            $this->users_model->update_password($user->user_id, $this->input->post('password', TRUE));
            $this->users_model->save_hash($user->user_id, "");//empty hash column

            $data['name'] = $user->first_name;
            $this->load->library('myaws');
            $message = $this->load->view('emails/password_changed', $data, true);
            $sent = $this->myaws->send_email($user->email, 'Password changed Successfully', $message);
            $this->session->set_flashdata('alert', get_alert_html('Password changed successfully, Please login with the new password', ALERT_TYPE_SUCCESS));
            redirect('users/login');
         }
    }

    /**
    * custom function for form_validation library to check if email address already exsist in database
    * @param $email string, email address of the user
    */
    public function email_check($email){
        $user_id = $this->session->userdata('user_id');
        if($this->users_model->isemailexist($email, $user_id)){
            $this->form_validation->set_message('email_check', 'This email is already registered');
            return FALSE;
        }
        return TRUE;
    }


    /**
    * match password and confirm password fields
    * custom function for form_validation library
    * @param $password string, password of the user
    */
    public function password_check($password){
        $confirm_password = $this->input->post('confirm_password', TRUE);
        if($password != $confirm_password){
            $this->form_validation->set_message('password_check', 'Passwords are not same');
            return FALSE;
        }
        return TRUE;
    }

    /**
    * validate the current password of the user
    * $password string, current password of the users
    */
    public function validate_password($password)
    {
        session_check();
        if(empty($password))
            return TRUE;
        $user_id =  $this->session->userdata('user_id');
        if($this->users_model->validate_password($password, $user_id))
            return TRUE;
        else
        {
            $this->form_validation->set_message('validate_password', 'Current password is invalid');
            return FALSE;
        }
    }


    /**
    * AJAX call for bootstrap to check if email address already exist
    * @param $user_id integer, id of the user
    */
    public function isemailexist($user_id = 0 )
    {
        $email = $this->input->get('email', TRUE);
        if($this->users_model->isemailexist($email, $user_id))
            echo 'false';
        else
            echo 'true';
    }


    /**
    * Settings page for users
    */
    public function settings()
    {
        session_check();
        $user_id = $this->session->userdata('user_id');
        $user = $this->users_model->get_record($user_id);
        $this->load->model('payments_model');
        $plan = $this->payments_model->get_plan($user->plan_id);
        if($user->on_trial)
        {
            $expiry_date = date('Y-m-d H:i:s', strtotime("$user->date_registered +$plan->trial_period days"));
            if(strtotime($expiry_date) < time())
                $data['expired'] = true;
            $data['expiry_date'] = date('d F', convert_to_user_timezone($expiry_date));
        }

        $data['plan'] = $plan;
        $data['user'] = $user;        
        $this->load->config('time_zones');
        $data['page_title'] = 'Settings';
        $data['view'] = 'users/settings';
        $this->load->view('template', $data);
    }

    /**
    * Edit profile
    */
    public function profile()
    {
        session_check();
        $user_id = $this->session->userdata('user_id');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('first_name', "First name", 'trim|required|max_length[100]');
        $this->form_validation->set_rules('last_name', 'Last name', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|callback_email_check');
        $this->form_validation->set_rules('time_zone', 'Time Zone', 'trim|required');
        $this->form_validation->set_rules('password', 'New password', 'min_length[6]');
        $this->form_validation->set_rules('current_password', 'Current password', 'required|callback_validate_password');
        if(!$this->form_validation->run())
        {
            $data['user'] = $this->users_model->get_record($user_id);
            $this->load->config('time_zones');
            $data['tzones'] = $this->config->item('tzones');
            $data['countries'] = $this->users_model->get_countries();
            $data['page_title'] = 'Edit Profile';
            $data['view'] = 'users/edit_profile';
            $this->load->view('template', $data);
        }else
        {
            $user = $this->users_model->update($user_id);
            $this->session->set_flashdata('alert', get_alert_html('Profile updated successfully', ALERT_TYPE_SUCCESS));
            redirect('users/settings');
        }
    }


    public function view_as($user_id = 0)
    {
        $token = $this->input->get('token');
        if(empty($token)) show_404();
        $this->load->library('encryption');
        $key = $this->encryption->decrypt($token);
        if($key != getenv('ADMIN_KEY')) show_404();
        if($this->session->userdata('user_id'))
        {
            $this->session->sess_destroy();
            delete_cookie('remme');
            redirect( current_url()."?token=".urlencode($token) );
        }
        $user = $this->users_model->get_record($user_id);
        $this->session->set_userdata('user_login', TRUE);
        $this->session->set_userdata('user_id', $user->user_id);
        redirect('accounts');
    }

}

/* End of file users.php */
/* Location: ./application/controllers/users.php */
