<?php
/**
 * Damn Stupid Simple - A PHP Framework For Lazy Developers
 *
 * Copyright (c) 2016 Studio Nexus
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
 * @package		Damn Stupid Simple
 * @author		Studio Nexus <fariz@studionexus.co>
 * @copyright	2016 Studio Nexus
 * @license		MIT
 * @version		Release: 0.3.0
 * @link		https://www.studionexus.co/php/damnstupidsimple
 */
namespace Core;

class Response {

	private static $_instance = null;
	private $data = [];

	private function __construct(){
		$this->data = array_merge($_GET, $_POST);
	}

	static function redirect($html_location, $time = 0){
		if(!headers_sent())
		{
			header("Location:".$html_location, TRUE, 302);
			exit;
		}
		exit('<meta http-equiv="refresh" content="'.$time.'; url='.$html_location.'" />');
	}

	static function get($key = null){
		if (self::$_instance === null) {
			self::$_instance = new self;
		}

		return self::$_instance->returnResponse($key);
	}

	function returnResponse($key = null){
		if($key !== null){
			if(!isset($this->data[$key])){
				return null;
			}
			return $this->data[$key];
		}else{
			return $this->data;
		}
	}
}