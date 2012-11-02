<?php
/**
 * PHP wrapper for Hello Sign API 
 * 
 * 
 * @author Anthony Gentile <asgentile@gmail.com>
 */
namespace HelloSign;

class API {
    protected $_api_key;
    protected $_email;
    protected $_password;
    
    
    public $last_response;
    
    public $use_curl = true;
    
    public function __construct($email, $password, $api_key = null)
    {
        $this->setEmail($email);
        $this->setPassword($password);
        $this->setApiKey($api_key);
    }
    
    public function setEmail($email)
    {
        $this->_email = $email;
    }
    
    public function getEmail($email)
    {
        return $this->_email;
    }
    
    public function setPassword($password)
    {
        $this->_password = $password;
    }
    
    public function getPassword($password)
    {
        return $this->_password;
    }
    
    public function setApiKey($api_key)
    {
        $this->_api_key = $api_key;
    }
    
    public function getApiKey($api_key)
    {
        return $this->_api_key;
    }
    
    /**
     * Generic API accessor
     */
    public function api($uri, $args = array(), $method = 'GET')
    {
        $request = new \HelloSign\Request();
        $request->setCredentials($this->_email, $this->_password);

        if ($this->use_curl) {
            $response = $request->fetchCurl($uri, $args, $method);
        } else {
            $response = $request->fetch($uri, $args, $method);
        }
        $this->last_response = $response;
        return $response->getResponse();
    }
}

class Request {
    protected $_email;
    protected $_password;
    protected $_api_end_point = 'https://api.hellosign.com/v3/';
    
    public function setCredentials($email, $password)
    {
        $this->_email = $email;
        $this->_password = $password;
    }
    
    public function fetchCurl($uri, $args = array(), $method = 'GET', $headers = array("Content-Type: multipart/form-data"))
    {
        // Add auth headers
        $auth = base64_encode($this->_email . ':'. $this->_password);
        $auth_header = "Authorization: Basic $auth";
        $headers[] = $auth_header;
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, 'HelloSign-PHP');
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);

        curl_setopt($curl, CURLOPT_URL, $this->_api_end_point . trim($uri, '/'));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        
        if (strtolower($method) == 'post') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
        }
                    
        $response = curl_exec($curl);

        if (!$response) {
            throw new \HelloSign\Exception("Problem connecting to HelloSign.");
        }
        
        @curl_close($curl);
        
        return new \HelloSign\Response($response);
    }
    
    /**
     * Use PHP streams to fetch from API end point.
     * 
     */
    public function fetch($uri, $args = array(), $method = 'GET', $headers = "Content-Type: multipart/form-data\r\n")
    {
        $opts = array(
            'http' => array(
                'method' => strtoupper($method),
            )
        );
        
        if (strtolower($method) == 'post') {
            $opts['http']['content'] = http_build_query($args);
        }
        
        if ($headers) {
            $opts['http']['header'] = $headers;
        }
        
        // Add auth headers
        $auth = base64_encode($this->_email . ':'. $this->_password);
        $header = "Authorization: Basic $auth";
        $opts['http']['header'] = isset($opts['http']['header']) ? $opts['http']['header'] . $header : $header;

        $context = stream_context_create($opts);

        $fp = fopen($this->_api_end_point . trim($uri, '/'), 'rb', FALSE, $context);
        if (!$fp) {
            throw new \HelloSign\Exception("Problem connecting to HelloSign.");
        }
        
        $response = @stream_get_contents($fp);

        if ($response === FALSE) {
            throw new \HelloSign\Exception("Problem reading data from HelloSign");
        }
        return new \HelloSign\Response($response);
    }
}

class Response {
    protected $_response;
    
    public function __construct($response)
    {
        $json = json_decode($response);
        if ($json === null) {
            throw new \HelloSign\Exception("Invalid JSON.");
        }
        $this->_response = $json;
    }
    
    public function getResponse()
    {
        return $this->_response;
    }
    
    public function getError()
    {
        if (!isset($this->_response->error)) {
            return false;
        }
        
        return $this->_response->error->error_name . ' : ' . $this->_response->error->error_msg;
    }
}


/**
 *
 * Hello Sign API Exception
 *
 */
class Exception extends \Exception {
    
}
