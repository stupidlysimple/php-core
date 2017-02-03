<?php
/**
 * StupidlySimple - A PHP Framework For Lazy Developers
 *
 * @package		StupidlySimple
 * @author		Fariz Luqman <fariz.fnb@gmail.com>
 * @copyright	2017 Fariz Luqman
 * @license		MIT
 * @link		https://stupidlysimple.github.io/
 */
namespace Core\Service;

class ServiceContainer {

  /**
   * @return ServiceContainer|object
   */
  public function getThis()
  {
    return $this;
  }

  /**
   * @return ServiceContainer|object
   */
  public function dumpThis()
  {
    dd($this);
  }
}
