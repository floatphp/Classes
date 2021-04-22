<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Filesystem Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Filesystem;

use DateTime;

class DatabaseLogger
{
	
	private $path = 'App/Storage/logs/database/';
			
	public function __construct()
	{
		date_default_timezone_set('Europe/Amsterdam');
	}

	public function write($message)
	{
		$date = new DateTime();
		$log = $this->path . $date->format('d-m-Y').".log";
		if(is_dir($this->path)) {
			if(!file_exists($log)) {
				$fh = fopen($log, 'a+') or die("Fatal Error !");
				$logcontent = "Time : " . $date->format('H:i:s')."\r\n" . $message ."\r\n";
				fwrite($fh, $logcontent);
				fclose($fh);
			}
			else {
				$this->edit($log,$date, $message);
			}
		}
		else {
			  if(mkdir($this->path,0777) === true) 
			  {
 				 $this->write($message);  
			  }	
		}
	}
	
	private function edit($log,$date,$message)
	{
		$logcontent = "Time : " . $date->format('H:i:s')."\r\n" . $message ."\r\n\r\n";
		$logcontent = $logcontent . file_get_contents($log);
		file_put_contents($log, $logcontent);
	}
}
