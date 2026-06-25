<?php
namespace App\Controller;
use Throwable;
use App\Service\GenAiService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
class IndexController extends AbstractController
{
    public const NO_NEWS = 'No News [%d]';
    public const ERR_API = 'Unable to make API call';
    public const CATEGORIES = ['World','North America','South America','Europe','Middle East','Asia','South Pacific'];
    public const DEFAULT_CATEGORY = 'World';
    public const DEFAULT_NEWS_SRC = 'https://www.yahoo.com/news/';
    public const INFO_MISSING = 'Info Missing';
    public const PROMPT_SYS = 'Use %s as the news source. '
                            . 'Do not provide any other explanations or reasoning -- just give me the JSON string with "headline", "summary", and "link" for each news item. '
                            . 'For each item only give me these 3 things: '
                            . 'headline: The headline of the news item with a link to the news story. '
                            . 'summary: A concise 50 word summary of the news story. '
                            . 'link: A URL link to the news story. ';
    public const PROMPT_USR = 'In JSON format, give me headlines and summaries of the top 10 news stories in this category: %s.';
    #[Route('/', name: 'app_news', methods: ['GET'])]
    public function index(Request $request, GenAiService $service): Response
    {
        return $this->render(
            'index.html.twig', [
                'categories' => self::CATEGORIES, 
                'news_src' => self::DEFAULT_NEWS_SRC,
                'news' => $this->getChat(self::DEFAULT_CATEGORY, self::DEFAULT_NEWS_SRC, $service)
            ]
        );
    }
    #[Route('/source', name: 'app_source', methods: ['GET'])]
    public function updateSrc(Request $request): Response
    {
        $data = $request->getContent('news_src') ?? self::DEFAULT_NEWS_SRC;
        $_SESSION['news_src'] = strip_tags($data);  // consider using Symfony session handling instead
        return $this->json(['success' => true]);
    }
    #[Route('/api/news', name: 'app_news_api', methods: ['POST'])]
    public function newsAjax(Request $request, GenAiService $service): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $category = strip_tags($data['category'] ?? self::DEFAULT_CATEGORY);
        $news_src = $_SESSION['news_src'] ?? self::DEFAULT_NEWS_SRC;
        // Validation
        if (empty($category) || !in_array($category, self::CATEGORIES)) {
            return $this->json(['success' => false,'error' => 'Missing required fields'], 400);
        } elseif (empty($news_src)) {
            return $this->json(['success' => false,'error' => 'Missing news source'], 400);
        } else {
            return $this->json(['success' => true, 'html' => $this->getChat($category, $news_src, $service)]);
        }
    }
    protected function getChat(string $category, string $news_src, GenAiService $service) : string
    {
        $prompt_sys = sprintf(self::PROMPT_SYS, $news_src);
        $prompt_usr = sprintf(self::PROMPT_USR, $category);
        $response = $service->chat($prompt_sys, $prompt_usr);
        $json     = trim($response->getContent() ?? '');
        $json     = str_replace(['```json','```'], '', $json);
        $arr      = json_decode($json, TRUE);
        $html     = sprintf(self::NO_NEWS, __LINE__);
        if (!empty($arr)) {
            $html = '<table>';
            $html .= '<tr><th>Headline</th><th>Summary</th></tr>';
            foreach ($arr as $item) {
                $link = strip_tags($item['link'] ?? '#');
                $head = strip_tags($item['headline'] ?? 'No Headline');
                $summ = strip_tags($item['summary'] ?? 'No Summary');
                $html .= '<tr>';
                $html .= '<td><a href="' . htmlspecialchars($link) . '">';
                $html .= htmlspecialchars($head) . '</a></td>';
                $html .= '<td>' . htmlspecialchars($summ) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        }
        return $html;
    }
}
