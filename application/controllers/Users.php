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
            redirect('home/dashboard');
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
                   // die('sdfsd');
                    $this->load->library('encryption');
                    set_cookie('rememberme', $this->encryption->encrypt($user->email), 10 * 365 * 24 * 60 * 60); // 10 years expiry time
                }

                $this->session->set_userdata('user_login', TRUE);
                $this->session->set_userdata('user_id', $user->user_id);
                $this->session->set_userdata('first_name', $user->first_name);
                $this->session->set_userdata('last_name', $user->last_name);
                $this->session->set_userdata('time_zone', $user->time_zone);
                $this->session->set_userdata('email', $user->email);
                $this->users_model->last_login($user->user_id);
                redirect('home/dashboard');
            }else
            {
                $this->session->set_flashdata('alert', get_alert_html('Invalid email or password', ALERT_TYPE_ERROR));
                redirect('users/login');
            }
        }
    }

    /**
    * Signup page for application
    */
    public function signup()
    {
        if($this->session->userdata('user_login') === TRUE )
            redirect('home/dashboard');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('first_name', "First name", 'trim|required|max_length[100]|alpha');
        $this->form_validation->set_rules('last_name', 'Last name', 'trim|required|max_length[100]|alpha');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|callback_email_check');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        if(!$this->form_validation->run())
        {
            $data['page_title'] = 'Signup';
            $data['view'] = 'signup';
            $this->load->view('users/layout', $data);
        }else
        {
            $user = $this->users_model->insert();
            $this->load->model('categories_model');
            $this->categories_model->create_default($user->user_id); //create default categories
            $this->session->set_userdata('user_login', TRUE);
            $this->session->set_userdata('user_id', $user->user_id);
            $this->session->set_userdata('first_name', $user->first_name);
            $this->session->set_userdata('last_name', $user->last_name);
            $this->session->set_userdata('time_zone', $user->time_zone);
            $this->session->set_userdata('email', $user->email);
            $this->load->library('myaws');
            $data['name'] = $user->first_name;
            $message = $this->load->view('emails/welcome', $data, true);
            $this->myaws->send_email($user->email, 'Welcome to '.$this->config->item('site_name'), $message);
            redirect('home/dashboard');
        }
    }

    /**
    * Logout from application link
    */
    public function logout()
    {
        $this->session->sess_destroy();
        delete_cookie('rememberme');
        redirect('users/login');
    }

    /**
    * Forgot password page to enter email address
    */
    public function forgot_password()
    {
        if($this->session->userdata('user_login') === TRUE )
            redirect('home/dashboard');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        if(!$this->form_validation->run())
        {
            $data['page_title'] = 'Forgot Password';
            $data['view'] = 'forgot_password';
            $this->load->view('users/layout', $data);
        }else
        {
            $user = $this->users_model->get_user($this->input->post('email', TRUE));
            if(!isset($user->user_id))
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
            redirect('home/dashboard');
        if(empty($hash))
            redirect('users/login');
        $user = $this->users_model->validate_hash($hash);
        if(!isset($user->user_id))
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
            $this->session->set_flashdata('alert', get_alert_html('Password changed successully, Please login with new password', ALERT_TYPE_SUCCESS));
            redirect('users/login');
         }
    }

    /**
    * custom function for form_validation library to check if email address already exsist in database
    * @param $email string, email address of the user
    */
    public function email_check($email){
        $user_id = $this->uri->segment(3);
        $user_id = empty($user_id) ? $this->session->userdata('user_id') : $user_id;
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
        $data['user'] = $this->users_model->get_record($user_id);
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
        $this->form_validation->set_rules('first_name', "First name", 'trim|required|max_length[100]|alpha');
        $this->form_validation->set_rules('last_name', 'Last name', 'trim|required|max_length[100]|alpha');
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
            $this->session->set_flashdata('alert', get_alert_html('Profile udpated successully', ALERT_TYPE_SUCCESS));
            redirect('users/settings');
        }
    }


}

/* End of file users.php */
/* Location: ./application/controllers/users.php */
