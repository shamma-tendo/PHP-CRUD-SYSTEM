<?php
class Student {
    private $conn;
    private $table = 'students';

    public $id, $full_name, $email, $course, $year_level;

    public function __construct($db) {
        $this->conn = $db;
    }

    // READ - get all students
    public function getAll() {
        $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ - get one student by ID
    public function getOne($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // CREATE
    public function create() {
        // Check for duplicate email first
        $check = $this->conn->prepare("SELECT id FROM {$this->table} WHERE email = :email");
        $check->bindParam(':email', $this->email);
        $check->execute();
        if ($check->rowCount() > 0) return false;

        $query = "INSERT INTO {$this->table} (full_name, email, course, year_level)
                  VALUES (:full_name, :email, :course, :year_level)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':email',     $this->email);
        $stmt->bindParam(':course',    $this->course);
        $stmt->bindParam(':year_level',$this->year_level, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // UPDATE
    public function update() {
        $query = "UPDATE {$this->table}
                  SET full_name=:full_name, email=:email, course=:course, year_level=:year_level
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':full_name',  $this->full_name);
        $stmt->bindParam(':email',      $this->email);
        $stmt->bindParam(':course',     $this->course);
        $stmt->bindParam(':year_level', $this->year_level, PDO::PARAM_INT);
        $stmt->bindParam(':id',         $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // DELETE
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
