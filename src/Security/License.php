<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Security Component
 * @version    : 1.2.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Security;

use FloatPHP\Classes\{
    Http\Server,
    Server\System,
    Filesystem\TypeCheck,
    Filesystem\Stringify,
    Filesystem\Arrayify
};

/**
 * Built-in licensing class.
 */
class License
{
    /**
     * @access protected
     * @var array $settings, License settings
     * @var array $server, Server data
     * @var array $strings, License strings
     * @var string $key, License crypto key
     * @var string $id, License crypto id
     */
    protected $settings = [];
    protected $server = [];
    protected $strings = [];
    protected $key;
    protected $id;

    /**
     * @access private
     * @var string $error, License error
     * @var array $data, License data
     */
    private $error;
    private $data;
    
    /**
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $this->init($settings);
        $this->setServerData();
        $this->setKey();
        $this->setId();
        $this->setStrings();
    }

    /**
     * Init license settings.
     *
     * @access public
     * @param array $settings
     * @return void
     */
    public function init(array $settings = [])
    {
        $this->settings = Arrayify::merge(
            $this->getDefaultSettings(),
            $settings
        );
    }

    /**
     * Set static key.
     *
     * @access public
     * @param string $key
     * @return void
     */
    public function setKey(?string $key = null)
    {
        $this->key = $key ?: $this->getDefaultKey();
    }

    /**
     * Set static id.
     *
     * @access public
     * @param string $id
     * @return void
     */
    public function setId(?string $id = null)
    {
        $this->id = $id ?: $this->getDefaultId();
    }

    /**
     * Set static strings.
     *
     * @access public
     * @param array $strings
     * @return void
     */
    public function setStrings(array $strings = [])
    {
        $this->strings = Arrayify::merge(
            $this->getDefaultStrings(),
            $strings
        );
    }

    /**
     * Get license data.
     *
     * @access public
     * @return array
     */
    public function getData() : array
    {
        return (array)$this->data;
    }

    /**
     * Get license error.
     *
     * @access public
     * @return string
     */
    public function getError() : string
    {
        return (string)$this->error;
    }

    /**
     * Get license data var.
     *
     * @access public
     * @param string $var
     * @return mixed
     */
    public function getDataVar(string $var)
    {
        return $this->data['args'][$var] ?? '';
    }

    /**
     * Generate licence.
     *
     * @access public
     * @param int $start
     * @param mixed $expireIn
     * @param array $args
     * @return mixed
     */
    public function generate(int $start = 0, $expireIn = 3600, array $args = [])
    {
        // Include key id
        $data['id'] = md5($this->key);

        // Include server IP
        $data['server']['ip'] = $this->server['ip'];
        // Include server HOST
        $data['server']['host'] = $this->server['host'];
        
        // Include time
        if ( $this->useTime() ) {

            $current = time();
            $start = ($current < $start) ? $start : $current + $start;

            // Set dates
            $data['date']['start'] = $start;
            $data['date']['span'] = $expireIn;

            if ( $expireIn === false ) {
                $data['date']['end'] = 'never';

            } else {
                $data['date']['end'] = $start + $expireIn;
            }
        }

        // Include args
        $args = Arrayify::merge($this->getDefaultArgs(), $args);
        $data['args'] = $args;

        // Encrypt the key
        return $this->wrap($data);
    }

