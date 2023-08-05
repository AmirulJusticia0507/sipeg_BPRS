<?php
// Informasi koneksi database
include 'koneksi.php';

// Fungsi untuk melakukan login
function login($username, $password) {
    global $koneksiku;
    // Lakukan pemeriksaan login berdasarkan tabel "Users" dengan username dan password yang sesuai
    $query = "SELECT * FROM Users WHERE Username = '$username'";
    $result = mysqli_query($koneksiku, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $hashedPassword = $user['PasswordHash'];
        $passwordSalt = $user['PasswordSalt'];

        // Verifikasi password menggunakan password salt yang sama seperti saat registrasi
        $passwordToVerify = hash('sha256', $password . $passwordSalt);

        if ($hashedPassword === $passwordToVerify) {
            // Login berhasil, set session dan last login
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['user_role'] = $user['Role'];
            $userId = $user['ID'];
            $currentTime = date('Y-m-d H:i:s');
            $query = "UPDATE Users SET LastLogin = '$currentTime' WHERE ID = $userId";
            mysqli_query($koneksiku, $query);
            return true;
        }
    }

    return false;
}

// Fungsi untuk melakukan registrasi pengguna baru
function register($newUsername, $newPassword, $confirmPassword) {
    global $koneksiku;
    // Cek apakah username telah digunakan sebelumnya
    $query = "SELECT * FROM Users WHERE Username = '$newUsername'";
    $result = mysqli_query($koneksiku, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        return "Username already exists"; // Username sudah digunakan, registrasi gagal
    }

    // Validate password complexity
    if (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $newPassword)) {
        return "Password must contain at least 8 characters including one uppercase letter, one lowercase letter, one number, and one special character";
    }

    // Check if passwords match
    if ($newPassword !== $confirmPassword) {
        return "Passwords do not match";
    }

    // Generate password salt
    $passwordSalt = bin2hex(random_bytes(16));

    // Hash password with password salt for added security
    $hashedPassword = hash('sha256', $newPassword . $passwordSalt);

    // Save new user data to the "Users" table
    $query = "INSERT INTO Users (Username, PasswordHash, PasswordSalt, Role) VALUES ('$newUsername', '$hashedPassword', '$passwordSalt', 'Pegawai')";
    mysqli_query($koneksiku, $query);

    return "success";
}

// Fungsi untuk reset password
function resetPassword($forgotUsername) {
    global $koneksiku;
    // Check if the username exists in the "Users" table
    $query = "SELECT * FROM Users WHERE Username = '$forgotUsername'";
    $result = mysqli_query($koneksiku, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Generate a new password randomly
        $newPassword = bin2hex(random_bytes(8));
        $passwordSalt = bin2hex(random_bytes(16));
        $hashedPassword = hash('sha256', $newPassword . $passwordSalt);

        // Save the new password to the "Users" table
        $userId = $user['ID'];
        $query = "UPDATE Users SET PasswordHash = '$hashedPassword', PasswordSalt = '$passwordSalt' WHERE ID = $userId";
        mysqli_query($koneksiku, $query);

        // Send the new password to the user's email (email implementation not included in this example)
        // Send an email using PHPMailer, mail(), or other email services.
        // Example: mail($forgotUsername, 'Reset Password', 'Your new password: '.$newPassword);

        return "success";
    }

    return "Username not found";
}

