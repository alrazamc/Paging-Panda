<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * users_model Class
 * 
 * @package   PHP_FMT
 * @subpackage  Models
 * @category  users_model
 */
class Users_model extends CI_Model{
    
    function __construct()
    {
        parent :: __construct();
    }
    
    /**
    * validate user through password
    * @return object
    */
    public function validate_user()
    {
        $email = $this->input->post('email', TRUE);
        $password = $this->input->post('password', TRUE);
        $password = $this->db->escape($password);
        $this->db->select('*')->from('users');
        $this->db->where("email", $email);
        $this->db->where("password = MD5($password)");
        $this->db->where("status !=", USER_STATUS_SUBSCRIBED);
        $result = $this->db->get();
        return $result->row();
    }
    /**
    * get a user record
    * @param $email string, Email address of user
    * @return object
    */
    public function get_user_by_email($email)
    {
        $this->db->select('*')->from('users');
        $this->db->where('email', $email);
        $result = $this->db->get();
        return $result->row();
    }
    /**
    * store a password reset request of a valid user
    * @param $user_id integer, user_id of the user
    * @param $hash string, random hash string
    * @return void
    */
    public function save_hash($user_id, $hash)
    {
        $record = array(
          'reset_hash' => $hash
        );
        $this->db->where('user_id', $user_id);
        $this->db->update('users', $record);
    }
    /**
    * validate a hash string for password reset request 
    * @param $hash string, random hash string
    * @return object
    */
    public function validate_hash($hash)
    {
        $this->db->select('*')->from('users');
        $this->db->where('reset_hash', $hash);
        $result =  $this->db->get();
        return $result->row();
    }
    /**
    * update password of a user
    * @param $user_id integer, The id of the user
    * @param $password string, The password of the user
    * @return void
    */
    public function update_password($user_id, $password)
    {
        $password = $this->db->escape($password);
        $this->db->set('password',"MD5($password)", false);
        $this->db->where('user_id', $user_id);
        $this->db->update('users');
    }

    /**
    * get a user from database
    * @param $user_id integer, The id of the user
    * @return object
    */
    public function get_record($user_id)
    {
        return $this->db->get_where('users', array('user_id'=>$user_id))->row();
    }

    
    /**
    * check if email address already exsist in database
    * @param $email string, The email to be checked
    * @param $user_id integer, The id of the user whose email to be exculded from check
    * @return boolean
    */
    public function isemailexist($email, $user_id = 0)
    {
        $this->db->where('email', $email);
        if($user_id)
            $this->db->where("user_id !=", $user_id); //edit profile
        else
            $this->db->where('status !=', USER_STATUS_SUBSCRIBED);//signup
        return $this->db->get('users')->num_rows();
    }
    /**
    * insert a new user
    * @return void
    */
    public function signup($plan_id = 0)
    {
        $user = $this->get_user_by_email( $this->input->post('email') );

        $this->db->set('first_name', $this->input->post('first_name'));
        $this->db->set('last_name', $this->input->post('last_name'));
        $this->db->set('email', $this->input->post('email'));
        $password = $this->db->escape($this->input->post('password'));
        $this->db->set('password',"MD5($password)", false);
        $this->db->set('status', USER_STATUS_ACTIVE);
        $this->db->set('plan_id', $plan_id);
        $this->db->set('on_trial', YES);
        $this->db->set('date_registered', date('Y-m-d H:i:s'));
        $this->db->set('date_updated', date('Y-m-d H:i:s'));
        $this->db->set('last_login', date('Y-m-d H:i:s'));
        $this->db->set('date_cancelled', date('Y-m-d H:i:s'));

        if(isset($user->user_id) && $user->status == USER_STATUS_SUBSCRIBED)
        {
            $this->db->where('user_id', $user->user_id);
            $this->db->update('users');
            return $this->get_record( $user->user_id );
        }

        $time_zone = '';
        if($this->input->post('tzone_offset'))
        {
            $timezone_name = timezone_name_from_abbr("", $this->input->post('tzone_offset')*60, false); //convert mins to secs
            if($timezone_name !== FALSE)
                $time_zone = $timezone_name;
        }

        $this->db->set('time_zone', $time_zone);
        $this->db->set('phone', '');
        $this->db->set('address', '');
        $this->db->set('city', '');
        $this->db->set('state', '');
        $this->db->set('zip_code', '');
        $this->db->set('country', 0);
        $this->db->set('reset_hash', '');
        $this->db->set('date_added', date('Y-m-d H:i:s'));
        

        $this->db->set('general_pointer', 0);
        $this->db->set('use_once_pointer', 0);
        $this->db->set('random_pointer', 0);
        $this->db->set('queue_paused', YES);
        $this->db->set('accounts_alert', NO);
        
        $this->db->insert('users');
        $user_id = $this->db->insert_id();
        return $this->get_record($user_id);
    }