    /**
     * Validate license secret.
     *
     * @access public
     * @param string $secret
     * @return bool
     */
    public function validate(string $secret) : bool
    {
        // Content validation
        if ( strlen($secret) <= 0 ) {
            $this->error = 'Empty';
            return false;
        }

        // Decrypt secret
        $this->data = $this->unwrap($secret);

        // Type validation
        if ( !TypeCheck::isArray($this->data) ) {
            $this->error = 'Invalid';
            return false;
        }

        // Id validation
        $id = $this->data['id'] ?? '';
        if ( $id !== md5($this->key) ) {
            $this->error = 'Corrupted';
            return false;
        }

        // Server validation
        if ( $this->useServer() ) {

            // Check localhost
            if ( $this->isLocalhost($this->data) && !$this->allowLocalhost() ) {
                $this->error = 'Denied localhost';
                return false;
            }

            // Check host
            if ( $this->data['server']['host'] !== $this->server['host'] ) {
                $this->error = 'Denied host';
                return false;
            }

            // Check host
            if ( $this->data['server']['ip'] !== $this->server['ip'] ) {
                $this->error = 'Denied ip';
                return false;
            }
        }

        // Time validation
        if ( $this->useTime() ) {

            // Formated start date
            $formated = date($this->settings['date-format'],$this->data['date']['start']);
            $this->data['date']['formated']['start'] = $formated;
            
            // Formated end date
            $formated = date($this->settings['date-format'],$this->data['date']['end']);
            $this->data['date']['formated']['end'] = $formated;

            // License used before start
            if ( $this->data['date']['start'] > (time() + $this->settings['start']) ) {
                $this->error = 'Minus';
                return false;
            }

            if ( $this->data['date']['end'] !== 'never' ) {

                // License expired
                if ( ($this->data['date']['end'] - time()) < 0 ) {
                    $this->error = 'Expired';
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Set server data.
     *
     * @access protected
     * @return void
     */
    protected function setServerData()
    {
        $this->server = [
            'ip'   => Server::getIp(),
            'host' => Server::isSetted('http-host') ? Server::get('http-host') : ''
        ];
    }

    /**
     * Server validation status.
     *
     * @access protected
     * @return bool
     */
    protected function useServer() : bool
    {
        return (bool)$this->settings['server'];
    }

    /**
     * Time validation status.
     *
     * @access protected
     * @return bool
     */
    protected function useTime() : bool
    {
        return (bool)$this->settings['time'];
    }

    /**
     * Localhost validation status.
     *
     * @access protected
     * @return bool
     */
    protected function allowLocalhost() : bool
    {
        return (bool)$this->settings['localhost'];
    }

    /**
     * Get default license key.
     *
     * @access protected
     * @return string
     */
    protected function getDefaultKey() : string
    {
        $key  = 'YmUzYWM2sNGU24NbA363zA7IDSDFGDFGB5aVi35B';
        $key .= 'DFGQ3YNO36ycDFGAATq4sYmSFVDFGDFGps7XDYEz';
        $key .= 'GDDw96OnMW3kjCFJ7M+UV2kHe1WTTEcM09UMHHTX';
        return $key;
    }

    /**
     * Get default license Id.
     *
     * @access protected
     * @return string
     */
    protected function getDefaultId() : string
    {
        return 'nSpkAHRiFfM2hE588eBe';
    }

    /**
     * Get default license strings.
     *
     * @access protected
     * @return array
     */
    protected function getDefaultStrings() : array
    {
        return [
            'begin'     => 'BEGIN LICENSE',
            'end'       => 'END LICENSE',
            'separator' => '-'
        ];
    }

    /**
     * Get default license settings.
     *
     * @access protected
     * @return array
     */
    protected function getDefaultSettings() : array
    {
        return [
            'time'        => true,
            'server'      => true,
            'localhost'   => true,
            'start'       => 129600,
            'wrap'        => 120,
            'date-format' => 'm/d/Y H:i:s'
        ];
    }

    /**
     * Get default license data args.
     *
     * @access protected
     * @return array
     */
    protected function getDefaultArgs() : array
    {
        return [
            '--PHP-OS'      => System::getOsName(),
            '--PHP-VERSION' => System::getPhpVersion()
        ];
    }

    /**
     * Inline license string.
     *
     * @access protected
     * @param string $string
     * @return string
     */
    protected function inlineString(string $string) : string
    {
        $length = strlen($string);
        $spaces = ($this->settings['wrap'] - $length) / 2;
        $str = '';
        for ($i = 0; $i < $spaces; $i++) {
            $str = "{$str}{$this->strings['separator']}";
        }
        if ( $spaces/2 != round($spaces/2) ) {
            $string = substr($str, 0, strlen($str) - 1) . $string;
        } else {
            $string = "{$str}{$string}";
        }
        $string = "{$string}{$str}";
        return $string;
    }

    /**
     * Encrypt data.
     *
     * @access protected
     * @param array $data
     * @return string
     */
    protected function encrypt(array $data) : string
    {
        // Get random
        $random = Tokenizer::generate(5);

        // Get key
        $key = "{$random}{$this->key}";

        // Init encryption
        $secret = '';

        // Regular encryption method
        $data = Stringify::serialize($data);
        for ($i = 1; $i <= strlen($data); $i++) {
            $char = substr($data, $i - 1, 1);
            $keyChar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) + ord($keyChar));
            $secret .= $char;
        }

        // Return license secret
        $secret = Tokenizer::base64(trim($secret), 2);
        return "{$random}{$secret}";
    }

    /**
     * Decrypt license.
     *
     * @access public
     * @param string $license
     * @return array
     */
    protected function decrypt(string $secret) : array
    {
        $random = substr($secret, 0, 5);
        $secret = Tokenizer::unbase64(substr($secret, 5), 2);

        // Get key
        $key = "{$random}{$this->key}";

        // Init decryption
        $data = '';

        // Regular decryption method
        for ($i = 1; $i <= strlen($secret); $i++) {
            $char = substr($secret, $i - 1, 1);
            $keyChar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) - ord($keyChar));
            $data .= $char;
        }

        // Return data
        return (array)Stringify::unserialize($data);
    }

    /**
     * Wrap license data.
     *
     * @access protected
     * @param array $data
     * @return string
     */
    protected function wrap(array $data) : string
    {
        // Encrypt license data
        $secret = $this->encrypt($data);

        // Wrap secret
        $result  = $this->inlineString($this->strings['begin']) . Stringify::break();
        $result .= wordwrap($secret, $this->settings['wrap'], Stringify::break(), true);
        $result .= Stringify::break() . $this->inlineString($this->strings['end']);

        return $result;
    }

    /**
     * Unwrap license.
     *
     * @access public
     * @param string $secret
     * @return array
     */
    protected function unwrap(string $secret) : array
    {
        // Sort variables
        $begin = $this->inlineString($this->strings['begin']);
        $end = $this->inlineString($this->strings['end']);

        // Format license secret
        $replace = [$begin, $end, "\r", "\n", "\t"];
        $secret = trim(Stringify::remove($replace, $secret));

        // Decrypt license secret
        return $this->decrypt($secret);
    }

    /**
     * Check localhost.
     *
     * @access private
     * @param array $data
     * @return bool
     */
    private function isLocalhost(array $data = []) : bool
    {
        $local = ['127.0.0.1', '::1'];
		if ( isset($data['server']['ip']) ) {
			if ( Stringify::contains($local, $data['server']['ip']) ) {
				return true;
			}
		} elseif ( isset($data['server']['host'] )) {
			if ( Stringify::contains($local, $data['server']['host']) ) {
				return true;
			}
		}
		return false;
    }
}
