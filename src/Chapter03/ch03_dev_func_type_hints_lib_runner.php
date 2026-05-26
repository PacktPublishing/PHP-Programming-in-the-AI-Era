<?php
namespace Library;
use DateTime;
use TypeError;
include (__DIR__ . DIRECTORY_SEPARATOR . 'ch03_developing_func_type_hints_library.php');

try {
    $callable = function () { return 'Callback Return'; };
    echo someTypeHint([1,2,3], new DateTime(), $callable);
    echo someTypeHint('A', 'B', 'C');
} catch (TypeError $e) {
    echo $e->getMessage();
    echo PHP_EOL;
}

try {
    echo someScalarHint(TRUE, 11, 22.22, 
        'This is a string');
    echo someScalarHint('A', 'B', 'C', 'D');
} catch (TypeError $e) {
    echo $e->getMessage();
}

try {
    // positive results
    $b = someBoolHint(TRUE);
    $i = someBoolHint(11);
    $f = someBoolHint(22.22);
    $s = someBoolHint('X');
    var_dump($b, $i, $f, $s);
    // negative results
    $b = someBoolHint(FALSE);
    $i = someBoolHint(0);
    $f = someBoolHint(0.0);
    $s = someBoolHint('');
    var_dump($b, $i, $f, $s);
} catch (TypeError $e) {
    echo $e->getMessage();
}
