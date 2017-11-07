<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * CodeIgniter Session Redis Driver
 *
 * @package	CodeIgniter
 * @subpackage	Libraries
 * @category	Sessions
 * @author	Andrey Andreev
 * @link	https://codeigniter.com/user_guide/libraries/sessions.html
 */
class Session_Redis implements SessionHandlerInterface
{
	protected $_config;

	/**
	 * phpRedis instance
	 *
	 * @var	resource
	 */
	protected $_redis;

	/**
	 * Key prefix
	 *
	 * @var	string
	 */
	protected $_key_prefix = 'ci_session:';

	/**
	 * Lock key
	 *
	 * @var	string
	 */
	protected $_lock_key;

	/**
	 * Key exists flag
	 *
	 * @var bool
	 */
	protected $_key_exists = FALSE;
	
	/**
	 * Success and failure return values
	 *
	 * Necessary due to a bug in all PHP 5 versions where return values
	 * from userspace handlers are not handled properly. PHP 7 fixes the
	 * bug, so we need to return different values depending on the version.
	 *
	 * @see	https://wiki.php.net/rfc/session.user.return-value
	 * @var	mixed
	 */
	protected $_success, $_failure;
	

	// ------------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @param	array	$params	Configuration parameters
	 * @return	void
	 */
	public function __construct()
	{
		$this->_config = array(
			'host'=> Yaf\Registry::get('config')->redis->host,
			'port'=> Yaf\Registry::get('config')->redis->port,
			'password'=> Yaf\Registry::get('config')->redis->password,
			'timeout'=> Yaf\Registry::get('config')->redis->timeout,
			'match_ip'=> Yaf\Registry::get('config')->session->match_ip,
		);
		
		if (is_php('7'))
		{
			$this->_success = TRUE;
			$this->_failure = FALSE;
		}
		else
		{
			$this->_success = 0;
			$this->_failure = -1;
		}

		if ($this->_config['match_ip'] === TRUE)
		{
			$this->_key_prefix .= $_SERVER['REMOTE_ADDR'].':';
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Open
	 *
	 * Sanitizes save_path and initializes connection.
	 *
	 * @param	string	$save_path	Server path
	 * @param	string	$name		Session cookie name, unused
	 * @return	bool
	 */
	public function open($save_path, $name)
	{
		if (empty($this->_config))
		{
			return $this->_fail();
		}
		
		$redis = new Redis();
		if ( ! $redis->connect($this->_config['host'], $this->_config['port'], $this->_config['timeout']))
		{
			exit('Session: Unable to connect to Redis with the configured settings.');
		}
		elseif (isset($this->_config['password']) && !empty($this->_config['password']) && ! $redis->auth($this->_config['password']))
		{
			exit('Session: Unable to authenticate to Redis instance.');
		}
		elseif (isset($this->_config['database']) && ! $redis->select($this->_config['database']))
		{
			exit('Session: Unable to select Redis database with index '.$this->_config['database']);
		}
		else
		{
			$this->_redis = $redis;
			return $this->_success;
		}

		return $this->_fail();
	}

	// ------------------------------------------------------------------------

	/**
	 * Read
	 *
	 * Reads session data and acquires a lock
	 *
	 * @param	string	$session_id	Session ID
	 * @return	string	Serialized session data
	 */
	public function read($session_id)
	{
		if (isset($this->_redis) && $this->_get_lock($session_id))
		{
			// Needed by write() to detect session_regenerate_id() calls
			$this->_session_id = $session_id;

			$session_data = $this->_redis->get($this->_key_prefix.$session_id);

			is_string($session_data)
				? $this->_key_exists = TRUE
				: $session_data = '';

			$this->_fingerprint = md5($session_data);
			return $session_data;
		}

		return $this->_fail();
	}

	// ------------------------------------------------------------------------

	/**
	 * Write
	 *
	 * Writes (create / update) session data
	 *
	 * @param	string	$session_id	Session ID
	 * @param	string	$session_data	Serialized session data
	 * @return	bool
	 */
	public function write($session_id, $session_data)
	{
		if ( ! isset($this->_redis))
		{
			return $this->_fail();
		}
		// Was the ID regenerated?
		elseif ($session_id !== $this->_session_id)
		{
			if ( ! $this->_release_lock() OR ! $this->_get_lock($session_id))
			{
				return $this->_fail();
			}

			$this->_key_exists = FALSE;
			$this->_session_id = $session_id;
		}

		if (isset($this->_lock_key))
		{
			$this->_redis->setTimeout($this->_lock_key, 300);
			if ($this->_fingerprint !== ($fingerprint = md5($session_data)) OR $this->_key_exists === FALSE)
			{
				if ($this->_redis->set($this->_key_prefix.$session_id, $session_data, $this->_config['expiration']))
				{
					$this->_fingerprint = $fingerprint;
					$this->_key_exists = TRUE;
					return $this->_success;
				}

				return $this->_fail();
			}

			return ($this->_redis->setTimeout($this->_key_prefix.$session_id, $this->_config['expiration']))
				? $this->_success
				: $this->_fail();
		}

		return $this->_fail();
	}

	// ------------------------------------------------------------------------

	/**
	 * Close
	 *
	 * Releases locks and closes connection.
	 *
	 * @return	bool
	 */
	public function close()
	{
		if (isset($this->_redis))
		{
			try {
				if ($this->_redis->ping() === '+PONG')
				{
					$this->_release_lock();
					if ($this->_redis->close() === $this->_failure)
					{
						return $this->_fail();
					}
				}
			}
			catch (RedisException $e)
			{
				exit('Session: Got RedisException on close(): '.$e->getMessage());
			}

			$this->_redis = NULL;
			return $this->_success;
		}

		return $this->_success;
	}

	// ------------------------------------------------------------------------

	/**
	 * Destroy
	 *
	 * Destroys the current session.
	 *
	 * @param	string	$session_id	Session ID
	 * @return	bool
	 */
	public function destroy($session_id)
	{
		if (isset($this->_redis, $this->_lock_key))
		{
			if (($result = $this->_redis->delete($this->_key_prefix.$session_id)) !== 1)
			{
				exit('Session: Redis::delete() expected to return 1, got '.var_export($result, TRUE).' instead.');
			}

			$this->_cookie_destroy();
			return $this->_success;
		}

		return $this->_fail();
	}

	// ------------------------------------------------------------------------

	/**
	 * Garbage Collector
	 *
	 * Deletes expired sessions
	 *
	 * @param	int 	$maxlifetime	Maximum lifetime of sessions
	 * @return	bool
	 */
	public function gc($maxlifetime)
	{
		// Not necessary, Redis takes care of that.
		return $this->_success;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get lock
	 *
	 * Acquires an (emulated) lock.
	 *
	 * @param	string	$session_id	Session ID
	 * @return	bool
	 */
	protected function _get_lock($session_id)
	{
		// PHP 7 reuses the SessionHandler object on regeneration,
		// so we need to check here if the lock key is for the
		// correct session ID.
		if ($this->_lock_key === $this->_key_prefix.$session_id.':lock')
		{
			return $this->_redis->setTimeout($this->_lock_key, 300);
		}

		// 30 attempts to obtain a lock, in case another request already has it
		$lock_key = $this->_key_prefix.$session_id.':lock';
		$attempt = 0;
		do
		{
			if (($ttl = $this->_redis->ttl($lock_key)) > 0)
			{
				sleep(1);
				continue;
			}

			if ( ! $this->_redis->setex($lock_key, 300, time()))
			{
				exit('Session: Error while trying to obtain lock for '.$this->_key_prefix.$session_id);
				return FALSE;
			}

			$this->_lock_key = $lock_key;
			break;
		}
		while (++$attempt < 30);

		if ($attempt === 30)
		{
			exit('Session: Unable to obtain lock for '.$this->_key_prefix.$session_id.' after 30 attempts, aborting.');
			return FALSE;
		}
		elseif ($ttl === -1)
		{
			exit('Session: Lock for '.$this->_key_prefix.$session_id.' had no TTL, overriding.');
		}

		$this->_lock = TRUE;
		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Release lock
	 *
	 * Releases a previously acquired lock
	 *
	 * @return	bool
	 */
	protected function _release_lock()
	{
		if (isset($this->_redis, $this->_lock_key) && $this->_lock)
		{
			if ( ! $this->_redis->delete($this->_lock_key))
			{
				exit('Session: Error while trying to free lock for '.$this->_lock_key);
				return FALSE;
			}

			$this->_lock_key = NULL;
			$this->_lock = FALSE;
		}

		return TRUE;
	}

}
