<?php
/**
 * Created by PhpStorm.
 * User: Trick
 * Date: 9/24/20
 * Time: 7:44 PM
 */
namespace App\Safety;

use PHPZlc\PHPZlc\Bundle\Business\AbstractBusiness;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ActionLoad extends AbstractBusiness implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    public static $globalContainer;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        self::$globalContainer = $container;
    }

    public function onKernelController(ControllerEvent $event)
    {

    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}