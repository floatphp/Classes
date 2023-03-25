<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Server Component
 * @version    : 1.0.2
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2023 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Server;

use FloatPHP\Classes\Filesystem\{
    TypeCheck, Stringify, Arrayify
};

final class System
{
	/**
	 * PHP CLI mode.
	 *
	 * @access public
	 * @param void
	 * @return bool
	 */
	public static function isCLI() : bool
	{
		if ( php_sapi_name() == 'cli' ) {
            return true;
        }
        return false;
	}

    /**
     * PHP Memory exceeded.
     *
     * @access public
     * @param float $percent
     * @return bool
     */
    public static function isMemoryOut($percent = 0.9) : bool
    {
        $limit = self::getMemoryLimit() * $percent;
        $current = self::getMemoryUsage(1,1);
        if ( $current >= $limit ) {
            return true;
        }
        return false;
    }

	/**
	 * Get memory limit.
	 *
	 * @access public
	 * @param void
	 * @return int
	 */
	public static function getMemoryLimit() : int
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
	 * Get PHP memory usage.
	 *
	 * @access public
	 * @param bool $real
     * @param bool $format
	 * @return int
	 */
	public static function getMemoryUsage($real = true, $format = true) : int
	{
        $usage = memory_get_usage($real);
        if ( $format ) {
            $usage = round($usage / 1000000,2);
        }
		return $usage;
	}

    /**
     * Get PHP version.
     *
     * @access public
     * @param void
     * @return string
     */
    public static function getPhpVersion() : string
    {
    	return strtolower(PHP_VERSION);
    }

    /**
     * Get OS.
     *
     * @access public
     * @param void
     * @return string
     */
    public static function getOs() : string
    {
        return strtolower(PHP_OS);
    }

    /**
     * Get OS name.
     *
     * @access public
     * @param void
     * @return string
     */
    public static function getOsName() : string
    {
        return strtolower(PHP_OS_FAMILY);
    }

    /**
     * Get schedule tasks.
     *
     * @access public
     * @param bool $format
     * @return array
     */
    public static function getSchedule($format = true) : array
    {
        $tasks = [];
        if ( System::getOsName() == 'windows' ) {
            if ( TypeCheck::isClass('COM') ) {
                $schedule = new \COM('Schedule.Service');
                $schedule->Connect();
                $folder = $schedule->GetFolder('\\');
                $collection = $folder->GetTasks(0);
                if ( $collection->Count ) {
                    foreach ($collection as $task) {
                        $name = $task->Name;
                        if ( $format ) {
                            $name = Stringify::lowercase($name);
                        }
                        $tasks['win'][$name] = $task->Enabled;
                    }
                }
            }

        } else {
            if ( ($return = System::execute('crontab -l')) ) {
                $tasks['lin'] = explode("\n", $return);
                if ( $format ) {
                    foreach ($tasks['lin'] as $key => $value) {
                        $tasks['lin'][$key] = Stringify::lowercase($value);
                    }
                }
            }
        }
        return $tasks;
    }

    /**
     * Check schedule task.
     *
     * @access public
     * @param string $name
     * @return bool
     */
    public static function hasScheduleTask($name) : bool
    {
        $status = false;
        if ( ($tasks = self::getSchedule()) ) {
            if ( isset($tasks['win']) ) {
                foreach ($tasks['win'] as $key => $value) {
                    if ( Stringify::contains($key, $name) && $value === true ) {
                        $status = true;
                        break;
                    }
                }
            } else {
                foreach ($tasks['lin'] as $line) {
                    if ( Stringify::contains($line, $name) && substr($line, 0, 1) !== '#' ) {
                        if ( !Stringify::contains($line, 'home=') ) {
                            $status = true;
                            break;
                        }
                    }
                }
            }
        }
        return $status;
    }

    /**
     * Set ini.
     *
     * @access public
     * @param mixed $option
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
     * Get ini value.
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
     * Set time limit.
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
     * Run shell command.
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
     * Run command.
     *
     * @access public
     * @param string $command
     * @param string $output
     * @param int $result
     * @return mixed
     */
    public static function execute(string $command = '', &$output = null, &$result = null)
    {
        return @exec($command, $output, $result);
    }

    /**
     * Get CPU usage.
     *
     * @access public
     * @param void
     * @return array
     */
    public static function getCpuUsage() : array
    {
        $usage = [];
        if ( self::getOsName() == 'windows' ) {
            if ( TypeCheck::isClass('COM') ) {
                $system = new \COM('WinMgmts:\\\\.');
                $cpu = $system->InstancesOf('Win32_Processor');
                $load = 0;
                $count = 0;
                foreach ($cpu as $key => $core) {
                    $load += $core->LoadPercentage;
                    $count++;
                }
                $usage = [
                    'usage' => $load,
                    'count' => $count
                ];
            }
        } else {
            $load = self::getLoadAvg();
            $usage = [
                'usage' => $load[0],
                'count' => count($load)
            ];
        }
        return $usage;
    }

