<?php

declare(strict_types = 1);

use Core\System;
use database\PearDatabase;
use Setup\Installer;

if (file_exists('./config/database.php')) {
    header('Location: /');
    exit(1);
}

define('EXTR_ROOT_DIR', dirname(__FILE__, 2));
const EXTR_SRC_DIR = EXTR_ROOT_DIR . '/src';

require_once EXTR_SRC_DIR . '/engine/ignition.php';
const IS_VALID_EXPENSE_TRACKER = null;

if (version_compare(PHP_VERSION, '7.4.0') < 0) {
    die('Sorry, but you need PHP 7.4.0 or later!');
}


set_time_limit(0);

if (!defined('DEBUG')) {
    define('DEBUG', true);
}

if (DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL | E_STRICT);
}

session_name('expenses-tracker-setup');
session_start();
$db = PearDatabase::getInstance();

$system = new System();
$installer = new Installer($system);
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="application-name" content="Expenses Tracker <?php echo System::getVersion() ?>">
    <meta name="copyright" content="(c) 2024-<?php echo date('Y') ?> Eli Tokar">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/font-awesome-4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="shortcut icon" href="../assets/img/favicon_1.ico">
    <title>Expenses Tracker <?php echo System::getVersion() ?> Setup</title>
</head>
<body>
<nav class="p-3 text-bg-dark border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
            </ul>
        </div>
    </div>
</nav>

