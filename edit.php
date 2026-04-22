<?php
require_once 'config/Database.php';
require_once 'models/Student.php';

$db      = (new Database())->getConnection();
$student = new Student($db);
$errors  = [];

$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if (!$id) { header("Location: index.php"); exit; }

// Load existing data
$data = $student->getOne($id);
if (!$data) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['full_name']  = trim(htmlspecialchars($_POST['full_name'] ?? ''));
    $data['email']      = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $data['course']     = trim(htmlspecialchars($_POST['course'] ?? ''));
    $data['year_level'] = (int)($_POST['year_level'] ?? 0);

    if (empty($data['full_name']))                          $errors[] = "Full name is required.";
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($data['course']))                             $errors[] = "Course is required.";
    if ($data['year_level'] < 1 || $data['year_level'] > 6) $errors[] = "Year level must be 1-6.";

    if (empty($errors)) {
        $student->id         = $id;
        $student->full_name  = $data['full_name'];
        $student->email      = $data['email'];
        $student->course     = $data['course'];
        $student->year_level = $data['year_level'];

        if ($student->update()) {
            header("Location: index.php?msg=Student+updated+successfully");
            exit;
        } else {
            $errors[] = "Update failed. Email may already be in use.";
        }
    }
}

require_once 'includes/header.php';
?>

<h3>Edit Student #<?= $id ?></h3>

<?php foreach ($errors as $e): ?>
    <p class="error">WARNING: <?= $e ?></p>
<?php endforeach; ?>

<form method="POST" action="edit.php?id=<?= $id ?>">
    <label>Full Name</label>
    <input type="text" name="full_name" value="<?= htmlspecialchars($data['full_name']) ?>" required>

    <label>Email Address</label>
    <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" required>

    <label>Course</label>
    <input type="text" name="course" value="<?= htmlspecialchars($data['course']) ?>" required>

    <label>Year Level (1-6)</label>
    <input type="number" name="year_level" min="1" max="6" value="<?= $data['year_level'] ?>" required>

    <button type="submit" class="btn btn-warning">Update Student</button>
    <a href="index.php" class="btn btn-danger">Cancel</a>
</form>

<?php require_once 'includes/footer.php'; ?>
