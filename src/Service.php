<?php
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
