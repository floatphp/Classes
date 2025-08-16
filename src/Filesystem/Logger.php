<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file is a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

use FloatPHP\Interfaces\Classes\LoggerInterface;

/**
 * Built-in secure file-based Logger class.
 */
class Logger implements LoggerInterface
{
    /**
     * @access protected
     * @var string $path
     * @var string $filename
     * @var string $extension
     */
    protected $path;
    protected $filename;
    protected $extension;

    /**
     * Init logger.
     * 
     * @param string|null $path
     * @param string $file
     * @param string $ext
     * @throws \InvalidArgumentException When path parameters are invalid
     * @throws \RuntimeException When directory creation fails
     */
    public function __construct(?string $path = '/', string $file = 'debug', string $ext = 'log')
    {
        $this->setPath($path ?? '/');
        $this->setFilename($file);
        $this->setExtension($ext);
    }

    /**
     * Set log path.
     *
     * @access public
     * @param string $path
     * @return self
     * @throws \InvalidArgumentException When path is invalid
     * @throws \RuntimeException When directory creation fails
     */
    public function setPath(string $path) : self
    {
        // Input validation
        if ( empty($path) ) {
            throw new \InvalidArgumentException('Log path cannot be empty');
        }

        // Sanitize path to prevent directory traversal attacks
        $path = $this->sanitizePath($path);

        // Validate path format
        if ( !$this->isValidPath($path) ) {
            throw new \InvalidArgumentException("Invalid log path format: {$path}");
        }

        $this->path = $path;

        // Create directory if it doesn't exist
        if ( !File::isDir($this->path) ) {
            if ( !File::addDir($this->path) ) {
                throw new \RuntimeException("Failed to create log directory: {$this->path}");
            }
        }

        return $this;
    }

    /**
     * Get logger path.
     * 
     * @access public
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * Set log filename.
     *
     * @access public
     * @param string $filename
     * @return self
     * @throws \InvalidArgumentException When filename is invalid
     */
    public function setFilename(string $filename) : self
    {
        // Input validation
        if ( empty($filename) ) {
            throw new \InvalidArgumentException('Log filename cannot be empty');
        }

        // Sanitize filename
        $filename = $this->sanitizeFilename($filename);

        $this->filename = $filename;
        return $this;
    }

    /**
     * Set log extension.
     *
     * @access public
     * @param string $extension
     * @return self
     * @throws \InvalidArgumentException When extension is invalid
     */
    public function setExtension(string $extension) : self
    {
        // Input validation
        if ( empty($extension) ) {
            throw new \InvalidArgumentException('Log extension cannot be empty');
        }

        // Remove leading dot if present and validate
        $extension = ltrim($extension, '.');
        if ( !preg_match('/^[a-zA-Z0-9]+$/', $extension) ) {
            throw new \InvalidArgumentException("Invalid log extension format: {$extension}");
        }

        $this->extension = $extension;
        return $this;
    }

    /**
     * Log debug message.
     *
     * @access public
     * @param mixed $message
     * @param bool $isArray
     * @return bool
     * @throws \InvalidArgumentException When message is invalid
     * @throws \RuntimeException When logging fails
     */
    public function debug($message, bool $isArray = false) : bool
    {
        $message = $this->formatMessage($message, $isArray);
        return $this->write('debug', $message);
    }

    /**
     * Log error message.
     *
     * @access public
     * @param string $message
     * @return bool
     * @throws \InvalidArgumentException When message is invalid
     * @throws \RuntimeException When logging fails
     */
    public function error(string $message) : bool
    {
        return $this->write('error', $this->validateMessage($message));
    }

    /**
     * Log warning message.
     *
     * @access public
     * @param string $message
     * @return bool
     * @throws \InvalidArgumentException When message is invalid
     * @throws \RuntimeException When logging fails
     */
    public function warning(string $message) : bool
    {
        return $this->write('warning', $this->validateMessage($message));
    }

    /**
     * Log info message.
     *
     * @access public
     * @param string $message
     * @return bool
     * @throws \InvalidArgumentException When message is invalid
     * @throws \RuntimeException When logging fails
     */
    public function info(string $message) : bool
    {
        return $this->write('info', $this->validateMessage($message));
    }

    /**
     * Log custom message.
     *
     * @access public
     * @param string $message
     * @param string $type
     * @return bool
     * @throws \InvalidArgumentException When message or type is invalid
     * @throws \RuntimeException When logging fails
     */
    public function custom(string $message, string $type = 'custom') : bool
    {
        return $this->write($this->validateLogType($type), $this->validateMessage($message));
    }

