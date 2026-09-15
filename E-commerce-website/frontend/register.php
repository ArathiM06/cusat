<?php
include 'header.php';

// redirect home if already logged in
if ($is_logged_in == true) {
    header("Location: index.php");
    exit();
}

$error_msg = null;
$success_msg = null;

// look for form post submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // payload block for backend account initialization
    $reg_data = array(
        'name' => $name,
        'email' => $email,
        'password' => $password
    );

    $register_url = "http://127.0.0.1:8000/api/auth/register";

    try {
        $options = array(
            'http' => array(
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($reg_data),
                'timeout' => 3.0,
                'ignore_errors' => true
            )
        );

        $context  = stream_context_create($options);
        $response = @file_get_contents($register_url, false, $context);

        if (isset($http_response_header)) {
            preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
            $status_code = intval($match[1]);
        } else {
            $status_code = 500;
        }

        if ($response === FALSE || $status_code != 201) {
            $err_json = json_decode($response, true);
            $error_msg = isset($err_json['detail']) ? $err_json['detail'] : "Registration failed. Try again.";
        } else {
            $success_msg = "Account created successfully! You can log in now.";
        }
    } catch (Exception $e) {
        $error_msg = "Connection error. Is FastAPI running?";
    }
}
?>

<!-- main registration form layout wrapper -->
<main class="container page-main">

    <div class="auth-card-container">
        <h2 class="auth-card-title">Create Account</h2>
        <p class="auth-card-subtitle">Join the CUSAT store catalog system</p>
        <hr class="title-underline-hr">
        <br>

        <?php if ($error_msg != null) { ?>
            <!-- registration bad outcome banner -->
            <div class="auth-error-alert">
                <b>Registration Issue:</b> <?php echo htmlspecialchars($error_msg); ?>
            </div>
            <br>
        <?php } ?>

        <?php if ($success_msg != null) { ?>
            <!-- registration good outcome banner -->
            <div class="auth-success-alert">
                <b>Success:</b> <?php echo htmlspecialchars($success_msg); ?>
                <br><br>
                <a href="login.php" class="auth-success-link-btn">Go to Login Page →</a>
            </div>
            <br>
        <?php } ?>

       
        <?php if ($success_msg == null) { ?>
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. Rahul Kumar" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" placeholder="username@cusat.ac.in" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Minimum 6 characters" minlength="6" required>
                </div>
                <br>

                <input type="submit" class="btn btn-teal full-width-input-btn" value="Register Account">
            </form>
        <?php } ?>

        <br>
        <p class="auth-footer-redirect-text">
            Already have an account? <a href="login.php" class="auth-redirect-link">Sign in here</a>
        </p>
    </div>

</main>

<br><br>
<hr>
<footer class="global-page-footer">
    <center>
        <p class="footer-brand-text"><b>CUSAT Store Auth Gateway</b></p>
        <p class="footer-copyright-text">&copy; 2026 Cochin University of Science and Technology. All rights reserved.</p>
    </center>
</footer>

<script src="app.js"></script>
</body>
</html>
\\NA