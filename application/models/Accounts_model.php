<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Accounts_model Class
 * 
 * @package   PHP_FMT
 * @subpackage  Models
 * @category  Accounts_model
 */
class Accounts_model extends CI_Model{
    
    function __construct()
    {
        parent :: __construct();
    }
    
    /**
    * get all accounts of user
    * @return object
    */
    public function get_all($user_id = 0, $have_post_permssions = false)
    {
        $this->db->where('user_id', $user_id);
        if($have_post_permssions)
            $this->db->where('post_perms', YES);
        $this->db->order_by('token_expired DESC, account_name ASC');
        return $this->db->get('accounts')->result();
    }

    /**
    * get all accounts of user
    * @return object
    */
    public function get_all_pages($user_id = 0)
    {
        $this->db->select('account_id, account_name, access_token, account_fb_id');
        $this->db->where('user_id', $user_id);
        $this->db->where('account_type', ACCOUNT_TYPE_FB_PAGE);
        $this->db->order_by('account_name ASC');
        return $this->db->get('accounts')->result();
    }

    /**
    * get all accounts of user
    * @return object
    */
    public function get_expired_pages($user_id = 0)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('token_expired', YES);
        return $this->db->get('accounts')->result();
    }

    /**
    * is page exist
    * @return object or null
    */
    public function is_page_exist($page_fb_id, $user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('account_fb_id', $page_fb_id);
        return $this->db->get('accounts')->num_rows();
    }

    /**
    * insert new page
    * @return  null
    */
    public function insert_page($node, $user_id)
    {
        $record = array(
            'user_id' => $user_id,
            'account_name' => $node['name'],
            'access_token' => $node['access_token'],
            'account_type' => ACCOUNT_TYPE_FB_PAGE,
            'token_expired' => NO,
            'date_added' => date('Y-m-d H:i:s'),
            'account_fb_id' => $node['id'],
            'post_perms' => in_array(FB_POST_PERMISSION, $node['perms']) ? YES : NO
        );
        $this->db->insert('accounts', $record);
    }

    /**
    * insert new page
    * @return  null
    */
    public function update_page($node, $user_id)
    {
        $record = array(
            'account_name' => $node['name'],
            'access_token' => $node['access_token'],
            'token_expired' => NO,
            'post_perms' => in_array(FB_POST_PERMISSION, $node['perms']) ? YES : NO
        );

        $this->db->where('user_id', $user_id);
        $this->db->where('account_fb_id', $node['id']);
        $this->db->update('accounts', $record);
    }

    /**
    * delete an account
    * @param $account_id integer
    */
    public function delete($account_id = 0, $user_id = 0)
    {
        $escaped_account_id = $this->db->escape($account_id);
        
        $query = "UPDATE schedule set accounts = array_remove(accounts, $escaped_account_id) WHERE user_id = $user_id";
        $this->db->query($query);
        
        //delete Slots where there are no pages any more
        $query = "DELETE FROM schedule WHERE user_id = $user_id AND array_length(accounts, 1) IS NULL"; 
        $this->db->query($query);

        $query = "UPDATE contents set accounts = array_remove(accounts, $escaped_account_id), published_on = array_remove(published_on, $escaped_account_id) WHERE user_id = $user_id";
        $this->db->query($query);
        $query = "UPDATE rss_feeds set accounts = array_remove(accounts, $escaped_account_id) WHERE user_id = $user_id";
        $this->db->query($query);

        $this->db->where('account_id', $account_id);
        $this->db->delete('posts');

        $this->db->where('account_id', $account_id);
        $this->db->delete('accounts');
    }


    /**
    * get a account from database
    * @param $account_id integer,
    * @return object
    */
    public function get_record($account_id)
    {
        return $this->db->get_where('accounts', array('account_id'=>$account_id))->row();
    }

    public function set_accounts_alert($user_id, $status)
    {
        $this->db->set('accounts_alert', $status);
        $this->db->where('user_id', $user_id);
        $this->db->update('users');
    }


    public function get_countries()
    {
        return $this->db->get('countries')->result();
    }
}

/* End of file Accounts_model.php */
/* Location: ./application/models/Accounts_model.php */