// Fungsi untuk memverifikasi apakah pengguna telah login
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Fungsi untuk mendapatkan data pengguna berdasarkan ID
function getUserData($userId) {
    global $koneksiku;
    $query = "SELECT * FROM Users WHERE ID = $userId";
    $result = mysqli_query($koneksiku, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }

    return null;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Bootstrap CSS CDN -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Custom CSS styling here */
    </style>
</head>
<body>
    <div class="container mt-5">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="login-tab" data-toggle="tab" href="#login" role="tab" aria-controls="login" aria-selected="true">Login</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="register-tab" data-toggle="tab" href="#register" role="tab" aria-controls="register" aria-selected="false">Register</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="forgot-password-tab" data-toggle="tab" href="#forgot-password" role="tab" aria-controls="forgot-password" aria-selected="false">Forgot Password</a>
            </li>
        </ul>
        <div class="tab-content mt-3" id="myTabContent">
            <!-- Login Tab -->
            <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                <h3>Login</h3>
                <form id="loginForm" method="post">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" class="form-control" id="username" name="username" required style="width:50%;">
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required style="width:50%;">
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
            </div>
            <!-- Register Tab -->
            <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                <h3>Register</h3>
                <form id="registerForm" method="post">
                    <div class="form-group">
                        <label for="newUsername">Username:</label>
                        <input type="text" class="form-control" id="newUsername" name="newUsername" required style="width:50%;">
                    </div>
                    <div class="form-group">
                        <label for="newPassword">Password:</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" name="newPassword" required style="width:50%;">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fa fa-eye-slash" id="togglePassword"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password:</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required style="width:50%;">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fa fa-eye-slash" id="toggleConfirmPassword"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
            </div>
            <!-- Forgot Password Tab -->
            <div class="tab-pane fade" id="forgot-password" role="tabpanel" aria-labelledby="forgot-password-tab">
                <h3>Forgot Password</h3>
                <form id="forgotPasswordForm" method="post">
                    <div class="form-group">
                        <label for="forgotUsername">Username:</label>
                        <input type="text" class="form-control" id="forgotUsername" name="forgotUsername" required style="width:50%;">
                    </div>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <script>
        // JavaScript Ajax for Login, Register, and Forgot Password
        $(document).ready(function() {
            // Handle login form submission
            $('#loginForm').submit(function(event) {
                event.preventDefault();
                var username = $('#username').val();
                var password = $('#password').val();
                // Your Ajax code here to submit login data to server and process response
                // Example: $.post('login.php', { username: username, password: password }, function(response) {});
            });

            // Handle register form submission
    $('#registerForm').submit(function(event) {
        event.preventDefault();
        var newUsername = $('#newUsername').val();
        var newPassword = $('#newPassword').val();
        var confirmPassword = $('#confirmPassword').val();

        // Your Ajax code here to submit register data to server and process response
        $.post('register.php', {
            newUsername: newUsername,
            newPassword: newPassword,
            confirmPassword: confirmPassword
        }, function(response) {
            if (response === 'success') {
                // Success message in green
                showMessage('Registration successful!', 'success');
            } else {
                // Error message in red
                showMessage(response, 'error');
            }
        });
    });

    $(document).ready(function() {
        // Handle toggle password visibility
        $('#togglePassword').click(function() {
            var passwordInput = $('#newPassword');
            var type = passwordInput.attr('type');
            passwordInput.attr('type', type === 'password' ? 'text' : 'password');
            $(this).toggleClass('fa-eye-slash fa-eye');
        });

        // Handle toggle confirm password visibility
        $('#toggleConfirmPassword').click(function() {
            var confirmPasswordInput = $('#confirmPassword');
            var type = confirmPasswordInput.attr('type');
            confirmPasswordInput.attr('type', type === 'password' ? 'text' : 'password');
            $(this).toggleClass('fa-eye-slash fa-eye');
        });
    });

            // Handle forgot password form submission
            $('#forgotPasswordForm').submit(function(event) {
                event.preventDefault();
                var forgotUsername = $('#forgotUsername').val();
                // Your Ajax code here to submit forgot password data to server and process response
                // Example: $.post('forgot_password.php', { forgotUsername: forgotUsername }, function(response) {});
            });
        });

         // Function to show alert message
    function showMessage(message, type) {
        var alertClass = 'alert-danger';
        if (type === 'success') {
            alertClass = 'alert-success';
        }

        var alertDiv = $('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>');

        $('#messages').html(alertDiv);
    }
    </script>
</body>
</html>
