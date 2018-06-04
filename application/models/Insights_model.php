<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Insights_model Class
 * 
 * @package   PHP_FMT
 * @subpackage  Models
 * @category  Insights_model
 */
class Insights_model extends CI_Model{
    
    function __construct()
    {
        parent :: __construct();
    }
    
    /**
    * get all accounts of user
    * @return object
    */
    public function get_favorite_metrics($user_id = 0)
    {
        $this->db->select('metric_id, metric_order');
        $this->db->where('user_id', $user_id);
        $this->db->order_by('metric_order ASC');
        return $this->db->get('favorite_metrics')->result();
    }

    /**
    * get all available insights metrics
    * @return object
    */
    public function get_favorite_metric_details($user_id = 0)
    {
        $this->db->select('*')->from('insights_metrics');
        $this->db->join('favorite_metrics', 'favorite_metrics.metric_id=insights_metrics.metric_id', 'inner');
        $this->db->where('favorite_metrics.user_id', $user_id);
        $this->db->where('metric_type', METRIC_TYPE_PAGE);
        $this->db->order_by('metric_order ASC');
        return $this->db->get()->result();
    }

    /**
    * get all available insights metrics
    * @return object
    */
    public function get_all_metrics()
    {
        $this->db->select('*')->from('insights_metrics');
        $this->db->where('metric_type', METRIC_TYPE_PAGE);
        $this->db->order_by('metric_id ASC');
        return $this->db->get()->result();
    }

    /**
    * update favorite metrics of user
    * @return object
    */
    public function update_favorite_metrics($user_id = 0, $metrics = array())
    {
        $this->db->where('user_id', $user_id);
        $this->db->delete('favorite_metrics');
        $batch = array();
        foreach ($metrics as $index => $metric_id)
            $batch[] = array(
                'user_id' => $user_id,
                'metric_id' => $metric_id,
                'metric_order' => $index + 1
            );

        if(!empty($batch))
            $this->db->insert_batch('favorite_metrics', $batch);
    }
}

/* End of file Insights_model.php */
/* Location: ./application/models/Insights_model.php */