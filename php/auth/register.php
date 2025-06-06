<?php
include 'db.php';
// Start session
session_start();

// Load language support
require_once __DIR__ . '/../lang/Language.php';
$lang = Language::getInstance();

// Initialize variables
$username = $email = $password = $confirm_password = "";
$username_err = $email_err = $password_err = $confirm_password_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 0x13E8FE
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = $lang->get('signup_username_required');
    } else {
        // Prepare a select statement
        $sql = "SELECT user_id FROM users WHERE username = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_username);

            // Set parameters
            $param_username = trim($_POST["username"]);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $username_err = $lang->get('signup_username_taken');
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo $lang->get('error_general');
            }

            // Close statement
            $stmt->close();
        }
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = $lang->get('signup_email_required');
    } else {
        // Check if email is valid
        if (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
            $email_err = $lang->get('signup_email_invalid');
        } else {
            // Check if email already exists
            $sql = "SELECT user_id FROM users WHERE email = ?";

            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("s", $param_email);

                // Set parameters
                $param_email = trim($_POST["email"]);

                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    // Store result
                    $stmt->store_result();

                    if ($stmt->num_rows == 1) {
                        $email_err = $lang->get('signup_email_taken');
                    } else {
                        $email = trim($_POST["email"]);
                    }
                } else {
                    echo $lang->get('error_general');
                }

                // Close statement
                $stmt->close();
            }
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = $lang->get('signup_password_required');
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = $lang->get('signup_password_length');
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = $lang->get('signup_confirm_password_required');
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = $lang->get('signup_password_mismatch');
        }
    }

    // Check input errors before inserting in database
    if (empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sss", $param_username, $param_email, $param_password);

            // Set parameters
            $param_username = $username;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to login page
                header("location: login.php");
            } else {
                echo $lang->get('error_general');
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang->get('signup_title'); ?> - SLAC</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/responsive.css">
    <style>
        .auth-container {
            max-width: 450px;
            margin: 0 auto;
            padding: 2rem;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">SLAC</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../../index.php"><?php echo $lang->get('nav_home'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../index.php#about"><?php echo $lang->get('nav_about'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../index.php#facilities"><?php echo $lang->get('nav_facilities'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../contact.php"><?php echo $lang->get('nav_contact'); ?></a>
                    </li>
                    <?php include __DIR__ . '/../components/language_selector.php'; ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light btn-sm ml-2" href="login.php"><?php echo $lang->get('nav_login'); ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Registration Section -->
    <section class="py-5">
        <div class="container">
            <div class="auth-container">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="text-center mb-4"><?php echo $lang->get('signup_heading'); ?></h2>
                        <p class="text-center"><?php echo $lang->get('signup_instruction'); ?></p>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group">
                                <label><?php echo $lang->get('login_username'); ?></label>
                                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                                <span class="invalid-feedback"><?php echo $username_err; ?></span>
                            </div>
                            <div class="form-group">
                                <label><?php echo $lang->get('signup_email'); ?></label>
                                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                                <span class="invalid-feedback"><?php echo $email_err; ?></span>
                            </div>
                            <div class="form-group">
                                <label><?php echo $lang->get('login_password'); ?></label>
                                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                                <span class="invalid-feedback"><?php echo $password_err; ?></span>
                            </div>
                            <div class="form-group">
                                <label><?php echo $lang->get('signup_confirm_password'); ?></label>
                                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block"><?php echo $lang->get('signup_button'); ?></button>
                            </div>
                            <p class="text-center"><?php echo $lang->get('signup_has_account'); ?> <a href="login.php"><?php echo $lang->get('signup_login_link'); ?></a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo $lang->get('welcome_subtitle'); ?></h5>
                    <p><?php echo $lang->get('footer_address'); ?></p>
                </div>
                <div class="col-md-3">
                    <h5><?php echo $lang->get('footer_quick_links'); ?></h5>
                    <ul class="list-unstyled">
                        <li><a href="../../index.php" class="text-white"><?php echo $lang->get('nav_home'); ?></a></li>
                        <li><a href="../../index.php#about" class="text-white"><?php echo $lang->get('nav_about'); ?></a></li>
                        <li><a href="../../index.php#floor-plans" class="text-white"><?php echo $lang->get('nav_floor_plans'); ?></a></li>
                        <li><a href="../../contact.php" class="text-white"><?php echo $lang->get('nav_contact'); ?></a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5><?php echo $lang->get('footer_connect'); ?></h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Facebook</a></li>
                        <li><a href="#" class="text-white">Twitter</a></li>
                        <li><a href="#" class="text-white">Instagram</a></li>
                        <li><a href="#" class="text-white">WeChat</a></li>
                    </ul>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <p class="mb-0"><?php echo $lang->get('footer_copyright'); ?></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script src="../../js/main.js"></script>
</body>

</html>