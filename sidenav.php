<?php

declare(strict_types = 1);

use Permissions\PermissionsManager;

?>

<nav class="navbar-default navbar-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="main-menu">
            <li class="text-center">
                <?php
                $src = fileExists(USER_AVATARS_UPLOAD_DIR, $current_user->id . '_avatar');
                if ($src) {
                    $src = USER_AVATARS_FILE_URL . $src;
                }

                if (!$src) {
                    $src = 'assets/img/find_user.png';
                }
                ?>
                <img src="<?php echo $src?>" class="user-image img-responsive" alt=""/>
            </li>
            <li>
                <a href='index.php' <?php

                if (!isset($_GET['action']) || $action === 'home') echo 'class="active-menu"' ?>><i class="fa fa-dashboard fa-2x"></i> Dashboard</a>
            </li>
            <?php
            if (PermissionsManager::isPermittedAction('expense_category', $current_user)):
                ?>
                <li>
                    <a href="?action=expense_category" <?php
                    if (isset($_GET['action']) && $action === 'expense_category') echo 'class="active-menu"' ?>><i class="fa fa-cog fa-2x"
                                                                                                                   aria-hidden="true"></i>
                        Expenses By Category</a>
                </li>
            <?php endif; ?>
            <?php
            if (PermissionsManager::isPermittedAction('expense_report', $current_user)):
            ?>
            <li>
                <a href="?action=expense_report" <?php
                if ($action === 'expense_report') echo 'class="active-menu"' ?>><i
                        class='fa fa-keyboard-o fa-2x'></i> Expenses Report</a>
            </li>
           <?php endif; ?>
            <?php
            if (PermissionsManager::isPermittedAction('view_user_management', $current_user)):
                ?>
                <li><a href="?action=user_management" <?php
                    if ($action === 'user_management') echo 'class="active-menu"' ?>><i
                            class="fa fa-users fa-2x"></i>User
                        Management</a></li>
            <?php
            endif; ?>
            <?php
            if (PermissionsManager::isAdmin($current_user)):
                ?>
                <li><a href="?action=manage_permissions" <?php
                    if ($action === 'manage_permissions') echo 'class="active-menu"' ?>><i
                            class="fa fa-tasks fa-2x"></i>Edit Permissions
                    </a></li>
            <?php
            endif; ?>
        </ul>
    </div>
</nav>
<!-- /. NAV SIDE  -->

