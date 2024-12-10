<?php
// post_job.php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'company') {
    header("Location: login.php");
    exit();
}

// Fetch company ID
$stmt = $conn->prepare("SELECT id FROM rozee_company WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($company_id);
$stmt->fetch();
$stmt->close();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $job_title = trim($_POST['job_title']);
    $job_description = trim($_POST['job_description']);
    $location = trim($_POST['location']);
    $category = $_POST['category'];

    // Validation
    if (empty($job_title)) { $errors[] = "Job title is required"; }
    if (empty($job_description)) { $errors[] = "Job description is required"; }
    if (empty($category) || !in_array($category, ['IT', 'Marketing', 'Engineering'])) { $errors[] = "Valid category is required"; }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO rozee_jobs (company_id, job_title, job_description, location, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $company_id, $job_title, $job_description, $location, $category);
        if ($stmt->execute()) {
            $success = "Job posted successfully";
        } else {
            $errors[] = "Failed to post job";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Job - Rozee</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .form-input:focus {
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
            <div style="background-color: rgb(39, 52, 103); color: white; padding: 24px;"  class="p-6">
                <h2 class="text-3xl font-bold text-white text-center">Post a New Job</h2>
            </div>

            <div class="p-6">
                <?php
                if (!empty($errors)) {
                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">';
                    echo '<ul class="list-disc list-inside">';
                    foreach($errors as $error){
                        echo '<li>'.htmlspecialchars($error).'</li>';
                    }
                    echo '</ul></div>';
                }
                if (isset($success)) {
                    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">'.htmlspecialchars($success).'</div>';
                }
                ?>

                <form name="jobForm" action="" method="POST" id="jobForm" class="space-y-4">
                    <div>
                        <label for="job_title" class="block text-gray-700 font-bold mb-2">Job Title</label>
                        <div class="relative">
                            <i class="fas fa-briefcase absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input 
                                type="text" 
                                name="job_title" 
                                id="job_title"
                                placeholder="Enter job title" 
                                class="form-input w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="<?php if(isset($job_title)) echo htmlspecialchars($job_title); ?>"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="job_description" class="block text-gray-700 font-bold mb-2">Job Description</label>
                        <textarea 
                            name="job_description" 
                            id="job_description"
                            placeholder="Detailed job description" 
                            rows="5"
                            class="form-input w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        ><?php if(isset($job_description)) echo htmlspecialchars($job_description); ?></textarea>
                    </div>

                    <div>
                        <label for="location" class="block text-gray-700 font-bold mb-2">Location</label>
                        <div class="relative">
                            <i class="fas fa-map-marker-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input 
                                type="text" 
                                name="location" 
                                id="location"
                                placeholder="Job location" 
                                class="form-input w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="<?php if(isset($location)) echo htmlspecialchars($location); ?>"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="category" class="block text-gray-700 font-bold mb-2">Job Category</label>
                        <select 
                            name="category" 
                            id="category"
                            class="form-input w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select Category</option>
                            <option value="IT" <?php if(isset($category) && $category == 'IT') echo 'selected'; ?>>IT</option>
                            <option value="Marketing" <?php if(isset($category) && $category == 'Marketing') echo 'selected'; ?>>Marketing</option>
                            <option value="Engineering" <?php if(isset($category) && $category == 'Engineering') echo 'selected'; ?>>Engineering</option>
                        </select>
                    </div>

                    <div class="pt-4">
                        <button 
                            type="submit" 
                            class="w-full bg-blue-900 text-white font-bold py-3 rounded-lg hover:opacity-90 transition duration-300 transform hover:scale-105"

                        >
                            Post Job
                        </button>
                    </div>
                </form>

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

    <script>
        document.getElementById('jobForm').addEventListener('submit', function(event) {
            const jobTitle = document.getElementById('job_title').value;
            const jobDescription = document.getElementById('job_description').value;
            const category = document.getElementById('category').value;
            
            let errors = [];
            
            if (jobTitle.trim() === '') {
                errors.push('Job Title is required');
            }
            
            if (jobDescription.trim() === '') {
                errors.push('Job Description is required');
            }
            
            if (category === '') {
                errors.push('Job Category is required');
            }
            
            if (errors.length > 0) {
                event.preventDefault();
                alert(errors.join('\n'));
            }
        });
    </script>
</body>
</html>