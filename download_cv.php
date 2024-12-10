<?php
// download_cv.php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'company') {
    header("Location: login.php");
    exit();
}

if(!isset($_GET['application_id'])){
    echo "Invalid request.";
    exit();
}

$application_id = intval($_GET['application_id']);

// Fetch application details and ensure it belongs to the company's job
$stmt = $conn->prepare("SELECT rozee_applications.cv_path 
                        FROM rozee_applications 
                        JOIN rozee_jobs ON rozee_applications.job_id = rozee_jobs.id 
                        JOIN rozee_company ON rozee_jobs.company_id = rozee_company.id 
                        WHERE rozee_applications.id = ? AND rozee_company.user_id = ?");
$stmt->bind_param("ii", $application_id, $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($cv_path);
if($stmt->fetch()){
    $stmt->close();
    if(file_exists($cv_path)){
        // Get file info
        $file_info = pathinfo($cv_path);
        $file_name = $file_info['basename'];
        $file_ext = strtolower($file_info['extension']);

        // Set appropriate headers
        if($file_ext == 'pdf'){
            header('Content-Type: application/pdf');
        } elseif($file_ext == 'docx'){
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        } else {
            header('Content-Type: application/octet-stream');
        }

        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Length: ' . filesize($cv_path));
        readfile($cv_path);
        exit();
    } else {
        echo "CV file not found.";
    }
} else {
    echo "You are not authorized to download this CV.";
    $stmt->close();
}
?>
