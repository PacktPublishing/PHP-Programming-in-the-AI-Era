<?php
namespace Cookbook\Middleware;
use Throwable;
use Exception;
use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
class Capital extends BaseHandler
{
    public const USAGE = 'USAGE: iso2 : ISO2 code of the requested country';
    #[Cookbook\Middleware\Distance\handle(
        "@param GenAiConnect connect : GenAiConnect instance",
        "@param string iso2_or_name  : ISO2 code or name of destination country",
        "@throws InvalidArgumentException"
    )]    
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        try {
            $data = $request->getParsedBody()['data'] ?? '';
            $args = json_decode($data, associative: TRUE, flags: JSON_THROW_ON_ERROR);
            $name = trim($args['iso2_or_name'] ?? 'Unknown');
            if (!$this->verify_iso2($name)) {
                throw new InvalidArgumentException("INVALID DESTINATION ISO2 CODE OR COUNTRY NAME: $name: " . self::USAGE);
            }
            $connect = $this->container->get('GenAiConnect');
            if (empty($connect)) {
                throw new RuntimeException('Required service is offline. ' . self::USAGE);
            }
            $prompt = 'Give me the name of the capital of that country. '
                    . 'Here is the country ' . ((strlen($name) === 2) ? 'ISO2 code ' : 'name ') . ':' . $name . '. '
                    . 'Return only the name of the capital city -- no additional explanation or text.';
            return $connect->genAIcall($prompt);
        } catch (Exception $e) {
            $response = ['success' => FALSE, 'data' => $e->getMessage()];
            return new JsonResponse($response)->withStatus(400);
        } catch (Throwable $t) {
            $response = ['success' => FALSE, 'data' => static::ERR_UNKNOWN];
            return new JsonResponse($response)->withStatus(500);
        }
    }
    public function verify_iso2(string $name)
    {
        $codes = $this->container->get('iso2');
        return (isset($codes[strtoupper($name)]) || in_array($name, $codes));
    }
}
