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

use Config;
use Model\Post;

class Router
{
    private static $mimeTypes;
    private static $config = null;
    private static $halts = false;
    private static $routes = array();
    private static $methods = array();
    private static $callbacks = array();
    private static $patterns = array(
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*'
    );

    private static $isGroup = false;
    private static $groupHalt = false;
    private static $groupController;
    private static $groupMethodName;

    private static $currentURI;
    private static $currentRoute;

    private static $errorCallback;

    /**
     * Defines a route w/ callback and method
     * @param $method
     * @param $params
     */
    public static function __callstatic($method, $params)
    {
        if ($method == 'group') {
            // Seperate controller name and the method
            $segments = explode('@', $params[0]);

            // Instanitate controller
            self::$groupController = new $segments[0]();
            self::$groupMethodName = $segments[1];
            self::$isGroup = true;

            // Access route groups
            call_user_func($params[1]);

            self::runDispatcher();

            self::$groupController = null;
            self::$groupMethodName = null;
            self::$isGroup = false;
        } else {
            // remove leading slash which may cause problems in Linux servers
            $params[0] = trim($params[0], '/');
            $uri = dirname($_SERVER["SCRIPT_NAME"]) . '/' . $params[0];
            $callback = $params[1];
            array_push(self::$routes, $uri);
            array_push(self::$methods, strtoupper($method));
            array_push(self::$callbacks, $callback);
            self::runDispatcher();
        }
    }

    /**
     * Load the configuration file
     */
    public static function start()
    {
        if (self::$config === null) {
            self::$config = Config::get('routes');
            self::$mimeTypes = Config::get('mimetypes');
        }

        foreach (self::$config['routes'] as $route) {
            include(self::$config['path'] . $route . '.php');
        }
    }

    public static function haltOnMatch($flag = true)
    {
        self::$halts = $flag;
    }

    /**
     * Run the dispatcher for the last time to collect all non-grouped
     * routes. Will throw a 404 error if any route is not found
     */
    public static function dispatch()
    {
        self::runDispatcher();
        if (!self::$halts && !self::$groupHalt) {
            Debugger::report(404);
        }
    }

    /**
     * @param $url
     * @param bool $permanent
     */
    public static function redirect($url, $permanent = false)
    {
        if (headers_sent() === false) {
            header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
        }
        exit();
    }

    /**
     * @param $file_name
     * @return mixed|string
     */
    public static function getMimeType($file_name)
    {
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if (isset(static::$mimeTypes['.' . $ext])) {
            return static::$mimeTypes['.' . $ext];
        } else {
            return 'text/plain';
        }
    }

    /**
     * @since Method available since 0.5.0
     * @return mixed|string
     */
    public static function getCurrentURI()
    {
        return self::$currentURI;
    }

    /**
     * @since Method available since 0.5.0
     * @param $file_name
     * @return mixed|string
     */
    public static function getCurrentRoute()
    {
        $a = self::findOverlap(SS_PATH, self::$currentURI)[0];
        return (str_replace($a, '', self::$currentURI));
    }

    public static function findOverlap($str1, $str2)
    {
        $return = array();
        $sl1 = strlen($str1);
        $sl2 = strlen($str2);
        $max = $sl1 > $sl2 ? $sl2 : $sl1;
        $i = 1;
        while ($i <= $max) {
            $s1 = substr($str1, -$i);
            $s2 = substr($str2, 0, $i);
            if ($s1 == $s2) {
                $return[] = $s1;
            }
            $i++;
        }
        if (!empty($return)) {
            return $return;
        }
        return false;
    }

    /**
     * Runs the callback for the given request
     * @since Method available since 0.5.0
     */
    private static function runDispatcher()
    {
        if (self::$groupHalt || self::$halts) return;
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        $searches = array_keys(static::$patterns);
        $replaces = array_values(static::$patterns);
        $found_route = false;

        self::$routes = str_replace('//', '/', self::$routes);
        // Check if route is defined without regex

        if (in_array($uri, self::$routes)) {
            $route_pos = array_keys(self::$routes, $uri);
            foreach ($route_pos as $route) {
                // Using an ANY option to match both GET and POST requests
                if (self::$methods[$route] === $method || self::$methods[$route] === 'ANY') {
                    //dd(preg_match('#^' . $route . '$#', $uri, $matched));

                    // If route is not an object
                    if (!is_object(self::$callbacks[$route])) {
                        self::haltOnMatch();
                        $found_route = true;
                        self::$currentURI = $uri;

                        if (self::$isGroup) {
                            self::$groupHalt = true;
                            self::$groupController->{self::$groupMethodName}();
                        }

                        // Grab all parts based on a / separator
                        $parts = explode('/', self::$callbacks[$route]);

                        // Collect the last index of the array
                        $last = end($parts);

                        // Grab the controller name and method call
                        $segments = explode('@', $last);

                        if (count($segments) >= 2) {
                            // Instanitate controller
                            $controller = new $segments[0]();

                            // Call method
                            $controller->{$segments[1]}();
                        } else {
                            Viewer::file(self::$callbacks[$route]);
                        }

                        if (self::$halts) {
                            return true;
                        }
                    } else {
                        self::haltOnMatch();
                        $found_route = true;
                        self::$currentURI = $uri;

                        // Call closure
                        if (self::$isGroup) {
                            self::$groupHalt = true;
                            self::$groupController->{self::$groupMethodName}();
                        }

                        if (is_object(self::$callbacks[$route])) {
                            call_user_func(self::$callbacks[$route]);
                        } else {
                            Viewer::file(self::$callbacks[$route]);
                        }

                        if (self::$halts) {
                            return true;
                        };
                    }
                }
            }
        } else {
            // Check if defined with regex
            $pos = 0;
            foreach (self::$routes as $route) {
                if (strpos($route, ':') !== false) {
                    $route = str_replace($searches, $replaces, $route);
                }

                if (preg_match('#^' . $route . '$#', $uri, $matched)) {
                    if (self::$methods[$pos] === $method || self::$methods[$pos] === 'ANY') {
                        self::haltOnMatch();
                        $found_route = true;
                        self::$currentURI = $uri;

                        if (self::$isGroup) {
                            self::$groupHalt = true;
                            self::$groupController->{self::$groupMethodName}();
                        }

                        // Remove $matched[0] as [1] is the first parameter.
                        array_shift($matched);
                        if (!is_object(self::$callbacks[$pos])) {
                            // Grab all parts based on a / separator
                            $parts = explode('/', self::$callbacks[$pos]);
                            // Collect the last index of the array
                            $last = end($parts);
                            // Grab the controller name and method call
                            $segments = explode('@', $last);
                            // Instanitate controller
                            $controller = new $segments[0]();

                            // Fix multi parameters
                            if (!method_exists($controller, $segments[1])) {
                                //"controller and action not found"
                                Debugger::report(500);
                            } else {
                                call_user_func_array(array($controller, $segments[1]), $matched);
                            }
                            if (self::$halts) return;
                        } else {
                            self::haltOnMatch();
                            $found_route = true;
                            self::$currentURI = $uri;

                            if (self::$isGroup) {
                                self::$groupHalt = true;
                                self::$groupController->{self::$groupMethodName}();
                            }

                            call_user_func_array(self::$callbacks[$pos], $matched);
                            if (self::$halts) return;
                        }
                    } else {
                        // continue searching
                    }
                }
                $pos++;
            }
        }

        // Tell if there is no found grouped routes
        return false;
    }

}