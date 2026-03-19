<?php
 
require 'config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username)) {
        $errors[] = 'Benutzername ist erforderlich';
    }
    if (empty($password)) {
        $errors[] = 'Passwort ist erforderlich';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('SELECT id, username, password, role, name FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && verifyPassword($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                
                logAction('LOGIN', "Benutzer angemeldet");
                redirect('dashboard.php');
            } else {
                $errors[] = 'Benutzername oder Passwort ungültig';
            }
        } catch (Exception $e) {
            $errors[] = 'Fehler: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo APP_NAME; ?> - Login</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/responsive.css">
    </head>
    <style>
        body { margin: 0; padding: 0; }
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px 36px;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 340px;
            border-top: 3px solid #1a7a4a;
        }
    </style>
    <body>
        <div class="login-wrapper">
            <div class="login-container">
                <div class="login-header">
                    <div class="login-logo">🚀</div>
                    <h1 class="login-title"><?php echo APP_NAME; ?></h1>
                    <p class="login-subtitle">Projekt Management System</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <span>❌</span>
                        <div>
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo htmlspecialchars($error); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="login-form needs-validation" id="login-form">
                    <div class="form-group">
                        <label for="username">Benutzername</label>
                        <input type="text" id="username" name="username" required 
                            value="<?php echo htmlspecialchars($username); ?>"
                            placeholder="admin">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="password">Passwort</label>
                        <input type="password" id="password" name="password" required
                            placeholder="password123">
                        <div class="invalid-feedback"></div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: var(--spacing-md);">
                        🔓 Anmelden
                    </button>
                </form>

                <div class="demo-box">
                    <p><strong>Admin:</strong> admin / password123</p>
                    <p><strong>Leiter:</strong> lp1 / password123</p>
                    <p><strong>Benutzer:</strong> user1 / password123</p>
                </div>
            </div>
        </div>

    <script src="js/validation.js"></script>
    <script src="js/app.js"></script>
</body>
</html>