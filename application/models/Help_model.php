<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Help_model Class
 * 
 * @package   PHP_FMT
 * @subpackage  Models
 * @category  Help_model
 */
class Help_model extends CI_Model{
    
    function __construct()
    {
        parent :: __construct();
    }

    /**
    * get all videos
    * @return array
    */
    public function get_all()
    {
        $this->db->select('*')->from('help_videos');
        $this->db->order_by('order', 'ASC');
        return $this->db->get()->result();
    }

    /**
    * get all videos
    * @return array
    */
    public function get_quick_setup()
    {
        $this->db->select('*')->from('help_videos');
        $this->db->order_by('order', 'ASC');
        $this->db->limit(1);
        return $this->db->get()->row();
    }
    
}

/* End of file Help_model.php */
/* Location: ./application/models/Help_model.php */