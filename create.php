<?php
/**
 * Student Registration System
 * 
 * DATABASE SETUP (Run this in your MySQL console):
 * CREATE DATABASE IF NOT EXISTS student_db;
 * USE student_db;
 * CREATE TABLE IF NOT EXISTS students (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     first_name VARCHAR(50) NOT NULL,
 *     last_name VARCHAR(50) NOT NULL,
 *     email VARCHAR(100) UNIQUE NOT NULL,
 *     phone VARCHAR(15),
 *     birth_date DATE NOT NULL,
 *     course VARCHAR(50) NOT NULL,
 *     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 * );
 */

session_start();

// Database Configuration
$host = 'localhost';
$db   = 'student_db';
$user = 'root';
$pass = ''; // Default XAMPP/WAMP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // For demonstration, we'll store this error in session if it happens during POST
    // But for initial load, we might just display it.
    $db_connection_error = "Database connection failed. Please ensure 'student_db' exists.";
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $old_input = $_POST;

    // 1. Sanitize Inputs
    $first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''));
    $last_name  = htmlspecialchars(trim($_POST['last_name'] ?? ''));
    $email      = trim($_POST['email'] ?? '');
    $phone      = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $birth_date = trim($_POST['birth_date'] ?? '');
    $course     = htmlspecialchars(trim($_POST['course'] ?? ''));

    // 2. Validation
    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name))  $errors[] = "Last name is required.";
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!empty($phone)) {
        // Simple numeric/standard phone pattern: 7 to 15 digits
        if (!preg_match('/^[0-9\-\+\s]{7,15}$/', $phone)) {
            $errors[] = "Invalid phone format. Use 7-15 digits, spaces, or +/-.";
        }
    }

    if (empty($birth_date)) {
        $errors[] = "Birth date is required.";
    } else {
        $dob = new DateTime($birth_date);
        $today = new DateTime();
        $age = $today->diff($dob)->y;
        if ($age < 15) {
            $errors[] = "Students must be at least 15 years old.";
        }
    }

    if (empty($course)) $errors[] = "Please select a course.";

    // 3. Database Operation
    if (empty($errors)) {
        try {
            if (!isset($pdo)) throw new Exception("Database connection unavailable.");

            $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, email, phone, birth_date, course) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $phone, $birth_date, $course]);

            $_SESSION['success'] = "Student added successfully!";
            $_SESSION['old_input'] = []; // Clear old input
            header("Location: create.php");
            exit();
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation (Duplicate entry)
                $errors[] = "This email is already registered.";
            } else {
                $errors[] = "Database error: " . $e->getMessage();
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    // 4. Store errors and redirect back
    $_SESSION['errors'] = $errors;
    $_SESSION['old_input'] = $old_input;
    header("Location: create.php");
    exit();
}

// Retrieve messages from session
$success_msg = $_SESSION['success'] ?? null;
$error_msgs = $_SESSION['errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];

// Clear session messages after retrieving
unset($_SESSION['success']);
unset($_SESSION['errors']);
unset($_SESSION['old_input']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Student | Student Management</title>
    <!-- Modern Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --error: #ef4444;
            --success: #10b981;
            --border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-align: center;
            color: var(--primary);
        }

        p.subtitle {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }

        /* Alert Boxes */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .alert-error {
            background-color: #fef2f2;
            color: var(--error);
            border: 1px solid #fee2e2;
        }

        .alert-success {
            background-color: #f0fdf4;
            color: var(--success);
            border: 1px solid #dcfce7;
        }

        .alert ul {
            list-style: none;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-main);
        }

        input, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s;
            outline: none;
        }

        input:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        input::placeholder {
            color: #94a3b8;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
        }

        .btn:hover {
            background-color: var(--primary-hover);
        }

        .btn:active {
            transform: scale(0.98);
        }

        .required-star {
            color: var(--error);
            margin-left: 2px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Add Student</h1>
        <p class="subtitle">Enter student details to register them in the system.</p>

        <!-- Success Message -->
        <?php if ($success_msg): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success_msg) ?>
            </div>
        <?php endif; ?>

        <!-- Error Messages -->
        <?php if (!empty($error_msgs)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($error_msgs as $msg): ?>
                        <li>• <?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- DB Connection Error (if any on initial load) -->
        <?php if (isset($db_connection_error) && empty($success_msg) && empty($error_msgs)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($db_connection_error) ?>
            </div>
        <?php endif; ?>

        <form action="create.php" method="POST">
            <div class="form-group">
                <label for="first_name">First Name <span class="required-star">*</span></label>
                <input type="text" id="first_name" name="first_name" placeholder="John" 
                       value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name <span class="required-star">*</span></label>
                <input type="text" id="last_name" name="last_name" placeholder="Doe" 
                       value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address <span class="required-star">*</span></label>
                <input type="email" id="email" name="email" placeholder="john.doe@example.com" 
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number (Optional)</label>
                <input type="text" id="phone" name="phone" placeholder="+1234567890" 
                       value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="birth_date">Birth Date <span class="required-star">*</span></label>
                <input type="date" id="birth_date" name="birth_date" 
                       value="<?= htmlspecialchars($old['birth_date'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="course">Course <span class="required-star">*</span></label>
                <?php $current_course = $old['course'] ?? ''; ?>
                <select id="course" name="course" required>
                    <option value="" disabled <?= empty($current_course) ? 'selected' : '' ?>>Select a course</option>
                    <option value="Web Development" <?= $current_course == 'Web Development' ? 'selected' : '' ?>>Web Development</option>
                    <option value="Mobile App" <?= $current_course == 'Mobile App' ? 'selected' : '' ?>>Mobile App</option>
                    <option value="Data Science" <?= $current_course == 'Data Science' ? 'selected' : '' ?>>Data Science</option>
                    <option value="Cybersecurity" <?= $current_course == 'Cybersecurity' ? 'selected' : '' ?>>Cybersecurity</option>
                    <option value="Cloud Computing" <?= $current_course == 'Cloud Computing' ? 'selected' : '' ?>>Cloud Computing</option>
                </select>
            </div>

            <button type="submit" class="btn">Register Student</button>
        </form>
    </div>

</body>
</html>