<main role="main">
    <section id="content">
        <div class="container shadow-lg p-5 mt-5 bg-light-subtle">
            <div id="pre-install-instructions" class="mb-5">
                <h2>Pre-installation Instructions</h2>
                <div class="alert alert-success">
                    Before proceeding with the installation, have you read the README file?
                </div>
                <p>Please follow these steps before proceeding with the setup:</p>
                <ol>
                    <li>Web server: Apache or Nginx.</li>
                    <li>PHP version 7.4.33 or newer.</li>
                    <li>Change the ownership of the config folder and its subdirectories to the web server user (apache/nginx) or create the required
                        directories under the config directory.
                        <ol>
                            <li>config</li>
                            <li>data</li>
                            <li>logs</li>
                            <li>user</li>
                        </ol>
                    </li>
                </ol>
                <button id="show-setup-form" class="btn btn-primary float-end">Proceed to Setup</button>
            </div>
            <form action="index.php" method="post" id="expenses-tracker-setup-form" name="expenses-tracker-setup-form" class="install-form d-none"
                  novalidate>
                <div class="form-header d-flex mb-4 justify-content-between">
                    <span class="stepIndicator">Database Setup</span>
                    <span class="stepIndicator">User System Setup</span>
                    <span class="stepIndicator">Admin user account</span>
                </div>
                <div data-form-step=1 id="step1" class="step">
                    <h3 class="mb-3">Step 1/4: Database setup</h3>
                    <!-- Basic Info Form -->
                    <div data-step=1 id="basic-info-form">
                        <div class="row mb-2">
                            <label class="col-sm-3 col-form-label" for="sql_type">Server:</label>
                            <div class="col-sm-9">
                                <select name="sql_type" id="sql_type" class="form-select" required>
                                    <option selected disabled value="">Please choose your preferred database ...</option>
                                    <?php echo implode('', $system->getSupportedSafeDatabases(true)) ?>
                                </select>
                                <small class="form-text text-muted">Please select your preferred database type.</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-3 col-form-label" for="sql_server">Host/Socket:</label>
                            <div class="col-sm-9">
                                <input type="text" name="sql_server" id="sql_server" class="form-control" placeholder="e.g. 127.0.0.1" required>
                                <small class="form-text text-muted">Please enter the host or path to the socket of your database server.</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-3 col-form-label" for="sql_port">Port:</label>
                            <div class="col-sm-9">
                                <input type="number" name="sql_port" id="sql_port" class="form-control" required>
                                <small class="form-text text-muted">Please enter the port your database server.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Connection Info Form -->
                    <div data-step=2 class="d-none" id="connection-info-form">
                        <div class="row mb-2">
                            <div class="col-sm-9 offset-sm-3">
                                <input type="checkbox" name="useSameUser" class="form-check-input" id="useSameUser">
                                <label for="useSameUser" class="form-check-label">Use the same user for database creation and system
                                    operations.</label>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-3 col-form-label" for="root_user">User for database creation:</label>
                            <div class="col-sm-9">
                                <input type="text" name="root_user" id="root_user" class="form-control" required>
                                <small class="form-text text-muted">Please enter your root database user.</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-3 col-form-label" for="root_password">Password for database creation:</label>
                            <div class="col-sm-9">
                                <div class="input-group" id="show_root_password">
                                    <input name="root_password" type="password" autocomplete="off" id="root_password" class="form-control" required>
                                    <span class="input-group-text cursor-pointer" id="toggleRootPassword"><i class="fa fa-eye"
                                                                                                             id="showRootPassWord"></i></span>
                                </div>
                                <small class="form-text text-muted">Please enter your root user password.</small>
                            </div>
                        </div>
                        <div class="row mb-2 sql_user_control">
                            <label class="col-sm-3 col-form-label" for="sql_user">Database User for system operations:</label>
                            <div class="col-sm-9">
                                <input type="text" name="sql_user" id="sql_user" class="form-control" required>
                                <small class="form-text text-muted">Please enter your database user for system operations.</small>
                            </div>
                        </div>
                        <div class="row mb-2 sql_user_control">
                            <label class="col-sm-3 col-form-label" for="sql_password">Password for system operations:</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input name="sql_password" type="password" autocomplete="off" id="sql_password" class="form-control" required>
                                    <span class="input-group-text cursor-pointer" id="toggleSqlPassword"><i class="fa fa-eye"
                                                                                                            id="showSqlPassword"></i></span>
                                </div>
                                <small class="form-text text-muted">Please enter your database password for system operations.</small>
                            </div>
                        </div>
                    </div>

                    <!-- DB Type Form -->
                    <div data-step=3 class="d-none" id="db-type-form">
                        <div class="row mb-2">
                            <div class="col-sm-9 offset-sm-3">
                                <input type="checkbox" name="createMyOwnDb" class="form-check-input" id="createMyOwnDb">
                                <label for="createMyOwnDb" class="form-check-label">I want to create my own Database or I have an existing Database</label>
                            </div>
                        </div>
                        <div class="row mb-2 d-flex" id="devMessageDb">
                            <div class="alert alert-success">
                                Bear in mind that Expense Tracker aims to minimize stress on any existing Databases,
                                and therefore aims to create its own database with its own internal process.
                                Of course, you may use your own existing databases,
                                but we recommend allowing our system to be independent of any additional systems.
                            </div>
                        </div>
                        <div class="row mb-2 d-none create-my-own-db-control">
                            <label class="col-sm-3 col-form-label" for="table_prefix">Table prefix:</label>
                            <div class="col-sm-9">
                                <input type="text" name="sqltblpre" id="table_prefix" class="form-control">
                                <small class="form-text text-muted">Please enter a table prefix here if you want to specify Expense Tracker specific table extensions.</small>
                            </div>
                        </div>
                        <div id="dbsqlite" class="d-none">
                            <div class="row mb-2">
                                <label class="col-sm-3 col-form-label" for="sql_sqlitefile">SQLite database file:</label>
                                <div class="col-sm-9">
                                    <input type="text" name="sql_sqlitefile" id="sql_sqlitefile" class="form-control"
                                           value="<?php echo dirname(__DIR__) ?>" required>
                                    <small class="form-text text-muted">
                                        Please enter the full path to your SQLite datafile which should be outside your document root.
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3 d-none create-my-own-db-control">
                            <label class="col-sm-3 col-form-label" for="sql_db">Database:</label>
                            <div class="col-sm-9">
                                <input type="text" name="sql_db" id="sql_db" class="form-control">
                                <small class="form-text text-muted">Please enter your existing database name or the name for a new one.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="step d-none" id="step2" data-form-step="2">
                    <h3 class="mb-3">Step 2/4: User system setup</h3>
                    <div class="row mb-2">
                        <div class="col-sm-9 offset-sm-3">
                            <input type="checkbox" name="useMyOwnUserSystem" class="form-check-input" id="useMyOwnUserSystem">
                            <label for="useMyOwnUserSystem" class="form-check-label">I have my own user system, no need for Expense Tracker's
                                system.</label>
                        </div>
                    </div>
                    <div class="row mb-2 create-my-own-user-control">
                        <div class="row mb-2">
                            <label class="col-sm-3 col-form-label" for="user_management">Server:</label>
                            <div class="col-sm-9">
                                <select name="user_management" id="user_management" class="form-select" required>
                                    <option selected disabled value="">Please choose your preferred cache system ...</option>
                                    <?php echo implode('', $system->getSupportedSafePermissionEngines(true)) ?>
                                </select>
                                <small class="form-text text-muted">Please select your preferred cache type.</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2 default-control d-none">
                        <div class="alert alert-warning">
                            Please note that using the default file system will create a permissions file
                            that will be loaded every time the user permissions need to be checked.
                            While this is not an issue for minimal user number, it is recommended to use a caching engine for better scale.
                        </div>
                    </div>
                    <div class="row mb-2 redis-control d-none">
                        <label class="col-sm-3 col-form-label" for="redis_host">Redis Host:</label>
                        <div class="col-sm-9">
                            <input type="text" name="redis_host" id="redis_host" class="form-control" required>
                            <small class="form-text text-muted">Please enter your redis host.</small>
                        </div>
                    </div>
                    <div class="row mb-2 redis-control d-none">
                        <label class="col-sm-3 col-form-label" for="redis_user">Redis Username:</label>
                        <div class="col-sm-9">
                            <input type="text" name="redis_user" id="redis_user" class="form-control" required>
                            <small class="form-text text-muted">Please enter your redis user.</small>
                        </div>
                    </div>
                    <div class="row mb-2 redis-control d-none">
                        <label class="col-sm-3 col-form-label" for="redis_password">Redis password:</label>
                        <div class="col-sm-9">
                            <div class="input-group" id="show_redis_password">
                                <input name="redis_password" type="password" autocomplete="off" id="redis_password" class="form-control">
                                <span class="input-group-text cursor-pointer" id="toggleRedisPassword"><i class="fa fa-eye" id="showRedisPass"></i></span>
                            </div>
                            <small class="form-text text-muted">Please enter your redis password. Leave blank if your redis server has no password.</small>
                        </div>
                    </div>
                </div>
                <!-- start previous / next buttons -->
                <div class="form-footer d-flex mt-5 float-end">
                    <button class="btn btn-lg btn-danger w-30" type="button" id="prevBtn">Previous</button>
                    <button class="btn btn-lg btn-success w-30" type="button" id="nextBtn">Next</button>
                </div>
                <!-- end previous / next buttons -->
            </form>

        </div>
    </section>
</main>

<?php
try {
    $installer->checkBasicStuff();
} catch (Throwable $exception) {
    echo sprintf('<div class="alert alert-danger alert-dismissible fade show mt-2">%s%s</div>',
                 '<h4 class="alert-heading">Error occurred during basic check</h4>',
                 "<p>{$exception->getMessage()}</p>");
}
?>
<div class="control">
    <?php
    $installer->checkFilesystemPermissions();
    ?>
</div>
<script defer src="../assets/js/install.js"></script>
<script defer src="../assets/js/install_step_one.js"></script>
<script defer src="../assets/js/install_step_two.js"></script>
</body>
</html>
