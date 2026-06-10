<?php
use App\Kernel;
require_once dirname(__DIR__).'/vendor/autoload_runtime.php';
// rewrite .env.local if it doesn't exist
$dest_fn = __DIR__ . '/../.env.local';
if (!file_exists($dest_fn)) {
    $key_fn = __DIR__ . '/../../secure/open_ai_api_key.txt';
    $env_vars = [
        'GENAI_API_KEY' => trim(file_get_contents($key_fn)),
        'GENAI_BRIDGE'  => 'OpenAi',
        // latest pricing: https://developers.openai.com/api/docs/pricing
        // IMPORTANT: chosen model must be in Symfony\AI\Platform\Bridge\$GENAI_BRIDGE\ModelCatalog
        'GENAI_MODEL'   => 'gpt-4o-mini',
    ];
    $fn = function($env_vars) { $str = ''; foreach ($env_vars as $key => $val) $str .= $key . '=' . $val . PHP_EOL; return $str; };
    file_put_contents($dest_fn, $fn($env_vars));
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
