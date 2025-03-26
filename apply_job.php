<?php
session_start(); // Start the session

// Include database connection
$conn = new mysqli('localhost', 'root', '', 'job_portal');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the job ID is provided
if (isset($_GET['job_id'])) {
    $job_id = intval($_GET['job_id']); // Get the job ID from the URL
} else {
    die("Job ID not specified."); // This message will show if job_id is not in the URL
}

// Handle form submission for applying to a job
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $resume = $_POST['resume']; // Assuming you have a field for resume

    // Check if the user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        // User does not exist, insert into users table
        $stmt->close();
        $password = password_hash(uniqid(), PASSWORD_DEFAULT); // Generate a random password
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'job_seeker')");
        $stmt->bind_param("sss", $name, $email, $password);
        $stmt->execute();
    }

    // Now insert the application into the applications table
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO applications (job_id, user_email) VALUES (?, ?)");
    $stmt->bind_param("is", $job_id, $email);
    
    if ($stmt->execute()) {
        echo "<p>Application submitted successfully!</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Fetch job details for display
$stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css"> <!-- Link to your CSS file -->
    <title>Apply for Job</title>
</head>
<body>
    <header>
        <h1>Apply for Job: <?php echo htmlspecialchars($job['title']); ?></h1>
    </header>
    <main>
        <h2>Job Details</h2>
        <p><strong>Company:</strong> <?php echo htmlspecialchars($job['company']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($job['description']); ?></p>

        <h2>Application Form</h2>
        <form action="apply_job.php?job_id=<?php echo $job_id; ?>" method="POST">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <textarea name="resume" placeholder="Your Resume" required></textarea>
            <button type="submit">Submit Application</button>
        </form>
        <p><a href="view_jobs.php">Back to Job Listings</a></p>
    </main>
    <footer>
        <p>&copy; 2023 Job Portal. All rights reserved.</p>
    </footer>
</body>
</html>