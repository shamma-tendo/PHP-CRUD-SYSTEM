<?php
require_once 'config/Database.php';
require_once 'models/Student.php';

$errors = [];
$data   = ['full_name' => '', 'email' => '', 'course' => '', 'year_level' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $data['full_name']  = trim(htmlspecialchars($_POST['full_name'] ?? ''));
    $data['email']      = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $data['course']     = trim(htmlspecialchars($_POST['course'] ?? ''));
    $data['year_level'] = (int)($_POST['year_level'] ?? 0);

    // Validate
    if (empty($data['full_name']))                       $errors[] = "Full name is required.";
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($data['course']))                          $errors[] = "Course is required.";
    if ($data['year_level'] < 1 || $data['year_level'] > 6) $errors[] = "Year level must be 1-6.";

    if (empty($errors)) {
        $db      = (new Database())->getConnection();
        $student = new Student($db);
        $student->full_name  = $data['full_name'];
        $student->email      = $data['email'];
        $student->course     = $data['course'];
        $student->year_level = $data['year_level'];

        if ($student->create()) {
            // POST-Redirect-GET: redirect so refresh won't resubmit
            header("Location: index.php?msg=Student+added+successfully");
            exit;
        } else {
            $errors[] = "Email already exists. Please use a different one.";
        }
    }
}

require_once 'includes/header.php';
?>

<h3>Add New Student</h3>

<?php foreach ($errors as $e): ?>
    <p class="error">⚠ <?= $e ?></p>
<?php endforeach; ?>

<form method="POST" action="create.php">
    <label>Full Name</label>
    <input type="text" name="full_name" value="<?= $data['full_name'] ?>" required>

    <label>Email Address</label>
    <input type="email" name="email" value="<?= $data['email'] ?>" required>

    <label>Course</label>
    <input type="text" name="course" value="<?= $data['course'] ?>" required>

    <label>Year Level (1-6)</label>
    <input type="number" name="year_level" min="1" max="6" value="<?= $data['year_level'] ?>" required>

    <button type="submit" class="btn btn-primary">Save Student</button>
    <a href="index.php" class="btn btn-danger">Cancel</a>
</form>

<?php require_once 'includes/footer.php'; ?>
