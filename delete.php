<?php
require_once 'config/Database.php';
require_once 'models/Student.php';

$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if ($id) {
    $db      = (new Database())->getConnection();
    $student = new Student($db);
    $student->delete($id);
}

header("Location: index.php?msg=Student+deleted");
exit;
