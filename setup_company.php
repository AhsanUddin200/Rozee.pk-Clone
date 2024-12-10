<?php
// setup_company.php
include 'db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $company_name = trim($_POST['company_name']);
    $company_description = trim($_POST['company_description']);
    $company_logo = 'uploads/logos/default.png'; // Default logo

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
    }

    // Validation
    if (empty($company_name)) { $errors[] = "Company name is required"; }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE rozee_company SET company_name = ?, company_description = ?, company_logo = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $company_name, $company_description, $company_logo, $user_id);
        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Company setup failed, please try again";
        }
    }
}

$user_id = $_GET['user_id'];
?>
    
<!DOCTYPE html>
<html>
<head>
    <title>Setup Company - Rozee</title>
    <style>
        /* Similar CSS as signup */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { width: 500px; margin: auto; padding: 20px; background: #fff; margin-top: 50px; border-radius: 5px; }
        input, textarea { width: 100%; padding: 10px; margin: 5px 0 10px 0; }
        .error { color: red; }
        button { padding: 10px; background: #5cb85c; color: #fff; border: none; width: 100%; }
        img { display: block; margin: 10px auto; width: 100px; height: 100px; border-radius: 50%; object-fit: cover; }
    </style>
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
<body>
<div class="container">
    <h2>Setup Company</h2>
    <?php
    if (!empty($errors)) {
        echo '<div class="error"><ul>';
        foreach($errors as $error){
            echo '<li>'.htmlspecialchars($error).'</li>';
        }
        echo '</ul></div>';
    }
    ?>
    <form name="companyForm" action="" method="POST" enctype="multipart/form-data" onsubmit="return validateForm();">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
        <img id="companyLogoPreview" src="uploads/logos/default.png" alt="Company Logo">
        <input type="file" name="company_logo" accept=".jpg,.jpeg,.png" onchange="previewCompanyLogo(this)">
        <input type="text" name="company_name" placeholder="Company Name" value="<?php if(isset($company_name)) echo htmlspecialchars($company_name); ?>">
        <textarea name="company_description" placeholder="Company Description"><?php if(isset($company_description)) echo htmlspecialchars($company_description); ?></textarea>
        <button type="submit">Save Company</button>
    </form>
</div>
</body>
</html>
