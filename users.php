<?php
session_start();
//Karnardia Simpson

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

//must be admin to view users - Selena Johnson
if ($_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    die('Forbidden: You do not have permission to access this page.');
}

require_once 'db.php';
//Get Users
try {
    $stmt = $conn->query(" SELECT firstname, lastname, email, role, created_at FROM users ORDER BY created_at DESC ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Dolphin CRM</title>
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
        .header-icon {
            width: 24px;
            height: 24px;
            background: #4a9eff;
            border-radius: 50%;
            margin-right: 10px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 400;
        }
        .container {
            display: flex;
            min-height: calc(100vh - 54px);
        }
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
        .sidebar a:hover {
            background: #f5f5f5;
        }
        .sidebar a.active {
            background: #e3f2fd;
            border-left: 3px solid #2b5ce6;
        }
        .sidebar-icon {
            width: 20px;
            margin-right: 15px;
            font-size: 18px;
        }
        .main-content {
            flex: 1;
            padding: 40px;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .dashboard-header h2 {
            font-size: 32px;
            font-weight: 400;
            color: #333;
        }
        .add-user-btn {
            background: #2b5ce6;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
        }
        .add-user-btn:hover {
            background: #1e4bcc;
        }
        .users-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background: #f8f8f8;
        }
        th {
            padding: 15px 20px;
            text-align: left;
            font-size: 13px;
            text-transform: uppercase;
            color: #666;
        }
        td {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }
    </style>
</head>

<body>

<div class="header">
    <div class="header-icon"></div>
    <h1>🐬 Dolphin CRM</h1>
</div>

<div class="container">
    <nav class="sidebar">
        <a href="dashboard.php">
            <span class="sidebar-icon">🏠</span>
            <span>Home</span>
        </a>
        <a href="new_contact.php">
            <span class="sidebar-icon">➕</span>
            <span>New Contact</span>
        </a>
        <a href="users.php" class="active">
            <span class="sidebar-icon">👥</span>
            <span>Users</span>
        </a>
        <a href="logout.php">
            <span class="sidebar-icon">🚪</span>
            <span>Logout</span>
        </a>
    </nav>

    <main class="main-content">

        <div class="dashboard-header">
            <h2>Users</h2>

            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <a href="adduser.php" class="add-user-btn">➕ Add User</a>
            <?php endif; ?>
        </div>

        <div class="users-table">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <strong>
                                    <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?>
                                </strong>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>
