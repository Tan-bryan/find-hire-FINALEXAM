<?php
require_once 'core/db.php';
require_once 'core/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'HR') {
    redirect('login.php');
}

$hrId = $_SESSION['user']['id'];

// Fetch all messages sent to the HR user (including replies)
$stmt = $pdo->prepare("SELECT m.id, m.sender_id, m.recipient_id, m.subject, m.message, m.sent_at, u.username AS sender_name 
                       FROM messages m
                       INNER JOIN users u ON m.sender_id = u.id
                       WHERE m.recipient_id = :hr_id
                       ORDER BY m.sent_at DESC");
$stmt->execute(['hr_id' => $hrId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle reply to message
if (isset($_POST['reply_message'])) {
    $message_id = $_POST['message_id'];
    $reply = $_POST['reply_message'];
    $sender_id = $hrId; // HR is replying
    $recipient_id = $_POST['recipient_id']; // The applicant or HR receiving the reply

    // Fetch the subject of the original message for the reply
    $stmt = $pdo->prepare("SELECT subject FROM messages WHERE id = :message_id");
    $stmt->execute(['message_id' => $message_id]);
    $originalMessage = $stmt->fetch(PDO::FETCH_ASSOC);
    $subject = 'Re: ' . $originalMessage['subject'];

    // Insert the reply message into the database
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, recipient_id, subject, message, sent_at) 
                           VALUES (:sender_id, :recipient_id, :subject, :message, NOW())");
    $stmt->execute([
        'sender_id' => $sender_id,
        'recipient_id' => $recipient_id,
        'subject' => $subject,
        'message' => $reply
    ]);

    echo "Reply sent successfully!";
}

// Fetch all conversations (messages between the logged-in HR and applicants)
$stmt = $pdo->prepare("SELECT m.id, m.sender_id, m.recipient_id, m.subject, m.message, m.sent_at, u.username AS sender_name 
                       FROM messages m
                       INNER JOIN users u ON m.sender_id = u.id
                       WHERE m.recipient_id = :hr_id OR m.sender_id = :hr_id
                       ORDER BY m.sent_at DESC");
$stmt->execute(['hr_id' => $hrId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Messages</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
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
        .reply-form {
            margin-top: 20px;
            padding: 20px;
            background-color: #e6ffe6;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Messages</h1>

        <?php if (count($messages) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Sender</th>
                        <th>Recipient</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Sent At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $message): ?>
                        <tr>
                            <td><?= htmlspecialchars($message['sender_name']) ?></td>
                            <td><?= htmlspecialchars($message['recipient_id'] == $hrId ? 'You' : $message['sender_name']) ?></td>
                            <td><?= htmlspecialchars($message['subject']) ?></td>
                            <td><?= nl2br(htmlspecialchars($message['message'])) ?></td>
                            <td><?= htmlspecialchars($message['sent_at']) ?></td>
                            <td><a href="check_messages.php?view=<?= $message['id'] ?>" class="button">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No messages yet.</p>
        <?php endif; ?>

        <?php
        if (isset($_GET['view'])) {
            $messageId = $_GET['view'];

            // Fetch the selected message details
            $stmt = $pdo->prepare("SELECT m.id, m.sender_id, m.recipient_id, m.subject, m.message, m.sent_at, u.username AS sender_name 
                                   FROM messages m
                                   INNER JOIN users u ON m.sender_id = u.id
                                   WHERE m.id = :message_id");
            $stmt->execute(['message_id' => $messageId]);
            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Show the message details and reply form
            if ($message) {
                echo "<h2>Message Details</h2>";
                echo "<p><strong>From:</strong> " . htmlspecialchars($message['sender_name']) . "</p>";
                echo "<p><strong>Subject:</strong> " . htmlspecialchars($message['subject']) . "</p>";
                echo "<p><strong>Message:</strong> " . nl2br(htmlspecialchars($message['message'])) . "</p>";
                echo "<p><strong>Sent At:</strong> " . htmlspecialchars($message['sent_at']) . "</p>";
                
                echo '<div class="reply-form">
                        <h3>Reply</h3>
                        <form method="POST">
                            <input type="hidden" name="message_id" value="' . $message['id'] . '">
                            <input type="hidden" name="recipient_id" value="' . $message['sender_id'] . '">
                            <textarea name="reply_message" rows="5" required></textarea>
                            <br><br>
                            <button type="submit" class="button">Send Reply</button>
                        </form>
                      </div>';
            }
        }
        ?>

        <a href="index.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>
