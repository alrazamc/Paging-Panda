<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Common function used in application
*/

/**
* get HTMl string for different types of alert messages
* @param $message, string, The message to be shown to users
* @param $type CONSTANT, The type of alert, info, error, warning, success
* @return string html string
*/
function get_alert_html($message='', $type = ALERT_TYPE_INFO, $dismiss = true)
{
    if($type == ALERT_TYPE_INFO)
        $icon = "fa-info-circle";
    else if($type == ALERT_TYPE_ERROR || $type == ALERT_TYPE_WARNING)
        $icon = 'fa-exclamation-triangle';
    else if($type == ALERT_TYPE_SUCCESS)
        $icon = 'fa-check';
    else if($type == ALERT_TYPE_ANNOUNCEMENT)
        $icon = 'fa-bullhorn';
    if($dismiss)
        $button = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">×</span>
            </button>";
    else
        $button = '';
    return "<div class=\"alert alert-$type\">
            $button
            <i class=\"fas $icon\"></i> 
            $message
            </div>";
}

/**
* check if any user or admin is logged in application
* @return void
*/
function session_check()
{
    $CI = get_instance();
    $user_id = $CI->session->userdata('user_id');
    if(empty($user_id)){
        if($CI->input->is_ajax_request()){
            $response =  array('type'=>AJAX_RESPONSE_TYPE_REDIRECT, 'message'=> site_url('users/login') );
            echo json_encode($response);
            exit();
        }
        redirect('users/login');
    }
}



/**
* get facebook api call error in single string
* @param $error_response array, The facebook api response if error occured
* @return string
*/
function get_fb_request_error_string($error_response){
    $message = 'Error Code: '.$error_response['error']['code'].'<br>';
    $message .= 'Type: '.$error_response['error']['type'].'<br>';
    $message .= 'Message: '.$error_response['error']['message'];
    return $message;
}
/**
* display facebook api call error in html table format while adding new facebook app
* @param $response array, The facebook api response if error occured
* @return html table string
*/
function get_fb_response_as_html_table($response){
    $html = '<table>';
    foreach ($response as $key => $value) {
       $html .= '<tr>';
       $html .= '<td>';
       $html .= $key;
       $html .= '</td>';
       $html .= '<td>';
       $html .= $value;
       $html .= '</td>';
       $html .= '</tr>';
    }
    $html .= '</table>';
    return $html;
}

function curl_browser_request($url){
    $agent= 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_URL,$url);
    $result=curl_exec($ch);
    curl_close( $ch );
    return $result;
}

/**
* create a writable folder
* @param $directory_path string, directory to be created
* @return void
*/

function create_folder($directory_path = '')
{
    if(is_dir($directory_path))
        return true;
    else
      return mkdir($directory_path, 0777, true);
}

/**
* create session if remember me set
*/
function check_remember_me(){
    include_once(APPPATH.'hooks/AppLoader.php');
    $app_loader = new AppLoader();
    $app_loader->remember_me();
}

function convert_to_utc($input_date_time)
{
    $ci = get_instance();
    date_default_timezone_set($ci->session->userdata('time_zone'));
    $datetime = new DateTime($input_date_time);
    $utc = new DateTimeZone('UTC');
    $datetime->setTimezone($utc);
    date_default_timezone_set('UTC');
    return $datetime->getTimestamp();
}

function convert_to_user_timezone($input_date_time, $time_zone = '')
{
    $ci = get_instance();
    $time_zone = empty($time_zone) ? $ci->session->userdata('time_zone') : $time_zone;
    date_default_timezone_set('UTC');
    $datetime = new DateTime($input_date_time);
    date_default_timezone_set($time_zone);
    return $datetime->getTimestamp();
}

function get_mime($file_name)
{
    if(empty($file_name)) return FALSE;
    $mimes = array(
        'png' => 'image/png',
        'gif' => 'image/gif',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jpe' => 'image/jpeg',
        'mp4' => 'video/mp4',
        'm4a' => 'video/mp4',
        'm4p' => 'video/mp4',
        'm4b' => 'video/mp4',
        'm4r' => 'video/mp4',
        'm4v' => 'video/mp4',
        'mov' => 'video/quicktime',
        'qt' => 'video/quicktime'
    );
    $extension = strtolower(substr(strrchr($file_name, '.'), 1));
    return isset($mimes[$extension]) ? $mimes[$extension] : FALSE;
}

function rebuild_queue($user_id)
{
    start_background_process("worker rebuild_queue $user_id");
}

function rebuild_category_queue($user_id, $category_id = 0, $rebuild_random = 0)
{
    start_background_process("worker rebuild_category_queue $user_id $category_id $rebuild_random");
}

function rebuild_random_queue($user_id)
{
    start_background_process("worker rebuild_random_queue $user_id");
}

function read_rss_feed($feed_id)
{
    start_background_process("worker read_rss $feed_id");
}

function delete_s3_media($user_id, $category_id = 0)
{
    start_background_process("worker delete_s3_media $user_id $category_id");
}

function start_background_process($arguments) {
    $index_path = getcwd().DIRECTORY_SEPARATOR."index.php";
    $command = "php \"$index_path\" $arguments"; 
    if (substr(php_uname(), 0, 7) == "Windows"){
         pclose(popen("start /B ". $command, "r"));  
    } 
    else { 
        exec($command . " > /dev/null &");   
    } 
} 

function get_random_order()
{
    return mt_rand(1, getenv('RANDOM_ORDER_LIMIT'));
}