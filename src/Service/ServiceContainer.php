<?php
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
