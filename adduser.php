<?php
session_start();
//Karnardia Simpson

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

// Admins add users
if ($_SESSION['role'] !== 'Admin') {
    die("Access denied. Only administrators can add new users.");
}

$success = '';
$error = '';
$showForm = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim(filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_SPECIAL_CHARS));
    $lastname  = trim(filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_SPECIAL_CHARS));
    $email     = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? 'Member';

    if (!$firstname || !$lastname || !$email || !$password) {
        $error = "All fields are required.";
    } elseif (!in_array($role, ['Admin', 'Member'])) {
        $error = "Invalid role selected.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/', $password)) {
        $error = "Password requirements: Minimum 8 characters, 1 uppercase letter, 1 lowercase letter, and 1 number.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $conn->prepare(
                "INSERT INTO users (firstname, lastname, email, password, role) VALUES (:firstname, :lastname, :email, :password, :role)");
            
                $stmt->execute([
                ':firstname' => $firstname,
                ':lastname'  => $lastname,
                ':email'     => $email,
                ':password'  => $hashedPassword,
                ':role'      => $role
            ]);
            $success = "User added successfully.";
            $showForm = false;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Email already exists.";
            } else {
                $error = "Database error occurred.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add User - Dolphin CRM</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #f5f5f5;
    }
    .header {
        background: #1a1a2e;
        color: white;
        padding: 15px 30px;
        display: flex;
        align-items: center;
    }
    .header-icon { width: 24px; height: 24px; background: #4a9eff; border-radius: 50%; margin-right: 10px; }
    .header h1 { font-size: 18px; font-weight: 400; }

    .container { display: flex; min-height: calc(100vh - 54px); }
    .sidebar {
        width: 250px;
        background: white;
        padding: 30px 0;
        box-shadow: 2px 0 5px rgba(0,0,0,0.05);
    }
    .sidebar a {
        display: flex;
        align-items: center;
        padding: 15px 30px;
        color: #333;
        text-decoration: none;
        transition: background 0.2s;
    }
    .sidebar a:hover { background: #f5f5f5; }
    .sidebar a.active { background: #e3f2fd; border-left: 3px solid #2b5ce6; }
    .sidebar-icon { width: 20px; margin-right: 15px; font-size: 18px; }

    .main-content { flex: 1; padding: 40px; }
    .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .dashboard-header h2 { font-size: 32px; font-weight: 400; color: #333; }

    form { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 500px; }
    input, select, button { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 4px; border: 1px solid #ccc; }
    button { background: #2b5ce6; color: white; border: none; cursor: pointer; }
    button:hover { background: #1e4bcc; }

    .success { color: green; margin-bottom: 15px; }
    .error { color: red; margin-bottom: 15px; }
    .options { display: flex; gap: 15px; margin-top: 15px; }
    .options a { flex: 1; text-align: center; text-decoration: none; padding: 10px; border-radius: 4px; background: #2b5ce6; color: white; }
    .options a:hover { background: #1e4bcc; }
</style>
</head>
<body>

<div class="header">
    <div class="header-icon"></div>
    <h1>🐬 Dolphin CRM</h1>
</div>

<div class="container">
    <nav class="sidebar">
        <a href="dashboard.php"><span class="sidebar-icon">🏠</span>Home</a>
        <a href="new_contact.php"><span class="sidebar-icon">➕</span>New Contact</a>
        <a href="users.php" class="active"><span class="sidebar-icon">👥</span>Users</a>
        <a href="logout.php"><span class="sidebar-icon">🚪</span>Logout</a>
    </nav>

    <main class="main-content">
        <div class="dashboard-header">
            <h2>Add User</h2>
        </div>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
            <div class="options">
                <a href="adduser.php">➕ Add Another User</a>
                <a href="users.php">👥 Go to Users Page</a>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($showForm): ?>
        <form method="POST">
            <input type="text" name="firstname" placeholder="First Name" required>
            <input type="text" name="lastname" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required>
                <option value="Member">Member</option>
                <option value="Admin">Admin</option>
            </select>
            <button type="submit">Add User</button>
        </form>
        <?php endif; ?>
    </main>
</div>

</body>
</html>
