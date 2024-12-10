<?php
// login.php
include 'db.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validation
    if (empty($email)) { $errors[] = "Email is required"; }
    if (empty($password)) { $errors[] = "Password is required"; }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM rozee_user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows == 1){
            $stmt->bind_result($id, $username, $hashed_password, $role);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                // Set session variables
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                // Redirect based on role
                if ($role == 'company') {
                    header("Location: company_home.php");
                } else {
                    header("Location: user_home.php");
                }
                exit();
            } else {
                $errors[] = "Incorrect password";
            }
        } else {
            $errors[] = "No account found with that email";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rozee.pk</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 400px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .logo {
            text-align: center;
            margin-bottom: 25px;
        }
        .logo img {
            width: 150px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: #273467;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 15px;
        }
        .login-btn:hover {
            background-color: #0052a3;
        }
        .forgot-password {
            text-align: right;
            margin-bottom: 15px;
        }
        .forgot-password a {
            color: #0066cc;
            text-decoration: none;
            font-size: 14px;
        }
        .signup-link {
            text-align: center;
            margin-top: 15px;
        }
        .signup-link a {
            color: #0066cc;
            text-decoration: none;
        }
        .error {
            background-color: #ffeeee;
            color: #cc0000;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .or-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
        }
        .or-divider::before,
        .or-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        .or-divider span {
            padding: 0 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="data:image/png;base64,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" alt="Rozee.pk Logo">
        </div>
        
        <form name="loginForm" action="" method="POST" onsubmit="return validateForm();">
            <?php
            if (!empty($errors)) {
                echo '<div class="error"><ul>';
                foreach($errors as $error){
                    echo '<li>'.htmlspecialchars($error).'</li>';
                }
                echo '</ul></div>';
            }
            ?>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" value="<?php if(isset($email)) echo htmlspecialchars($email); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter your password">
            </div>

            <div class="forgot-password">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>

            <button type="submit" class="login-btn">Login</button>

            <div class="or-divider">
                <span>OR</span>
            </div>

            <div class="signup-link">
                Don't have an account? <a href="signup.php">Sign up here</a>
            </div>
        </form>
    </div>

    <script>
        function validateForm() {
            let email = document.forms["loginForm"]["email"].value;
            let password = document.forms["loginForm"]["password"].value;
            
            if (email == "" || password == "") {
                alert("Please fill out all fields");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>