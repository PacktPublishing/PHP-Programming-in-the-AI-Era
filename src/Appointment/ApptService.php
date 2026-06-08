<?php
namespace Cookbook\Appointment;
use Cookbook\Appointment\ApptRepository;
use Cookbook\Appointment\Appointment;
class ApptService
{
    public function __construct(public ApptRepository $repo) {}
    public function list(
        string $start_date = '',
        string $end_date   = '',
        int    $page       = 1,
        int    $per_page   = 20
    ): array {
        // replace *date == '' with !empty(*date)
        $start = (!empty($start_date)) ? $start_date . ' 00:00:00' : null;
        $end   = (!empty($end_date))   ? $end_date   . ' 23:59:59' : null;
        $total = $this->repo->countList($start, $end);
        $pages = max(1, (int) ceil($total / $per_page));
        $page  = max(1, min($page, $pages));
        $rows  = $this->repo->fetchList($start, $end, $per_page, ($page - 1) * $per_page);
        return ['rows' => $rows, 'total' => $total, 'pages' => $pages, 'page' => $page];
    }
    public function getById(int $id): ?Appointment
    {
        return $this->repo->fetchById($id);
    }
}