    /**
     * Log natif error.
     *
     * @access public
     * @param string $message
     * @param int $type 0
     * @param string $path
     * @param string $headers
     * @return bool
     */
    public function log(string $message, int $type = 0, ?string $path = null, ?string $headers = null) : bool
    {
        return error_log($message, $type, $path, $headers);
    }

    /**
     * Write message.
     *
     * @access protected
     * @param string $status 
     * @param string $message 
     * @return bool
     * @throws \RuntimeException When file operations fail
     */
    protected function write(string $status, string $message) : bool
    {
        try {
            // Optimize: Calculate date once
            $currentDate = date('[d-m-Y]');
            $currentDateTime = date('[d-m-Y H:i:s]');

            $log = "{$this->path}/{$this->filename}-{$currentDate}.{$this->extension}";
            $msg = "{$currentDateTime} : [{$status}] - {$message}" . Stringify::break();

            $result = File::w($log, $msg, true);
            if ( !$result ) {
                throw new \RuntimeException("Failed to write to log file: {$log}");
            }

            return $result;

        } catch (\Exception $e) {
            // In production, you might want to handle this differently
            throw new \RuntimeException("Logging failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Sanitize file path to prevent directory traversal attacks.
     *
     * @access private
     * @param string $path
     * @return string
     */
    private function sanitizePath(string $path) : string
    {
        // Remove null bytes
        $path = str_replace("\0", '', $path);

        // Normalize path separators to forward slashes
        $path = str_replace('\\', '/', $path);

        // Remove dangerous patterns but preserve valid relative paths
        $path = preg_replace('/\.\.+\//', '', $path);

        // Normalize multiple slashes
        $path = preg_replace('#/+#', '/', $path);

        // Remove trailing slash except for root and Windows drive root
        if ( !preg_match('/^[a-zA-Z]:\/$/i', $path) && $path !== '/' ) {
            $path = rtrim($path, '/');
        }

        // Handle empty path
        if ( empty($path) ) {
            $path = '/';
        }

        return $path;
    }

    /**
     * Validate path format.
     *
     * @access private
     * @param string $path
     * @return bool
     */
    private function isValidPath(string $path) : bool
    {
        // Check for dangerous patterns
        if ( strpos($path, '..') !== false ) {
            return false;
        }

        // Check for null bytes
        if ( strpos($path, "\0") !== false ) {
            return false;
        }

        // Allow Windows drive letters (C:, D:, etc.) and standard path characters
        // Updated regex to support Windows paths with drive letters and colons
        return (bool)preg_match('/^[a-zA-Z]?:?[a-zA-Z0-9\/\\._\-\s]*$/', $path);
    }

    /**
     * Sanitize filename.
     *
     * @access private
     * @param string $filename
     * @return string
     */
    private function sanitizeFilename(string $filename) : string
    {
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9\._-]/', '', $filename);

        // Remove multiple dots
        $filename = preg_replace('/\.+/', '.', $filename);

        // Remove leading/trailing dots and dashes
        $filename = trim($filename, '.-');

        return $filename ?: 'default';
    }

    /**
     * Format message for logging.
     *
     * @access private
     * @param mixed $message
     * @param bool $isArray
     * @return string
     * @throws \InvalidArgumentException When message cannot be formatted
     */
    private function formatMessage($message, bool $isArray = false) : string
    {
        if ( is_resource($message) ) {
            throw new \InvalidArgumentException('Cannot log resource type');
        }

        if ( $isArray || is_array($message) || is_object($message) ) {
            return print_r($message, true);
        }

        return (string)$message;
    }

    /**
     * Validate log message.
     *
     * @access private
     * @param string $message
     * @return string
     * @throws \InvalidArgumentException When message is invalid
     */
    private function validateMessage(string $message) : string
    {
        if ( empty($message) ) {
            throw new \InvalidArgumentException('Log message cannot be empty');
        }

        // Remove null bytes and control characters (except newlines and tabs)
        $message = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F]/', '', $message);

        // Limit message length to prevent log file bloat
        if ( strlen($message) > 8192 ) {
            $message = substr($message, 0, 8189) . '...';
        }

        return $message;
    }

    /**
     * Validate log type.
     *
     * @access private
     * @param string $type
     * @return string
     * @throws \InvalidArgumentException When type is invalid
     */
    private function validateLogType(string $type) : string
    {
        if ( empty($type) ) {
            throw new \InvalidArgumentException('Log type cannot be empty');
        }

        // Sanitize log type
        $type = preg_replace('/[^a-zA-Z0-9_-]/', '', $type);

        if ( empty($type) ) {
            throw new \InvalidArgumentException('Invalid log type format');
        }

        return strtolower($type);
    }
}
