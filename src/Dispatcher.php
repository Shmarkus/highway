<?php
namespace CodeHouse\Highway;

use Phroute\Phroute\HandlerResolverInterface;
use Phroute\Phroute\RouteDataInterface;

class Dispatcher extends \Phroute\Phroute\Dispatcher
{
    /**
     * Dispatcher constructor.
     */
    public function __construct(RouteDataInterface $data, HandlerResolverInterface $resolver)
    {
        parent::__construct($data, $resolver);
    }
}