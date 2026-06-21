<?php
// TO DEMO: set up and run ch07_microservices_library.php to add entries to the api_call.log
include __DIR__ . '/../../vendor/autoload.php';
use Cookbook\Usage\GenAiUsageTracker;
use Cookbook\Usage\OpenAiPlatform;
// create GenAiUsageTracker instance accepting defaults for GenAiUsageTracker
$tracker = new GenAiUsageTracker(new OpenAiPlatform('|',1,2));
$num = $tracker->updateCsv(eraseLog: TRUE);
if (empty($num)) {
    echo 'No Updates' . PHP_EOL;
} else {
    echo 'Number of log entries added: ' . $num . PHP_EOL;
}
readfile(GenAiUsageTracker::CSV_FN);

