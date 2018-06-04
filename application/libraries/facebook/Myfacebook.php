<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Facebook Class
 */
 
class Myfacebook {
    var $fb;
    var $helper;
    var $access_token;
    var $api_version ;

    public function __construct()
    {
        $this->init();
    }
    /**
    * init Facebook SDK
    * @param $app_id string, facebook id string of app
    * @param $app_secret string, facebook secret of the app
    * @return void
    */
    public function init()
    {   

        $config = array(
            'app_id' => getenv('FB_APP_ID'),
            'app_secret' => getenv('FB_APP_SECRET')
        );
        $this->fb = new Facebook\Facebook($config);
    }
    /**
    * get Login url for redirect to Facebook
    * @return string , Login URL
    */
    public function get_login_url($redirect_url)
    {
        $this->ci =& get_instance();
        $this->permissions = $this->ci->config->item('facebook', 'scope');

        if($this->fb)
        {
            $this->helper = $this->fb->getRedirectLoginHelper();
            $loginUrl = $this->helper->getLoginUrl($redirect_url, $this->permissions);
            return $loginUrl;
        }
    }
    /**
    * get access token from Facebook for login into script
    * @return boolean
    */
    public function get_access_token()
    {
        if($this->fb)
        {
            $this->helper = $this->fb->getRedirectLoginHelper();

            try {
              $accessToken = $this->helper->getAccessToken();
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
              // When Graph returns an error
              return $e->getMessage();
              
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
              // When validation fails or other local issues
              return $e->getMessage();
            }

            if(isset($accessToken))
            {
                $this->access_token = $accessToken->getValue();
                return true;
            }
        }
    }

    /**
    * get user profile for login into script
    * @return string , User data
    */
    public function get_user_profile()
    {
        if($this->fb && $this->access_token)
        {
            try {
              // Returns a `Facebook\FacebookResponse` object
              $response = $this->fb->get('/me?fields=id,name, first_name, email,picture.type(large)', $this->access_token);
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
              return $e->getMessage();
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                return  $e->getMessage();
            }

            $user = $response->getGraphUser();
            return $user;
        }
    }

    /**
    * get facebook app details to validate a app before adding it to database
    * @return object
    */
    public function get_app_details()
    {
        $this->set_token($this->fb->getApp()->getAccessToken()->getValue());
        return $this->get_request('/app');
    }

    /**
    * return access token
    * @return string
    */
    public function get_token()
    {
        return $this->access_token;
    }

    /**
    * set access token
    * @return void
    */
    public function set_token($token)
    {
        return $this->access_token = $token;
    }
    /**
    * make get request to graph api
    * @return array
    */
    public function get_request($edge)
    {
        try
        {
          $response = $this->fb->get($edge, $this->access_token);
          return $response->getGraphObject()->asArray();
        } catch(Facebook\Exceptions\FacebookResponseException $e) 
        {
            return $e->getResponse()->getGraphObject()->asArray();
        } catch(Facebook\Exceptions\FacebookSDKException $e)
        {
            return  $this->format_facebook_SDK_exception($e);
        }
    }
    /**
    * make post request to graph api
    * @return array
    */
    public function post_request($edge, $post_params)
    {
        try
        {
          $response = $this->fb->post($edge, $post_params, $this->access_token);
          return $response->getGraphObject()->asArray();
        } catch(Facebook\Exceptions\FacebookResponseException $e) 
        {
            return $e->getResponse()->getGraphObject()->asArray();
        } catch(Facebook\Exceptions\FacebookSDKException $e)
        {
            return  $this->format_facebook_SDK_exception($e);
        }
    }
    /**
    * debug access token
    * @return array
    */
    public function debug_token($token = '')
    {
        return $this->get_request('/debug_token?input_token='.$token);
    }


    /**
    * Format Facebook SDK exception object
    */
    public function format_facebook_SDK_exception($e)
    {
        $err['error']['message'] = $e->getMessage();
        $err['error']['code'] = $e->getCode();
        $err['error']['type'] = 'FacebookSDKException';
        return  $err;
    }
    
    /**
    * get user profile data
    * @return array api call response
    */
    public function get_profile()
    {
        try
        {
          $response = $this->fb->get('/me?fields=id,name', $this->access_token);
          return $response->getGraphObject()->asArray();
        } catch(Facebook\Exceptions\FacebookResponseException $e) 
        {
            return $e->getResponse()->getGraphObject()->asArray();
        } catch(Facebook\Exceptions\FacebookSDKException $e)
        {
            return  $this->format_facebook_SDK_exception($e);
        }  
    }

