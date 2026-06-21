<?php
namespace Cookbook\Middleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
abstract class BaseHandler implements RequestHandlerInterface
{
    public const ERR_UNKNOWN = 'Unknown error';
    public Traversable $middleware;
    #[Cookbook\Middleware\BaseHandler(
        "Builds iteration of handlers",
        "@param ContainerInterface : universal services container"
    )]
    public function __construct(public ContainerInterface $container)
    {}
}
