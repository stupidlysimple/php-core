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
 * The Singleton
 * -----------------------------------------------------------------------
 *
 * Simply extends this Singleton class if you wish to use the Singleton
 * pattern of programming in your project
 *
 */
class Singleton {
	private static $instances = array();

	/**
	 * Constructor method
	 *
	 * @access protected
	 * @since Method available since Release 0.1.1
	 */
	protected function __construct() {
		//
	}

	/**
	 * Avoid cloning
	 *
	 * @access protected
	 * @since Method available since Release 0.1.1
	 */
	protected function __clone() {
		//
	}

	/**
	 * Avoid unserialization
	 *
	 * @access public
	 * @since Method available since Release 0.1.1
	 */
	public function __wakeup(){
		throw new Exception("Cannot unserialize singleton");
	}

	/**
	 * Get the instance of desired class
	 *
	 * @access public
	 * @since Method available since Release 0.1.1
	 */
	public static function getInstance(){
		$class = get_called_class(); // late-static-bound class name
		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new static;
		}
		return self::$instances[$class];
	}
}