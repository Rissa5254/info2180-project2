<?php


session_start();




// Handle login if POST request (before any output from HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    require_once 'db.php';
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit();
    }
    
    try {
        $stmt = $conn->prepare("SELECT id, firstname, lastname, email, password, role FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['lastname'] = $user['lastname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            echo json_encode(['success' => true]);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            exit();
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
        exit();
    }
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dolphin CRM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 50%, #90caf9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .login-box {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 40px;
        }
        .login-box h2 {
            text-align: center;
            font-size: 28px;
            font-weight: 400;
            margin-bottom: 30px;
            color: #333;
        }
        form {
            padding: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
            background: #f5f5f5;
        }
        .form-group input:focus {
            outline: none;
            border-color: #4a9eff;
            background: white;
        }
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
            border-left: 4px solid #c33;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #2b5ce6;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-login:hover {
            background: #1e4bcc;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #333;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-icon"></div>
        <h1>🐬 Dolphin CRM</h1>
    </div>
    
    <div class="main-content">
        <div class="login-container">
            <div class="login-box">
                <h2>Login</h2>
                
                <form id="loginForm" method="POST">
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div id="error-message" class="error-message"></div>
                    
                    <button type="submit" class="btn-login">Login</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="footer">
        Copyright © 2022 Dolphin CRM
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('error-message');
            
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'login.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            window.location.href = 'dashboard.php';
                        } else {
                            errorDiv.textContent = response.message;
                            errorDiv.style.display = 'block';
                        }
                    } catch (e) {
                        errorDiv.textContent = 'An error occurred. Please try again.';
                        errorDiv.style.display = 'block';
                        console.error('Parse error:', xhr.responseText);
                    }
                }
            };
            
            xhr.onerror = function() {
                errorDiv.textContent = 'Connection error. Please try again.';
                errorDiv.style.display = 'block';
            };
            
            xhr.send('email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password));
        });
    </script>
</body>
</html>