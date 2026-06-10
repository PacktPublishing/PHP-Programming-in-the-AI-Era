<?php
namespace App\Service;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsTool('scraper', 'Loads the visible text and title of a website by URL.')]
class CustomScraper
{
    private const MAX_CHARS = 40000;
    public function __construct(private readonly HttpClientInterface $httpClient) {}
    /**
     * @param string $url the URL of the page to load data from
     * @return array{title: string, content: string}
     */
    public function __invoke(string $url): array
    {
        $response = $this->httpClient->request('GET', $url);
        $crawler  = new DomCrawler($response->getContent());
        $title    = $crawler->filter('title')->text('');
        $content  = substr($crawler->filter('body')->text(''), 0, self::MAX_CHARS);
        return ['title' => $title, 'content' => $content];
    }
}
