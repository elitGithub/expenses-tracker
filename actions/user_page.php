<?php

declare(strict_types = 1);

global $app_unique_key;

$userInfo = $current_user->session->sessionReadKey('authenticated_user_data');
?>

<div class="container">
    <form id="my-preferences-form" action="index.php?action=save_my_preferences" method="POST" enctype="multipart/form-data" class="p-3">
        <div class="row mb-3">
            <?php
            $src = fileExists(USER_AVATARS_UPLOAD_DIR, $current_user->id . '_avatar');
            if ($src) {
                $src = USER_AVATARS_FILE_URL . $src;
            }            if (!$src) {
                $src = 'assets/img/find_user.png';
            }
            ?>
            <img src="<?php
            echo $src ?>" class="user-image img-responsive" alt="User image" id="profileImage"/>
        </div>
        <div class="row">
            <div class="mb-3">
                <label for="user_photo" class="form-label">Change my profile picture</label>
                <input class="form-control" name="user_photo" type="file" id="user_photo">
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6 mb-3">
                <label for="user_name">Username:</label>
                <input type="text" class="form-control mt-2" name="user_name" id="user_name" value="<?php
                echo $userInfo['userName'] ?>" placeholder="Please Enter the username" disabled="disabled">
            </div>
            <div class="form-group col-md-6 mb-3">
                <label for="email">Email:</label>
                <input type="email" class="form-control mt-2" value="<?php
                echo $userInfo['email'] ?>" name="email" id="email" placeholder="Please Enter user email" required>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6 mb-3">
                <label for="first_name">First Name:</label>
                <input type="text" class="form-control mt-2" name="first_name" id="first_name" value="<?php
                echo $userInfo['first_name'] ?>" placeholder="Please Enter first name" required>
            </div>
            <div class="form-group col-md-6 mb-3">
                <label for="last_name">Last Name:</label>
                <input type="text" class="form-control mt-2" name="last_name" id="last_name" value="<?php
                echo $userInfo['last_name'] ?>" placeholder="Please Enter last name" required>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6 mb-3">
                <label for="password">Change password:</label>
                <div class="input-group mt-2">
                    <input name="password" type="password" minlength="8" autocomplete="off" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                           id="password" class="form-control">
                    <span class="input-group-text cursor-pointer" id="toggleUserPassword"><i class="fa fa-eye" id="showUserPassword"></i></span>
                </div>
            </div>
            <div class="form-group col-md-6 mb-3">
                <label for="password_retype">Confirm change password:</label>
                <div class="input-group mt-2">
                    <input type="password" autocomplete="off" name="password_retype" id="password_retype" minlength="8" class="form-control">
                    <span class="input-group-text cursor-pointer" id="toggleRetypePassword"><i class="fa fa-eye" id="showRetypePassword"></i></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 d-flex align-content-center justify-content-center invalid-feedback d-none">Passwords do not match</div>
        </div>
        <input type="hidden" name="formToken" value="<?php
        echo htmlspecialchars($app_unique_key); ?>">
        <input type="hidden" id="upload_user_photo" name="upload_user_photo" value="">
        <input type="hidden" id="my_id" name="userId" value="<?php echo $current_user->id?>">
        <input type="hidden" id="change_password_request" name="change_password_request" value="">
        <div class="form-footer mt-3 pull-right">
            <input type="submit" id="save_my_preferences" name="submit" value="Save" class="btn btn-primary">
        </div>
    </form>

</div>

<script>
    $(document).ready(function () {
        // Store the initial src
        const originalSrc = $('#profileImage').attr('src');

        $('#user_photo').change(function () {
            const file = this.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#profileImage').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
                $('#upload_user_photo').val('1');
            } else {
                // No file selected (input cleared), revert to the original src
                $('#profileImage').attr('src', originalSrc);
                $('#upload_user_photo').val('');
            }
        });

        const password = document.getElementById('password');
        const passwordRetype = document.getElementById('password_retype');
        const toggleUserPassword = document.getElementById('toggleUserPassword');

        const toggleRetypePassword = document.getElementById('toggleRetypePassword');
        const showUserPassword = document.getElementById('showUserPassword');
        const showRetypePassword = document.getElementById('showRetypePassword');

        toggleUserPassword?.addEventListener('click', () => {
            if (password.type === 'password') {
                password.type = 'text';
                showUserPassword.className = 'fa fa-eye-slash';
            } else {
                password.type = 'password';
                showUserPassword.className = 'fa fa-eye';
            }
        });
        toggleRetypePassword?.addEventListener('click', () => {
            if (passwordRetype.type === 'password') {
                passwordRetype.type = 'text';
                showRetypePassword.className = 'fa fa-eye-slash';
            } else {
                passwordRetype.type = 'password';
                showRetypePassword.className = 'fa fa-eye';
            }
        });

        document.getElementById('password').addEventListener('input', validatePasswords);
        document.getElementById('password_retype').addEventListener('input', validatePasswords);
        document.getElementById('password_retype').addEventListener('blur', function () {
            if (this.value !== document.getElementById('password').value) {
                this.classList.add('is-invalid');
                document.getElementById('password').classList.add('is-invalid');
                document.querySelector('.invalid-feedback').style.display = 'block';
            }
        });

        document.getElementById('my-preferences-form').addEventListener('submit', function (event) {
            if (!validatePasswords()) {
                event.preventDefault();  // Prevent form submission if passwords do not match
            }
            if (password.value.length > 0) {
                document.querySelector('#change_password_request').value = 1;
            }
        });
    });

    function validatePasswords() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_retype');
        const feedback = document.querySelector('.invalid-feedback');
        if (password.value !== confirmPassword.value) {
            feedback.classList.remove('d-none');
            return false;
        } else {
            password.classList.remove('is-invalid');
            confirmPassword.classList.remove('is-invalid');
            feedback.classList.add('d-none');
            return true;
        }
    }
</script>
