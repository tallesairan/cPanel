<?php

namespace AiranDev;
/**
 *
 *  The cPanel class is a PHP library designed to interact with cPanel APIs, a popular web hosting management platform. This class allows developers to execute various functions of the cPanel UAPI and APIv2 directly from their PHP applications. With the ability to use a proxy and an advanced system of cookies and logs
 *  this class offers enhanced flexibility and security for developers.
 *  @author Talles Airan <airan.talles@gmail.com>
 *  @copyright Copyright (c) 2023 AiranDev
 *  @date 2023-05-09
 *   
 */
class cPanel
{
    private $host;
    private $port;
    private $username;
    private $password;
 
    private $log;
    private $cFile;
    private $curlfile;
    private $emailArray;
    private $cpsess;
    private $homepage;
    private $exPage; 
    public $ips;
    /**
     * most easy way to change ua
     *
     * @var string $useragent
     */
    public $useragent = 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:29.0) Gecko/20100101 Firefox/29.0';
    /**
     * @var bool $current_ip current ip for this session
     */
    public $current_ip = '';

    /**
     * @var bool $useProxy use proxy or not
     */
    public $useProxy=false;

    /**
     * Constructor
     *
     * @param  string $username
     * @param  string $password
     * @param  string $host
     * @param integer $port
     * @param boolean $log
     * @param boolean $useProxy use proxy or not
     * @param array $proxies array of proxies
     * Example with proxy:
     * @example $cpanel = new cPanel('root','toor','localhost',2083,true,true,["128.0.0.1:3309","8.8.8.8:3309","8.4.4.8:3309"]);
     */
    function __construct($username, $password, $host, $port = 2083, $log = false, $useProxy=false, $proxies=array())
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->log = $log;

        if($useProxy and !empty($proxies)){
            $this->ips = $proxies;
        }

        /**
         * set an ip from the list of proxies that way you won't have disconnection if another request is invoked, preventing you from using an different ip for each request
         */
        if($useProxy){
            $this->current_ip = $this->getRandomProxy();
            $this->useProxy = true;
        }
        /**
         * FIX
         * If log is enabled, we will create a log file based on host for most correct logging
         */
        if($log){

            $this->curlfile = "logs/curl_".$this->slugify($this->host).".txt";

        }
        /**
         * FIX
         * cookies with the same name will be overwritten, so we will create a cookie file based on host for most correct cookie handling
         */
        $this->cFile = "cookies/cookie_".$this->slugify($this->host).".txt";
        $this->signIn();
    }

    /**
     * Returns current session url
     *
     * @return void
     */
    public function getSessionUrl(){
        return $this->homepage;
    }
    /**
     * get random proxy from the list of proxies
     *
     * @return void
     */
    public function getRandomProxy()
    {
        $proxy = $this->ips[array_rand($this->ips)];
        if(empty($proxy)) {
            $proxy = $this->ips[array_rand($this->ips)];
        }
        return $proxy;
    }
    /**
     * handle http request, if params is set, it will be a post request
     * Makes an HTTP request to the cPanel server.
     * @param [type] $url
     * @param array $params
     * @return mixed 
     */

    private function Request($url,$params=array()){
        if($this->log){
            $curl_log = fopen($this->curlfile, 'a+');
        }
        if(!file_exists($this->cFile)){
            try{
                fopen($this->cFile, "w");
            }catch(\Exception $ex){
                if(!file_exists($this->cFile)){
                    echo $ex.'Cookie file missing.'; exit;
                }
            }
        }else if(!is_writable($this->cFile)){
            echo 'Cookie file not writable.'; exit;
        }
        $ch = curl_init();
      
        $defaultContentType = "Content-Type: application/x-www-form-urlencoded";
        if(!empty($params)){
            /**
             * FIX
             * if we are uploading files, we need to change the content type to multipart/form-data
             */
            if(strpos($url,'upload_files')){
                $defaultContentType = "Content-type: multipart/form-data";
            }else{ 
                $defaultContentType = "Content-Type: application/x-www-form-urlencoded";
            }
        }
     
        $defaultarray = array(
            "Host: ".$this->host,
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-US,en;q=0.5",
            "Accept-Encoding: gzip, deflate",
            "Connection: keep-alive",
            $defaultContentType,
    
        );

     

        $curlOpts = array(
            CURLOPT_URL             => $url,
            CURLOPT_USERAGENT       => $this->useragent,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_COOKIEJAR       => realpath($this->cFile),
            CURLOPT_COOKIEFILE      => realpath($this->cFile),
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_HTTPHEADER      => $defaultarray
        );
        
        if(!empty($params)){
 
            $curlOpts[CURLOPT_POST] = true;
            $curlOpts[CURLOPT_POSTFIELDS] = $params;
            
        }
        if($this->log){
            $curlOpts[CURLOPT_STDERR] = $curl_log;
            $curlOpts[CURLOPT_FAILONERROR] = false;
            $curlOpts[CURLOPT_VERBOSE] = true;
        }
        if($this->useProxy){
            $curlOpts[CURLOPT_PROXY] = $this->current_ip;
        }
        curl_setopt_array($ch,$curlOpts);
        $answer = curl_exec($ch);
        if (curl_error($ch)) {
            throw new \Exception("Curl Exec Error: ".curl_error($ch), 1);            
        }
        curl_close($ch);
        if($this->log){
            fclose($curl_log);
        }
        return (@gzdecode($answer)) ? gzdecode($answer) : $answer;
    }
    /**
     * Function to start a session at cPanel server
     * @return void
     */
    private function signIn() {
        $url = 'https://'.$this->host.":".$this->port."/login/?login_only=1";
        $url .= "&user=".$this->username."&pass=".urlencode($this->password);
        $reply = $this->Request($url);
        $reply = json_decode($reply, true);
        if(isset($reply['status']) && $reply['status'] == 1){
            $this->cpsess = $reply['security_token'];
            $this->homepage = 'https://'.$this->host.":".$this->port.$reply['redirect'];
            $this->exPage = 'https://'.$this->host.":".$this->port. "/{$this->cpsess}/execute/";
        }
        else {
            throw new \Exception("Cannot connect to your cPanel server : Invalid Credentials", 1);            
        }
    }
    /**
     * execute the function
     *
     * @param string $api
     * @param string $module
     * @param string $function
     * @param array $parameters
     * @return void
     */
    public function execute($api, $module, $function, array $parameters)
    {
        switch ($api) {
            case 'api2':
                return $this->api2($module, $function, $parameters);
                break;
            case 'uapi':
                return $this->uapi($module, $function, $parameters);
                break;
            default:
                throw new \Exception("Invalid API type : api2 and uapi are accepted", 1);                
                break;
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @param [type] $function
     * @param array $parameters
     * @return void
     */
    public function uapi($module, $function,  array $parameters = [])
    {   
        /**
         * If the function is upload_files, we need to send the files as an array params
         */
        $params=[];
        if($function === 'upload_files'){

            $params = $parameters;        
            // We need to remove the files from the http_build_query    
            $parameters = "";

        }else{
            if (count($parameters) < 1) {
                $parameters = "";
            } else {
                $parameters = (http_build_query($parameters));
            }
        }
    

        return  json_decode($this->Request($this->exPage . $module . "/" . $function . "?" . $parameters,$params));

    }
    // API 2 Handler
    public function api2($module, $function, array $parameters = [])
    {
        if (count($parameters) < 1) {
            $parameters = "";
        } else {
            $parameters = (http_build_query($parameters));
        }
        $url = "https://".$this->host.":".$this->port.$this->cpsess."/json-api/cpanel".
        "?cpanel_jsonapi_version=2".
        "&cpanel_jsonapi_func={$function}".
        "&cpanel_jsonapi_module={$module}&". $parameters;
        return json_decode($this->Request($url,$parameters));
    }
    /**
     * Simple function to slugify a string
     *
     * @param string $text
     * @return void
     */
    public function slugify($text){
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicated - symbols
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
        return 'n-a';
        }

        return $text;
    }
}
