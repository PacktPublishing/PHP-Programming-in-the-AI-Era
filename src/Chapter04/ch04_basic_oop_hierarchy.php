<?php
// Usage: php __FILE__ city|postcode item[,item,item]
// To test:
/*
ed@localhost:~/Repos/Packt/PHP-Programming-in-the-AI-Era
$ ./admin.sh shell php8
php8:/repo# cd data
php8:/repo/data# wget https://download.geonames.org/export/dump/cities15000.zip
php8:/repo/data# unzip cities15000.zip 
php8:/repo/data# rm cities15000.zip 
*/

$city_fn   = __DIR__ . '/../../data/cities15000.txt';
$post_fn   = __DIR__ . '/../../data/US.txt';
include __DIR__ . '/../../vendor/autoload.php';
use Cookbook\Geonames\{City,PostCode};
use Cookbook\Iterator\LargeFile;
// grab inputs
$action = trim(strip_tags($_GET['action'] ?? $argv[1] ?? 'city'));
$item = trim(strip_tags($_GET['search'] ?? $argv[2] ?? 'Rochester'));
$item = explode(',', $item);
$largeFile = new LargeFile(($action === 'city') ? $city_fn : $post_fn);
$iterator  = $largeFile->getIterator();
// define a filter that finds elements contains $item
$filter = new class ($iterator) extends FilterIterator {
    public array $item = [];
    public function accept() : bool
    {
        $count = count($this->item);
        $found = 0;
        $row   = parent::current();
        foreach ($this->item as $search)
            $found += (int) str_contains($row, $search);
        return ($found === $count);
    }
};
$filter->item = $item;
// determine which class to use
$class = match($action) {
    'city' => City::class,
    default => PostCode::class
};
printf("%30s | %10s | %4s\n", 'City', 'State/Prov', 'ISO2');
printf("%30s | %10s | %4s\n", str_repeat('-', 30), '----------', '----');
foreach ($filter as $row) {
    $item = new $class(str_getcsv($row, "\t"));
    printf( "%30s | %10s | %4s\n", 
            $item->getCityName(), 
            $item->getStateProvCode(), 
            $item->getCountry());
}
// default output:
/*
                City | State/Prov | ISO2
-------------------- | ---------- | ----
           Rochester |        ENG |   GB
     Rochester Hills |         MI |   US
           Rochester |         MN |   US
           Rochester |         NH |   US
           Rochester |         NY |   US
     North Kingstown |         RI |   US
*/
