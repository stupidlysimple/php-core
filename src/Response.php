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

use Illuminate\Support\Facades\Session;

class Response {

    /**
     * Hive containing all response values
     * @var array
     */
    private static $hive = [];

    private static $htmlLocation;
    private static $time;
    private static $flashVarsTTL = 10;

    /**
     * Response constructor.
     */
	private function __construct()
    {
        if(!isset($_SESSION)){
            session_start();
        }
	}

    /**
     * Redirect to a location
     * @param $htmlLocation
     * @param int $time
     * @return Response
     */
    public static function redirect($htmlLocation, $time = 0)
    {
        self::$htmlLocation = $htmlLocation;
        self::$time = $time;
        return new self;
	}

    // hello?
    public function with(array $flashVars)
    {
        $_SESSION['ss_flash_variables'] = $flashVars;
    }

    /**
     * We do redirect when the Response object is destroyed.
     */
    public function __destruct()
    {
        if(!headers_sent())
        {
            header("Location:".self::$htmlLocation, TRUE, 302);
            exit();
        }
        // Something has caused our redirect not working properly. Using redirect with meta
        exit('<meta http-equiv="refresh" content="'.self::$time.'; url='.self::$htmlLocation.'" />');
    }
}