    /**
     * Get memory usage.
     *
     * @access public
     * @param void
     * @return array
     */
    public static function getSystemMemoryUsage() : array
    {
        $usage = [];
        if ( self::getOsName() == 'windows' ) {
            if ( TypeCheck::isClass('COM') ) {
                $system = new \COM('WinMgmts:\\\\.');
                $query  = 'SELECT FreePhysicalMemory,FreeVirtualMemory,';
                $query .= 'TotalSwapSpaceSize,TotalVirtualMemorySize,';
                $query .= 'TotalVisibleMemorySize FROM Win32_OperatingSystem';
                $memory = $system->ExecQuery($query);
                $memory = $memory->ItemIndex(0);
                $total = round($memory->TotalVisibleMemorySize / 1000000, 2);
                $available = round($memory->FreePhysicalMemory / 1000000, 2);
                $usage = [
                    'total'     => $total,
                    'available' => $available,
                    'used'      => round($total - $available, 2),
                    'free'      => false,
                    'shared'    => false,
                    'cached'    => false,
                    'usage'     => round(($available / $total) * 100)
                ];
            }
        } else {
            $free = self::runCommand('free');
            $free = (string)trim($free);
            $args = explode("\n",$free);
            $memory = explode(' ',$args[1]);
            // Format array
            $memory = Arrayify::filter($memory, function($value) {
                return ($value !== null && $value !== false && $value !== '');
            });
            // Reset array positions
            $memory = Arrayify::merge($memory);
            $total = round($memory[1] / 1000000, 2);
            $available = round($memory[3] / 1000000, 2);
            $usage = [
                'total'     => $total,
                'available' => $available,
                'used'      => round($memory[2] / 1000000, 2),
                'free'      => round($memory[6] / 1000000, 2),
                'shared'    => round($memory[4] / 1000000, 2),
                'cached'    => round($memory[5] / 1000000, 2),
                'usage'     => round(($available / $total) * 100)
            ];
        }
        return $usage;
    }

    /**
     * Get network usage.
     *
     * @access public
     * @param void
     * @return array
     */
    public static function getNetworkUsage() : array
    {
        $usage = [];
        if ( self::getOsName() == 'windows' ) {
            $command = 'netstat -nt | findstr :80 | findstr ESTABLISHED | find /C /V ""';
            $connections = self::runCommand($command);
            $command = 'netstat -nt | findstr :80 | find /C /V ""';
            $total = self::runCommand($command);
            $usage = [
                'usage'       => $total,
                'connections' => $connections
            ];
        } else {
            $command  = 'netstat -ntu | grep :80 | grep ESTABLISHED | grep -v LISTEN | ';
            $command .= "awk '{print $5}' | cut -d: -f1 | sort | uniq -c | sort -rn | ";
            $command .= 'grep -v 127.0.0.1 | wc -l';
            $connections = `$command`;
            $command  = 'netstat -ntu | grep :80 | grep -v LISTEN | ';
            $command .= "awk '{print $5}' | cut -d: -f1 | sort | uniq -c | ";
            $command .= 'sort -rn | grep -v 127.0.0.1 | wc -l';
            $total = `$command`;
            $usage = [
                'usage'       => $total,
                'connections' => $connections
            ];
        }
        return $usage;
    }

    /**
     * Get disk usage.
     *
     * @access public
     * @param void
     * @return array
     */
    public static function getUsage() : array
    {
        return [
            'cpu'     => self::getCpuUsage(),
            'memory'  => self::getSystemMemoryUsage(),
            'disk'    => self::getDiskUsage(),
            'network' => self::getNetworkUsage()
        ];
    }

    /**
     * Get disk usage.
     *
     * @access public
     * @param void
     * @return array
     */
    public static function getDiskUsage() : array
    {
        $free = self::getDiskFreeSpace();
        $total = self::getDiskTotalSpace();
        $used = round($total - $free);
        return [
            'total' => $total,
            'free'  => $free,
            'usage' => round(($used / $total) * 100)
        ];
    }

    /**
     * Get disk free space.
     *
     * @access public
     * @param string $directory
     * @param bool $format
     * @return mixed
     */
    public static function getDiskFreeSpace($directory = '.', $format = true)
    {
        $space = disk_free_space($directory);
        if ( $format ) {
            round($space / 1000000000);
        }
        return $space;
    }

    /**
     * Get disk total space.
     *
     * @access public
     * @param string $directory
     * @param bool $format
     * @return mixed
     */
    public static function getDiskTotalSpace($directory = '.', $format = true)
    {
        $space = disk_total_space($directory);
        if ( $format ) {
            round($space / 1000000000);
        }
        return $space;
    }

    /**
     * Get load avg.
     *
     * @access public
     * @param void
     * @return mixed
     */
    public static function getLoadAvg()
    {
        return sys_getloadavg();
    }

    /**
     * Get system file size.
     *
     * @access public
     * @param string $directory
     * @param bool $format
     * @return mixed
     */
    public static function getSize($directory = '.', $format = true)
    {
        $size = false;
        if ( self::getOsName() == 'windows' ) {
            if ( TypeCheck::isClass('COM') ) {
                $system = new \COM('scripting.filesystemobject');
                if ( TypeCheck::isObject($system) ) {
                    $path = $system->getfolder($directory);
                    $size = $path->size;
                    unset($system);
                }
            }
        } else {
            $path = popen("/usr/bin/du -sk {$directory}", 'r');
            $size = fgets($path, 4096);
            $size = substr($size, 0, strpos($size, "\t"));
            pclose ($path);
        }
        if ( $format ) {
            $size = round($size / 1000000, 2);
        }
        return $size;
    }

    /**
     * Get system current MAC address.
     *
     * @access public
     * @param void
     * @return string
     */
    public static function getMAC() : string
    {
        $mac = self::execute('getmac');
        return (string)strtok($mac, ' ');
    }
}
