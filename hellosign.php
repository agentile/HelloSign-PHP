<?php
/**
 * PHP wrapper for Hello Sign API 
 * 
 * NOTE: Hello Sign API is in its infancy, expect
 * changes.
 * 
 * @link https://github.com/agentile/HelloSign-PHP
 * @version 0.0.3
 * @author Anthony Gentile <asgentile@gmail.com>
 */
namespace HelloSign;

class API {

    /**
     * Hello Sign API Key
     */
    protected $_api_key;
    
    /**
     * Hello Sign Credentials: E-mail Address
     */
    protected $_email;
    
    /**
     * Hello Sign Credentials: Password
     */
    protected $_password;
    
    /**
     * Last response of an api() method call.
     */
    public $last_response;
    
    /**
     * Use cURL?
     */
    public static $use_curl = true;
    
    /**
     * Constructor!
     * 
     * @param $email
     * @param $password
     * @param $api_key
     * 
     * @return null
     */
    public function __construct($email, $password, $api_key = null)
    {
        $this->setEmail($email);
        $this->setPassword($password);
        $this->setApiKey($api_key);
    }
    
    /**
     * E-mail setter
     *
     * @param $email
     *
     * @return null
     */
    public function setEmail($email)
    {
        $this->_email = $email;
    }
    
    /**
     * E-mail getter
     *
     * @return mixed
     */
    public function getEmail($email)
    {
        return $this->_email;
    }
    
    /**
     * Password setter
     *
     * @param $password
     *
     * @return null
     */
    public function setPassword($password)
    {
        $this->_password = $password;
    }
    
    /**
     * Password getter
     *
     * @return mixed
     */
    public function getPassword($password)
    {
        return $this->_password;
    }
    
    /**
     * API Key setter
     *
     * @param $api_key
     *
     * @return null
     */
    public function setApiKey($api_key)
    {
        $this->_api_key = $api_key;
    }
    
    /**
     * API Key getter
     *
     * @return mixed
     */
    public function getApiKey($api_key)
    {
        return $this->_api_key;
    }
    
    /**
     * Generic API accessor
     * 
     * @param $uri API uri action e.g. 'account'
     * @param $args 
     * @param $method GET/POST
     * 
     * @return \HelloSign\Response object
     */
    public function api($uri, $args = array(), $method = 'GET')
    {
        $request = new \HelloSign\Request();
        $request->setCredentials($this->_email, $this->_password);

        if (self::$use_curl) {
            $response = $request->fetchWithCurl($uri, $args, $method);
        } else {
            $response = $request->fetch($uri, $args, $method);
        }
        $this->last_response = $response;
        return $response;
    }
}

/**
 *
 * Request Object
 *
 */
class Request {
    
    /**
     * Credentials: E-mail Address
     */
    protected $_email;
    
    /**
     * Credentials: Password
     */
    protected $_password;
    
    /**
     * HelloSign API end point url
     */
    protected $_api_end_point = 'https://api.hellosign.com/v3/';
    
    /**
     * Connection time out in seconds
     */
    public static $time_out = 120; // seconds
    
    /**
     * Set credentials for request
     *
     * @param $email
     * @param $password
     *
     * @return null
     */
    public function setCredentials($email, $password)
    {
        $this->_email = $email;
        $this->_password = $password;
    }
    
    /**
     * Make request to HelloSign API using cURL
     * 
     * @param $uri API uri action e.g. 'account'
     * @param $args 
     * @param $method GET/POST
     * @param $headers array headers to send with request.
     * 
     * @return mixed \HelloSign\Response object or \HelloSign\Exception object
     */
    public function fetchWithCurl($uri, $args = array(), $method = 'GET', $headers = array("Content-Type: multipart/form-data"))
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
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::$time_out); 
        
        if (strtolower($method) == 'post') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
        }
                    
        $response = curl_exec($curl);

        if (!$response) {
            throw new \HelloSign\Exception("Problem connecting to HelloSign.");
        }
        
        // Get the status code
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        @curl_close($curl);
        
        return new \HelloSign\Response($response, $http_status);
    }
    
    /**
     * Make request to HelloSign API using PHP Streams
     * 
     * TODO: Currently HelloSign API doesn't like POST requests using
     * PHP streams. States that there are bad params, even when legit.
     * Talk to HelloSign and figure this out.
     * 
     * @param $uri API uri action e.g. 'account'
     * @param $args 
     * @param $method GET/POST
     * @param $headers array headers to send with request.
     * 
     * @return mixed \HelloSign\Response object or \HelloSign\Exception object
     */
    public function fetch($uri, $args = array(), $method = 'GET', $headers = array("Content-Type: multipart/form-data"))
    {
        $headers = array_merge($headers, array("User-Agent: HelloSign-PHP"));
        $opts = array(
            'http' => array(
                'method' => strtoupper($method),
                'request_fulluri' => true,
                'timeout' => self::$time_out,
                'ignore_errors' => true,
            )
        );
        
        if (strtolower($method) == 'post') {
            $opts['http']['content'] = http_build_query($args, '', '&');
            $headers = array_merge($headers, array("Content-Length: " . strlen($opts['http']['content'])));
        }
        
        // Add auth headers
        $auth = base64_encode($this->_email . ':'. $this->_password);
        $headers = array_merge($headers, array("Authorization: Basic $auth"));
        
        if ($headers) {
            $opts['http']['header'] = implode("\r\n", $headers) . "\r\n";
        }

        $context = stream_context_create($opts);

        $fp = fopen($this->_api_end_point . trim($uri, '/'), 'rb', FALSE, $context);
        
        // Get the status code
        preg_match("/\d{3}/", $http_response_header[0], $matches);
        $http_status = isset($matches[0]) ? (int) $matches[0] : null;
        
        if (!$fp) {
            throw new \HelloSign\Exception("Problem connecting to HelloSign.");
        }
        
        $response = @stream_get_contents($fp);

        if ($response === FALSE) {
            throw new \HelloSign\Exception("Problem reading data from HelloSign");
        }
        return new \HelloSign\Response($response, $http_status);
    }
}

/**
 *
 * Response Object
 *
 */
class Response {
    
    /**
     * Decoded JSON response array
     */
    protected $_response;
    
    /**
     * HTTP status code
     */
    protected $_status_code;
    
    /**
     * Constructor!
     * 
     * @param $response
     * @param $status_code
     * 
     * @return mixed
     */
    public function __construct($response, $status_code = null)
    {
        $json = json_decode($response);
        
        if ($json === null) {
            throw new \HelloSign\Exception("Invalid JSON.");
        }
        
        $this->_response = $json;
        $this->_status_code = $status_code;
    }
    
    /**
     * Does this response contain a HelloSign API error?
     * 
     * @return null
     */
    public function containsError()
    {
        if (isset($this->_response->error)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * HTTP status code getter
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->_status_code;
    }
    
    /**
     * Response array getter
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->_response;
    }
    
    /**
     * Combine error info into useful error message.
     *
     * @return mixed false if no error, string otherwise
     */
    public function getError()
    {
        if (!$this->containsError()) {
            return false;
        }
        
        if ($this->getStatusCode()) {
            $error = array($this->getStatusCode(), $this->_response->error->error_name, $this->_response->error->error_msg);
        } else {
            $error = array($this->_response->error->error_name, $this->_response->error->error_msg);
        }
        
        $error = implode(' : ', $error);

        if (php_sapi_name() == 'cli') {
            $error .= "\n";
        }
        
        return $error;
    }
}


/**
 *
 * Hello Sign API Exception
 *
 */
class Exception extends \Exception {
    
}
