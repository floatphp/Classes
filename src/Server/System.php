<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Server Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Server;

use FloatPHP\Classes\Filesystem\{TypeCheck, Stringify, File};

final class System
{
    /**
     * PHP CLI mode.
     *
     * @access public
     * @return bool
     */
    public static function isCli() : bool
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * PHP Memory exceeded.
     *
     * @access public
     * @param float $percent
     * @return bool
     */
    public static function isMemoryOut(float $percent = 0.9) : bool
    {
        $limit = self::getMemoryLimit() * $percent;
        $current = self::getMemoryUsage();
        return $current >= $limit;
    }

    /**
     * Get memory limit.
     *
     * @access public
     * @return int
     */
    public static function getMemoryLimit() : int
    {
        if ( TypeCheck::isFunction('ini-get') ) {
            $limit = self::getIni('memory-limit');
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
    public static function getMemoryUsage(bool $real = true, bool $format = true) : int
    {
        $usage = memory_get_usage($real);
        if ( $format ) {
            $usage = (int)round($usage / 1000000, 2);
        }
        return $usage;
    }

    /**
     * Get PHP version.
     *
     * @access public
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
    public static function getSchedule(bool $format = true) : array
    {
        return self::isWindowsEnv()
            ? self::getWindowsSchedule($format)
            : self::getLinuxSchedule($format);
    }

    /**
     * Get schedule tasks (Windows).
     *
     * @access public
     * @param bool $format
     * @return array
     */
    public static function getWindowsSchedule(bool $format = true) : array
    {
        $tasks = [];

        if ( self::isWindowsEnv() ) {

            $schedule = new \COM('Schedule.Service');
            $schedule?->Connect();

            $folder = $schedule?->GetFolder('\\');
            $data = $folder?->GetTasks(0);

            if ( $data?->Count ) {
                foreach ($data as $task) {
                    $name = $task->Name ?? 'unknown';
                    if ( $format ) {
                        $name = Stringify::lowercase($name);
                    }
                    $tasks[$name] = $task->Enabled;
                }
            }
        }

        return $tasks;
    }

    /**
     * Get schedule tasks (Linux).
     *
     * @access public
     * @param bool $format
     * @return array
     */
    public static function getLinuxSchedule(bool $format = true) : array
    {
        $tasks = [];
        if ( ($return = self::execute('crontab -l')) ) {
            $tasks = explode("\n", $return);
            if ( $format ) {
                foreach ($tasks as $key => $value) {
                    $tasks[$key] = Stringify::lowercase($value);
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
    public static function hasScheduleTask(string $name) : bool
    {
        return self::isWindowsEnv()
            ? self::hasWindowsScheduleTask($name)
            : self::hasLinuxScheduleTask($name);
    }

    /**
     * Check schedule task (Windows).
     *
     * @access public
     * @param string $name
     * @return bool
     */
    public static function hasWindowsScheduleTask(string $name) : bool
    {
        $status = false;
        $name = Stringify::lowercase($name);
        $tasks = self::getWindowsSchedule() ?? [];

        foreach ($tasks as $key => $value) {
            if ( Stringify::contains($key, $name) && $value === true ) {
                $status = true;
                break;
            }
        }

        return $status;
    }

    /**
     * Check schedule task (Linux).
     *
     * @access public
     * @param string $name
     * @return bool
     */
    public static function hasLinuxScheduleTask(string $name) : bool
    {
        $status = false;
        $name = Stringify::lowercase($name);
        $tasks = self::getLinuxSchedule() ?? [];

        foreach ($tasks as $key => $value) {
            if ( Stringify::contains($key, $name) && $value === true ) {
                $status = true;
                break;
            }
        }

        return $status;
    }

    /**
     * Set ini option.
     *
     * @access public
     * @param mixed $option
     * @param mixed $value
     * @return mixed
     */
    public static function setIni($option, $value = null) : mixed
    {
        if ( TypeCheck::isArray($option) ) {
            $temp = [];
            foreach ($option as $key => $value) {
                $temp = ini_set($key, $value);
            }
            return $temp;
        }
        return ini_set($option, $value);
    }

    /**
     * Get ini value.
     *
     * @access public
     * @param string $option
     * @return mixed
     */
    public static function getIni(string $option) : mixed
    {
        $option = Stringify::undash($option);
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
    public static function setTimeLimit(int $seconds = 30) : bool
    {
        return set_time_limit($seconds);
    }

    /**
     * Set memory limit.
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public static function setMemoryLimit($value = '128M') : mixed
    {
        return self::setIni('memory-limit', $value);
    }

    /**
     * Run shell command.
     *
     * @access public
     * @param string $command
     * @return mixed
     */
    public static function runCommand(string $command) : mixed
    {
        return @shell_exec($command);
    }

    /**
     * Run command.
     *
     * @access public
     * @param string $command
     * @param array $output
     * @param int $result
     * @return mixed
     */
    public static function execute(string $command, ?array &$output = null, ?int &$result = null) : mixed
    {
        return @exec($command, $output, $result);
    }

    /**
     * Get CPU usage.
     *
     * @access public
     * @return array
     */
    public static function getCpuUsage() : array
    {
        return self::isWindowsEnv()
            ? self::getWindowsCpuUsage()
            : self::getLinuxCpuUsage();
    }

    /**
     * Get CPU usage (Windows).
     *
     * @access public
     * @return array
     */
    public static function getWindowsCpuUsage() : array
    {
        $usage = [
            'usage' => -1,
            'count' => -1
        ];

        if ( self::isWindowsEnv() ) {

            $system = new \COM('WinMgmts:\\\\.');
            $object = 'Win32_PerfFormattedData_PerfOS_Processor';
            $cpu = $system?->InstancesOf($object) ?: [];
            $load = $count = 0;

            foreach ($cpu as $core) {
                if ( isset($core->Name) && $core->Name === '_Total' ) {
                    continue;
                }
                $load += $core->PercentProcessorTime ?? 0;
                $count++;
            }

            if ( $count > 0 ) {
                $percent = round($load / $count, 2);
                $usage = [
                    'usage' => $percent,
                    'count' => $count
                ];
            }
        }

        return $usage;
    }

    /**
     * Get CPU usage (Linux).
     *
     * @access public
     * @return array
     */
    public static function getLinuxCpuUsage() : array
    {
        $usage = [
            'usage' => -1,
            'count' => -1
        ];

        if ( self::isLinuxEnv() ) {
            if ( ($load = self::getLoadAvg()) ) {
                $usage = [
                    'usage' => $load[0] ?? 0,
                    'count' => count($load)
                ];
            }
        }

        return $usage;
    }

    /**
     * Get CPU cores count.
     *
     * @access public
     * @return int
     */
    public static function getCpuCores() : int
    {
        $count = 1;

        if ( !TypeCheck::isFunction('ini-get') ) {
            return $count;
        }

        if ( self::getIni('open-basedir') ) {
            return $count;
        }

        return self::isWindowsEnv()
            ? self::getWindowsCpuCores()
            : self::getLinuxCpuCores();
    }

    /**
     * Get CPU cores count (Windows).
     *
     * @access public
     * @return int
     */
    public static function getWindowsCpuCores() : int
    {
        $count = 1;
        if ( self::isWindowsEnv() ) {
            $count = (int)getenv('NUMBER_OF_PROCESSORS');
        }
        return $count;
    }

    /**
     * Get CPU cores count (Linux).
     *
     * @access public
     * @return int
     */
    public static function getLinuxCpuCores() : int
    {
        $count = 1;
        if ( self::isLinuxEnv() ) {
            $info = File::r('/proc/cpuinfo');
            if ( Stringify::matchAll('/^processor/m', $info, $matches) ) {
                $count = count($matches);
            }
        }
        return $count;
    }

    /**
     * Get memory usage.
     *
     * @access public
     * @return array
     */
    public static function getSystemMemoryUsage() : array
    {
        return self::isWindowsEnv()
            ? self::getWindowsMemoryUsage()
            : self::getLinuxMemoryUsage();
    }

    /**
     * Get memory usage (Windows).
     *
     * @access public
     * @return array
     */
    public static function getWindowsMemoryUsage() : array
    {
        $usage = [
            'total' => -1,
            'free'  => -1,
            'used'  => -1,
            'usage' => -1
        ];

        if ( self::isWindowsEnv() ) {

            $system = new \COM('WinMgmts:\\\\.');

            $query = 'SELECT FreePhysicalMemory, FreeVirtualMemory, ';
            $query .= 'TotalSwapSpaceSize, TotalVirtualMemorySize, ';
            $query .= 'TotalVisibleMemorySize FROM Win32_OperatingSystem';

            $memory = null;
            $data = $system?->ExecQuery($query) ?: [];
            if ( $data ) {
                foreach ($data as $item) {
                    $memory = $item;
                    break;
                }
            }

            if ( $memory ) {

                // Total available memory
                $total = $memory->TotalVisibleMemorySize ?? 0;
                $total = round($total / 1024, 2);

                // Total free memory
                $free = $memory->FreePhysicalMemory ?? 0;
                $free = round($free / 1024, 2);

                // Total used memory
                $used = $total - $free;
                $used = max($used, 0);
                $used = round($used, 2);

                // Percent of used memory
                $percent = $total > 0 ? ($used / $total) * 100 : 0;
                $percent = round($percent, 2);

                $usage = [
                    'total' => $total,
                    'free'  => $free,
                    'used'  => $used,
                    'usage' => $percent
                ];
            }

        }

        return $usage;
    }

    /**
     * Get memory usage (Linux).
     *
     * @access public
     * @return array
     */
    public static function getLinuxMemoryUsage() : array
    {
        $usage = [
            'total' => -1,
            'free'  => -1,
            'used'  => -1,
            'usage' => -1
        ];

        if ( self::isLinuxEnv() ) {

            $data = File::r('/proc/meminfo') ?: '';
            $data = trim($data);
            $data = explode("\n", $data);

            if ( $data ) {

                $memory = [];
                foreach ($data as $line) {
                    if ( Stringify::matchAll('/^(\w+):\s+(\d+)/', $line, $matches) ) {
                        $memory[$matches[1]] = (int)$matches[2];
                    }
                }

                // Total available memory
                $tTotal = $memory['MemTotal'] ?? 0;

                // Free memory parts
                $tBuffer = $memory['Buffers'] ?? 0;
                $tCached = $memory['Cached'] ?? 0;
                $tFree = $memory['MemFree'] ?? 0;

                // Total free memory
                $tFree = $tFree + $tBuffer + $tCached;

                // Total used memory
                $tUsed = $tTotal - $tFree;

                // MB conversion
                $total = round($tTotal / 1024, 2);
                $free = round($tFree / 1024, 2);
                $used = round($tUsed / 1024, 2);

                // Percent of used memory
                $percent = $tTotal > 0 ? ($tUsed / $tTotal) * 100 : 0;
                $percent = round($percent, 2);

                $usage = [
                    'total' => $total,
                    'free'  => $free,
                    'used'  => $used,
                    'usage' => $percent
                ];
            }

        }

        return $usage;
    }

    /**
     * Get network usage.
     *
     * @access public
     * @return array
     */
    public static function getNetworkUsage() : array
    {
        return self::isWindowsEnv(false)
            ? self::getWindowsNetUsage()
            : self::getLinuxNetUsage();
    }

    /**
     * Get network usage (Windows).
     *
     * @access public
     * @param string $local, Excluded IP
     * @return array
     */
    public static function getWindowsNetUsage(?string $local = '127.0.0.1') : array
    {
        $usage = [
            'total'     => -1,
            'connected' => -1,
            'usage'     => -1
        ];

        if ( self::getOsName() == 'windows' ) {

            // Get total connections
            $cmd = 'netstat -nt | ';
            if ( $local ) {
                $cmd .= "findstr /V {$local} | ";
            }
            $cmd .= 'find /C /V ""';

            $total = self::runCommand($cmd);
            $total = intval(trim($total));

            // Get established connections
            $cmd = 'netstat -nt | findstr ESTABLISHED |';
            if ( $local ) {
                $cmd .= "findstr /V {$local} | ";
            }
            $cmd .= 'find /C /V ""';

            $connected = self::runCommand($cmd);
            $connected = intval(trim($connected));

            // Percent of used connections
            $percent = $total > 0 ? ($connected / $total) * 100 : 0;
            $percent = round($percent, 2);

            $usage = [
                'total'     => $total,
                'connected' => $connected,
                'usage'     => $percent,
            ];

        }

        return $usage;
    }

    /**
     * Get network usage (Linux).
     *
     * @access public
     * @param string $local
     * @return array
     */
    public static function getLinuxNetUsage(?string $local = '127.0.0.1') : array
    {
        $usage = [
            'total'     => -1,
            'connected' => -1,
            'usage'     => -1
        ];

        if ( self::isLinuxEnv() ) {

            // Get total connections
            $cmd = 'netstat -ntu | grep -v LISTEN | ';
            $cmd .= "awk '{print $5}' | cut -d: -f1 | ";
            $cmd .= 'sort | uniq -c | sort -rn | ';
            if ( $local ) {
                $cmd .= 'grep -v 127.0.0.1 | ';
            }
            $cmd .= 'wc -l';

            $total = self::runCommand($cmd);
            $total = intval(trim($total));

            // Get established connections
            $cmd = 'netstat -ntu | grep ESTABLISHED | ';
            $cmd .= 'grep -v LISTEN | ';
            $cmd .= "awk '{print $5}' | cut -d: -f1 | ";
            $cmd .= 'sort | uniq -c | sort -rn | ';
            if ( $local ) {
                $cmd .= 'grep -v 127.0.0.1 | ';
            }
            $cmd .= 'wc -l';

            $connected = self::runCommand($cmd);
            $connected = intval(trim($connected));

            // Percent of used connections
            $percent = $total > 0 ? ($connected / $total) * 100 : 0;
            $percent = round($percent, 2);

            $usage = [
                'total'     => $total,
                'connected' => $connected,
                'usage'     => $percent,
            ];
        }

        return $usage;
    }

    /**
     * Get disk usage.
     *
     * @access public
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
     * @return array
     */
    public static function getDiskUsage() : array
    {
        $total = self::getDiskTotalSpace();
        $free = self::getDiskFreeSpace();
        $used = round($total - $free);
        $percent = round(($used / $total) * 100);

        return [
            'total' => $total,
            'free'  => $free,
            'usage' => $percent
        ];
    }

    /**
     * Get disk free space.
     *
     * @access public
     * @param string $dir
     * @param bool $format
     * @return float
     */
    public static function getDiskFreeSpace(string $dir = '.', bool $format = true) : float
    {
        $space = disk_free_space($dir);
        if ( $format ) {
            round($space / 1000000000);
        }
        return (float)$space;
    }

    /**
     * Get disk total space.
     *
     * @access public
     * @param string $dir
     * @param bool $format
     * @return float
     */
    public static function getDiskTotalSpace(string $dir = '.', bool $format = true) : float
    {
        $space = disk_total_space($dir);
        if ( $format ) {
            round($space / 1000000000);
        }
        return (float)$space;
    }

    /**
     * Get load avg.
     *
     * @access public
     * @return mixed
     */
    public static function getLoadAvg() : mixed
    {
        return sys_getloadavg();
    }

    /**
     * Get system file size.
     *
     * @access public
     * @param string $dir
     * @param bool $format
     * @return float
     */
    public static function getSize(string $dir = '.', bool $format = true) : float
    {
        $size = self::isWindowsEnv()
            ? self::getWindowsSize($dir)
            : self::getLinuxSize($dir);

        if ( $format ) {
            $size /= 1000000;
            $size = round($size, 2);
        }

        return $size;
    }

    /**
     * Get system file size (Windows).
     *
     * @access public
     * @param string $dir
     * @return float
     */
    public static function getWindowsSize(string $dir = '.') : float
    {
        $size = 0;
        if ( self::isWindowsEnv() ) {
            $system = new \COM('scripting.filesystemobject');
            $path = $system?->getfolder($dir);
            $size = $path?->size ?? 0;
        }
        return (float)$size;
    }

    /**
     * Get system file size (Linux).
     *
     * @access public
     * @param string $dir
     * @return float
     */
    public static function getLinuxSize(string $dir = '.') : float
    {
        $size = 0;
        if ( self::isLinuxEnv() ) {
            $path = popen("/usr/bin/du -sk {$dir}", 'r');
            $size = fgets($path, 4096);
            $size = substr($size, 0, strpos($size, "\t"));
            pclose($path);
        }
        return (float)$size;
    }

    /**
     * Get system current MAC address.
     *
     * @access public
     * @return string
     */
    public static function getMac() : string
    {
        $mac = self::execute('getmac');
        return (string)strtok($mac, ' ');
    }

    /**
     * Get GLOBALS item value.
     *
     * @access public
     * @param ?string $key
     * @return mixed
     */
    public static function getGlobal(?string $key = null) : mixed
    {
        return self::hasGlobal($key) ? $GLOBALS[$key] : null;
    }

    /**
     * Check GLOBALS item value.
     *
     * @access public
     * @param string $key
     * @return bool
     */
    public static function hasGlobal(string $key) : bool
    {
        return isset($GLOBALS[$key]);
    }

    /**
     * Check Windows env with COM extension.
     *
     * @access public
     * @param bool $com
     * @return bool
     */
    public static function isWindowsEnv(bool $com = true) : bool
    {
        if ( self::getOsName() == 'windows' ) {
            if ( $com ) {
                return TypeCheck::isClass('COM');
            }
            return true;
        }
        return false;
    }

    /**
     * Check Linux env.
     *
     * @access public
     * @return bool
     */
    public static function isLinuxEnv() : bool
    {
        return self::getOsName() == 'linux';
    }
}
