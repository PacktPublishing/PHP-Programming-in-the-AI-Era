<?php
namespace Cookbook\Usage;
use SplFileObject;
#[GenAiUsageTracker("Tracks GenAI token usage")]
class GenAiUsageTracker
{
    public const CSV_FN  = __DIR__ . '/../Chapter07/api_call_usage.csv';
    public const CALL_LOG  = __DIR__ . '/../Chapter07/api_call.log';
    public ?ArrayObject $logInfo = NULL;
    public string $csv_fn    = '';
    public string $log_fn    = '';
    public string $separator = ',';
    public string $enclosure = '"';
    public string $escape    = '\\';
    public ?PlatformInterface $platform = NULL;
    public function __construct(?PlatformInterface $platform = NULL, array $config = [])
    {
        $this->csv_fn    = $config['csv_fn']    ?? static::CSV_FN;
        $this->log_fn    = $config['call_log']  ?? static::CALL_LOG;
        $this->separator = $config['separator'] ?? $this->separator;
        $this->enclosure = $config['enclosure'] ?? $this->enclosure;
        $this->escape    = $config['escape']    ?? $this->escape;
        $this->platform  = $platform;
    }
    // adds log entries to spreadsheet
    public function updateCsv(bool $eraseLog = FALSE) : int
    {
        $iter = $this->platform->parseLog($this->log_fn);
        $mode = ($eraseLog) ? 'w' : 'a';
        $csv = new SplFileObject($this->csv_fn, $mode);
        if ($eraseLog) {
            $csv->fputcsv($this->platform->headers, separator: $this->separator, enclosure: $this->enclosure, escape: $this->escape);
        }
        foreach ($iter as $row) {
            $csv->fputcsv(array_values($row), separator: $this->separator, enclosure: $this->enclosure, escape: $this->escape);
        }
        return $iter->getReturn();
    }
}
