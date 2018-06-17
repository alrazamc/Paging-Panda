<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

define('FACEBOOK_SDK_SRC_DIR', APPPATH.'libraries/facebook/Facebook/');

define('ALERT_TYPE_ERROR', 'danger');
define('ALERT_TYPE_SUCCESS', 'success');
define('ALERT_TYPE_INFO', 'info');
define('ALERT_TYPE_WARNING', 'warning');
define('ALERT_TYPE_ANNOUNCEMENT', 'info');

define('USER_STATUS_SUBSCRIBED', 1); //added
define('USER_STATUS_ACTIVE', 2); // active user
define('USER_STATUS_SUSPENDED', 3);
define('USER_STATUS_CANCELLED', 4);

//AJAX response type
define('AJAX_RESPONSE_TYPE_REDIRECT', 0);
define('AJAX_RESPONSE_TYPE_SUCCESS', 1);
define('AJAX_RESPONSE_TYPE_ERROR', 2);

//Account Types
define('ACCOUNT_TYPE_FB_PAGE', 1);


//Boolean flags
define('NO', 0);
define('YES', 1);

define('OFF', 0);
define('ON', 1);

//Graph API URL
define('GRAPH_API_URL', 'https://graph.facebook.com/');
//Graph API enum for editor role
define('FB_POST_PERMISSION', 'CREATE_CONTENT');


//metric Types
define('METRIC_TYPE_PAGE', 1);
define('METRIC_TYPE_POST', 2);

//graph Type
define('GRAPH_TYPE_LINE', 1);
define('GRAPH_TYPE_MULTI_LINE', 2);
define('GRAPH_TYPE_BAR', 3);
define('GRAPH_TYPE_PIE', 4);

//Post Types
define('POST_TYPE_TEXT', 1);
define('POST_TYPE_LINK', 2);
define('POST_TYPE_PHOTO', 3);
define('POST_TYPE_VIDEO', 4);

define('CONTENT_MODERATION_PENDING', 1);
define('CONTENT_MODERATION_APPROVED', 2);
define('CONTENT_MODERATION_DECLINED', 3);


define('POST_STATUS_PENDING', 1);
define('POST_STATUS_PUBLISHED', 2);
define('POST_STATUS_ERROR', 3);

define("CATEGORY_GENERAL", '0');
define("CATEGORY_USE_ONCE", '-1');
define("CATEGORY_RANDOM", '-2');


define("REBUILD_RANDOM_YES", 1);
define("REBUILD_RANDOM_NO", 0);

define('CRON_QUEUE_PROCESSOR', 1);
define('CRON_QUEUE_BATCH_PROCESSOR', 2);
define('CRON_DAILY_UPDATE', 3);

//Transaction Types
define('PAYMENT_TYPE_NEW_SUBSCRIPTION', 1);
define('PAYMENT_TYPE_CHANGE_OF_PLAN', 2);
define('PAYMENT_TYPE_SUSPENDED_REACTIVATION', 3);
define('PAYMENT_TYPE_CLOSED_REACTIVATION', 4);
define('PAYMENT_TYPE_INSTALLMENT', 5);

define('TRANS_TYPE_CREDIT', 1);
define('TRANS_TYPE_PARTIAL_REFUND', 2);
define('TRANS_TYPE_FULL_REFUND', 3);

define("RECUR_STOP_REASON_CHANGE_OF_PLAN", 1);
define("RECUR_STOP_REASON_ACCOUNT_CLOSED", 2);
define("RECUR_STOP_REASON_BILLING_FAILED", 3);
define("RECUR_STOP_REASON_EXTERNAL", 4); //stopped by event outside the system, buyer stopped 