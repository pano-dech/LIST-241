<?php
namespace App\Core;

/**
 * Session Class
 * 
 * Handles session management
 */
class Session
{
    /**
     * Start the session
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            $sessionParams = [
                'cookie_httponly' => true,     // Prevent JavaScript access to session cookie
                'cookie_secure' => isset($_SERVER['HTTPS']), // Secure cookie if using HTTPS
                'use_strict_mode' => true,     // Enforce strict session ID mode
                'use_only_cookies' => true,    // Force using cookies for session
                'gc_maxlifetime' => 7200       // Session timeout (2 hours)
            ];
            
            session_set_cookie_params($sessionParams);
            session_start();
        }
        
        // Regenerate session ID periodically to prevent session fixation
        if (!isset($_SESSION['last_regeneration'])) {
            $this->regenerateId();
        } elseif ($_SESSION['last_regeneration'] < (time() - 1800)) {
            // Regenerate session ID every 30 minutes
            $this->regenerateId();
        }
    }
    
    /**
     * Regenerate session ID
     */
    public function regenerateId()
    {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    /**
     * Set a value in the session
     * 
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get a value from the session
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if a session key exists
     * 
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove a value from the session
     * 
     * @param string $key
     */
    public function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Clear all session data
     */
    public function clear()
    {
        session_unset();
    }
    
    /**
     * Destroy the session
     */
    public function destroy()
    {
        session_unset();
        session_destroy();
        
        // Clear the session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
    }
    
    /**
     * Set a flash message
     * 
     * @param string $key
     * @param mixed $value
     */
    public function setFlash($key, $value)
    {
        $_SESSION['flash'][$key] = $value;
    }
    
    /**
     * Get a flash message and remove it
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getFlash($key, $default = null)
    {
        $value = $_SESSION['flash'][$key] ?? $default;
        unset($_SESSION['flash'][$key]);
        return $value;
    }
    
    /**
     * Check if a flash message exists
     * 
     * @param string $key
     * @return bool
     */
    public function hasFlash($key)
    {
        return isset($_SESSION['flash'][$key]);
    }
    
    /**
     * Set CSRF token
     * 
     * @return string
     */
    public function setCsrfToken()
    {
        $token = bin2hex(random_bytes(32));
        $this->set('csrf_token', $token);
        return $token;
    }
    
    /**
     * Get CSRF token
     * 
     * @return string
     */
    public function getCsrfToken()
    {
        if (!$this->has('csrf_token')) {
            return $this->setCsrfToken();
        }
        
        return $this->get('csrf_token');
    }
    
    /**
     * Validate CSRF token
     * 
     * @param string $token
     * @return bool
     */
    public function validateCsrfToken($token)
    {
        return $token === $this->get('csrf_token');
    }
}