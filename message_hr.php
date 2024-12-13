<?php
require_once 'core/db.php';
require_once 'core/functions.php';

// Debugging: Check if the session is set
if (!isset($_SESSION['user'])) {
    echo "Session not set!"; // Debugging message
    exit;
}

if (!isLoggedIn()) {
    echo "Not logged in."; // Debugging message
    exit;
}

if ($_SESSION['user']['role'] !== 'applicant') {
    echo "User is not an applicant."; // Debugging message
    exit;
}

// Get the current user's ID
$sender_id = $_SESSION['user']['id'];

// Get the list of HR users to populate the "To" dropdown
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'HR'");
$stmt->execute();
$hrUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all messages (conversation) between the logged-in user (Applicant) and the selected HR
$conversation = [];
if (isset($_GET['to'])) {
    $recipient_id = $_GET['to'];
    
    // Query for conversation between the logged-in user and the selected HR
    $stmt = $pdo->prepare("SELECT m.id, m.sender_id, m.recipient_id, m.subject, m.message, m.sent_at, u.username AS sender_name
                           FROM messages m
                           INNER JOIN users u ON m.sender_id = u.id
                           WHERE (m.sender_id = :sender_id AND m.recipient_id = :recipient_id)
                           OR (m.sender_id = :recipient_id AND m.recipient_id = :sender_id)
                           ORDER BY m.sent_at ASC");
    $stmt->execute(['sender_id' => $sender_id, 'recipient_id' => $recipient_id]);
    $conversation = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['to'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Insert the message into the messages table
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, recipient_id, subject, message, sent_at) 
                           VALUES (:sender_id, :recipient_id, :subject, :message, NOW())");
    $stmt->execute([
        'sender_id' => $sender_id,
        'recipient_id' => $to,
        'subject' => $subject,
        'message' => $message
    ]);

    // After sending the message, redirect to the same page to reload the conversation
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message HR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1d441e; /* Dark green background */
            color: #006400; /* Dark green text */
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .container {
            background-color: #e6ffe6;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 800px;
        }
        h1 {
            color: green;
            text-align: center;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
            color: green;
        }
        select, input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid green;
            border-radius: 5px;
            color: green;
        }
        button {
            background-color: red;
            color: white;
            padding: 10px;
            border: 1px solid green;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: darkred;
        }
        table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid green;
            padding: 10px;
            text-align: left;
            color: green;
        }
        th {
            background-color: #e6ffe6;
        }
        td {
            background-color: #fff;
        }
        a {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: green;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Send Message to HR</h1>

        <!-- Form to send a new message -->
        <form method="POST">
            <label for="to">To:</label>
            <select name="to" id="to" required>
                <option value="">Select HR</option>
                <?php foreach ($hrUsers as $hr): ?>
                    <option value="<?= htmlspecialchars($hr['id']) ?>" <?php echo isset($_GET['to']) && $_GET['to'] == $hr['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($hr['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            
            <label for="subject">Subject:</label>
            <input type="text" name="subject" id="subject" required>
            <br><br>
            
            <label for="message">Message:</label>
            <textarea name="message" id="message" rows="5" required></textarea>
            <br><br>
            
            <button type="submit">Send Message</button>
        </form>

        <hr>

        <h2>Messages</h2>

        <?php if (!empty($conversation)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Sender</th>
                        <th>Receiver</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Time Sent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($conversation as $msg): ?>
                        <tr>
                            <td><?= htmlspecialchars($msg['sender_name']) ?></td>
                            <td><?= ($msg['recipient_id'] == $sender_id) ? 'Applicant' : 'HR' ?></td>
                            <td><?= htmlspecialchars($msg['subject']) ?></td>
                            <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
                            <td><?= htmlspecialchars($msg['sent_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No conversation yet. Start by sending a message.</p>
        <?php endif; ?>

        <a href="index.php">Back to Dashboard</a>
    </div>
</body>
</html>
