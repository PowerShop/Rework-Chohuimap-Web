<?php

require dirname(__FILE__) . '/_funcs.php';
require dirname(__FILE__) . '/_config.php';
// require dirname(__FILE__) . '/_user.php';
// require dirname(__FILE__) . '/_shop.php';
// require dirname(__FILE__) . '/_notify.php';
// require dirname(__FILE__) . '/_backend.php';
// require dirname(__FILE__) . '/_alert.php';
// require dirname(__FILE__) . '/_merchant.php';


/* APIs */
$api = (object) array(
    'sql' => new PDO('mysql:host=' . $_config['db_host'] . '; dbname=' . $_config['db_database'] . ';', $_config['db_user'], $_config['db_password']),
    // 'user' => new User(),
);

$api->sql->exec('set names utf8');
