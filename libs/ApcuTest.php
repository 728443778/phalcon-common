<?php

require_once "./Apcu.php";

$frontCache = new \Phalcon\Cache\Frontend\Data([
    'lifetime' => 300
]);
$cache = new \app\core\libs\Apcu($frontCache);
if ($cache->save('key1', [1,2,3,4,5])) {
    echo 'store ok',PHP_EOL;
} else {
    echo 'store failed',PHP_EOL;
}
var_dump(apcu_fetch('key1'));
var_dump(apcu_cache_info());
$result = $cache->get('key1');
if ($result) {
    echo 'get ok',PHP_EOL;
} else {
    echo 'get failed',PHP_EOL;
}