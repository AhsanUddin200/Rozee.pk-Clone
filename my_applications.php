<?php
// my_applications.php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Fetch user applications
$stmt = $conn->prepare("SELECT rozee_jobs.job_title, rozee_company.company_name, rozee_applications.cover_letter, rozee_applications.applied_at 
                        FROM rozee_applications 
                        JOIN rozee_jobs ON rozee_applications.job_id = rozee_jobs.id 
                        JOIN rozee_company ON rozee_jobs.company_id = rozee_company.id 
                        WHERE rozee_applications.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$applications = [];
while($app = $result->fetch_assoc()){
    $applications[] = $app;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Rozee</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table-hover tr:hover {
            background-color: #f1f3f5;
            transition: background-color 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-b">
                <h2 class="text-2xl font-bold text-gray-800">My Applications</h2>
                <a href="logout.php" class="text-red-500 hover:text-red-700 font-semibold">
                    Logout
                </a>
            </div>

            <?php if(count($applications) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full table-hover">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">Job Title</th>
                                <th class="py-3 px-6 text-left">Company</th>
                                <th class="py-3 px-6 text-left">Cover Letter</th>
                                <th class="py-3 px-6 text-left">Applied At</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            <?php foreach($applications as $app): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-3 px-6">
                                        <div class="flex items-center">
                                            <span class="font-medium"><?php echo htmlspecialchars($app['job_title']); ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-6">
                                        <div class="flex items-center">
                                            <span><?php echo htmlspecialchars($app['company_name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-6">
                                        <div class="max-w-xs truncate">
                                            <?php echo nl2br(htmlspecialchars($app['cover_letter'])); ?>
                                        </div>
                                    </td>
                                    <td class="py-3 px-6">
                                        <span class="text-gray-500"><?php echo htmlspecialchars($app['applied_at']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <p class="text-gray-600 text-lg">You have not applied for any jobs yet.</p>
                </div>
            <?php endif; ?>

            <div class="bg-gray-50 px-6 py-4 text-center border-t">
                <a href="user_home.php" class="text-blue-500 hover:text-blue-700 font-semibold">
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>