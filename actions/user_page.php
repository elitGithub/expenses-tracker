<?php

declare(strict_types = 1);

global $app_unique_key;
?>


<form action="index.php?action=save_my_preferences" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <?php
        $src = fileExists(USER_AVATARS_UPLOAD_DIR, $current_user->id . '_avatar');
        $src = USER_AVATARS_FILE_URL . $src;
        if (!$src) {
            $src = 'assets/img/find_user.png';
        }
        ?>
        <img src="<?php echo $src?>" class="user-image img-responsive" alt=""/>
    </div>
    <div class="mb-3">
        <div class="mb-3">
            <label for="user_photo" class="form-label">Change my profile picture</label>
            <input class="form-control" name="user_photo" type="file" id="user_photo">
        </div>
    </div>
    <div class="mb-3">
        <div class="form-group col-md-6">
            <label for="user_name">Username:</label>
            <input type="text" class="form-control" name="user_name" id="user_name" placeholder="Please Enter the username" disabled="disabled">
        </div>
        <div class="form-group col-md-6">
            <label for="email">User email:</label>
            <input type="email" class="form-control" name="email" id="email" placeholder="Please Enter user email" required>
        </div>
    </div>

    <div class="mb-3">
        <div class="form-group col-md-6">
            <label for="first_name">User first name:</label>
            <input type="text" class="form-control" name="first_name" id="first_name" placeholder="Please Enter first name" required>
        </div>

        <div class="form-group col-md-6">
            <label for="last_name">User last name:</label>
            <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Please Enter last name" required>
        </div>
    </div>

    <div class="mb-3">
        <div class="form-group col-md-6">
            <label for="password">User password:</label>
            <div class="input-group" id="show_user_password">
                <input name="password" type="password" minlength="8" autocomplete="off" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" id="password" class="form-control">
                <span class="input-group-text cursor-pointer" id="toggleUserPassword"><i class="fa fa-eye" id="showUserPassword"></i></span>
            </div>
        </div>

        <div class="form-group col-md-6">
            <label for="password_retype">Retype password:</label>
            <div class="input-group" id="show_retype_password">
                <input type="password" autocomplete="off" name="password_retype" id="password_retype" minlength="8" class="form-control" required>
                <span class="input-group-text cursor-pointer" id="toggleRetypePassword"><i class="fa fa-eye" id="showRetypePassword"></i></span>
            </div>
        </div>
    </div>
    <input type="hidden" name="formToken" value="<?php
    echo htmlspecialchars($app_unique_key); ?>">
    <input type="hidden" id="upload_user_photo" name="upload_user_photo" value="">

    <div class="modal-footer">
        <input type="submit" id="save_my_preferences" name="submit" value="Save" class="btn btn-primary">
    </div>
</form>