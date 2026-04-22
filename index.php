<?php
require_once 'includes/header.php';
require_once 'config/Database.php';
require_once 'models/Student.php';

$db      = (new Database())->getConnection();
$student = new Student($db);
$result  = $student->getAll();
?>

<?php if (isset($_GET['msg'])): ?>
    <p class="success"><?= htmlspecialchars($_GET['msg']) ?></p>
<?php endif; ?>

<h3>All Students (<?= $result->rowCount() ?> records)</h3>

<?php if ($result->rowCount() > 0): ?>
<table>
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Course</th><th>Year</th><th>Actions</th></tr>
    <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['full_name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['course']) ?></td>
        <td>Year <?= $row['year_level'] ?></td>
        <td>
            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning">Edit</a>
            <a href="delete.php?id=<?= $row['id'] ?>"
               class="btn btn-danger"
               onclick="return confirm('Delete this student?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
    <p>No students found. <a href="create.php">Add one now.</a></p>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
