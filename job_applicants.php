<?php
require_once 'core/db.php';
require_once 'core/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'HR') {
    redirect('login.php');
}

$hrId = $_SESSION['user']['id'];

// Fetch all job applications for jobs posted by the logged-in HR
$stmt = $pdo->prepare("
    SELECT 
        a.id AS application_id,
        a.resume, 
        a.applied_at, 
        u.username AS applicant_name,  -- Updated to use username
        j.title AS job_title
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    INNER JOIN users u ON a.applicant_id = u.id
    WHERE j.posted_by = :hr_id
    ORDER BY a.applied_at DESC
");
$stmt->execute(['hr_id' => $hrId]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Applicants</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            color: #006400;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: green;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid green;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #e6ffe6;
            color: green;
        }
        td {
            background-color: #fff;
        }
        a {
            color: #006400;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .button {
            background-color: green;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            display: inline-block;
            text-decoration: none;
        }
        .button:hover {
            background-color: darkgreen;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #e6ffe6;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 16px;
            color: green;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Job Applicants</h1>

        <?php if (count($applications) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Applicant Name</th>
                        <th>Resume</th>
                        <th>Applied At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $application): ?>
                        <tr>
                            <td><?= htmlspecialchars($application['job_title']) ?></td>
                            <td><?= htmlspecialchars($application['applicant_name']) ?></td>
                            <td>
                                <a href="<?= htmlspecialchars($application['resume']) ?>" target="_blank">Download Resume</a>
                            </td>
                            <td><?= htmlspecialchars($application['applied_at']) ?></td>
                            <td>
                                <form method="POST" action="hire_applicant.php">
                                    <input type="hidden" name="application_id" value="<?= $application['application_id'] ?>">
                                    <button type="submit" class="button">Hire Applicant</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No job applications found for your job postings.</p>
        <?php endif; ?>

        <a href="index.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>
