<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes HTTP Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 */

namespace floatphp\Classes\Http;

class Curl
{

    public $url;
    public $options = [];
    public $response;
    private $request;
    private $post = [];
    private $log;
    
    public function __construct($url = null)
    {

        $this->init($url);
        $this->setOptions();

    }
    public function __destruct(){

        curl_close($this->request);

    }
    public function post($data = [])
    {
        $this->options[] = [CURLOPT_POST => TRUE];
        $this->options += [CURLOPT_POSTFIELDS => $data];
    }
    private function init($url)
    {
        // init curl
        $this->request = curl_init();
        // set default options
        $this->options = [CURLOPT_RETURNTRANSFER=>1,CURLOPT_URL=>$url];
        
        return $this;
    }
    public function addOption($option = [])
    {
        $this->options[] = $option;
        return $this;
    }
    private function setOptions()
    {
        curl_setopt_array($this->request, $this->options);
    }
    public function info()
    {
        if (!curl_errno($this->request)) 
        {
          $this->log = curl_getinfo($this->request);
          return $this;
        }
    }
    public function execute()
    {
        $this->setOptions();
        $this->response = curl_exec($this->request);
        return $this->response;
    }
}

