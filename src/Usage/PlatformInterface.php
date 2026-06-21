<?php
namespace Cookbook\Usage;
use Generator;
#[PlatformInterface("Defines mandatory methods")]
interface PlatformInterface
{
    public const FIELD_DELIM = '|';     // log field delimiter
    public const DATE_FIELD_LOC = 1;    // array element containing date/time after explode()
    public const GENAI_RESULT_LOC = 2;  // array element containing GenAI JSON result after explode()
    public const ERR_LOG_FMT = 'ERROR: log file does not meet format [%s]';
    public function parseLog(string $log_fn) : Generator;
}
