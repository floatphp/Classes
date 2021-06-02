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

use FloatPHP\Classes\Http\Server;
use FloatPHP\Classes\Server\System;
use FloatPHP\Classes\Security\Tokenizer;

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
     * Init license settings
     *
     * @access public
     * @param array $settings
     * @return void
     */
    public function init(array $settings = [])
    {
        $this->settings = Arrayify::merge($this->getDefaultSettings(),$settings);
    }

    /**
     * Set static key
     *
     * @access public
     * @param string $key
     * @return void
     */
    public function setKey(string $key = '')
    {
        $this->key = !empty($key) ? $key : $this->getDefaultKey();
    }

    /**
     * Set static id
     *
     * @access public
     * @param string $id
     * @return void
     */
    public function setId(string $id = '')
    {
        $this->id = !empty($id) ? $id : $this->getDefaultId();
    }

    /**
     * Set static strings
     *
     * @access public
     * @param array $strings
     * @return void
     */
    public function setStrings(array $strings = [])
    {
        $this->strings = Arrayify::merge($this->getDefaultStrings(),$strings);
    }

    /**
     * Validate license and return data
     *
     * @access public
     * @param string $license
     * @return array
     */
    public function validate(string $license = '') : array
    {
        // Content validation
        if ( strlen($license) <= 0 ) {
            return ['error' => 'empty'];
        }

        // Decrypt license
        $data = $this->unwrap($license);

        // Type validation
        if ( !TypeCheck::isArray($data) ) {
            return ['error' => 'invalid'];
        }

        // ID validation
        if ( $data['ID'] !== md5($this->key) ) {
            return ['error' => 'corrupted'];
        }

        // Time validation
        if ( $this->useTime() ) {

            // License used before start
            if ( $data['date']['start'] > (time() + $this->settings['start']) ) {
                return ['error' => 'minus'];
            } else {
                $data['date']['formated']['start'] = date($this->settings['date-format'],$data['date']['start']);
            }

            if ( $data['date']['end'] !== 'never' ) {
                // License expired
                if ( ($data['date']['end'] - time()) < 0 ) {
                    return ['error' => 'expired'];
                } else {
                    $data['date']['formated']['end'] = date($this->settings['date-format'],$data['date']['end']);
                }
            }
        }

        // Server validation
        if ( $this->useServer() ) {

            // Check localhost
            if ( $this->isLocalhost($data) && !$this->allowLocalhost() ) {
                return ['error' => 'localhost-denied'];
            }

            // Check domain
            if ( !$this->validateIP($data['server']['domain']) ) {
                return ['error' => 'domain-denied'];
            }

            // Check mac
            if ( $data['server']['mac'] !== $this->server['mac'] ) {
                return ['error' => 'mac-denied'];
            }

            // Check host
            if ( $data['server']['host'] !== $this->server['host'] ) {
                return ['error' => 'host-denied'];
            }

            // Check host
            if ( $data['server']['ip'] !== $this->server['ip'] ) {
                return ['error' => 'ip-denied'];
            }
        }

        return ['result' => 'success'];
    }

    /**
     * Set server data
     *
     * @access protected
     * @param void
     * @return void
     */
    protected function setServerData()
    {
        $this->server = [
            'ip'      => Server::getIP(),
            'mac'     => Server::getMac(),
            'address' => Server::isSetted('server-addr') ? Server::get('server-addr') : '',
            'host'    => Server::isSetted('http-host') ? Server::get('http-host') : ''
        ];
    }

    /**
     * Server validation status
     *
     * @access protected
     * @param void
     * @return bool
     */
    protected function useServer() : bool
    {
        return $this->settings['server'];
    }

    /**
     * Time validation status
     *
     * @access protected
     * @param void
     * @return bool
     */
    protected function useTime() : bool
    {
        return $this->settings['time'];
    }

    /**
     * Localhost validation status
     *
     * @access protected
     * @param void
     * @return bool
     */
    protected function allowLocalhost() : bool
    {
        return $this->settings['localhost'];
    }

    /**
     * Get default license key
     *
     * @access protected
     * @param void
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
     * Get default license Id
     *
     * @access protected
     * @param void
     * @return string
     */
    protected function getDefaultId() : string
    {
        return 'nSpkAHRiFfM2hE588eBe';
    }

    /**
     * Get default license strings
     *
     * @access protected
     * @param void
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
     * Get default license settings
     *
     * @access protected
     * @param void
     * @return array
     */
    protected function getDefaultSettings() : array
    {
        return [
            'time'        => true,
            'server'      => true,
            'localhost'   => true,
            'start'       => 129600,
            'wrap'      => 120,
            'date-format' => 'm/d/Y H:i:s'
        ];
    }

    /**
     * Get default license data args
     *
     * @access protected
     * @param void
     * @return array
     */
    protected function getDefaultArgs() : array
    {
        return [
            '--PHP-OS'      => System::getOs(),
            '--PHP-VERSION' => System::getPhpVersion()
        ];
    }

    /**
     * Generate licence
     *
     * @access public
     * @param string $domain
     * @param int $start
     * @param int|bool $expireIn
     * @param array $args
     * @return string
     */
    public function generate(string $domain = '', int $start = 0, $expireIn = 31449600, array $args = []) : string
    {
        // Include key id
        $data['id'] = md5($this->key);

        // Include server vars
        if ( $this->useServer() ) {

            // Validate domain IP
            if ( !$this->validateIP($domain) ) {
                return ['error' => 'domain-denied'];
            }

            // Set domain
            $data['server']['domain'] = $domain;
            // Set MAC
            $data['server']['mac'] = $this->server['mac'];
            // Set server HOST
            $data['server']['host'] = $this->server['host'];
            // Set server ADDRESS
            $data['server']['address'] = $this->server['address'];
            // Set server IP
            $data['server']['ip'] = $this->server['ip'];
        }
        
        // Include time
        if ( $this->useTime() ) {

            $current = time();
            $start = ($current < $start) ? $start : $current + $start;

            // Set dates
            $data['date']['start'] = $start;
            $data['date']['SPAN'] = $expireIn;
            if ( $expireIn === false ) {
                $data['date']['end'] = 'never';
            } else {
                $data['date']['end'] = $start + $expireIn;
            }
        }

        // Include args
        $args = Arrayify::merge($this->getDefaultArgs(),$args);
        $data['DATA'] = $args;

        // Encrypt the key
        return $this->wrap($data);
    }

    /**
     * Validate domain IP
     *
     * @access protected
     * @param string $domain
     * @return bool
     */
    protected function validateIP(string $domain) : bool
    {
        // Get domain IP list
        $ips = gethostbynamel($domain);
        if ( $domain == 'localhost' ) {
            $ips[] = '::1';
        }
        if ( TypeCheck::isArray($ips) && count($ips) > 0 ) {
            if ( Stringify::contains($ips,$this->server['ip']) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Inline license string
     *
     * @access protected
     * @param string $string
     * @return string
     */
    protected function inline(string $string) : string
    {
        $length = strlen($string);
        $spaces = ($this->settings['wrap'] - $length) / 2;
        $str = '';
        for ($i = 0; $i < $spaces; $i++) {
            $str = "{$str}{$this->strings['separator']}";
        }
        if ( $spaces/2 != round($spaces/2) ) {
            $string = substr($str,0,strlen($str) - 1) . $string;
        } else {
            $string = "{$str}{$string}";
        }
        $string = "{$string}{$str}";
        return $string;
    }

    /**
     * Encrypt data
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
        $license = '';

        // Regular encryption method
        $data = Stringify::serialize($data);
        for ($i = 1; $i <= strlen($data); $i++) {
            $char = substr($data,$i - 1,1);
            $keyChar = substr($key,($i % strlen($key)) - 1,1);
            $char = chr(ord($char) + ord($keyChar));
            $license .= $char;
        }

        // Return license
        $license = Tokenizer::base64(trim($license),2);
        return "{$random}{$license}";
    }

    /**
     * Decrypt license
     *
     * @access public
     * @param string $license
     * @param string $type
     * @return array
     */
    protected function decrypt(string $license, string $type = 'KEY') : array
    {
        $random = substr($license,0,5);
        $license = Tokenizer::unbase64(substr($license,5),2);

        // Get key
        $key = "{$random}{$this->key}";

        // Init decryption
        $data = '';

        // Regular decryption method
        for ($i = 1; $i <= strlen($license); $i++) {
            $char = substr($license, $i - 1, 1);
            $keyChar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) - ord($keyChar));
            $data .= $char;
        }

        // Return data
        return Stringify::unserialize($data);
    }

    /**
     * Wrap license data
     *
     * @access protected
     * @param array $data
     * @param string $type
     * @return string
     */
    protected function wrap($data, $type = 'KEY') : string
    {
        // Encrypt license data
        $license = $this->encrypt($data,$type);
        // Wrap license
        $result  = $this->inline($this->strings['begin']) . PHP_EOL;
        $result .= wordwrap($license,$this->settings['wrap'],PHP_EOL,1);
        $result .= PHP_EOL . $this->inline($this->strings['end']);
        return $result;
    }

    /**
     * Unwrap license
     *
     * @access public
     * @param string $license
     * @param string $type
     * @return array
     */
    protected function unwrap($license, $type = 'KEY') : array
    {
        // Sort variables
        $begin = $this->inline($this->strings['begin']);
        $end = $this->inline($this->strings['end']);
        // Format license
        $license = trim(Stringify::replace([$begin,$end,"\r","\n","\t"],'',$license));
        // Decrypt license
        return $this->decrypt($license,$type);
    }

    /**
     * Check localhost
     *
     * @access private
     * @param array $data
     * @return bool
     */
    private function isLocalhost($data = []) : bool
    {
        $local = ['127.0.0.1','::1'];
		if ( isset($data['server']['ip']) ) {
			if ( Stringify::contains($local,$data['server']['ip']) ) {
				return true;
			}

		} elseif ( isset($data['server']['address'] )) {
			if ( Stringify::contains($local,$data['server']['address']) ) {
				return true;
			}

		} elseif ( isset($data['server']['host'] )) {
			if ( Stringify::contains($local,$data['server']['host']) ) {
				return true;
			}
		}
		return false;
    }
}
