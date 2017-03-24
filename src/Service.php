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
 * Class Service
 * Service is where all the applications written by user resides, for both
 * input and output processing
 * @package Core
 */
class Service extends Service\ServiceContainer {
    /**
     * Contains the object of instantiation of the Service class
     * @static
     * @var object
     */
    private static $serviceObject;

    /**
     * @var array
     */
    private $config = '';

    /**
     * @static
     * @return Service|object
     */
    public static function loadServices()
    {
        if(isset(self::$serviceObject) === false){
            self::$serviceObject = new self;
        }

        self::$serviceObject->prepare();

        return self::$serviceObject;
    }

    /**
     * Object preparer
     */
    private function prepare(){
        if(isset($config) === false){
            $this->config = Config::get('services');
        }

        foreach($this->config as $className => $varName){
            $this->$varName = new $className;
        }
    }
}
