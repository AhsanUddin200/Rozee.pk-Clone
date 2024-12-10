<?php
// user_home.php
include 'db.php';
session_start();

// Enable mysqli error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if user is logged in and has 'user' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details: username and profile picture
try {
    $stmt = $conn->prepare("SELECT username, profile_pic FROM rozee_user WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username, $profile_pic);
    $stmt->fetch();
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    die("Error fetching user details: " . $e->getMessage());
}

// Fetch messages related to user's applications
try {
    $stmt = $conn->prepare("
        SELECT rm.message, rm.sent_at, rc.company_name 
        FROM rozee_messages rm
        JOIN rozee_applications ra ON rm.application_id = ra.id
        JOIN rozee_jobs rj ON ra.job_id = rj.id
        JOIN rozee_company rc ON rj.company_id = rc.id
        WHERE ra.user_id = ?
        ORDER BY rm.sent_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($msg = $result->fetch_assoc()) {
        $messages[] = $msg;
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    die("Error fetching messages: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Home - Rozee.pk</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
        }
        .container {
            width: 100%;
            max-width: 1000px;
            margin: 30px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            position: relative;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 15px;
        }
        .welcome-text h2 {
            color: #0066cc;
            font-size: 24px;
        }
        .profile-info {
            text-align: center;
        }
        .profile-info img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f0f0f0;
        }
        .profile-info p {
            margin-top: 10px;
            font-weight: 600;
            color: #666;
        }
        .navigation {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        .nav-button {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background-color: #273467;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            font-size: 14px;
        }
        .nav-button:hover {
            background-color: #0052a3;
        }
        .nav-button i {
            margin-right: 8px;
        }
        .messages-section {
            background-color: #f9f9f9;
            border-radius: 4px;
            padding: 20px;
        }
        .message {
            background-color: white;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .message-company {
            font-weight: 600;
            color: #0066cc;
        }
        .message-time {
            font-size: 12px;
            color: #666;
        }
        .message-body {
            color: #333;
        }
        .no-messages {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        .logout-link {
            text-align: center;
            margin-top: 20px;
        }
        .logout-link a {
            color: #cc0000;
            text-decoration: none;
            font-weight: 600;
        }
        .logout-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .container {
                margin: 15px;
                padding: 20px;
            }
            .header {
                flex-direction: column;
                text-align: center;
            }
            .navigation {
                flex-direction: column;
                align-items: center;
            }
            .nav-button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
    <div class="logo" style="display: flex; justify-content: center; align-items: center; background-color: #FFFFFF; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); padding: 5px 8px; margin: 2px auto; max-width: 100px; border-radius: 8px;">
            <img style="width: 80px; height: 80px; object-fit: contain;" src="data:image/png;base64,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" alt="Rozee.pk Logo">
        </div>
        <div class="header">
            <div class="welcome-text">
                <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            </div>
            <div class="profile-info">
                <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
                <p><?php echo htmlspecialchars($username); ?></p>
            </div>
        </div>
        
        <div class="navigation">
            <a href="homepage.php" class="nav-button">
                <i class="fas fa-search"></i>Browse Jobs
            </a>
            <a href="profile.php" class="nav-button">
                <i class="fas fa-user"></i>My Profile
            </a>
            <a href="my_applications.php" class="nav-button">
                <i class="fas fa-file-alt"></i>My Applications
            </a>
        </div>
        
        <div class="messages-section">
            <h3>Messages from Companies</h3>
            <?php if(count($messages) > 0): ?>
                <?php foreach($messages as $msg): ?>
                    <div class="message">
                        <div class="message-header">
                            <div class="message-company"><?php echo htmlspecialchars($msg['company_name']); ?></div>
                            <div class="message-time"><?php echo htmlspecialchars($msg['sent_at']); ?></div>
                        </div>
                        <div class="message-body"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-messages">No messages yet. Check back later!</p>
            <?php endif; ?>
        </div>
        
        <div class="logout-link">
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</body>
</html>
