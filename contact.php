<!--Create contact.php-->
<?php 
session_start();
require 'db.php';

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    function clean($data){
        return htmlspecialchars(trim($data));
    }

    // Sanitize Input
    $title = clean($_POST['title'] ?? '');
    $first_name = clean($_POST['first_name'] ?? '');
    $last_name = clean($_POST['last_name'] ?? '');
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $telephone = clean($_POST['telephone'] ?? '');
    $company = clean($_POST['company'] ?? '');
    $type = clean($_POST['type'] ?? '');
    $assigned_to = intval($_POST['assigned to']) ?? '';
    $created_by = $_SESSION['user_id'] ?? 1;   // logged-in user 

    // Validate input
    if (!$title || !$first_name || !$last_name ||!$email || !$telephone || !$company || !$type || !$assigned_to){
        $errors[] = "All fields are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Invalid email address.";
    }
    if (!$assigned_to){
        $errors[] = "Assigned user required.";
    }

    // Check if email already existed
    $stmt = $pdo->prepare("SELECT COUNT(*)FROM users  WHERE email = :email"); 
    $stmt->execute(['email' => $email]);
    if ($stmt->fetchColumn() > 0){
        $errors[] = "Email already exists.";
    }

    // Insert contacts into database
    if (empty($errors)){
        $stmt = $pdo->prepare("
        INSERT INTO contacts (title, first_name, last_name, email, telephone, company, type, assigned_to, created_by, created_at, updated_at)
        VALUES (:title, :first_name, :last_name, :email, :telephone, :company, :type, :assigned_to, :created_by, :created_at, :updated_at)
        ");

        if ($stmt->execute([
            'title' => $title, 
            'first_name' => $first_name, 
            'last-name' => $last_name, 
            'email' => $email, 
            'telephone' => $telephone,
            'company' => $company, 
            'type' => $type, 
            'assigned_to' => $assigned_to,
            'created_by' => $created_by,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])){
            $success = "Contact added successfully.";
        }else{
            $errors[] = "Failed to add contact.";
        }
    }
}

// Fetch users from drop-down
$users = $pdo->query("SELECT id, first_name, last_name FROM users")->fetchAll(PDO::FETCH_ASSOC);

?>