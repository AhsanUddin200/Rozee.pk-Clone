<?php
// company_home.php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'company') {
    header("Location: login.php");
    exit();
}

// Fetch company details
$stmt = $conn->prepare("SELECT company_name, company_logo FROM rozee_company WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($company_name, $company_logo);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Home - Rozee</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-100">
    
    <div class="container mx-auto px-4 py-8">
        
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="logo" style="display: flex; justify-content: center; align-items: center; background-color: #FFFFFF; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); padding: 5px 8px; margin: 2px auto; max-width: 100px; border-radius: 8px;">
            <img style="width: 80px; height: 80px; object-fit: contain; " src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAARMAAAC3CAMAAAAGjUrGAAAA3lBMVEX///8hL2IAFFYUJV3T1dwfLWEHHlqlqLh2fJYAD1RHtEkAAFHn6OzY2eAADVPt7vENIVsAGlirrr1vdZEYKF4ACFIAFlaVmawAElX29/je3+UABVLMztbDxdA+SHHp6u5TW34uOmm4u8dlbIpHUHY2QW1dZIWbn7FQWHy0t8SDiJ8AAEaSlqqKjqRzeZQyPWvd790vrjLL58y33riHyoh6xXtLtk2Z0Zo4sDrLytgwaltlvWYGAF5Pr1ac0p1FfWU2VmccGmMdIWMqPmVHnldBjVnu9+4xZVzCzstiwWG5BwWSAAAJpklEQVR4nO2aeZvbthHGeQEUlxIFUCCX1H1rpUi7XtdO3KZNUye9vv8XKjjgDWobe+XaeTq/f7wiQWLwYjAYDG0YCIIgCIIgCIIgCIIgCIIgCIIgCIIgCIIgCIIgCIIgCIIgCIIgCIIgCIIgCIL8rhjs5pevbcO3hhOwx69tw7eGY9m9r23DtwZqooOa6KAmOqiJDmqig5rooCY6qIkOaqKDmuigJjqoiQ5qooOa6KAmOqiJDmqig5rofD1N0sUi/bwnh4vhi7eHn/negv+RJsPp+rh2yqGMD8t4EgSTeHkYf9qLjiPTD4LAX/VmXbed05O8PaGb8Dfpkjqz9WzQbvoFNRlYRGINDMPd+THn3IrEqC9vzOYREyYgWDQvBzeNyRUEAznTS8RtAg8SO2Zuu8fZk8fUbUF96KqD9X6velycn3yL89jyd/eLeou2JqPNpmfMTuOTcX6lBxoDLxuO159xrgw1TftBusyDlwuSy+Jtc4um1jVNfPCmWcxI7UHCV41hp/ugftv2uz9cuVYSyn+GG58Jm8qpYoIwf1Rbji1NtpSupBahsRn2Fu23faomFgz5sbKU0NSY+Q1FoMlETdw0bt/J8eH+wSet68KfVt31bbt1m666huBSKjU5+raIWc9dz9aH/YSadrS+oskDE5kkhmvMNoPBTTQxaxL4jhFOioERIco//fAlTax7kCQqHIByWgx/4hS9jb22YpkLdowBNLlMCK8WberGNvHPnZos5TKHFTNIF+P0Nn6iBk1jK6bJ2Vj7ubWxv9vvdz7PFfPX1zVhIxAsf5LHj+7a7cVUvTjJV/gwzgMJ96LIK15LfF0UqckxjEz/UL+Y7rkZFVfqmuypIK8NIp2aWDt3Op6GPaOfu7/3kM/R7MHLje9nw2Z1cl8Qc2hJidIyLIamopK9Vz/n8ItY86N8UX+9T3JRIm1fc6l94vlyrNHj5mSqabKhwn5x4/9MTUhSLtWdspzXDJqpCRY76f7n+zpnEIUImKYLA5fZVnM2XMHLIhiICy5GgrKnGVeCsg5NTNsMjpq5G5vY6q9KkxEn7JaSlJok5e5wVJaLRjdDFVZizcpNNmYygadTCEPZtlWRmtk1scz+Vm7hlQIsTKW0qY9IamLaow57CaFq9ZSaPHLCXxtBmuSaxFVAJwTcuZU49CO4TFqP92Cmcx/PBqI1GUOI8aXVLrT1Svcbq93f3nWEAvkqEnWFiFliWvBHoUkvJtaVJOdzUZqQp6pTuMC1VEuNyGqu8AMEGi+Pe1uhtzCMU7a6qJtHE1F60UBt/2zbZZbUhHVnLnPCYf5yTU4W8T4xy/6vKE1YFd97NqwcvSWsnmbuqDYoml9Ls19k135uOMmk2KvbZhmlpiqS02WnWVKToHuoRy422b9Kk4tFrrR7BUqTuEqrYOjsXm95nwXQIsKpZ2GUdjHRsEvrubyxJdleIwcBiyhfEGslCd90m+XKHaz7TuqTJPsXNDl75uS1GZqO0sQqXzyMmr/bLaMqHg4tCDGl7RBOxP5x1ORxnjWLUthkitahSmSsyu3eNjqTe/G1E96OBFn8kJqc3IB4zpVmr6CtiZrNrvCWTqBlaUMKwdgMypivdmJht4Fm1ngNmqg85qAkCaqQ8Yd3jc5Ubt9JzwavloauqHm11WtoazKDnTjpago5SlyGUJWA1Q4zvfZRpo7lKD9ZZS0vAVyrZalvnt83+nJp3FlmkBwYzzICmDxhxrdfOromYDnvagpuUJq6gZ9BbZ5GL2kSO6onXzbsqf3fr55983z3faMvl1rXFkXuQpkm8ZJ9gRCraaKOM1FX06gejU+wNfNT7b7yExF08p1j+LmmG3iU1BL3t893d8//qPf1mzRJwiyH/VL7TqXJOGkGiYoFOHyiLHDhh72sN4B4IpbpsJMiP9lvqJKkWnQ/SEnuPjSCrFw7U6ObM4MExbFElud+CVHamqjZ5Gu95ZoXvi9XGLQSq0YDlcba+pM5BxBDqNNUUPb45vsPd5kmPzTfxfXDjmIkLBVj1c604TdP2jRNtmo29ZZ7uAHJyBiSCxI3zylTeFWgJdrjfMaHfhldBC9bvX3/7k5RfyY7F1+x2CaTrN/yvCNFSW4riqbJAWKnr/Wizi2Q8A7VQcVvr3jVQkvJHyYbJd5jEYRF44D5R/CTu+c3tWsyZ2s6YWWHp3Kc6lyciXLTE4+myUItC+0UslVbbxZo1Pl/ojk3eBIJWqfco2XaqjxWOAqZ19Of/p9Akh/v6osny+27d9mTzcCDGvWT2x4DNU2MDcwmbbnuCYKBnaXiW2gQ60c0dXwUjVqBMYYCAoWc/qBqdOKhpsn4z3/JveSn2lNZ/aSrVGCkEYnA2HqdTYoS31AUXZO+lnYbVUrRzwyAcXUdVFQ9hNVv5QUBNbe5s9UKcTKj/StE2GYaC/FaW5sZIzsv6TXqsVIUfjtRdE2Ms6p+0V25G053qrDK5RK4qLlmbui2caaqQs125etCKLuUu1FafOfg9mXtOOsTD37WA6yh6idasUZyDIrMuVm339MbFpY6NMkXh0mS1Tkz/LxK1EiyE3BYFOYZbROd8+RWxpRt6PT704OZfzQqK/f9onBPGLcsbn/8WY+vShOyZblH1JA5AMuXVOv7jhSF3kqULk2MJ7tueDG39qoqzHfBpBc9FZ8OqeV55devWhbf57XPJuRvKr4+F/naeb5Z5JrQ9Yjaze9lhusTUVS/2t8BbyhKpybGnOpDpnK3GE+uSwKapCumXSeT+uF1OOfF9Y+//PqhIckpEbZVaBIaD5T4l2oXGzzEcl6K8Kx9L5ai3KhU3a2J0Wt/CBT+qRYOrmoiTWt/1mLtLP3el68h9sdf/g5O8uHH8qSTpYLqkKkONaOIMH/pTsf98ex+FwhiLcu36N/Q90zcRhTnOzihabmAM/fs6nOp7c2dTBLfe4GJ2p2PrLY85Jh6Wi1meAoS9s9fQZDn72vnnOzzUDCtNDFmJBaCxomXyHUoOK85nJOwdtVpSW8jStpfSPodNaTpKJKxRMKtaASGDvsvU9gTbv2YMls+GYhL9xr/17t/f3h+vnv/UyO4hhNmqbha1pTWSx+soLG/bdSPuv6vxV4I+7afNXQG4f3pdB9+enlvOHMvp8th/ak5g3POx12rs6XT8HA+hNMbfvz8ffJC7fH/FtREBzXRQU10UBMd1EQHNdFBTXRQEx3URAc10UFNdFATHdREBzXRceMYNWkR7nbX/l8BgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiDX+A/WeKJX2bvtaQAAAABJRU5ErkJggg==" alt="Rozee.pk Logo">
        </div>
            <div style="background-color: rgb(39, 52, 103); color: white; padding: 24px;" class=" p-6 flex justify-between items-center">
      
            <div>
                    <h2 class="text-3xl font-bold text-white">Welcome, <?php echo htmlspecialchars($company_name); ?></h2>
                </div>
                <div class="flex items-center">
                    <div class="text-center mr-4">
                        <img src="<?php echo htmlspecialchars($company_logo); ?>" alt="Company Logo" class="w-16 h-16 rounded-full object-cover border-4 border-white shadow-md">
                        <p class="text-white mt-2 font-semibold"><?php echo htmlspecialchars($company_name); ?></p>
                    </div>
                    <a href="logout.php" class="text-white hover:text-red-200 transition duration-300">
                        <i class="fas fa-sign-out-alt text-2xl"></i>
                    </a>
                </div>
            </div>

            <div class="p-6">
                <div class="grid md:grid-cols-3 gap-6">
                    <a href="post_job.php" class="dashboard-card block bg-white border border-gray-200 rounded-lg p-6 text-center hover:shadow-lg transition duration-300">
                        <i class="fas fa-plus-circle text-4xl text-green-500 mb-4 block"></i>
                        <h3 class="text-xl font-semibold text-gray-800">Post a Job</h3>
                        <p class="text-gray-500 mt-2">Create a new job listing</p>
                    </a>

                    <a href="view_applications.php" class="dashboard-card block bg-white border border-gray-200 rounded-lg p-6 text-center hover:shadow-lg transition duration-300">
                        <i class="fas fa-file-alt text-4xl text-blue-500 mb-4 block"></i>
                        <h3 class="text-xl font-semibold text-gray-800">View Applications</h3>
                        <p class="text-gray-500 mt-2">Review job applications</p>
                    </a>

                    <a href="company_profile.php" class="dashboard-card block bg-white border border-gray-200 rounded-lg p-6 text-center hover:shadow-lg transition duration-300">
                        <i class="fas fa-building text-4xl text-purple-500 mb-4 block"></i>
                        <h3 class="text-xl font-semibold text-gray-800">My Company Profile</h3>
                        <p class="text-gray-500 mt-2">Manage company details</p>
                    </a>
                </div>

                <div class="mt-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Dashboard</h3>
                    <!-- Placeholder for company-specific dashboard information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-600">Dashboard content will be added here. This could include recent job postings, application statistics, etc.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
