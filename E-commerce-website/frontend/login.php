<?php
include 'header.php';

// redirect home if the user is already logged in
if ($is_logged_in == true) {
    header("Location: index.php");
    exit();
}

$error_msg = null;

// check if user submitted the login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // payload for authentication api
    $login_data = array(
        'username' => $email, // fastapi OAuth2 uses username field for email
        'password' => $password
    );

    $login_url = "http://127.0.0.1:8000/api/auth/login";
    
    try {
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($login_data),
                'timeout' => 3.0,
                'ignore_errors' => true
            )
        );
        
        $context  = stream_context_create($options);
        $response = @file_get_contents($login_url, false, $context);

        if (isset($http_response_header)) {
            preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
            $status_code = intval($match[1]);
        } else {
            $status_code = 500;
        }

        if ($response === FALSE || $status_code != 200) {
            $err_json = json_decode($response, true);
            $error_msg = isset($err_json['detail']) ? $err_json['detail'] : "Invalid email or password.";
        } else {
            $res_data = json_decode($response, true);
            
            // save token data inside session storage
            $_SESSION['token'] = $res_data['access_token'];
            $_SESSION['user'] = $res_data['user'];
            
            // store admin parameters if account has clearance privileges
            if (isset($res_data['user']['is_admin']) && $res_data['user']['is_admin'] == true) {
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_token'] = $res_data['access_token'];
            }

            // kick user back to store catalog homepage
            header("Location: index.php");
            exit();
        }
    } catch (Exception $e) {
        $error_msg = "Connection error. Is FastAPI running?";
    }
}
?>

<!-- main login box section -->
<main class="container page-main">
    
    <div class="auth-card-container">
        <h2 class="auth-card-title">Welcome Back</h2>
        <p class="auth-card-subtitle">Sign in to your account to place orders</p>
        <hr class="title-underline-hr">
        <br>

        <?php if ($error_msg != null) { ?>
            <!-- standard login error block alert -->
            <div class="auth-error-alert">
                <b>Login Failed:</b> <?php echo htmlspecialchars($error_msg); ?>
            </div>
            <br>
        <?php } ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="Enter your CUSAT email" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <br>

            <input type="submit" class="btn btn-teal full-width-input-btn" value="Sign In">
        </form>

        <br>
        <p class="auth-footer-redirect-text">
            Don't have an account? <a href="register.php" class="auth-redirect-link">Register here</a>
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