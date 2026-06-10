<?php
namespace Cookbook\Appointment;
use PDO;
use PDOStatement;
use Cookbook\Appointment\Appointment;
class ApptRepository
{
    public const string TABLE = 'appointment';
    // excludes `id` (auto-increment); add `id` explicitly in SELECT queries
    public array $cols = ['title', 'location', 'contact_info', 'start_date_and_time', 'end_date_and_time'];
    // modified to accept PDO instance directly
    public function __construct(public PDO $pdo)
    {}
    public function add(Appointment $appt) : bool
    {
        // replaced hard-coded column list with an implode() using $this->cols
        $cols = '`' . implode('`,`', $this->cols) . '`';
        $placeholders = ':' . implode(', :', $this->cols);
        // replaced with sprintf()
        $sql = sprintf('INSERT INTO `%s` (%s) VALUES (%s)', static::TABLE, $cols, $placeholders);
        $stmt = $this->pdo->prepare($sql);
        return (bool) $stmt->execute($appt->extract(true));
    }
    public function edit(Appointment $appt) : bool
    {
        $set = implode(', ', array_map(fn($col) => "`{$col}` = :{$col}", $this->cols));
        $sql = sprintf('UPDATE `%s` SET %s WHERE `id` = :id', static::TABLE, $set);
        $stmt = $this->pdo->prepare($sql);
        return (bool) $stmt->execute($appt->extract());
    }
    public function delete(Appointment $appt) : bool
    {
        // NOTE: 
        $sql = sprintf('DELETE FROM `%s` WHERE `id` = :id', static::TABLE);
        $stmt = $this->pdo->prepare($sql);
        return (bool) $stmt->execute(['id' => $appt->id]);
    }
    // ── Listing helpers ───────────────────────────────────────────────────
    private function buildWhere(?string $start, ?string $end): array
    {
        if ($start !== null && $end !== null) {
            return [
                ' WHERE `start_date_and_time` BETWEEN :start AND :end',
                [':start' => $start, ':end' => $end],
            ];
        }
        if ($start !== null) {
            return [' WHERE `start_date_and_time` >= :start', [':start' => $start]];
        }
        if ($end !== null) {
            return [' WHERE `start_date_and_time` <= :end', [':end' => $end]];
        }
        return ['', []];
    }
    public function fetchList(?string $start, ?string $end, int $limit, int $offset): array
    {
        [$where, $params] = $this->buildWhere($start, $end);
        // replaced hard-coded column list with an implode() using $this->cols
        $sql  = 'SELECT ' . '`id`,`' . implode('`,`', $this->cols) . '` '
              . 'FROM `appointment` '
              . $where . ' '
              . 'ORDER BY `start_date_and_time` ASC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function countList(?string $start, ?string $end): int
    {
        [$where, $params] = $this->buildWhere($start, $end);
        $sql  = 'SELECT COUNT(*) FROM `appointment` ' . $where;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
    public function fetchById(int $id): ?Appointment
    {
        // replaced hard-coded column list with an implode() using $this->cols
        $sql  = 'SELECT ' . '`id`,`' . implode('`,`', $this->cols) . '`'
              . ' FROM `appointment` WHERE `id` = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        return (!empty($row)) ? new Appointment(...$row) : null;
    }
}
