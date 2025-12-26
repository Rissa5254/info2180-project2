<!--Create view_contact.php-->
<?php 
//Created by Marissa O'Meally
session_start();

require_once 'db.php';

$contact_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'] ?? null;

if (!$contact_id) {
    die("Invalid contact");
}

// Fetch contact details
$sql = "
SELECT c.*,
       u1.firstname AS creator_fn, u1.lastname AS creator_ln,
       u2.firstname AS assigned_fn, u2.lastname AS assigned_ln
FROM contacts c
JOIN users u1 ON c.created_by = u1.id
JOIN users u2 ON c.assigned_to = u2.id
WHERE c.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([$contact_id]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contact) {
    die("Contact not found");
}

// Assign to me
if (isset($_POST['assign_to_me'])) {
    $stmt = $conn->prepare(
        "UPDATE contacts
         SET assigned_to = ?, updated_at = NOW()
         WHERE id = ?"
    );
    $stmt->execute([$user_id, $contact_id]);

    header("Location: view_contact.php?id=$contact_id");
    exit;
}

//Switch the contact type
if (isset($_POST['switch_type'])) {
    $newType = ($contact['type'] === 'sales_lead') ? 'support' : 'sales_lead';

    $stmt = $conn->prepare(
        "UPDATE contacts
         SET type = ?, updated_at = NOW()
         WHERE id = ?"
    );
    $stmt->execute([$newType, $contact_id]);

    header("Location: view_contact.php?id=$contact_id");
    exit;
}

// Add note about users
if (isset($_POST['add_note'])) {
    $comment = htmlspecialchars(trim($_POST['comment'] ?? ''));

    if ($comment) {
        // Prepend contact name automatically
        $comment = "Note about " . $contact['firstname'] . ": " . $comment;

        $stmt = $conn->prepare(
            "INSERT INTO notes
             (contact_id, created_by, comment, created_at)
             VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([$contact_id, $user_id, $comment]);
    }

    header("Location: view_contact.php?id=$contact_id");
    exit;
}

// Fetch notes about users
$stmt = $conn->prepare(
    "SELECT n.comment, n.created_at,
            u.firstname, u.lastname
     FROM notes n
     JOIN users u ON n.created_by = u.id
     WHERE n.contact_id = ?
     ORDER BY n.created_at DESC"
);
$stmt->execute([$contact_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html> 
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>View Contact - Dolphin CRM </title> 
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

            .card {
                background: white;
                padding: 20px;
                border-radius: 6px;
                border: 1px solid #ddd;
                margin-bottom: 30px;
            }

            .grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 45px;
                padding: 20px 15px;
                margin-bottom: 15px;
            }

            .actions {
                display: flex;
                gap: 10px;
            }

            .btn {
                padding: 10px 16px;
                border-radius: 4px;
                border: none;
                cursor: pointer;
                font-weight: 500;
            }

            .btn.green {
                background: #2ecc71;
            }

            .btn.yellow {
                background: #f1c40f;
            }

            .add-note{
                width: 10%;
                background: #2b5ce6; 
                color: white;
                padding: 10px 18px; 
                border: 1px solid #ccc;
                border-radius: 4px;
                border: none; 
                cursor: pointer;

                display: flex;
                justify-self: flex-end;
            }

            .add-note:hover{
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
                <a href="users.php"><span class="sidebar-icon">👥</span>Users</a>
                <a href="logout.php"><span class="sidebar-icon">🚪</span>Logout</a>
            </nav>

        <main class="main-content">
            <div class="contact-header">
                <div>
                    <h2>
                        <?= htmlspecialchars($contact['title']) ?>
                        <?= htmlspecialchars($contact['firstname']) ?>
                        <?= htmlspecialchars($contact['lastname']) ?>
                    </h2>

                    <p class="meta">
                        Created on <?= $contact['created_at'] ?> by <?= $contact['creator_fn']. " " . $contact['creator_ln'] ?>
                    </p>

                    <p class="meta">
                            Updated on <?= $contact['updated_at'] ?>
                    </p>
                </div>
            
                <div class="actions">
                    <form method="POST">
                        <button type="submit" name="assign_to_me" class="btn green">✋ Assign to me</button>
                    </form>
            
                    <form method="POST">
                        <button type="submit" name="switch_type" class="btn yellow">
                            🔁Switch to <?= ($contact['type'] === 'sales_lead') ? 'Support' : 'Sales Lead' ?>   
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="grid">
                    <div>
                        <strong>Email</strong>
                        <p><?= htmlspecialchars($contact['email']) ?></p>
                    </div>

                    <div>
                        <strong>Telephone</strong>
                        <p><?= htmlspecialchars($contact['telephone']) ?></p>
                    </div>

                    <div>
                        <strong>Company</strong>
                        <p><?= htmlspecialchars($contact['company']) ?></p>
                    </div>

                    <div>
                        <strong>Assigned To</strong>
                        <p><?= $contact['assigned_fn']." ".$contact['assigned_ln'] ?></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>📝 Notes</h3>

                <?php if (empty($notes)): ?>
                    <p>No notes yet.</p>
                <?php endif; ?>

                <?php foreach ($notes as $note): ?>
                    <div class="note">
                        <strong><?= $note['firstname'] . " " . $note['lastname'] ?></strong><br>
                        <?= $note['comment'] ?><br>
                        <small><?= $note['created_at'] ?></small>
                    </div>
                <?php endforeach; ?>

                <h4>Add a note</h4>
                <form method="POST">
                    <textarea name="comment" id="notes" placeholder="Enter details here" required style="width: 100%; height: 80px;"></textarea><br /><br />
                    <button type="submit" name="add_note" class="add-note">Add note</button>
                </form>            
            </div>
        </main> 
        </div>
    </body>
</html>