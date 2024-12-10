<?php
// view_applications.php
include 'db.php';
session_start();

// Enable mysqli error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if user is logged in and has 'company' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'company') {
    header("Location: login.php");
    exit();
}

$company_id = null;

// Fetch company ID based on logged-in user
try {
    $stmt = $conn->prepare("SELECT id FROM rozee_company WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($company_id);
    $stmt->fetch();
    $stmt->close();
    
    if ($company_id === null) {
        die("Company profile not found.");
    }
} catch (mysqli_sql_exception $e) {
    die("Error fetching company details: " . $e->getMessage());
}

// Initialize error and success messages
$errors = [];
$success = "";

// Handle sending message to candidate
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $application_id = intval($_POST['application_id']);
    $predefined_message = "We see your CV, appreciate your time, we will contact you soon for a general interview.";

    // Insert message into rozee_messages table
    try {
        $stmt = $conn->prepare("INSERT INTO rozee_messages (application_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $application_id, $predefined_message);
        $stmt->execute();
        $stmt->close();

        $success = "Message sent to the candidate successfully.";
    } catch (mysqli_sql_exception $e) {
        $errors[] = "Failed to send message: " . $e->getMessage();
    }
}

// Fetch applications including resume and cover letter
try {
    $stmt = $conn->prepare("
        SELECT ra.id, ru.username, rj.job_title, ra.resume, ra.cover_letter
        FROM rozee_applications ra
        JOIN rozee_jobs rj ON ra.job_id = rj.id
        JOIN rozee_user ru ON ra.user_id = ru.id
        WHERE rj.company_id = ?
        ORDER BY ra.application_date DESC
    ");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $applications = [];
    while ($app = $result->fetch_assoc()) {
        $applications[] = $app;
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    die("Error fetching applications: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Applications - Rozee</title>
    <style>
        /* Basic styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            width: 90%;
            max-width: 1000px;
            margin: auto;
            padding: 20px;
            background: #fff;
            margin-top: 20px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ccc;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            padding: 6px 12px;
            background: #5cb85c;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        button:hover {
            background: #4cae4c;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .success {
            color: green;
            margin-bottom: 15px;
        }
        a {
            text-decoration: none;
            color: #5cb85c;
        }
        pre {
            white-space: pre-wrap; /* Wrap text */
            word-wrap: break-word;
        }
    </style>
</head>
<body>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications for Your Jobs - Rozee</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .application-row:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div     class=" p-6">
                <h2 class="text-3xl font-bold text-white text-center flex items-center justify-center">
                    <i class="fas fa-file-alt mr-4"></i>
                    Applications for Your Jobs
                </h2>
            </div>

            <div class="p-6">
                <!-- Error and Success Messages -->
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        <ul class="list-disc list-inside">
                            <?php foreach($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <!-- Applications Table -->
                <?php if(count($applications) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                    <th class="py-3 px-6 text-left">Application ID</th>
                                    <th class="py-3 px-6 text-left">Candidate Name</th>
                                    <th class="py-3 px-6 text-left">Job Title</th>
                                    <th class="py-3 px-6 text-left">Resume</th>
                                    <th class="py-3 px-6 text-left">Cover Letter</th>
                                    <th class="py-3 px-6 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($applications as $app): ?>
                                    <tr class="border-b border-gray-200 application-row hover:bg-gray-50">
                                        <td class="py-3 px-6">
                                            <span class="font-medium text-gray-700"><?php echo htmlspecialchars($app['id']); ?></span>
                                        </td>
                                        <td class="py-3 px-6">
                                            <div class="flex items-center">
                                                <i class="fas fa-user mr-2 text-blue-500"></i>
                                                <?php echo htmlspecialchars($app['username']); ?>
                                            </div>
                                        </td>
                                        <td class="py-3 px-6">
                                            <span class="text-gray-600"><?php echo htmlspecialchars($app['job_title']); ?></span>
                                        </td>
                                        <td class="py-3 px-6">
                                            <?php if(!empty($app['resume'])): ?>
                                                <div class="bg-blue-50 p-2 rounded text-blue-700">
                                                    <i class="fas fa-file-pdf mr-2"></i>
                                                    Resume Available
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400 italic">No Resume</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-6">
                                            <?php if(!empty($app['cover_letter'])): ?>
                                                <div class="bg-green-50 p-2 rounded text-green-700">
                                                    <i class="fas fa-envelope mr-2"></i>
                                                    Cover Letter Available
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400 italic">No Cover Letter</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-6 text-center">
                                            <form action="" method="POST">
                                                <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($app['id']); ?>">
                                                <button 
                                                    type="submit" 
                                                    name="send_message" 
                                                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300 flex items-center justify-center w-full"
                                                >
                                                    <i class="fas fa-paper-plane mr-2"></i>
                                                    Interview Message
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 bg-gray-50 rounded">
                        <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600 text-lg">No applications found for your jobs.</p>
                    </div>
                <?php endif; ?>

                <!-- Back to Home Link -->
                <div class="mt-6 text-center">
                    <a 
                        href="company_home.php" 
                        class="text-blue-500 hover:text-blue-700 font-semibold flex items-center justify-center"
                    >
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
