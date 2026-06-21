<?php
namespace Cookbook\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
class Logger implements MiddlewareInterface 
{
    const ERR_LOG = 'ERROR: unable to log entry';
    const OK_LOG  = 'SUCCESS: entry logged';
    const LOG_FILE = __DIR__ . '/../Chapter07/middleware.log';
    public function process(ServerRequestInterface $request,
        RequestHandlerInterface $handler) : ResponseInterface
    {
        $text = sprintf('%20s : %10s : %30s : %s' . PHP_EOL,
            date('Y-m-d H:i:s'),
            ($request->getParsedBody()['action'] ?? 'Unknown'),
            ($request->getParsedBody()['data'] ?? 'No Data'),
            ($request->getServerParams()['REMOTE_ADDR']) ?? 'Command Line');
        file_put_contents(self::LOG_FILE, $text, FILE_APPEND);
        return $handler->handle($request);
    }
}
