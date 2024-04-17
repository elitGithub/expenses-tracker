<?php

declare(strict_types=1);
global $default_language, $app_unique_key;
require_once 'src/engine/ignition.php';
if (!file_exists('system/installation_includes.php')) {
    require_once 'install/index.php';
    exit(1);
}

use Core\System;
use engine\User;
use Session\JWTHelper;

$current_user = new User();
if ($current_user->isLoggedIn()) {
    header('Location: index.php');
}
$_SESSION['formToken']['login'] = $app_unique_key;

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
    <script src="https://cdn.jsdelivr.net/npm/zod@3.22.4/lib/index.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous" async></script>
</head>

<body>
    <div class="container mt-5">
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p class="alert alert-danger">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h1 class="h3 mb-3 fw-normal text-center">Expense Tracker Login</h1>
                        <h3 class="h5 mb-3 fw-normal text-center">Enter your login credentials</h3>
                        <form action="authenticate.php" method="POST" class="needs-validation" id="loginForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $app_unique_key; ?>">
                            <div class="form-group mb-3">
                                <label for="username" class="form-label">Username:</label>
                                <input type="text" id="username" name="username" class="form-control" required autofocus>
                                <div class="error-message" id="usernameError"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                                <div class="error-message" id="passwordError"></div>
                            </div>
                            <div style="display:none">
                                <input type="text" name="website" value="">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" formnovalidate>Log In</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        /**
         * Validates a given input element against multiple criteria.
         *
         * @param {HTMLElement} input The input element to validate.
         * @param {Object[]} validators An array of validator objects.
         * @param {HTMLElement} errorElement The element to display the error message in.
         * @returns {Boolean} True if all validations pass; otherwise, false.
         */
        function validateInput(input, validators, errorElement) {
            let isValid = true;
            // Clear previous error messages
            errorElement.textContent = '';
            input.classList.remove('is-invalid');

            validators.forEach(validator => {
                if (!validator.test(input.value)) {
                    input.classList.add('is-invalid');
                    // Append error messages if multiple failures
                    errorElement.textContent += (errorElement.textContent ? ' ' : '') + validator.message;
                    isValid = false;
                }
            });

            return isValid;
        }

        // Example usage with multiple validation rules for a username
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('loginForm');
            const username = document.getElementById('username');
            const password = document.getElementById('password');
            const usernameError = document.getElementById('usernameError');
            const passwordError = document.getElementById('passwordError');

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const isUsernameValid = validateInput(username, [{
                        test: value => value.trim() !== '',
                        message: 'Please enter your username.'
                    },
                    {
                        test: value => value.length >= 3,
                        message: 'Username must be at least 3 characters long.'
                    }
                ], usernameError);

                const isPasswordValid = validateInput(password, [{
                        test: value => value.trim() !== '',
                        message: 'Please enter your password.'
                    },
                    {
                        test: value => value.length >= 8,
                        message: 'Password must be at least 8 characters long.'
                    },
                    {
                        test: value => /[A-Z]/.test(value),
                        message: 'Password must contain an uppercase letter.'
                    },
                    {
                        test: value => /[a-z]/.test(value),
                        message: 'Password must contain a lowercase letter.'
                    },
                    {
                        test: value => /[0-9]/.test(value),
                        message: 'Password must contain a number.'
                    }
                ], passwordError);

                if (isUsernameValid && isPasswordValid) {
                    form.submit();
                }
            });
        });
    </script>
</body>

</html>
