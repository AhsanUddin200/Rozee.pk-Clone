<?php
// homepage.php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch job listings with optional filtering
$category = isset($_GET['category']) ? $_GET['category'] : '';

if ($category) {
    $stmt = $conn->prepare("SELECT rozee_jobs.*, rozee_company.company_name FROM rozee_jobs JOIN rozee_company ON rozee_jobs.company_id = rozee_company.id WHERE category = ?");
    $stmt->bind_param("s", $category);
} else {
    $stmt = $conn->prepare("SELECT rozee_jobs.*, rozee_company.company_name FROM rozee_jobs JOIN rozee_company ON rozee_jobs.company_id = rozee_company.id");
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch categories
$categories = ['IT', 'Marketing', 'Engineering'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings - Rozee.pk</title>
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
        .logout-btn {
            color: #cc0000;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-weight: 600;
        }
        .logout-btn i {
            margin-right: 5px;
        }
        .filters {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .filters strong {
            margin-right: 15px;
            color: #666;
        }
        .category-filter {
            text-decoration: none;
            padding: 5px 10px;
            background-color: #273467;
            color: white;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        .category-filter:hover {
            background-color: #0052a3;
        }
        .category-filter.active {
            background-color: #004080;
        }
        .job-listing {
            border-bottom: 1px solid #e0e0e0;
            padding: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .job-listing:last-child {
            border-bottom: none;
        }
        .job-details {
            flex-grow: 1;
        }
        .job-details h4 {
            color: #0066cc;
            margin-bottom: 10px;
        }
        .job-details p {
            color: #666;
            margin-bottom: 5px;
        }
        .apply-btn {
            background-color: #5cb85c;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .apply-btn:hover {
            background-color: #4cae4c;
        }
        .no-jobs {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
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
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            .job-listing {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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
                <h2>Job Listings</h2>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="filters">
            <strong>Filter by Category:</strong>
            <a href="homepage.php" class="category-filter <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">All</a>
            <?php foreach($categories as $cat): ?>
                <a href="homepage.php?category=<?php echo urlencode($cat); ?>" class="category-filter <?php echo (isset($_GET['category']) && $_GET['category'] == $cat) ? 'active' : ''; ?>"><?php echo htmlspecialchars($cat); ?></a>
            <?php endforeach; ?>
        </div>

        <?php
        if ($result->num_rows > 0) {
            while($job = $result->fetch_assoc()) {
                echo '<div class="job-listing">';
                echo '<div class="job-details">';
                echo '<h4>' . htmlspecialchars($job['job_title']) . '</h4>';
                echo '<p><i class="fas fa-building"></i> <strong>Company:</strong> ' . htmlspecialchars($job['company_name']) . '</p>';
                echo '<p><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> ' . htmlspecialchars($job['location']) . '</p>';
                echo '<p><i class="fas fa-tag"></i> <strong>Category:</strong> ' . htmlspecialchars($job['category']) . '</p>';
                echo '</div>';
                echo '<a href="apply_job.php?job_id=' . $job['id'] . '" class="apply-btn">Apply Now</a>';
                echo '</div>';
            }
        } else {
            echo "<p class='no-jobs'>No job listings found. Check back later!</p>";
        }
        ?>
    </div>
</body>
</html>
