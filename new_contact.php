<!--Create new contact.php-->
<?php 
//Created by Marissa O'Meallly
session_start();
require_once 'db.php';

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
            'last_name' => $last_name, 
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
$users = $pdo->query("SELECT id, first_name, last_name FROM users")->fetchAll();

?>

<!DOCTYPE html> 
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>New Contact - Dolphin CRM </title> 
         <style>
            /* Add your own styles */
            * { 
                margin: 0; 
                padding: 0; 
                box-sizing: border-box; 
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: #f5f5f5;
            }

            /*Header Style*/
            header {
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

            header h1 { 
                font-size: 18px; 
                font-weight: 400; 
            }

            .container { 
                display: flex; 
                min-height: calc(100vh - 54px); 
            }

            /*Sidebar Style*/
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

            /* Main Style*/
            .main-content { 
                flex: 1; 
                padding: 40px; 
            }

            .contact-header {
                display: flex; 
                justify-content: space-between; 
                align-items: center; 
                margin-bottom: 30px; 
                }

            .contact-header h2 { 
                font-size: 32px; 
                font-weight: bold; 
                color: #333; 
            }

            /*Form Style*/
            .contact-form { 
                background: white;
                padding: 30px; 
                border-radius: 8px; 
                box-shadow: 0 2px 4px rgba(0,0,0,0.05); 
                max-width: 100%; 

                display: grid;
                grid-template-columns: 1fr 1fr;
                column-gap: 24px;
                row-gap: 20px;
            }

            .contact-form .input-group:nth-child(1),   /* Title */
            .contact-form .input-group:nth-child(13) { /* Assigned To */
            grid-column: span 2;
            }

            /*Labels*/
            .input-group label{
                margin-bottom: 6px;
                display: block;
            }

            .form-control, .form-select{ 
                width: 100%; 
                padding: 12px;     
                border-radius: 4px; 
                border: 1px solid #ccc; 
            }

            #title{
                width: 8%;
                padding: 8px 10px;
            }

            /*Inputs and Selects*/
            button { 
                width: 10%;
                background: #2b5ce6; 
                color: white;
                padding: 10px 18px; 
                border: 1px solid #ccc;
                border-radius: 4px;
                border: none; 
                cursor: pointer;

                grid-column: span 2;
                justify-self: end;
            }

            button:hover { 
                background: #1e4bcc; 
            }
         </style>
    </head>
    <body>
        <header>
            <div class="header-icon"></div>
                <h1>🐬 Dolphin CRM </h1> 
        </header>

        <div class="container">
            <nav class="sidebar">
                <a href="dashboard.php"><span class="sidebar-icon">🏠</span>Home</a>
                <a href="new_contact.php"><span class="sidebar-icon">➕</span>New Contact</a>
                <a href="users.php" class="active"><span class="sidebar-icon">👥</span>Users</a>
                <a href="logout.php"><span class="sidebar-icon">🚪</span>Logout</a>
            </nav>

        <main class="main-content">
            <div class="contact-header">
                <h2>New Contact</h2>
            </div>

            <form action="#" method="POST" class="contact-form">
            <div class="input-group">
                <label for="title">Title</label>
                <select name="title" id="title" class="form-select">
                    <option value="mr">Mr</option>
                    <option value="mrs">Mrs</option>
                    <option value="ms">Ms</option>
                    <option value="dr">Dr</option>
                    <option value="prof">Prof</option>
                </select>
            </div>
            
            <div class="input-group">
                <label for="first_name">First Name</label>
                <input 
                    type="text" 
                    name="first_name" 
                    id="first_name" 
                    placeholder="Enter first name" 
                    class="form-control"
                />
            </div>

            <div class="input-group">
                <label for="last_name">Last Name</label>
                    <input 
                        type="text" 
                        name="last_name" 
                        id="last_name" 
                        placeholder="Enter last name"
                        class="form-control"
                    />
            </div>

            <div class="input-group">
                <label for="email">Email</label>
                <input 
                    type="text" 
                    name="email" 
                    id="email" 
                    placeholder="Enter email address"
                    class="form-control"
                />                    
            </div>

            <div class="input-group">
                <label for="telephone">Telephone</label>
                    <input 
                        type="text" 
                        name="telephone" 
                        id="telephone" 
                        placeholder="Enter phone number"
                        class="form-control"
                    />   
            </div>

            <div class="input-group">
                <label for="company">Company</label>
                <input 
                    type="text"
                    name="company"
                    id="company"
                    placeholder="Enter a company"
                    class="form-control"
                />
            </div>

            <div class="input-group">
                <label for="type">Type</label>
                <select name="type" id="type" class="form-select">
                    <option value="sales_lead">Sales Lead</option>
                    <option value="support">Support</option>
                </select> 
            </div>

            <div class="input-group">
                <label for="assigned_to">Assigned To</label>
                <select name="assigned_to" id="assigned_to" class="form-select">
                    <option value="">Assign To</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $users['id'] ?>">
                            <?= $users['first_name'] . " " . $users['last_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div> 
            
            <button type="submit">Save</button>
            
            </form>
        </main>
    </body>
</html>