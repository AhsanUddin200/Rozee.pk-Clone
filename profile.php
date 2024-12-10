<?php
// profile.php
include 'db.php';
session_start();

// Enable mysqli error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Fetch existing profile
try {
    $stmt = $conn->prepare("SELECT resume, experience, profile_pic FROM rozee_user WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($resume_db, $experience_db, $profile_pic_db);
    $stmt->fetch();
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    die("Error fetching profile: " . $e->getMessage());
}

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $resume = trim($_POST['resume']);
    $experience = trim($_POST['experience']);
    $profile_pic = $profile_pic_db; // Default to existing profile picture

    // Handle profile picture upload
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0){
        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
        $filename = $_FILES['profile_pic']['name'];
        $filetype = $_FILES['profile_pic']['type'];
        $filesize = $_FILES['profile_pic']['size'];

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if(!array_key_exists($ext, $allowed)){
            $errors[] = "Please select a valid file format (JPG, JPEG, PNG).";
        }

        // Verify file size - 2MB maximum
        if($filesize > 2 * 1024 * 1024){
            $errors[] = "File size is larger than the allowed limit (2MB).";
        }

        // Verify MIME type
        if(in_array($filetype, $allowed)){
            // Check whether the uploads/profiles directory exists, if not, create it
            $upload_dir = 'uploads/profiles/';
            if(!is_dir($upload_dir)){
                mkdir($upload_dir, 0755, true);
            }

            // Generate a unique file name
            $new_filename = uniqid() . "." . $ext;
            $profile_pic = $upload_dir . $new_filename;

            // Move the file
            if(!move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic)){
                $errors[] = "Failed to upload profile picture.";
            }
        } else {
            $errors[] = "There was a problem with your file upload. Please try again.";
        }
    }

    // Validation
    if (empty($resume)) { $errors[] = "Resume is required"; }
    if (empty($experience)) { $errors[] = "Experience details are required"; }

    if (empty($errors)) {
        try {
            // Update user profile
            $stmt = $conn->prepare("UPDATE rozee_user SET resume = ?, experience = ?, profile_pic = ? WHERE id = ?");
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sssi", $resume, $experience, $profile_pic, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            $success = "Profile updated successfully";
            $profile_pic_db = $profile_pic; // Update the profile picture variable
        } catch (Exception $e) {
            $errors[] = "Failed to update profile: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Rozee</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Profile Header -->
            <div style="background-color: rgb(39, 52, 103); color: white; padding: 24px;">
                <div class="flex items-center">
                    <!-- Profile Picture -->
                    <div class="relative">
                        <img id="profilePicPreview" 
                             src="<?php echo !empty($user_profile['profile_pic']) ? htmlspecialchars($user_profile['profile_pic']) : 'https://ui-avatars.com/api/?name=' . urlencode($user_profile['full_name'] ?? 'User'); ?>" 
                             alt="Profile Picture" 
                             class="w-24 h-24 rounded-full object-cover border-4 border-white">
                        <label for="profile_pic" class="absolute bottom-0 right-0 bg-blue-500 text-white rounded-full p-2 cursor-pointer">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>
                    
                    <!-- User Info -->
                    <div class="ml-6">
                        <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($user_profile['full_name'] ?? 'User Profile'); ?></h1>
                        <p class="text-blue-200"><?php echo htmlspecialchars($user_profile['email'] ?? 'No email'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Notification Area -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3" role="alert">
                    <?php foreach($errors as $error): ?>
                        <p><i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3" role="alert">
                    <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Form -->
            <form action="" method="POST" enctype="multipart/form-data" class="p-6">
                <!-- Hidden file input -->
                <input type="file" name="profile_pic" id="profile_pic" class="hidden" 
                       accept=".jpg,.jpeg,.png,.webp" 
                       onchange="previewProfilePic(this)">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Contact Information -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">
                            <i class="fas fa-phone mr-2"></i>Phone Number
                        </label>
                        <input type="tel" name="phone" 
                               value="<?php echo htmlspecialchars($user_profile['phone'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               placeholder="Enter your phone number">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">
                            <i class="fas fa-map-marker-alt mr-2"></i>Location
                        </label>
                        <input type="text" name="location" 
                               value="<?php echo htmlspecialchars($user_profile['location'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               placeholder="Your current location">
                    </div>
                </div>

                <!-- Skills -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">
                        <i class="fas fa-laptop-code mr-2"></i>Skills
                    </label>
                    <input type="text" name="skills" 
                           value="<?php echo htmlspecialchars($user_profile['skills'] ?? ''); ?>" 
                           class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Your professional skills (e.g., PHP, JavaScript, Python)">
                </div>

                <!-- Resume -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">
                        <i class="fas fa-file-alt mr-2"></i>Resume
                    </label>
                    <textarea name="resume" rows="4" 
                              class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                              placeholder="Briefly describe your professional background"><?php echo htmlspecialchars($user_profile['resume'] ?? ''); ?></textarea>
                </div>

                <!-- Experience -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">
                        <i class="fas fa-briefcase mr-2"></i>Professional Experience
                    </label>
                    <textarea name="experience" rows="4" 
                              class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                              placeholder="Detail your work experience and achievements"><?php echo htmlspecialchars($user_profile['experience'] ?? ''); ?></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" style="background-color: rgb(39, 52, 103); color: white; padding: 4px;" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>Update Profile
                </button>
            </form>

            <!-- Footer -->
            <div class="bg-gray-200 p-4 text-center">
                <a href="user_home.php" class="text-blue-600 hover:underline">
                    <i class="fas fa-home mr-2"></i>Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        function previewProfilePic(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePicPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Optional: Use SweetAlert for notifications
        <?php if (!empty($success)): ?>
        Swal.fire({
            icon: 'success',
            title: 'Profile Updated',
            text: '<?php echo htmlspecialchars($success); ?>',
            showConfirmButton: false,
            timer: 2000
        });
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        Swal.fire({
            icon: 'error',
            title: 'Update Failed',
            html: '<?php 
                $error_html = implode('<br>', array_map('htmlspecialchars', $errors));
                echo $error_html;
            ?>',
        });
        <?php endif; ?>
    </script>
</body>
</html>
