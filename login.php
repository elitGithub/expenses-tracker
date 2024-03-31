<?php

declare(strict_types=1);
global $default_language;
require_once 'src/engine/ignition.php';
if (!file_exists('system/installation_includes.php')) {
    require_once 'install/index.php';
    exit(1);
}

use Core\System;
use Session\JWTHelper;

$user = new User();
if ($user->isLoggedIn()) {
    header('Location: index.php');
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="application-name" content="Expenses Tracker <?php echo System::getVersion(); ?>">
    <meta name="copyright" content="(c) 2024-<?php echo date('Y'); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/font-awesome-4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="shortcut icon" href="assets/img/favicon_1.ico">
    <title>Expenses Tracker <?php echo System::getVersion(); ?> Login</title>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h1 class="h3 mb-3 fw-normal text-center">Expense Tracker Login</h1>
                        <h3 class="h5 mb-3 fw-normal text-center">Enter your login credentials</h3>
                        <form action="authenticate.php" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo $app_unique_key; ?>">
                            <div class="form-group mb-3">
                                <label for="username" class="form-label">Username:</label>
                                <input type="text" id="username" name="username" class="form-control" required autofocus>
                            </div>
                            <div class="form-group mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div style="display:none">
                                <input type="text" name="website" value="">
                            </div>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-lg" type="submit">Log In</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-qO/NnmcE5Zkz7Ql2zMS4Rc/kyTg4IxN9kDfzRlPBmE5zJ4//kCJ6YdPkPBiMqx8j" crossorigin="anonymous"></script>
</body>

</html>