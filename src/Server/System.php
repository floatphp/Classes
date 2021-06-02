<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Server Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Server;

use FloatPHP\Classes\Filesystem\TypeCheck;

final class System
{
	/**
	 * Memory exceeded
	 *
	 * @access public
	 * @param float $percent
	 * @return bool
	 */
	public static function isMemoryOut($percent = 0.9)
	{
		$limit = self::getMemoryLimit() * $percent;
		$current = self::getMemoryUsage();
		if ( $current >= $limit ) {
			return true;
		}
		return false;
	}

	/**
	 * Get memory limit
	 *
	 * @access public
	 * @param void
	 * @return int
	 */
	public static function getMemoryLimit()
	{
		if ( TypeCheck::isFunction('ini_get') ) {
			$limit = self::getIni('memory_limit');
			if ( Stringify::contains(Stringify::lowercase($limit), 'g') ) {
				$limit = intval($limit) * 1024;
				$limit = "{$limit}M";
			}
		} else {
			// Default
			$limit = '128M';
		}
		if ( !$limit || $limit === -1 ) {
			// Unlimited
			$limit = '32000M';
		}
		return intval($limit) * 1024 * 1024;
	}

	/**
	 * Get memory usage
	 *
	 * @access public
	 * @param void
	 * @return int
	 */
	public static function getMemoryUsage($real = true)
	{
		return memory_get_usage($real);
	}

    /**
     * Get OS
     *
     * @access public
     * @param void
     * @return string
     */
    public static function getPhpVersion()
    {
    	return strtolower(PHP_VERSION);
    }

    /**
     * Get OS
     *
     * @access public
     * @param void
     * @return string
     */
    public static function getOs()
    {
        return strtolower(PHP_OS);
    }

    /**
     * Get OS command
     *
     * @access public
     * @param string $data
     * @return string
     */
    public static function getOsCommand($data = '')
    {
        if ( self::getIni('safe_mode') ) {
            return false;
        }
        $data = strtolower($data);
        switch ( self::getOs() ) {
            case 'winnt':
                switch ($data) {
                    case 'mac':
                        $command = 'getmac';
                        break;
                }
                break;
            case 'freebsd':
            case 'netbsd':
            case 'solaris':
            case 'sunos':
            case 'darwin':
                switch ($data) {
                    case 'mac':
                        $command = 'ether';
                        break;
                    case 'ip':
                        $command = 'inet';
                        break;
                }
                break;
            case 'linux':
                switch ($data) {
                    case 'mac':
                        $command = 'HWaddr';
                        break;
                    case 'ip':
                        $command = 'inet addr:';
                        break;
                }
                break;
        }
        return $command;
    }

    /**
     * Set ini
     *
     * @access public
     * @param string|array $option
     * @param string $value
     * @return mixed
     */
    public static function setIni($option, $value)
    {
        if ( TypeCheck::isArray($option) ) {
            $temp = [];
            foreach ($option as $key => $value) {
                $temp = ini_set($key,(string)$value);
            }
            return $temp;
        }
        return ini_set($option,(string)$value);
    }

    /**
     * Get ini
     *
     * @access public
     * @param string $option
     * @return mixed
     */
    public static function getIni(string $option)
    {
        return ini_get($option);
    }

    /**
     * Set time limit
     *
     * @access public
     * @param int $seconds
     * @param string $value
     * @return bool
     */
    public static function setTimeLimit($seconds = 30) : bool
    {
        return set_time_limit((int)$seconds);
    }

    /**
     * Set memory limit
     *
     * @access public
     * @param int|string $value
     * @return mixed
     */
    public static function setMemoryLimit($value = '128M')
    {
        return self::setIni('memory_limit',$value);
    }

    /**
     * Run shell command
     *
     * @access public
     * @param string $command
     * @return string
     */
    public static function runCommand(string $command = '')
    {
        return @shell_exec($command);
    }

    /**
     * Run command
     *
     * @access public
     * @param string $command
     * @param string $output
     * @param int $result
     * @return mixed
     */
    public static function execute(string $command = '', array &$output = null, int &$result = null)
    {
        return @exec($command,$output,$result);
    }

    /**
     * Get system config data
     *
     * @access public
     * @param void
     * @return mixed
     */
    public static function getConfig()
    {
        // Check windows environment
        if ( substr(self::getOs(),0,3) === 'win' ) {
            // Execute ipconfig
            self::execute('ipconfig/all',$lines);
            if ( count($lines) == 0 ) {
                return false;
            }
            $config = implode(PHP_EOL,$lines);
        } else {
            // Get config file
            $file = $this->getOsFile('conf');
            // Open the ipconfig
            $fp = @popen($file,'rb');
            if ( !$fp ) {
                return false;
            }
            $config = @fread($fp,4096);
            @pclose($fp);
        }
        return $config;
    }
}
