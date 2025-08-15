<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file is a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Server\{Date, System};
use FloatPHP\Classes\Filesystem\{Arrayify, Validator};
use \RuntimeException;

/**
 * Advanced session manipulation with enhanced security features.
 */
final class Session
{
    /**
     * @access public
     * @var array CONFIG Default secure session configuration
     */
    public const CONFIG = [
        'cookie_lifetime' => 0,
        'cookie_path'     => '/',
        'cookie_domain'   => '',
        'cookie_secure'   => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,
        'use_only_cookies' => true,
        'entropy_length'   => 32,
        'hash_function'    => 'sha256'
    ];

    /**
     * @access private
     * @var array $config Session configuration
     * @var bool $regenerated Whether session ID regenerated
     */
    private static array $config = [];
    private static bool $regenerated = false;

    /**
     * Start session with secure configuration.
     *
     * @access public
     * @param array $config Custom session configuration
     * @throws RuntimeException
     */
    public function __construct(array $config = [])
    {
        if ( !self::isActive() ) {
            self::configure($config);
            if ( !@session_start() ) {
                throw new RuntimeException('Failed to start session');
            }
            self::validateSession();
        }
    }

    /**
     * Configure session with secure defaults.
     *
     * @access public
     * @param array $config
     * @return void
     */
    public static function configure(array $config = []) : void
    {
        self::$config = Arrayify::merge(self::CONFIG, $config);
        
        // Apply configuration
        foreach (self::$config as $key => $value) {
            if ( strpos($key, 'cookie_') === 0 ) {
                $iniKey = "session.{$key}";
                System::setIni($iniKey, (string)$value);
            }
        }
        
        // Set additional security settings
        System::setIni('session.use_strict_mode', self::$config['use_strict_mode'] ? '1' : '0');
        System::setIni('session.use_only_cookies', self::$config['use_only_cookies'] ? '1' : '0');
        System::setIni('session.entropy_length', (string)self::$config['entropy_length']);
        System::setIni('session.hash_function', self::$config['hash_function']);
        
        // Disable session.auto_start for security
        System::setIni('session.auto_start', '0');
    }

    /**
     * Start session securely.
     *
     * @access public
     * @param array $config
     * @return bool
     */
    public static function start(array $config = []) : bool
    {
        if ( self::isActive() ) {
            return true;
        }
        
        self::configure($config);
        
        if ( @session_start() ) {
            self::validateSession();
            return true;
        }
        
        return false;
    }

    /**
     * Regenerate session ID for security.
     *
     * @access public
     * @param bool $deleteOld Whether to delete old session data
     * @return bool
     */
    public static function regenerate(bool $deleteOld = true) : bool
    {
        if ( !self::isActive() ) {
            return false;
        }
        
        $result = session_regenerate_id($deleteOld);
        
        if ( $result ) {
            self::$regenerated = true;
            self::set('--session-id', session_id());
            self::set('--session-regenerated-at', time());
        }
        
        return $result;
    }

    /**
     * Validate session security.
     *
     * @access public
     * @return bool
     */
    public static function validateSession() : bool
    {
        // Check for session hijacking
        if ( !self::validateUserAgent() || !self::validateIpAddress() || !self::validateFingerprint() ) {
            self::end();
            return false;
        }
        
        // Check for session timeout
        if ( self::isTimedOut() ) {
            self::end();
            return false;
        }
        
        // Auto-regenerate session ID periodically
        if ( self::shouldRegenerate() ) {
            self::regenerate();
        }
        
        return true;
    }

    /**
     * Check if session ID should be regenerated.
     *
     * @access public
     * @return bool
     */
    public static function shouldRegenerate() : bool
    {
        $last = self::get('--session-regenerated-at');
        
        if ( !$last ) {
            return true;
        }
        
        // Regenerate every 15 minutes
        return (time() - $last) > 900;
    }

    /**
     * Register session.
     *
     * @access public
     * @param int $time
     * @return void
     */
    public static function register(int $time = 60) : void
    {
        self::set('--session-id', session_id());
        self::set('--session-time', intval($time));

        $time = self::get('--session-time');
        self::set('--session-start', Date::newTime(h: 0, m: 0, s: $time));
    }

    /**
     * Check whether session is registered.
     *
     * @access public
     * @return bool
     */
    public static function isRegistered() : bool
    {
        if ( !empty(self::get('--session-id')) ) {
            return true;
        }
        return false;
    }

    /**
     * Get _SESSION value.
     * 
     * @access public
     * @param string $key
     * @return mixed
     */
    public static function get(?string $key = null) : mixed
    {
        if ( $key ) {
            return self::isSetted($key) ? $_SESSION[$key] : null;
        }
        return self::isSetted() ? $_SESSION : null;
    }

    /**
     * Set _SESSION value.
     * 
     * @access public
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public static function set(mixed $key, mixed $value = null) : void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Check _SESSION value.
     * 
     * @access public
     * @param string $key
     * @return bool
     */
    public static function isSetted(?string $key = null) : bool
    {
        if ( $key ) {
            return isset($_SESSION[$key]);
        }
        return isset($_SESSION) && !empty($_SESSION);
    }

    /**
     * Unset _SESSION value.
     * 
     * @access public
     * @param string $key
     * @return void
     */
    public static function unset(?string $key = null) : void
    {
        if ( $key ) {
            unset($_SESSION[$key]);

        } else {
            $_SESSION = [];
        }
    }

    /**
     * Check whether session is expired.
     *
     * @access public
     * @return bool
     */
    public static function isExpired() : bool
    {
        return self::get('--session-start') < Date::timeNow();
    }

