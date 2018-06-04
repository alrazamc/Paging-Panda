<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Schedule_model Class
 * 
 * @package   PHP_FMT
 * @subpackage  Models
 * @category  Schedule_model
 */
class Schedule_model extends CI_Model{
    
    function __construct()
    {
        parent :: __construct();
    }
    /**
    * get all available insights metrics
    * @return object
    */
    public function get_list($user_id = 0)
    {
        $this->db->select('schedule.*, array_to_json(accounts) as accounts')->from('schedule');
        $this->db->where('user_id', $user_id);
        return $this->db->get()->result();
    }

    /**
    * insert new category
    * @return object
    */
    public function insert($user_id = 0, $slot)
    {
        $record = array(
            'user_id' => $user_id,
            'day' => $slot['day'],
            'time' => $slot['time'],
            'category_id' => $slot['category_id'],
            'use_once' => $slot['use_once'],
            'use_random' => $slot['use_random'],
            'accounts' => '{'.implode(',', $slot['accounts']).'}'
        );
        $this->db->insert('schedule', $record);
        return $this->db->insert_id();
    }

    /**
    * update category
    * @return object
    */
    public function update($category_id = 0, $slot)
    {
        $record = array(
            'day' => $slot['day'],
            'time' => $slot['time'],
            'category_id' => $slot['category_id'],
            'use_once' => $slot['use_once'],
            'use_random' => $slot['use_random'],
            'accounts' => '{'.implode(',', $slot['accounts']).'}'
        );
        $this->db->where('schedule_id', $slot['schedule_id']);
        $this->db->update('schedule', $record);
    }

    /**
    * delete category
    * @return object
    */
    public function delete($schedule_id = 0)
    {
        $this->db->where('schedule_id', $schedule_id);
        $this->db->delete('schedule');
    }

    /**
    * get single record
    * @return object
    */
    public function get_record($schedule_id = 0)
    {
        return $this->db->get_where('schedule', array('schedule_id' => $schedule_id))->row();
    }
}

/* End of file Schedule_model.php */
/* Location: ./application/models/Schedule_model.php */