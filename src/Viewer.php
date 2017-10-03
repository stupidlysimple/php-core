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

    /**
     * the hive is where all data is stored, which is then usable from all template
     * files
     */
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
    public static function file($file, array $data = []) {
        // Do you love displaying blank pages?
        if ($file === 'index' || $file === 'index.php') {
            Debugger::report(404, true);
        } else {
            /**
             * Get the path of the calling script and get it's containing Directory
             * to enable include() style of accessing files
             */
            $calling_script_path = debug_backtrace()[0]['file'];
            $calling_script_directory = realpath(dirname($calling_script_path));

            /**
             * Check if file exists, try directories
             * 1. in the same directory as the calling script
             * 2. same as #1 but without .tpl.php
             * 3. Check in resources/views directory
             * 4. same as #3 but without .tpl.php
             * 5. check on the root directory
             * 6. same #5 but without .tpl.php
             */
            if (file_exists($render_path = $calling_script_directory.'/'.$file.'.tpl.php')) {
                self::render($render_path, $data);
            } elseif (file_exists($render_path = $calling_script_directory.'/'.$file)) {
                self::render($render_path, $data);
            } elseif(file_exists($render_path = SS_PATH.'/resources/views/'.$file.'.tpl.php')) {
                self::render($render_path, $data);
            }  elseif(file_exists($render_path = SS_PATH.'/resources/views/'.$file)) {
                self::render($render_path, $data);
            } elseif(file_exists($render_path = SS_PATH.'/'.$file.'.tpl.php')) {
                self::render($render_path, $data);
            } elseif(file_exists($render_path = SS_PATH.'/'.$file)) {
                self::render($render_path, $data);
            } else {
                Debugger::report(404, true);
            }
        }
    }

    /**
     * Renders a template file. Inject dependencies from the Application
     * Container and the Core\Sharer before viewing the file. Also,
     * extracts $data into variables usable from the template files
     *
     * The template file will be echoed in the scope of this static
     * private method.
     *
     * @param string	$file		file name / path to the file
     *
     * @static
     * @access private
     * @since Method available since Release 0.1.0
     *
     */
    private static function render($file, $data)
    {
        // Extract data passed by the user
        extract($data);

        // Extract data from the Sharer
        if (Sharer::get() !== null) {
            extract(Sharer::get());
        }

        // Merge data into the hive
        self::$hive = array_merge(self::$hive, get_defined_vars());

        // Unset data since we have extracted it
        unset($data);

        // Capture all contents of the template file into string $input
        ob_start();
        include($file);
        $input = ob_get_contents();
        ob_end_clean();

        // Replace all {{ }} with values
        $output = preg_replace_callback('!\{\{(.*?)\}\}!', 'Viewer::replace', $input);

        // Display final output of the template file
        echo($output);
    }

    /**
     * Replace {{ }} with values
     *
     * @static
     * @access private
     * @param $matches
     * @since Method available since Release 0.5
     * @return mixed
     */
    private static function replace($matches)
    {
        // If '.' is found in the $matches[1], assume it is an object
        // which have a property.
        if (strpos($matches[1], '.') !== false) {
            // Explode the part before and after '.'
            // the part before '.' is an object, while the part after '.' is a property
            list($object, $property) = explode('.', $matches[1]);

            // If a '()' is found in $property, we will then assume it to be a callable
            // method.
            if (strpos($property, '()') !== false) {
                // Remove paranthesis
                list($function, $parenthesis) = explode('()', $property);

                // Execute the method and return the value given by the method
                return(self::$hive[$object]->$function());
            } else {
                // Return the property of the object from the hive
                return(self::$hive[$object]->$property);
            }
        } else {
            if (strpos($matches[1], '()') !== false) {
                // Remove paranthesis
                list($function, $parenthesis) = explode('()', $matches[1]);

                // Execute function and return the value given by the function
                return self::$hive[$function]();
            }elseif(isset(self::$hive[$matches[1]])){
                return self::$hive[$matches[1]];
            }
        }
    }

}