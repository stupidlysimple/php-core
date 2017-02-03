<?php
/**
 * StupidlySimple Framework - A PHP Framework For Lazy Developers
 *
 * Copyright (c) 2017 Fariz Luqman
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package     StupidlySimple
 * @author      Fariz Luqman <fariz.fnb@gmail.com>
 * @copyright   2017 Fariz Luqman
 * @license     MIT
 * @since       0.3.3
 * @link        https://stupidlysimple.github.io/
 */
namespace Core;

/**
 * The Configuration Loader
 * -----------------------------------------------------------------------
 *
 * The Configuration loader are responsible to read and return the
 * configurations in a form of array.
 *
 * Usage:
 * 1. Get the entire configuration from a file:
 * 	  $config = Config::get('filename');
 *
 * 2. Get specific configuration from a file:
 *    $config = Config::get('filename', 'configuration_key');
 *
 */
class Config {

	/**
	 * The array of configuration from config/env.php
	 * @var array
	 * @access protected
	 * @static
	 */
	protected static $env = null;

	/**
	 * The array of configuration from files located on config directory
	 * @var array
	 * @access protected
	 * @static
	 */
	protected static $hive = null;

	/**
	 * Link a variable or an object to the container
	 *
	 * @param string	$file 	the configuration file name (without .php)
	 * @param string	$key	the array key
	 *
	 * @return array	$hive	the array of configurations
	 *
	 * @static
	 * @access public
	 * @since Method available since 0.1.1
	 */
	public static function get($file, $key = null){
		if(isset(self::$hive[$file]) === false){
			self::$hive[$file] = include_once(SS_PATH.'config/'.$file.'.php');
		}

		if($key === null){
			return self::$hive[$file];
		}else{
			return self::$hive[$file][$key];
		}
	}

	/**
	 * Reads the configuration file (config/env.php) and include each of the
	 * variables (retrieved in a form of associative array) to the Environment
	 * Variable. Also store the configurations into static variable $env
	 *
	 * @static
	 * @access public
	 * @since Method available since Release 0.1.1
	 */
	public static function setEnv(){
		if(self::$env === null){
			self::$env = require_once(SS_PATH.'config/env.php');
		}

		foreach(self::$env as $v => $a){
			putenv($v.'='.$a);
		}
	}
}