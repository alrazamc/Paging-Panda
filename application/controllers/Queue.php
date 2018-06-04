<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Queue Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Queue
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Queue extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('queue_model');
    }

}

/* End of file Queue.php */
/* Location: ./application/controllers/Queue.php */
