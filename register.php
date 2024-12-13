<?php
require_once 'core/db.php';
require_once 'core/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $role = $_POST['role'];

    if (empty($username) || empty($password) || empty($confirmPassword) || empty($role)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['applicant', 'HR'])) {
        $error = 'Invalid role selected.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
            $stmt->execute([
                'username' => $username,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'role' => $role
            ]);
            redirect('login.php');
        } catch (PDOException $e) {
            $error = 'Registration failed: ' . ($e->errorInfo[1] === 1062 ? 'Username already exists.' : $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
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
            max-width: 400px;
            padding: 20px;
            background-color: #e6ffe6;
            border: 2px solid green;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1 {
            color: green;
            margin-bottom: 20px;
        }
        p {
            color: red;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: block;
            color: green;
            margin-bottom: 5px;
            text-align: left;
        }
        input[type="text"], input[type="password"], select {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid green;
            border-radius: 5px;
        }
        button {
            background-color: red;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: darkred;
        }
        a {
            color: green;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if ($error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form action="" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required><br>
            
            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="applicant">Applicant</option>
                <option value="HR">HR</option>
            </select><br>
            
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>
