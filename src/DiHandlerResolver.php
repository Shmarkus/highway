<?php
namespace CodeHouse\Highway;

use Phroute\Phroute\HandlerResolverInterface;

class DIHandlerResolver implements HandlerResolverInterface
{
    private $_container;

    public function setContainer($diContainer)
    {
        $this->_container = $diContainer;
    }

    /**
     * Create an instance of the given handler.
     *
     * @param $handler
     * @return array
     */
    public function resolve($handler)
    {
        if (is_array($handler) && is_string($handler[0])) {
            $handler[0] = $this->_container->get($handler[0]);
        }
        return $handler;
    }
}