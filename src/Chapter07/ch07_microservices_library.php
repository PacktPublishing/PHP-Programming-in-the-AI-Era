<?php
// TO DEMO: ./admin.sh shell
// composer.phar require psr/http-server-middleware
// composer require laminas/laminas-httphandlerrunner
// php -S 0.0.0.0:8889 src/Chapter07/ch07_microservices_library.php
// sample CURL requests (from a terminal window on your host):
// curl -X POST -F 'data={"lang_from":"en","lang_to":"it","phrase":"Hello, how are you today?"}' http://localhost:8889/translate
// curl -X POST -F 'data={"city_from":"Paris","city_to":"Rome","iso2_from":"fr","iso2_to":"it","units":"km"}' http://localhost:8889/distance
// curl -X POST -F 'data={"iso2_or_name":"it"}' http://localhost:8889/capital

include __DIR__ . '/../../vendor/autoload.php';
use Cookbook\Middleware\Logger;
use Cookbook\Middleware\Translate;
use Cookbook\Middleware\Distance;
use Cookbook\Middleware\Capital;
use Cookbook\Services\Container;
use Cookbook\Services\GenAiConnect;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\Handler\NotFoundHandler;
use Laminas\Stratigility\MiddlewarePipe;
use Laminas\Stratigility\Middleware\RequestHandlerMiddleware;
use function Laminas\Stratigility\middleware;
use function Laminas\Stratigility\path;

// GenAI configuration
$config = [
    'ai_api_key' => trim(file_get_contents(__DIR__ . '/../../secure/monica_api_key.txt')),
    'ai_model'   => 'gpt-4.1-nano',     // $0.10 / 1M input tokens | $0.40 / 1M output tokens
    'ai_api_url' => 'https://openapi.monica.im/v1/chat/completions',   
];
// get service container instance
$container = Container::getInstance();
$container->add('ai_config', fn () => $config);
$container->add('iso2', fn () => include __DIR__ . '/../../config/ch07_iso2.php');
$container->add('languages', fn () => include __DIR__ . '/../../config/ch07_languages.php');
$container->add('GenAiConnect', fn () => new GenAiConnect($container));
$container->add('translate', fn () => new Translate($container));
$container->add('distance', fn () => new Distance($container));
$container->add('capital', fn () => new Capital($container));

// build the middlware pipe
$app = new MiddlewarePipe();

// Logger
$app->pipe(new Logger());

// Distance API call
$app->pipe(path('/distance', middleware(
    function ($req, $handler) use ($container) {
        return $container->get('distance')->handle($req); }
)));

// Translate API call
$app->pipe(path('/translate', middleware(
    function ($req, $handler) use ($container) {
        return $container->get('translate')->handle($req); }
)));

// Capital API call
$app->pipe(path('/capital', middleware(
    function ($req, $handler) use ($container) {
        return $container->get('capital')->handle($req); }
)));

// 404 handler
$app->pipe(middleware(function ($req, $handler) {
    return (new NotFoundHandler(new ResponseFactory()))->handle($req);
}));

$server = new RequestHandlerRunner(
    $app,
    new SapiEmitter(),
    static function () {
        return ServerRequestFactory::fromGlobals();
    },
    static function (\Throwable $e) {
        $response = (new ResponseFactory())->createResponse(500);
        $response->getBody()->write(sprintf(
            'An error occurred: %s',
            $e->getMessage
        ));
        return $response;
    }
);
$server->run();
