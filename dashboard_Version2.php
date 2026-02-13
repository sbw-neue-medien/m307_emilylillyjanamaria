<?php
/**
 * Dashboard
 */

require 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <div class="wrapper">
        <nav class="navbar">
            <div class="container">
                <div class="navbar-brand">
                    <span>🚀</span>
                    <span><?php echo APP_NAME; ?></span>
                </div>
                <div class="navbar-menu">
                    <div class="user-info">
                        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?> (<?php echo ucfirst($_SESSION['role']); ?>)</span>
                        <a href="logout.php" class="logout-btn">Abmelden</a>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <div class="container">
                <div style="margin-bottom: var(--spacing-2xl);">
                    <h1>👋 Willkommen, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
                    <p style="color: var(--gray-color); margin-top: var(--spacing-md);">Wählen Sie eine Funktion aus, um zu beginnen.</p>
                </div>

                <div class="grid-3">
                    <?php if (hasPermission('manage_permissions')): ?>
                        <div class="card">
                            <div style="padding: var(--spacing-xl); text-align: center;">
                                <div style="font-size: 40px; margin-bottom: var(--spacing-lg);">🔐</div>
                                <h3>Benutzerrechte</h3>
                                <p style="color: var(--gray-color); font-size: 12px; margin: var(--spacing-md) 0;">Verwalten Sie Berechtigungen</p>
                                <a href="pages/manage_permissions.php" class="btn btn-primary" style="width: 100%; margin-top: var(--spacing-lg);">Öffnen →</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (hasPermission('view_customer_data')): ?>
                        <div class="card">
                            <div style="padding: var(--spacing-xl); text-align: center;">
                                <div style="font-size: 40px; margin-bottom: var(--spacing-lg);">👥</div>
                                <h3>Kundenverwaltung</h3>
                                <p style="color: var(--gray-color); font-size: 12px; margin: var(--spacing-md) 0;">Verwalten Sie Kundendaten</p>
                                <a href="pages/manage_customers.php" class="btn btn-primary" style="width: 100%; margin-top: var(--spacing-lg);">Öffnen →</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (hasPermission('log_hours')): ?>
                        <div class="card">
                            <div style="padding: var(--spacing-xl); text-align: center;">
                                <div style="font-size: 40px; margin-bottom: var(--spacing-lg);">⏱️</div>
                                <h3>Stunden erfassen</h3>
                                <p style="color: var(--gray-color); font-size: 12px; margin: var(--spacing-md) 0;">Erfassen Sie Arbeitsstunden</p>
                                <a href="pages/log_hours.php" class="btn btn-primary" style="width: 100%; margin-top: var(--spacing-lg);">Öffnen →</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (hasPermission('view_all_projects')): ?>
                        <div class="card">
                            <div style="padding: var(--spacing-xl); text-align: center;">
                                <div style="font-size: 40px; margin-bottom: var(--spacing-lg);">📊</div>
                                <h3>Projektstatus</h3>
                                <p style="color: var(--gray-color); font-size: 12px; margin: var(--spacing-md) 0;">Übersicht aller Projekte</p>
                                <a href="pages/project_status.php" class="btn btn-primary" style="width: 100%; margin-top: var(--spacing-lg);">Öffnen →</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div style="padding: var(--spacing-xl); text-align: center;">
                            <div style="font-size: 40px; margin-bottom: var(--spacing-lg);">📧</div>
                            <h3>Kundenanfragen</h3>
                            <p style="color: var(--gray-color); font-size: 12px; margin: var(--spacing-md) 0;">Verwalt Kundenanfragen</p>
                            <a href="pages/customer_inquiry.php" class="btn btn-primary" style="width: 100%; margin-top: var(--spacing-lg);">Öffnen →</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2026 <?php echo APP_NAME; ?> | BBK Modul 307 - Interaktive Website mit Formularen entwickeln</p>
        </footer>
    </div>

    <script src="js/app.js"></script>
</body>
</html>