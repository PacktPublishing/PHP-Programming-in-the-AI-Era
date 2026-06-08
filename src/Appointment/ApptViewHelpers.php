<?php
namespace Cookbook\Appointment;
class ApptViewHelpers
{
    public const DATE_FMT = 'j M Y h:i:s';
    public static function h(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    public static function fmtDt(string $dt): string
    {
        return $dt !== '' ? date(static::DATE_FMT, strtotime($dt)) : '—';
    }
    public static function pageUrl(array $base, int $p): string
    {
        // Replaced hard-coded "index.php" with basename(__FILE__)
        return 'ch04_appointment.php?' . http_build_query(array_merge($base, ['page' => $p]));
    }
    public static function dtToInput(string $dt): string
    {
        // YYYY-MM-DD HH:MM:SS → YYYY-MM-DDTHH:MM  (datetime-local format)
        return $dt !== '' ? substr(str_replace(' ', 'T', $dt), 0, 16) : '';
    }
}
