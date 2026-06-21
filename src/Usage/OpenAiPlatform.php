<?php
namespace Cookbook\Usage;
use Generator;
use DateTime;
use SplFileObject;
use InvalidArgumentException;
#[OpenAiPlatform("Tracks token usage for OpenAI")]
class OpenAiPlatform implements PlatformInterface
{
    public array $headers = ['Timestamp','Y','M','D','H','Model','Prompt Tokens','Completion Tokens','Total Tokens'];
    public string $field_delim = '';     // log field delimiter
    public int $date_field_loc = 1;     // array element containing date/time after explode()
    public int $genai_result_loc = 2;  // array element containing GenAI JSON result after explode()
    public function __construct(?string $field_delim = NULL, ?int $date_field_loc = NULL, ?int $genai_result_loc = NULL)
    {
        $this->field_delim = $field_delim ?? PlatformInterface::FIELD_DELIM;
        $this->date_field_loc = $date_field_loc ?? PlatformInterface::DATE_FIELD_LOC;
        $this->genai_result_loc = $genai_result_loc ?? PlatformInterface::GENAI_RESULT_LOC;
    }
    // parses log file info into array
    public function parseLog(string $log_fn) : Generator
    {
        $log = new SplFileObject($log_fn, 'r');
        $log->rewind();
        $lines = 0;
        $fmt_check = FALSE;
        while (!$log->eof()) {
            $logInfo = [];
            $line = $log->fgets();
            // parse log
            $split = explode($this->field_delim, $line);
            $date_info = trim($split[$this->date_field_loc] ?? '');
            $genai_json = trim($split[$this->genai_result_loc] ?? '');
            if (empty($date_info) || empty($genai_json)) continue;
            try {
                $genai_result = json_decode($genai_json, TRUE, flags: JSON_THROW_ON_ERROR);
            } catch (Throwable $t) {
                error_log(__METHOD__ . ':' . $t->getMessage());
                error_log(__METHOD__ . ':' . json_last_error_msg());
                continue;
            }
            $date = (ctype_digit($date_info))
                  ? DateTime::createFromTimestamp($date_info)
                  : new DateTime($date_info);
            $y = $date->format('Y');
            $m = $date->format('m');
            $d = $date->format('d');
            $h = $date->format('H');
            $logInfo = [
                'timestamp' => $date_info,
                'y' => $y,
                'm' => $m,
                'd' => $d,
                'h' => $h,
                'model' => $genai_result['model'],
                'prompt_tokens'      => $genai_result['usage']['prompt_tokens'],
                'completion_tokens'  => $genai_result['usage']['completion_tokens'],
                'total_tokens'       => $genai_result['usage']['total_tokens'],
            ];
            yield $logInfo;
            $lines++;
        }
        return $lines;
    }
}
