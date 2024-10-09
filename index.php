<!doctype html>
<html lang="en">

<head>
    <title>โชห่วยแมพ | หน้า <?php echo isset($_GET['page']) ? strtoupper(htmlspecialchars($_GET['page'])) : 'โชห่วยแมพ | หน้าหลัก'; ?></title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.2.1 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
        crossorigin="anonymous" />

    <!-- SweetAlert2 CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.6.0/js/all.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="_dist/_favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="_dist/_favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="_dist/_favicon/favicon-16x16.png">
    <link rel="manifest" href="_dist/_favicon/site.webmanifest">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="_dist/_css/_main.css" />

    <!-- Require User Location -->
    <script src="_dist/_js/_requirePermissionLocation.js"></script>
</head>

<body>
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
    ?>
    <script
        src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>

</body>

</html>