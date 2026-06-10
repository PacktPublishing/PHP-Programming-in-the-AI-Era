<?php
namespace App\Service;
use InvalidArgumentException;
use App\Service\CustomScraper as Scraper;
use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\Toolbox\Toolbox;
use Symfony\AI\Agent\Toolbox\AgentProcessor;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Bridge\OpenAi\PlatformFactory;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\Component\HttpClient\HttpClient;
class GenAiService
{
    public const LOG_FN = __DIR__ . '/../../var/api.log';
    public ?PlatformInterface $platform = NULL;
    public function __construct(
        public string $apiKey, public string $apiBridge, public string $apiModel)
    {
        $factory = '\\Symfony\\AI\\Platform\\Bridge\\' . $apiBridge . '\\PlatformFactory';
        if (!class_exists($factory)) throw new InvalidArgumentException('ERROR: provider unavailable');
        $this->platform = $factory::create($this->apiKey, HttpClient::create());
    }
    public function chat(string $text_sys, string $text_usr): ResultInterface
    {
        $scraper        = new Scraper(HttpClient::create());
        $toolbox        = new Toolbox([$scraper]);
        $agentProcessor = new AgentProcessor($toolbox);
        $agent          = new Agent($this->platform, $this->apiModel, [$agentProcessor], [$agentProcessor]);
        $messages       = new MessageBag(
            Message::forSystem($text_sys),
            Message::ofUser($text_usr)
        );
        $result = $agent->call($messages);
        // remove after testing
        file_put_contents(self::LOG_FN, __METHOD__ . ':' . var_export($result, TRUE));
        return $result;
    }
}
