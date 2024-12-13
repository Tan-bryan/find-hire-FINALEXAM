<?php
require_once 'core/db.php';
require_once 'core/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'HR') {
    redirect('login.php');
}

$successMessage = '';
$errorMessage = '';

// Handle form submission for creating a job
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_job'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (empty($title) || empty($description)) {
        $errorMessage = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO jobs (title, description, posted_by) VALUES (:title, :description, :posted_by)");
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'posted_by' => $_SESSION['user']['id']
        ]);
        $successMessage = 'Job created successfully!';
    }
}

// Handle form submission for deleting a job
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_job'])) {
    $jobId = $_POST['job_id'];

    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = :job_id AND posted_by = :posted_by");
    $stmt->execute([
        'job_id' => $jobId,
        'posted_by' => $_SESSION['user']['id']
    ]);
    $successMessage = 'Job deleted successfully!';
}

// Fetch all jobs created by the logged-in HR
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE posted_by = :posted_by");
$stmt->execute(['posted_by' => $_SESSION['user']['id']]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Job Applications</title>
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
        max-width: 600px;
        width: 100%;
    }
    h1, h2 {
        color: green;
        text-align: center;
    }
    p {
        color: red;
    }
    form {
        margin-bottom: 20px;
    }
    input, textarea, button {
        display: block;
        width: 100%;
        margin-bottom: 10px;
        padding: 10px;
        border: 1px solid green;
        border-radius: 5px;
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
        color: #333;
        padding: 10px;
        border: 1px solid green;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    li strong {
        color: green; 
    }
    li {
        color: green; 
    }
    a {
        color: red;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>

</head>
<body>
    <div class="container">
        <h1>Create or Delete Job Applications</h1>

        <!-- Success and Error Messages -->
        <?php if ($successMessage): ?>
            <p><?= htmlspecialchars($successMessage) ?></p>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <p><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>

        <!-- Form to Create a Job -->
        <h2 >Create a Job</h2>
        <form action="" method="post">
            <label for="title"  style="color: #1d441e;">Job Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="description"  style="color: #1d441e;">Job Description:</label>
            <textarea id="description" name="description" rows="5" required></textarea>

            <button type="submit" name="create_job">Create Job</button>
        </form>

        <!-- List of Jobs with Delete Option -->
        <h2>Your Posted Jobs</h2>
        <?php if (count($jobs) > 0): ?>
            <ul>
                <?php foreach ($jobs as $job): ?>
                    <li>
                        <strong><?= htmlspecialchars($job['title']) ?></strong><br>
                        <?= htmlspecialchars($job['description']) ?><br>
                        <form action="" method="post" style="display: inline;">
                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                            <button type="submit" name="delete_job" onclick="return confirm('Are you sure you want to delete this job?');">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You have not posted any jobs yet.</p>
        <?php endif; ?>

        <a href="index.php">Back to Dashboard</a>
    </div>
</body>
</html>
