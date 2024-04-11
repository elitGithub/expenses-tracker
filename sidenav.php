<nav class="navbar-default navbar-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav" id="main-menu">
            <li class="text-center">
                <img src="assets/img/find_user.png" class="user-image img-responsive" alt=""/>
            </li>
            <li>
                <a href='index.php' <?php
                if (!isset($_GET['action'])) echo 'class="active-menu"' ?>><i class="fa fa-dashboard fa-3x"></i> Dashboard</a>
            </li>
            <li>
                <a href="?action=expense_report" <?php
                if (isset($_GET['action']) && $_GET['action'] === 'expense_report') echo 'class="active-menu"' ?>><i
                        class='fa fa-keyboard-o fa-2x'></i> Expenses Report</a>
            </li>
            <li>
                <a href="?action=expense_category" <?php
                if (isset($_GET['action']) && $_GET['action'] === 'expense_category') echo 'class="active-menu"' ?>><i class="fa fa-cog fa-2x" aria-hidden="true"></i> Expenses By Category</a>
            </li>
            <li><a href="?action=user_management"><i class="fa fa-list fa-2x"></i>User Management</a></li>
        </ul>
    </div>
</nav>
<!-- /. NAV SIDE  -->

