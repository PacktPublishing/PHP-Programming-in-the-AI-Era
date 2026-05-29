<?php
namespace Cookbook\Iterator;

use Exception;
use InvalidArgumentException;
use SplFileObject;
use NoRewindIterator;
use Generator;
use Traversable;
use ArrayObject;
use IteratorAggregate;
class LargeFile implements IteratorAggregate
{
    const ERROR_UNABLE = 'ERROR: Unable to open file';
    const ERROR_TYPE   = 'ERROR: Type must be "ByLength", "ByLine" or "CSV"';     
    protected $file;
    protected array $lines = [];
    protected $allowedTypes = ['ByLine', 'ByLength', 'ByArrayObj', 'CSV'];
    public function __construct(string $filename, 
                                string $mode = 'r', 
                                public string $delim = ',',     
                                public bool $hasHeaders = TRUE)
    {
        if (!file_exists($filename)) {
            $message = __METHOD__ . ' : ' . self::ERROR_UNABLE . PHP_EOL;
            $message .= strip_tags($filename) . PHP_EOL;
            throw new Exception($message);
        }
        $this->file = new SplFileObject($filename, $mode);
    }
    protected function fileIteratorByLine() : Generator
    {
        $count = 0;
        while (!$this->file->eof()) {
            $line = trim($this->file->fgets() ?? '');
            if (!empty($line)) {
                // using “yield” instead of building an array
                // saves resources
                yield $line . PHP_EOL;
                $count++;
            }
        }
        // the return value is available only after full iteration has concluded
        return $count;
    }
    protected function fileIteratorByArrayObj() : Traversable
    {
        $count = 0;
        $lines = new ArrayObject();
        while (!$this->file->eof()) {
            $line = trim($this->file->fgets() ?? '');
            if (!empty($line)) {
                $lines[] = $line;
            }
        }
        return $lines;
    }
    protected function fileIteratorByLength($numBytes = 1024) : Generator
    {
        $count = 0;
        while (!$this->file->eof()) {
            yield $this->file->fread($numBytes);
            $count++;
        }
        return $count; 
    }
    public function getIterator($type = 'ByLine', $numBytes = NULL) : iterable
    {
        if(!in_array($type, $this->allowedTypes)) {
            $message = __METHOD__ . ' : '  . self::ERROR_TYPE . PHP_EOL;
            throw new InvalidArgumentException($message);
        }
        $method = 'fileIterator' . $type;
        if (!empty($numBytes)) {
            return $this->$method($numBytes);
        } else {
            return $this->$method();
        }
    }
    public function fileIteratorCSV()
    {
        $count = 0;
        while (!$this->file->eof()) {
            //yield $this->file->fgetcsv(separator:$this->delim, enclosure:'"', escape:'\\');
            yield $this->file->fgetcsv(separator:$this->delim);
            $count++;
        }
        return $count;        
    }
}
