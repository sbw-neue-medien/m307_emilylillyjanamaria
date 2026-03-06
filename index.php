<?php
/**
 * Login Page
 * BBK Modul 307 - Interaktive Website
 */

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
    <link rel="stylesheet" href="htdocs/css/style.css">
    <link rel="stylesheet" href="htdocs/css/responsive.css">
    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: var(--spacing-lg);
        }

        .login-container {
            background: white;
            padding: var(--spacing-2xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
        }

        .login-logo {
            font-size: 48px;
            margin-bottom: var(--spacing-md);
        }

        .login-title {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: var(--primary-color);
        }

        .login-subtitle {
            color: var(--gray-color);
            margin-top: var(--spacing-sm);
        }

        .login-form .form-group {
            margin-bottom: var(--spacing-lg);
        }

        .login-button {
            width: 100%;
            padding: var(--spacing-md);
            font-size: var(--font-size-lg);
        }

        .demo-box {
            background: #e3f2fd;
            padding: var(--spacing-lg);
            border-radius: var(--radius-md);
            margin-top: var(--spacing-xl);
            border-left: 4px solid var(--info-color);
        }

        .demo-box h4 {
            color: var(--info-color);
            font-size: var(--font-size-base);
            margin-bottom: var(--spacing-sm);
        }

        .demo-box p {
            font-size: 12px;
            color: #0d47a1;
            margin: 3px 0;
        }
    </style>
</head>
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
                <h4>📋 Demo Zugangsdaten:</h4>
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