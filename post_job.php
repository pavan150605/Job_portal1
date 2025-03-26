<?php
session_start(); // Start the session

// Check if the user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Include database connection
$conn = new mysqli('localhost', 'root', '', 'job_portal');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $company = $_POST['company'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $employer_id = $_SESSION['user_id']; // Get the logged-in employer's ID

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO jobs (title, company, location, description, employer_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $title, $company, $location, $description, $employer_id);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<p>Job posted successfully!</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    // Close the statement
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css"> <!-- Link to your CSS file -->
    <title>Post a Job</title>
</head>
<body>
    <header>
        <h1>Post a Job</h1>
    </header>
    <main>
        <form action="post_job.php" method="POST">
            <input type="text" name="title" placeholder="Job Title" required>
            <input type="text" name="company" placeholder="Company Name" required>
            <input type="text" name="location" placeholder="Location" required>
            <textarea name="description" placeholder="Job Description" required></textarea>
            <button type="submit">Post Job</button>
        </form>
        
        <!-- Link to navigate back to the home page -->
        <p><a href="index.php">Home</a></p> <!-- Simple link to navigate to the job portal home page -->
    </main>
    <footer>
        <p>&copy; 2023 Job Portal. All rights reserved.</p>
    </footer>
</body>
</html>