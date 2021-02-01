<?php

$time = time();
echo "Time: $time".PHP_EOL."Hash: ".sha1($argv[1].$time.'Sh!! No se lo cuentes a nadie!');
echo "\n";
echo 'curl http://localhost:8000/books -H "X-HASH: '.sha1($argv[1].$time.'Sh!! No se lo cuentes a nadie!').'" -H "X-UID: 1" -H "X-TIMESTAMP: '.$time.'"'.PHP_EOL;