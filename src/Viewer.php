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
 * The Viewer
 * -----------------------------------------------------------------------
 *
 * Reads and render the template file. Responsible for injecting
 * dependencies from both Container and the Core\Sharer
 *
 */
class Viewer {

    // the hive is where all data is stored, which is then usable from all template
    // files
    private static $hive = [];

    /**
     * Finds, renders and displays a template file. Reports a 404 error in
     * case of missing files.
     *
     * @param string	$file		file name / path to the file
     * @param array		$data	array of data
     *
     * @static
     * @access public
     * @see Viewer::render()
     * @since Method available since Release 0.1.0
     */
    static function file($file, array $data = []){
        // Do you love displaying blank pages?
        if($file === 'index' || $file === 'index.php'){
            Debugger::report(404, true);
        }else{
            /**
             * Get the path of the calling script and get it's containing Directory
             * to enable include() style of accessing files
             */
            $callingScriptPath = debug_backtrace()[0]['file'];
            $callingScriptDirectory = realpath(dirname($callingScriptPath));
            if(file_exists($callingScriptDirectory.'/'.$file)){
                self::render($callingScriptDirectory.'/'.$file, $data);
            }else if(file_exists($callingScriptDirectory.'/'.$file.'.php')){
                self::render($callingScriptDirectory.'/'.$file.'.php', $data);
            }else if(file_exists(SS_PATH.$file)){
                self::render($file, $data);
            }else if(file_exists(SS_PATH.$file.'.php')){
                self::render(SS_PATH.$file.'.php', $data);
            }else{
                Debugger::report(404, true);
            }
        }
    }

    /**
     * Renders a template file. Inject dependencies from the Application
     * Container and the Core\Sharer before viewing the file. Also,
     * extracts &$data into variables usable from the template files
     *
     * @param string	$file		file name / path to the file
     *
     * @static
     * @access private
     * @since Method available since Release 0.1.0
     */
    static private function render($file, $data){
        extract($data);
        // Extract data retreived from the Sharer
        if(Sharer::get() !== null){
            extract(Sharer::get());
        }

        // Merge data into the hive
        self::$hive = array_merge(self::$hive, get_defined_vars());
        unset($data);

        ob_start();
        include($file);
        $input = ob_get_contents();
        ob_end_clean();

        $output = preg_replace_callback('!\{\{(.*?)\}\}!', 'Viewer::replace', $input);


        echo($output);
    }

    static private function replace($matches) {
        // If '.' is found in the $matches[1], assume it is an object
        // which have a property

        // else, assume it is a variable
        if (strpos($matches[1], '.') !== false) {
            // explode the part before and after '.'
            // the part before '.' is an object, while the part after '.' is a property
            list($object, $property) = explode('.', $matches[1]);

            // if a '()' is found in $property, we will then assume it to be a callable
            // method.
            if (strpos($property, '()') !== false) {
                // remove paranthesis
                list($function, $parenthesis) = explode('()', $property);

                // return the callable method of the object from the hive
                return(self::$hive[$object]->$function());
            }else{
                // return the property of the object from the hive
                return(self::$hive[$object]->$property);
            }
        }else{
            if(isset(self::$hive[$matches[1]])){
                return self::$hive[$matches[1]];
            }
        }

    }

}