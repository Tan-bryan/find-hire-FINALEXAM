<?php
session_start();
require_once 'core/db.php';
require_once 'core/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1d441e; /* Dark green background */
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
            width: 100%;
            max-width: 500px;
            padding: 20px;
            background-color: #e6ffe5;
            border: 2px solid green;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1 {
            color: green;
            margin-bottom: 20px;
        }
        h2 {
            color: red;
        }
        p {
            color: red;
        }
        a button {
            background-color: red;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            margin: 10px 5px;
            cursor: pointer;
        }
        a button:hover {
            background-color: darkred;
        }
        .logout {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?= htmlspecialchars($user['username']) ?></h1>

        <?php if (!isset($user['role'])): ?>
            <p>Error: User role is not set. Please log in.</p>
        <?php elseif (strtolower($user['role']) === 'applicant'): ?>
            <h2>Applicant Dashboard</h2>
            <a href="job_listings.php"><button>Check Job Listings</button></a>
            <a href="message_hr.php"><button>Message HR</button></a>
        <?php elseif (strtolower($user['role']) === 'hr'): ?>
            <h2>HR Dashboard</h2>
            <a href="post_job.php"><button>Create Job Placement</button></a>
            <a href="check_messages.php"><button>Check Messages</button></a>
            <a href="job_applicants.php"><button>View Job Applicants</button></a>
        <?php else: ?>
            <p>User role not recognized.</p>
        <?php endif; ?>

        <a href="logout.php" class="logout"><button>Logout</button></a>
    </div>
</body>
</html>
