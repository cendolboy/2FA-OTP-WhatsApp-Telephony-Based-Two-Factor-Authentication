<?php
session_start();

// Check if welcome message is set
if (!isset($_SESSION['welcome_message'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit;
}

// Get the welcome message
$welcomeMessage = $_SESSION['welcome_message'];

// Clear the message after displaying it
unset($_SESSION['welcome_message']);

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy(); // Destroy the session
    header('Location: login.php'); // Redirect to login page
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 100px;
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <h1 class="display-4 text-success"><?php echo htmlspecialchars($welcomeMessage); ?></h1>
        <form method="POST" class="mt-4">
            <button type="submit" name="logout" class="btn btn-primary btn-lg">Go Home / Logout</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
