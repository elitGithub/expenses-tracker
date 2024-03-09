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
    <meta name="application-name" content="Expenses Tracker <?= System::getVersion() ?>">
    <meta name="copyright" content="(c) 2024-<?= date('Y') ?> Eli Tokar">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/font-awesome-4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="shortcut icon" href="../assets/img/favicon_1.ico">
    <title>Expenses Tracker <?= System::getVersion() ?> Setup</title>
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
                    <li>Change the ownership of the config folder and its subdirectories to the web server user (apache/nginx) or create the required directories under the config directory.
                        <ol>
                            <li>config</li>
                            <li>data</li>
                            <li>logs</li>
                            <li>user</li>
                        </ol>
                    </li>
                </ol>
                <button id="show-setup-form" class="btn btn-primary">Proceed to Setup</button>
            </div>
            <form action="index.php"
                  method="post"
                  id="expenses-tracker-setup-form"
                  name="expenses-tracker-setup-form"
                  class="needs-validation d-none" novalidate>
                <div class="form-header d-flex mb-4 justify-content-between">
                    <span class="stepIndicator">Database Setup</span>
                    <span class="stepIndicator">LDAP Setup</span>
                    <span class="stepIndicator">Elasticsearch Setup</span>
                    <span class="stepIndicator">Admin user account</span>
                </div>
                <input type="hidden" id="sqlite_default_path" value="<?php echo htmlspecialchars(dirname(__DIR__)); ?>">
                <div id="available-databases" data-databases='<?php echo json_encode($system->getSupportedSafeDatabases()); ?>'></div>
                <div class="step">
                    <h3 class="mb-3"> Step 1/4: Database setup</h3>
                    <div class="row mb-2">
                        <label class="col-sm-3 col-form-label" for="sql_type">Server:</label>
                        <div class="col-sm-9">
                            <select name="sql_type" id="sql_type" class="form-select" required>
                                <option selected disabled value="">Please choose your preferred database ...</option>
                                <?= implode('', $system->getSupportedSafeDatabases(true)) ?>
                            </select>
                            <small class="form-text text-muted">Please select your preferred database type.</small>
                        </div>
                    </div>

                    <div id="dbdatafull" class="d-block">
                        <div class="row mb-2">
                            <label class="col-sm-3 col-form-label" for="sql_server">Host/Socket:</label>
                            <div class="col-sm-9">
                                <input type="text" name="sql_server" id="sql_server" class="form-control"
                                       placeholder="e.g. 127.0.0.1" required>
                                <small class="form-text text-muted">
                                    Please enter the host or path to the socket of your database server.
                                </small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-3 col-form-label" for="sql_port">Port:</label>
                            <div class="col-sm-9">
                                <input type="number" name="sql_port" id="sql_port" class="form-control"
                                       value="" required>
                                <small class="form-text text-muted">Please enter the port your database server.</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-9 offset-sm-3">
                                <input type="checkbox" name="useSameUser" class="form-check-input" id="useSameUser">
                                <label for="useSameUser" class="form-check-label">Use the same user to create the database and for system operations.</label>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-3 col-form-label" for="root_user">User for database creation:</label>
                            <div class="col-sm-9">
                                <input type="text" name="root_user" id="root_user" class="form-control" required>
                                <small class="form-text text-muted">Please enter your database user.</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-3 col-form-label" for="root_password">Password for database creation:</label>
                            <div class="col-sm-9">
                                <div class="input-group" id="show_root_password">
                                    <input name="root_password" type="password" autocomplete="off" id="root_password"
                                           class="form-control" required>
                                    <span class="input-group-text cursor-pointer" id="toggleRootPassword"><i class="fa fa-eye" id="showRootPassWord"></i></span>
                                </div>
                                <small class="form-text text-muted">Please enter your root user password.</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-3 col-form-label" for="sql_user">Database User for system operations:</label>
                            <div class="col-sm-9">
                                <input type="text" name="sql_user" id="sql_user" class="form-control" required>
                                <small class="form-text text-muted">Please enter your database user.</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-3 col-form-label" for="sql_password">Password for system operations:</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input name="sql_password" type="password" autocomplete="off" id="sql_password"
                                           class="form-control" required>
                                    <span class="input-group-text cursor-pointer" id="toggleSqlPassword"><i class="fa fa-eye" id="showSqlPassword"></i></span>
                                </div>
                                <small class="form-text text-muted">Please enter your database password.</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-9 offset-sm-3">
                                <input type="checkbox" name="createMyOwnDb" class="form-check-input" id="createMyOwnDb">
                                <label for="createMyOwnDb" class="form-check-label">I want to create my own db or I have an existing db</label>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label" for="sql_db">Database:</label>
                            <div class="col-sm-9">
                                <input type="text" name="sql_db" id="sql_db" class="form-control" required>
                                <small class="form-text text-muted">Please enter your existing database name.</small>
                            </div>
                        </div>
                    </div>

                    <div id="dbsqlite" class="d-none">
                        <div class="row mb-2">
                            <label class="col-sm-3 col-form-label" for="sql_sqlitefile">SQLite database file:</label>
                            <div class="col-sm-9">
                                <input type="text" name="sql_sqlitefile" id="sql_sqlitefile" class="form-control"
                                       value="<?= dirname(__DIR__) ?>" required>
                                <small class="form-text text-muted">
                                    Please enter the full path to your SQLite datafile which should be outside your document root.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label class="col-sm-3 col-form-label" for="sqltblpre">Table prefix:</label>
                        <div class="col-sm-9">
                            <input type="text" name="sqltblpre" id="sqltblpre" class="form-control">
                            <small class="form-text text-muted">
                                Please enter a table prefix here if you want to specify Expense Tracker specific table extensions
                            </small>
                        </div>
                    </div>
                </div>
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
</body>
</html>
