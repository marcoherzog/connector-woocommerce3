<?php

/**
 * @author    Jan Weskamp <jan.weskamp@jtl-software.com>
 * @copyright 2010-2013 JTL-Software GmbH
 */

namespace JtlWooCommerceConnector\Event;

use Symfony\Contracts\EventDispatcher\Event;

class HandleDeleteEvent extends Event
{
    public const EVENT_NAME = 'connector.handle.delete';

    protected $result;
    protected $controller;
    protected $entities;

    public function __construct($controller, $entities)
    {
        $this->controller = $controller;
        $this->entities   = $entities;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getEntities()
    {
        return $this->entities;
    }

    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param $result
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }
}
