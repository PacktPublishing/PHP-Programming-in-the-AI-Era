<?php
namespace Cookbook\Appointment;
class Appointment
{
    public function __construct(
        public int    $id                  = 0,
        public string $title               = '',
        public string $location            = '',
        public string $contact_info        = '',
        public string $start_date_and_time = '',
        public string $end_date_and_time   = '',
    ) {}
    // if $insert === true exclude "id" arg
    public function extract(bool $insert = false) : array
    {
        $arr = get_object_vars($this);
        if ($insert) unset($arr['id']);
        return $arr;
    }
}
