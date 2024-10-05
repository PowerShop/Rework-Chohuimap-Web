<?php

// Set timezone
date_default_timezone_set("Asia/Bangkok");

// Hide errors
@ini_set('display_errors', '0');

// Include API (Main core)
require dirname(__FILE__) . '/_funcs/_api.php';

// Include Navbar
include '_pages/navbar.php';

$user = $_SESSION['user'];

if ($_GET) {
} else {
    rdr('?page=home', 500);
}

if (isset($_GET['page'])) {
    $page = '_pages/' . $_GET['page'] . '.php';
    if (file_exists($page)) {
        include $page;
    } else {
        rdr('?page=404', 500);
    }
}
include '_pages/footer.php';
