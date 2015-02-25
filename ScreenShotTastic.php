<?php

class ScreenShotTastic {
    
    private $username;
    private $password;
    
    private $timeout = 5;
    
    private $api_url = 'http://api.screenshottastic.com/api/';
    private $version = 'v1/';
    
    public $token;
    public $user;
    
    /*
     * Instantiates the class
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        
        $data = array(
            'username' => $username,
            'password' => $password
        );
        
    }
    
    /*
     * Authorizes the user
     * @returns: token
     */
    public function authorize()
    {
        $url = 'authorize';
        
        $ch = curl_init($this->api_url . $this->version . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);

        $data = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if($status != 200)
        {
            return $data;
        }
        
        $this->token = $data;
        return $this->token;
        return $data;
    }
    
    /*
     * Function that makes the api call
     * @parameters: $url - the api url
     *      $method - GET, POST, DELETE, UPDATE
     *      $data - array of data to be passed
     * @returns: resource
     */
    public function make_api_call($url, $data = array(), $method = 'GET')
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Auth-Token: ' . $this->token));

        $postfields = $this->stringify($data);
        $postfields .= 'auth_token=' . $this->token;
        
        if($method == 'POST')
        {
            curl_setopt($ch, CURLOPT_URL, $this->api_url . $this->version . $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        }else
        {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_URL, $this->api_url . $this->version . $url . '?' . $postfields);
        }
        
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if($status != 200)
        {
            return $result;
        }
        
        return $result;
    }
    
    /*
     * Set the current auth token
     */
    public function set_token($token)
    {
        $this->token = $token;
        
        // test if token is valid
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url . $this->version . 'handshake?auth_token=' . $this->token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);

        $token = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // provided token is valid, so we return it
        if($status == 200)
        {
            return $this->token;
        }
        
        // provided token is no longer valid, so we create a new one
        return $this->authorize();
    }
    
    /*
     * Retrieves the current auth token
     * @returns: token
     */
    public function get_token()
    {
        if($this->token)
        {
            return $this->token;
        }
        
        return 'Not authorized';
    }
    
    /*
     * Converts an array into a url-type string
     * @parameter: array
     * @returns: string
     */
    private function stringify($data = array())
    {
        $string = '';
        foreach($data as $key => $value)
        {
            if($key == 'css')
            {
                foreach($value as $css)
                {
                    $string .= 'css%5B%5D=' . $css . '&';
                }
            }else
            {
                $string .= $key . '=' . $value . '&';
            }
        }
        
        return $string;
    }
}
?>