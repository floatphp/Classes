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

class Cloaking
{
	/**
	 * @access public
	 */
	public $isGoogleBot = FALSE;
	public $reverseIp;
	public $reverseName;
	public $userAgent;
	/**
	 * @param string $ip
	 * @param string $useragent
	 * @return object
	 */
	public function __construct($ip = null, $useragent = null)
	{
		$this->reverseIp($ip);
		$this->setUserAgent($useragent);
		$this->checkCloacking();
		return $this;
	}
	/**
	 * @param string $ip
	 * @return void
	 */
	public function reverseIp($ip)
	{
		if ( !is_null($ip) && !empty($ip) )
		$this->reverseIp = gethostbyaddr($ip);
	}
	/**
	 * @param string $name
	 * @return void
	 */
	public function reverseName($name)
	{
		if ( !is_null($name) && !empty($name) )
		$this->reverseName = gethostbyname($name);
	}
	/**
	 * @param string $useragent
	 * @return void
	 */
	public function setUserAgent($useragent)
	{
		if ( !is_null($useragent) && !empty($useragent) )
		$this->userAgent = $useragent;
	}
    /**
	 * @param void
	 * @return object
     */
    protected function checkCloacking()
    {
    	if ( $this->isGoogleDNS() || $this->isGoogleUA() )
    	{
    		$this->isGoogleBot = TRUE;
    	}
    }
    /**
	 * @param void
	 * @return boolean
     */
    protected function isGoogleDNS()
    {
        preg_match('/googlebot/', $this->reverseIp, $dns, PREG_OFFSET_CAPTURE);

        if(count($dns) > 0)
        {
           return TRUE;
        }
    }
    /**
	 * @param void
	 * @return boolean
     */
    protected function isGoogleUA()
    {
        preg_match('/Googlebot/', $this->userAgent, $useragent, PREG_OFFSET_CAPTURE);

        if(count($useragent) > 0)
        {
           return TRUE;
        }
    }
}
