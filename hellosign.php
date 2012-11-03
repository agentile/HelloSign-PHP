<?php
/**
 * PHP wrapper for Hello Sign API 
 * 
 * NOTE: Hello Sign API is in its infancy, expect
 * changes as from making this API I am seeing things 
 * that they should rework/rethink.
 * 
 * Examples:
 * - Using 0 based index for signers, but 1 based index for files. Consistency please.
 * - Creating user account instead of invitations. Really?
 *   - Results in vague 'emma' e-mails talking about hellofax
 *   - This should be account/invite that gives back a token/statement that account exists.
 *     Invitation to the email address to create an account with a password that THEY set.
 * - Some poor semantics for API e.g. /team/destroy vs /team/delete, add_member vs add_user (consistency),
 *   cc_email_addresses vs ccs
 * - Odd API argument ordering e.g. /reusable_form/add_user/[:reusable_form_id] 
 *   This can cause confusing, thinking that the form_id is the user id.
 *   Something more along the lines of /reusable_form/[:reusable_form_id]/add_user/[:user_id_or_email] 
 *   would seem more logical.
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
        $url = $this->_api_end_point . trim($uri, '/');
        
        // Add auth headers
        $auth = base64_encode($this->_email . ':'. $this->_password);
        $auth_header = "Authorization: Basic $auth";
        $headers[] = $auth_header;
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, 'HelloSign-PHP');
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        
        if (strtolower($method) == 'post') {
            // arguments we need to collapse
            $multidimensional = array(
                'file' => 1,
                'signers' => 0,
                'ccs' => 0,
                'cc_email_addresses' => 0,
                'custom_fields' => 0,
            );
            
            foreach ($multidimensional as $key => $index) {
                $args = $this->collapseArgs($args, $key, $index);
            }

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
        } else if (strtolower($method) == 'get' && !empty($args)) {
            $url .= '?' . http_build_query($args);
        }
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::$time_out); 
                    
        $response = curl_exec($curl);
        
        // Get the status code
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        if (!$response && $http_status != 200) {
            throw new \HelloSign\Exception("Problem connecting to HelloSign.");
        }
        
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
        $url = $this->_api_end_point . trim($uri, '/');
        
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
        } else if (strtolower($method) == 'get' && !empty($args)) {
            $url .= '?' . http_build_query($args);
        }
        
        // Add auth headers
        $auth = base64_encode($this->_email . ':'. $this->_password);
        $headers = array_merge($headers, array("Authorization: Basic $auth"));
        
        if ($headers) {
            $opts['http']['header'] = implode("\r\n", $headers) . "\r\n";
        }

        $context = stream_context_create($opts);

        $fp = fopen($url, 'rb', FALSE, $context);
        
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
    
    /**
     * Turn an array into the the fields cURL will expect for POST data
     * TODO: Rework this to be smarter using some lambda funcs.
     * 
     * e.g. 
     * array('signers' => 
     *    array(
     *        array(
     *          'name' => 'Joe',
     *          'email_address' => 'joe@example.com',
     *          'order' => 0,
     *        )
     *    )
     * )
     * 
     * turns into
     * 
     * signers[0][name] = Joe
     * signers[0][email_address] = joe@example.com
     * signers[0][order] = 0
     * 
     * @param $args
     * @param $key
     * @param $index we have this because HelloSign flip flops starting indices. (0 or 1)
     * 
     * @return array
     */
    public function collapseArgs($args, $key, $index = 0)
    {
        if (isset($args[$key])) {
            if (is_array($args[$key])) {
                foreach ($args[$key] as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $a => $b) {
                            if (!is_numeric($k)) {
                                $args[$key . '[' . $k . '][' . $a . ']'] = $b;
                            } else {
                                $args[$key . '[' . $index . '][' . $a . ']'] = $b;
                            }
                        }
                    } else {
                        if (!is_numeric($k)) {
                            $args[$key . '[' . $k . ']'] = ($key == 'file') ? '@' . $v : $v;
                        } else {
                            $args[$key . '[' . $index . ']'] = ($key == 'file') ? '@' . $v : $v;
                        }
                    }
                    $index++;
                }
            } else {
                $args[$key . '[' . $index . ']'] = ($key == 'file') ? '@' . $args[$key] : $args[$key];
            }
            unset($args[$key]);
        }
        
        return $args;
    }
}

/**
 *
 * Response Object
 *
 */
class Response {
    
    /**
     * Decoded JSON response array / File Data
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
        
        // cheap check to see if we have a file instead of JSON
        if ($json === null) {
            $json = $response;
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
        if (is_object($this->_response) && isset($this->_response->error)) {
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