    /**
    * subscribe
    * @return void
    */
    public function subscribe($email = '')
    {
        $this->db->set('first_name', '');
        $this->db->set('last_name', '');
        $this->db->set('email', $email);
        $this->db->set('password',"MD5('82&@*!#^')", false);
        $this->db->set('status', USER_STATUS_SUBSCRIBED);
        $this->db->set('plan_id', 0);

        $this->db->set('time_zone', '');
        $this->db->set('phone', '');
        $this->db->set('address', '');
        $this->db->set('city', '');
        $this->db->set('state', '');
        $this->db->set('zip_code', '');
        $this->db->set('country', 0);
        $this->db->set('reset_hash', '');
        $this->db->set('date_added', date('Y-m-d H:i:s'));
        $this->db->set('date_registered', date('Y-m-d H:i:s'));
        $this->db->set('date_updated', date('Y-m-d H:i:s'));
        $this->db->set('last_login', date('Y-m-d H:i:s'));
        $this->db->set('date_cancelled', date('Y-m-d H:i:s'));

        $this->db->set('general_pointer', 0);
        $this->db->set('use_once_pointer', 0);
        $this->db->set('random_pointer', 0);
        $this->db->set('queue_paused', YES);
        $this->db->set('accounts_alert', NO);

        $this->db->set('on_trial', NO);

        $this->db->insert('users');
        return $this->db->insert_id();
        //return $this->get_record($user_id);
    }
    /**
    * update a user record
    * @param $user_id integer, The id of the user
    * @return void
    */
    public function update($user_id)
    {
        $this->db->set('first_name', $this->input->post('first_name'));
        $this->db->set('last_name', $this->input->post('last_name'));
        $this->db->set('email', $this->input->post('email'));
        if($this->input->post('password'))
        {
            $password = $this->db->escape($this->input->post('password'));
            $this->db->set('password',"MD5($password)", false);
        }
        $this->db->set('time_zone', $this->input->post('time_zone') ? $this->input->post('time_zone') : '' );
        $this->db->set('phone', $this->input->post('phone') ? $this->input->post('phone') : '');
        $this->db->set('address', $this->input->post('address') ? $this->input->post('address') : '');
        $this->db->set('city', $this->input->post('city') ? $this->input->post('city') : '');
        $this->db->set('state', $this->input->post('state') ? $this->input->post('state') : '');
        $this->db->set('zip_code', $this->input->post('zip_code') ? $this->input->post('zip_code') : '');
        $this->db->set('country', (int) ($this->input->post('country') ? $this->input->post('country') : 0) );
        $this->db->set('date_updated', date('Y-m-d H:i:s'));
        $this->db->where('user_id', $user_id);
        $this->db->update('users');
        return $user_id;
    }

    /**
    * update a user record
    * @param $user_id integer, The id of the user
    * @return void
    */
    public function last_login($user_id)
    {
        $this->db->set('last_login', date('Y-m-d H:i:s'));
        $this->db->where('user_id', $user_id);
        $this->db->update('users');
        return $user_id;
    }
    /**
    * validate old password of logged in user for changing password
    * @param $password string, The password of the user
    * @param $user_id integer, The id of the user
    * @return boolean
    */
    public function validate_password($password, $user_id)
    {
        $password = $this->db->escape($password);
        $this->db->where("password = MD5($password)");
        $this->db->where('user_id', $user_id);
        return $this->db->get('users')->num_rows();
    }
    

    public function get_countries()
    {
        return $this->db->get('countries')->result();
    }
}

/* End of file users_model.php */
/* Location: ./application/models/users_model.php */