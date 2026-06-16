<?php
namespace Cookbook\Database;
use PDO;
use DateTime;
use DateInterval;
use Psr\SimpleCache\CacheInterface;
use Psr\Container\ContainerInterface;
// Table definition:
/* 
CREATE TABLE gen_ai_cache (
  `cache_key` char(64) NOT NULL,
  `response` text NOT NULL,
  `ttl_exp` int(16) DEFAULT 0,
  PRIMARY KEY (`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
*/
#[GenAiCache("Provides a GenAI caching service as per https://www.php-fig.org/psr/psr-16/")]
class GenAiCache implements CacheInterface
{
    public ?PDO $pdo = NULL;
    public const string TABLE  = 'gen_ai_cache';
    public const int DEFAULT_TTL = 604800;  // 1 week in seconds
    public const string FIND_SQL  = 'SELECT response,ttl_exp FROM %s WHERE cache_key = ?';
    public const string HAS_SQL   = 'SELECT count(cache_key) FROM %s WHERE cache_key = ?';
    public const string SAVE_SQL  = 'INSERT INTO %s (cache_key, response, ttl_exp) VALUES (?,?, ?)';
    public const string DEL_SQL   = 'DELETE FROM %s WHERE cache_key = ?';
    public const string CLEAR_SQL = 'DELETE FROM %s';
    public function __construct(ContainerInterface $container)
    {
        $this->pdo = $container->get('db_connect');        
    }
    public function get(string $key, mixed $default = NULL) : mixed
    {
        $value  = '';
        $stmt   = $this->pdo->prepare(sprintf(static::FIND_SQL, static::TABLE));
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC) ?? [];
        // check for TTL
        if (!empty($result)) {
            if ((int) $result['ttl_exp'] === 0) {
                $value = $result['response'] ?? '';
            } else {
                if ($result['ttl_exp'] < time()) {
                    $this->delete($key);
                } else {
                    $value = $result['response'] ?? '';
                }
            }
        }
        return $value;
    }
    // $ttl value of 0 = never expires
    public function set(string $key, mixed $value, DateInterval|int|null $ttl = NULL): bool
    {
        if ($this->has($key)) return true;
        if (is_object($ttl) && $ttl instanceof DateInterval) {
            // calculate future timestamp
            $ttl = (new DateTime())->add($ttl)->getTimestamp();
        } else {
            $ttl = (int) $ttl;
            // calculate future timestamp
            if ($ttl > 0) $ttl = time() +  $ttl;
        }
        $stmt = $this->pdo->prepare(sprintf(static::SAVE_SQL, static::TABLE));
        return (bool) $stmt->execute([$key, $value, $ttl]);
    }
    public function delete(string $key) : bool
    {
        $stmt = $this->pdo->prepare(sprintf(static::DEL_SQL, static::TABLE));
        return (bool) $stmt->execute([$key]);
    }
    public function clear() : bool
    {
        return (bool) $this->pdo->exec(sprintf(static::CLEAR_SQL, static::TABLE));
    }
    public function has(string $key) : bool
    {
        $stmt = $this->pdo->prepare(sprintf(static::HAS_SQL, static::TABLE));
        $stmt->execute([$key]);
        return (bool) $stmt->fetchColumn();
    }
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        // do nothing
    }
    public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
    {
        // do nothing
    }
    public function deleteMultiple(iterable $keys) : bool
    {
        // do nothing
    }
}
