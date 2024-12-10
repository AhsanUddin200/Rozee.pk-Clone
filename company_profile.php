<?php
// company_profile.php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'company') {
    header("Location: login.php");
    exit();
}

// Fetch company details
$stmt = $conn->prepare("SELECT company_name, company_description, company_logo FROM rozee_company WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($company_name, $company_description, $company_logo_db);
$stmt->fetch();
$stmt->close();

$errors = [];
$success = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_company_name = trim($_POST['company_name']);
    $new_company_description = trim($_POST['company_description']);

    // Handle company logo upload
    if(isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] == 0){
        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
        $filename = $_FILES['company_logo']['name'];
        $filetype = $_FILES['company_logo']['type'];
        $filesize = $_FILES['company_logo']['size'];

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
            // Check whether the uploads/logos directory exists, if not, create it
            $upload_dir = 'uploads/logos/';
            if(!is_dir($upload_dir)){
                mkdir($upload_dir, 0755, true);
            }

            // Generate a unique file name
            $new_filename = uniqid() . "." . $ext;
            $company_logo = $upload_dir . $new_filename;

            // Move the file
            if(!move_uploaded_file($_FILES['company_logo']['tmp_name'], $company_logo)){
                $errors[] = "Failed to upload company logo.";
            }
        } else {
            $errors[] = "There was a problem with your file upload. Please try again.";
        }
    } else {
        $company_logo = $company_logo_db; // Keep existing logo if no new upload
    }

    // Validation
    if (empty($new_company_name)) { $errors[] = "Company name is required"; }

    if (empty($errors)) {
        // Update company profile
        if(isset($company_logo)){
            $stmt = $conn->prepare("UPDATE rozee_company SET company_name = ?, company_description = ?, company_logo = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $new_company_name, $new_company_description, $company_logo, $_SESSION['user_id']);
        } else {
            $stmt = $conn->prepare("UPDATE rozee_company SET company_name = ?, company_description = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $new_company_name, $new_company_description, $_SESSION['user_id']);
        }

        if ($stmt->execute()) {
            $success = "Company profile updated successfully";
            $company_name = $new_company_name;
            $company_description = $new_company_description;
            $company_logo_db = $company_logo;
        } else {
            $errors[] = "Failed to update company profile";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Profile - Rozee</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script>
        function validateForm() {
            let company_name = document.forms["companyForm"]["company_name"].value;
            if (company_name == "") {
                alert("Company name must be filled out");
                return false;
            }
            return true;
        }

        // Preview Company Logo
        function previewCompanyLogo(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('companyLogoPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    
    <div class="container mx-auto px-4 py-8">

        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            
            <div  style="background-color: rgb(39, 52, 103); color: white; padding: 24px;"  class=" p-6">
                <h2 class="text-3xl font-bold text-white text-center flex items-center justify-center">
                    <i class="fas fa-building mr-4"></i>
                    My Company Profile
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

                <form name="companyForm" action="" method="POST" enctype="multipart/form-data" onsubmit="return validateForm();">
                    <div class="flex justify-center mb-4">
                        <img id="companyLogoPreview" src="<?php echo htmlspecialchars($company_logo_db); ?>" alt="Company Logo" class="w-24 h-24 rounded-full object-cover">
                    </div>
                    <div class="mb-4">
                        <label for="company_logo" class="block text-gray-700 font-bold mb-2">
                            Company Logo
                        </label>
                        <input type="file" name="company_logo" id="company_logo" accept=".jpg,.jpeg,.png" onchange="previewCompanyLogo(this)" class="bg-white border border-gray-300 rounded py-2 px-3 w-full focus:outline-none focus:ring focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="company_name" class="block text-gray-700 font-bold mb-2">
                            Company Name
                        </label>
                        <input type="text" name="company_name" id="company_name" placeholder="Company Name" value="<?php echo htmlspecialchars($company_name); ?>" class="bg-white border border-gray-300 rounded py-2 px-3 w-full focus:outline-none focus:ring focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="company_description" class="block text-gray-700 font-bold mb-2">
                            Company Description
                        </label>
                        <textarea name="company_description" id="company_description" placeholder="Company Description" class="bg-white border border-gray-300 rounded py-2 px-3 w-full focus:outline-none focus:ring focus:border-blue-500"><?php echo htmlspecialchars($company_description); ?></textarea>
                    </div>
                    <button type="submit"  style="background-color: rgb(39, 52, 103); color: white; padding: 4px;"  class="text-white py-2 px-4 rounded hover:bg-blue-600 transition duration-300 w-full">
                        Update Profile
                    </button>
                </form>
                <div class="mt-6 text-center">
                    <a href="company_home.php" class="text-blue-500 hover:text-blue-700 font-semibold flex items-center justify-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>