    /**
     * Renew session when the given time is not up.
     *
     * @access public
     * @return void
     */
    public static function renew() : void
    {
        $time = self::get('--session-time');
        self::set('--session-start', Date::newTime(h: 0, m: 0, s: $time));
    }

    /**
     * Get current session id.
     *
     * @access public
     * @return int
     */
    public static function getSessionId() : int
    {
        return (int)self::get('--session-id');
    }

    /**
     * Get session name.
     *
     * @access public
     * @return mixed
     */
    public static function getName() : mixed
    {
        return session_name();
    }

    /**
     * Get session status.
     *
     * [Disabled : 0].
     * [None     : 1].
     * [Active   : 2].
     *
     * @access public
     * @return int
     */
    public static function getStatus() : int
    {
        return session_status();
    }

    /**
     * Check whether session is active.
     *
     * @access public
     * @return bool
     */
    public static function isActive() : bool
    {
        $status = self::getStatus();
        return $status === 2;
    }

    /**
     * Close session (Read-only).
     *
     * @access public
     * @return bool
     */
    public static function close() : bool
    {
        return session_write_close();
    }

    /**
     * End session (Destroy).
     *
     * @access public
     * @return bool
     */
    public static function end() : bool
    {
        if ( !self::isActive() ) {
            new self();
        }
        self::unset();
        return @session_destroy();
    }

    /**
     * Set session timeout.
     *
     * @access public
     * @param int $seconds
     * @return void
     */
    public static function setTimeout(int $seconds) : void
    {
        System::setIni('session.gc_maxlifetime', (string)$seconds);
        self::set('--session-timeout', $seconds);
        self::set('--session-expires-at', time() + $seconds);
    }

    /**
     * Check if session has timed out.
     *
     * @access public
     * @return bool
     */
    public static function isTimedOut() : bool
    {
        $expiresAt = self::get('--session-expires-at');
        return $expiresAt && time() > $expiresAt;
    }

    /**
     * Get session configuration.
     *
     * @access public
     * @param ?string $key
     * @return mixed
     */
    public static function getConfig(?string $key = null) : mixed
    {
        if ( $key ) {
            return self::$config[$key] ?? null;
        }
        return self::$config;
    }

    /**
     * Update session configuration at runtime.
     *
     * @access public
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function setConfig(string $key, mixed $value) : void
    {
        self::$config[$key] = $value;
        
        if ( strpos($key, 'cookie_') === 0 ) {
            $iniKey = "session.{$key}";
            System::setIni($iniKey, (string)$value);
        }
    }

    /**
     * Clear session data without destroying the session.
     *
     * @access public
     * @return void
     */
    public static function clear() : void
    {
        $_SESSION = [];
    }

    /**
     * Get session save path.
     *
     * @access public
     * @return string
     */
    public static function getSavePath() : string
    {
        return session_save_path();
    }

    /**
     * Set session save path.
     *
     * @access public
     * @param string $path
     * @return void
     */
    public static function setSavePath(string $path) : void
    {
        session_save_path($path);
    }

    /**
     * Check if session was regenerated in this request.
     *
     * @access public
     * @return bool
     */
    public static function wasRegenerated() : bool
    {
        return self::$regenerated;
    }

    /**
     * Flash a message for the next request.
     *
     * @access public
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function flash(string $key, mixed $value) : void
    {
        if ( !isset($_SESSION['--flash']) ) {
            $_SESSION['--flash'] = [];
        }
        $_SESSION['--flash'][$key] = $value;
    }

    /**
     * Get and remove flash message.
     *
     * @access public
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getFlash(string $key, mixed $default = null) : mixed
    {
        $value = $_SESSION['--flash'][$key] ?? $default;
        unset($_SESSION['--flash'][$key]);
        
        if ( empty($_SESSION['--flash']) ) {
            unset($_SESSION['--flash']);
        }
        
        return $value;
    }

    /**
     * Check if a flash message exists.
     *
     * @access public
     * @param string $key
     * @return bool
     */
    public static function hasFlash(string $key) : bool
    {
        return isset($_SESSION['--flash'][$key]);
    }

    /**
     * Get session fingerprint for security validation.
     *
     * @access public
     * @return string
     */
    public static function getFingerprint() : string
    {
        $userAgent = Server::get('http-user-agent') ?? '';
        $acceptLanguage = Server::get('http-accept-language') ?? '';
        $acceptEncoding = Server::get('http-accept-encoding') ?? '';
        
        return hash('sha256', $userAgent . $acceptLanguage . $acceptEncoding);
    }

    /**
     * Validate session fingerprint.
     *
     * @access public
     * @return bool
     */
    public static function validateFingerprint() : bool
    {
        $current = self::getFingerprint();
        $session = self::get('--session-fingerprint');
        
        if ( !$session ) {
            self::set('--session-fingerprint', $current);
            return true;
        }
        
        return $session === $current;
    }

    /**
     * Validate User-Agent consistency.
     *
     * @access private
     * @return bool
     */
    private static function validateUserAgent() : bool
    {
        $current = Server::get('http-user-agent') ?? '';
        $session = self::get('--session-user-agent');
        
        if ( !$session ) {
            self::set('--session-user-agent', $current);
            return true;
        }
        
        return $session === $current;
    }

    /**
     * Validate IP address consistency.
     *
     * @access private
     * @return bool
     */
    private static function validateIpAddress() : bool
    {
        $current = Server::getIp();
        $session = self::get('--session-ip-address');
        
        if ( !$session ) {
            self::set('--session-ip-address', $current);
            return true;
        }
        
        // Allow for proxy/load balancer
        return Validator::isSameSubnet($session, $current);
    }
}
