<?php
// apply_job.php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

// Fetch job details
$stmt = $conn->prepare("SELECT rozee_jobs.*, rozee_company.company_name FROM rozee_jobs JOIN rozee_company ON rozee_jobs.company_id = rozee_company.id WHERE rozee_jobs.id = ?");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows != 1){
    echo "Job not found.";
    exit();
}
$job = $result->fetch_assoc();

// Check if user has already applied
$stmt = $conn->prepare("SELECT id FROM rozee_applications WHERE job_id = ? AND user_id = ?");
$stmt->bind_param("ii", $job_id, $_SESSION['user_id']);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0){
    $already_applied = true;
} else {
    $already_applied = false;
}
$stmt->close();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$already_applied) {
    $cover_letter = trim($_POST['cover_letter']);

    // File upload handling
    if(isset($_FILES['cv']) && $_FILES['cv']['error'] == 0){
        $allowed = ['pdf' => 'application/pdf', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $filename = $_FILES['cv']['name'];
        $filetype = $_FILES['cv']['type'];
        $filesize = $_FILES['cv']['size'];

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)){
            $errors[] = "Please select a valid file format (PDF or DOCX).";
        }

        // Verify file size - 5MB maximum
        if($filesize > 5 * 1024 * 1024){
            $errors[] = "File size is larger than the allowed limit (5MB).";
        }

        // Verify MIME type
        if(in_array($filetype, $allowed)){
            // Check whether the uploads directory exists, if not, create it
            $upload_dir = 'uploads/';
            if(!is_dir($upload_dir)){
                mkdir($upload_dir, 0755, true);
            }

            // Generate a unique file name
            $new_filename = uniqid() . "." . $ext;
            $cv_path = $upload_dir . $new_filename;

            // Move the file
            if(!move_uploaded_file($_FILES['cv']['tmp_name'], $cv_path)){
                $errors[] = "Failed to upload CV.";
            }
        } else {
            $errors[] = "There was a problem with your file upload. Please try again.";
        }
    } else {
        $errors[] = "CV is required.";
    }

    // Validation
    if (empty($cover_letter)) { $errors[] = "Cover letter is required"; }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO rozee_applications (job_id, user_id, cover_letter, cv_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $job_id, $_SESSION['user_id'], $cover_letter, $cv_path);
        if ($stmt->execute()) {
            $success = "Applied successfully";
            $already_applied = true;
        } else {
            $errors[] = "Failed to apply for the job. You might have already applied.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for <?php echo htmlspecialchars($job['job_title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Job Header -->
            <div style="background-color: rgb(39, 52, 103); color: white; padding: 24px;">
                <div class="flex items-center">
                    <?php if(!empty($job['company_logo'])): ?>
                        <img src="<?php echo htmlspecialchars($job['company_logo']); ?>" alt="Company Logo" class="w-16 h-16 rounded-full mr-4">
                    <?php endif; ?>
                    <div>
                        <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($job['job_title']); ?></h1>
                        <p class="text-blue-200"><?php echo htmlspecialchars($job['company_name']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Notification Area -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <?php foreach($errors as $error): ?>
                        <p><i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Job Details -->
            <div class="p-6 bg-gray-50">
                <h2 class="text-xl font-semibold mb-4">Job Details</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?>
                    </div>
                   
                    
                   
                </div>
            </div>

            <!-- Application Form -->
            <?php if (!$already_applied): ?>
                <form method="POST" action="" enctype="multipart/form-data" class="p-6">
                    <div class="mb-4">
                        <label for="cv" class="block text-gray-700 font-bold mb-2">
                            <i class="fas fa-file-upload mr-2"></i>Upload CV (PDF/DOCX max 5MB)
                        </label>
                        <input type="file" name="cv" id="cv" accept=".pdf,.docx,.doc" 
                               class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label for="cover_letter" class="block text-gray-700 font-bold mb-2">
                            <i class="fas fa-pen mr-2"></i>Cover Letter
                        </label>
                        <textarea name="cover_letter" id="cover_letter" rows="6" 
                                  class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                  placeholder="Write your cover letter here (minimum 50 characters)"
                                  required><?php echo htmlspecialchars($_POST['cover_letter'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" style="background-color: rgb(39, 52, 103); color: white; padding: 4px;" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition duration-300">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Application
                    </button>
                </form>
            <?php else: ?>
                <div class="p-6 text-center bg-green-50">
                    <i class="fas fa-check-circle text-4xl text-green-600 mb-4"></i>
                    <h2 class="text-2xl font-bold text-green-800 mb-2">Application Submitted</h2>
                    <p class="text-green-700">You have already applied for this job.</p>
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="bg-gray-200 p-4 text-center">
                <a href="user_home.php" class="text-blue-600 hover:underline">
                    <i class="fas fa-home mr-2"></i>Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>