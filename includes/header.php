<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Manager</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; padding: 0 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #4a90d9; color: white; }
        tr:nth-child(even) { background: #f2f2f2; }
        .btn { padding: 8px 14px; text-decoration: none; border-radius: 4px; color: white; }
        .btn-primary { background: #4a90d9; }
        .btn-warning { background: #e67e22; }
        .btn-danger  { background: #e74c3c; }
        .error { color: red; } .success { color: green; }
        input, select { padding: 8px; width: 100%; margin: 5px 0 12px; box-sizing: border-box; }
        label { font-weight: bold; }
    </style>
</head>
<body>
<h2>🎓 Student Manager</h2>
<nav>
    <a href="index.php" class="btn btn-primary">View All</a>
    <a href="create.php" class="btn btn-primary" style="margin-left:8px">+ Add Student</a>
</nav><hr>
