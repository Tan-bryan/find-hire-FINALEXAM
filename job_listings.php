<?php
require_once 'core/db.php';
require_once 'core/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'applicant') {
    redirect('login.php');
}

$successMessage = '';
$errorMessage = '';

// Directory for storing uploaded resumes
$uploadsDir = '../uploads';

// Ensure the uploads directory exists
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true); // Create the folder with proper permissions
}

// Handle job application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_job'])) {
    $jobId = $_POST['job_id'];
    $applicantId = $_SESSION['user']['id'];

    // Check if a file was uploaded
    if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = 'Please upload a valid resume file.';
    } else {
        $file = $_FILES['resume'];
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

        // Validate file type
        if ($fileExtension !== 'pdf') {
            $errorMessage = 'Only PDF files are allowed.';
        } else {
            // Generate a unique file name and move the uploaded file
            $resumePath = $uploadsDir . '/resume_' . uniqid('', true) . '.pdf';
            if (move_uploaded_file($file['tmp_name'], $resumePath)) {
                // Save the application to the database
                $stmt = $pdo->prepare("INSERT INTO applications (job_id, applicant_id, resume) VALUES (:job_id, :applicant_id, :resume)");
                $stmt->execute([
                    'job_id' => $jobId,
                    'applicant_id' => $applicantId,
                    'resume' => $resumePath
                ]);
                $successMessage = 'Your application has been submitted successfully!';
            } else {
                $errorMessage = 'Failed to upload the resume. Please try again.';
            }
        }
    }
}

// Fetch all available jobs
$stmt = $pdo->query("SELECT * FROM jobs");
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1d441e; /* Dark green background */
            color: #fff;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            background-color: #e6ffe6;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            width: 100%;
        }
        h1 {
            color: green;
            text-align: center;
        }
        h3 {
            color: green; /* Job title in green */
        }
        p {
            color: #f44336; /* Error message in red */
        }
        form {
            margin-top: 20px;
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        input[type="file"], button {
            display: block;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid green;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
        }
        button {
            background-color: red;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: darkred;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            background-color: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid green;
            border-radius: 5px;
        }
        a {
            color: red;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Available Job Listings</h1>

        <!-- Success and Error Messages -->
        <?php if ($successMessage): ?>
            <p style="color: green;"><?= htmlspecialchars($successMessage) ?></p>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <p style="color: red;"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>

        <ul>
            <?php foreach ($jobs as $job): ?>
                <li>
                    <h3><?= htmlspecialchars($job['title']) ?></h3>
                    <p><?= htmlspecialchars($job['description']) ?></p>
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                        <label for="resume_<?= $job['id'] ?>">Upload Resume (PDF only):</label>
                        <input type="file" id="resume_<?= $job['id'] ?>" name="resume" accept=".pdf" required>
                        <button type="submit" name="apply_job">Apply</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <a href="index.php">Back to Dashboard</a>
    </div>
</body>
</html>