    /**
    * get mange/owned pages from facebook account 
    * @param $limit integer, application limit to retrive n records from facebooks
    * @return array api call response
    */
    public function get_manage_pages($before = '', $after = '')
    {
        $q = '&';
        if($before)
            $q .= "before=$before&";
        if($after)
            $q .= "after=$after";
        try
        {
          $response = $this->fb->get('/me/accounts?fields=access_token,category,name,id,perms,fan_count,picture&limit=100'.$q, $this->access_token);
          return $response->getGraphEdge();
        } catch(Facebook\Exceptions\FacebookResponseException $e) 
        {
            return $e->getResponse()->getGraphObject()->asArray();
        } catch(Facebook\Exceptions\FacebookSDKException $e)
        {
            return  $this->format_facebook_SDK_exception($e);
        }
    }


    /**
    * get a facebook post fresh insights from facebook i.e. likes, coments, shares
    */
    public function publish_batch($posts)
    {
        $this->fb->setDefaultAccessToken($this->fb->getApp()->getAccessToken());
        $request_batch = array();
        foreach ($posts as $post)
        {
            $params = array();
            if($post->post_type == POST_TYPE_TEXT)
            {
                $edge = 'feed';
                $params['message'] = $post->message;
            }else if($post->post_type == POST_TYPE_LINK)
            {
                $edge = 'feed';
                $params['link'] = $post->link_url;
                $messsage = preg_replace('/\bhttps?:\/\/\S+\s*(\r\n|\r|\n)?/', "", $post->message, 1); //replace first URL with optional trailing space or new line
                $messsage = trim($messsage);
                if(!empty($messsage))
                    $params['message'] = $messsage;
            }else if($post->post_type == POST_TYPE_VIDEO)
            {   
                $edge = 'videos';
                if(!empty($post->message))
                    $params['description'] = $post->message;
                $params['file_url'] = $post->attachments[0];
            }else if($post->post_type == POST_TYPE_PHOTO)
            {
                if(count($post->attachments) <= 1) //single photo
                {
                    $edge = 'photos';
                    if(!empty($post->message))
                        $params['caption'] = $post->message;
                    $params['url'] = $post->attachments[0];
                }else
                {
                    $edge = 'photos';
                    foreach ($post->attachments as $index => $attachment_url)
                    {
                        $params['url'] = $attachment_url;
                        $params['published'] = false;
                        $request_batch["p$post->post_id-a$index"] = $this->fb->request('POST', "/$post->account_fb_id/$edge", $params, $post->access_token);
                    }
                    $params = array();
                    $edge = 'feed';
                    if(!empty($post->message))
                        $params['message'] = $post->message;
                    $params['attached_media'] = array();
                    foreach ($post->attachments as $index => $attachment_url)
                        $params['attached_media'][$index]['media_fbid'] = "{result=p$post->post_id-a$index:$.id}";
                }
            }
            $request_batch["p$post->post_id"] = $this->fb->request('POST', "/$post->account_fb_id/$edge", $params, $post->access_token);
        }
        try
        {
          $result = $this->fb->sendBatchRequest($request_batch);
          if (!is_object($result) || $result->isError()) 
                    return $this->set_error_on_batch($posts, $result->getDecodedBody());
          $responses = $result->getResponses();
          foreach($posts as $post)
          {
            if(!isset($responses["p$post->post_id"]))
            {
                $post->status = POST_STATUS_ERROR;
                $post->error_message = json_encode(array('message' => "No response from API"));
                continue;
            }
            $response = $responses["p$post->post_id"]->getDecodedBody();
            if(isset($response['error']))
            {
                $error = $response['error'];
                $post->status = POST_STATUS_ERROR;
                $post->error_message = json_encode($error);
                if((isset($error['type']) && strtolower($error['type']) == strtolower('OAuthException')) || in_array($error['code'], array(102,10,190, 'OAuthException') ))
                    $post->token_expired = YES;
                continue;
            }else
            {
                $post->status = POST_STATUS_PUBLISHED;
                $post->published_time = date('Y-m-d H:i:s');
                $post->post_fb_id = isset($response['post_id']) ? $response['post_id'] : $response['id'];
            }
          }
          return $posts;
        }catch(Facebook\Exceptions\FacebookResponseException $e) 
        {
            $response['error']['message'] = $e->getMessage();
            return $this->set_error_on_batch($posts, $response);
            //return $e->getResponse()->getGraphObject()->asArray();
        } catch(Facebook\Exceptions\FacebookSDKException $e)
        {
            $response['error']['message'] = $e->getMessage();
            return $this->set_error_on_batch($posts, $response);
            // return  $this->format_facebook_SDK_exception($e);
        } 
    }

    public function set_error_on_batch($posts, $response)
    {
        $error_message = json_encode($response['error']);
        foreach ($posts as $post)
        {
            $post->status = POST_STATUS_ERROR;
            $post->error_message = $error_message;
        }
        return $posts;
    }
           
    public function next($edge)
    {
        $this->fb->next($edge);
    }
}