<?php
/**
 * USER STORY 4: Projektstatus kennen
 * BBK Anforderung: Datenbankgestützte Anzeige mit Status-Updates
 */

require '../config.php';

if (!isLoggedIn()) redirect('../index.php');
if (!hasPermission('view_all_projects')) die('❌ Keine Berechtigung!');

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $status = sanitize($_POST['status']);
    
    $validStatus = ['planning', 'in_progress', 'completed', 'on_hold'];
    
    if (!in_array($status, $validStatus)) {
        $error = 'Ungültiger Status!';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE projects SET status = ? WHERE id = ?');
            $stmt->execute([$status, $id]);
            $message = '✅ Status aktualisiert!';
            logAction('PROJECT_STATUS', "Projektstatus aktualisiert auf $status");
        } catch (Exception $e) {
            $error = 'Fehler: ' . $e->getMessage();
        }
    }
}

// Projekte mit Statistiken laden
try {
    $stmt = $pdo->query('
        SELECT 
            p.id, p.name, p.status, p.description, p.budget, c.name as customer_name,
            (SELECT COUNT(*) FROM hours WHERE project_id = p.id) as total_entries,
            (SELECT SUM(hours) FROM hours WHERE project_id = p.id) as sum_hours
        FROM projects p
        LEFT JOIN customers c ON p.customer_id = c.id
        ORDER BY p.created_at DESC
    ');
    $projects = $stmt->fetchAll();
} catch (Exception $e) {
    $projects = [];
    $error = 'Fehler beim Laden: ' . $e->getMessage();
}

$statusTexts = [
    'planning' => '📋 Planung',
    'in_progress' => '⚙️ In Bearbeitung',
    'completed' => '✅ Abgeschlossen',
    'on_hold' => '⏸️ Pause'
];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projektstatus - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
    <div class="wrapper">
        <nav class="navbar">
            <div class="container">
                <div class="navbar-brand">
                    <span>📊</span>
                    <span><?php echo APP_NAME; ?></span>
                </div>
                <div class="navbar-menu">
                    <a href="../dashboard.php">Dashboard</a>
                    <div class="user-info">
                        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <a href="../logout.php" class="logout-btn">Abmelden</a>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <div class="container">
                <h1>📊 Projektstatus</h1>
                <p style="color: var(--gray-color); margin-bottom: var(--spacing-xl);">Übersicht aller Projekte und deren Status.</p>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success">
                        <span>✅</span>
                        <span><?php echo $message; ?></span>
                        <span class="alert-close" onclick="this.parentElement.remove()">×</span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <span>❌</span>
                        <span><?php echo $error; ?></span>
                        <span class="alert-close" onclick="this.parentElement.remove()">×</span>
                    </div>
                <?php endif; ?>

                <div class="grid-2">
                    <?php foreach ($projects as $project): ?>
                        <div class="form-section" style="border-left-color: var(--primary-color);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: var(--spacing-lg);">
                                <div>
                                    <h3><?php echo htmlspecialchars($project['name']); ?></h3>
                                    <p style="color: var(--gray-color); font-size: 12px; margin-top: 5px;">
                                        <?php echo htmlspecialchars(substr($project['description'], 0, 80)); ?>
                                    </p>
                                </div>
                                <span class="status-badge status-<?php echo $project['status']; ?>">
                                    <?php echo $statusTexts[$project['status']]; ?>
                                </span>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; padding: 15px 0; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); margin-bottom: 15px;">
                                <div>
                                    <div style="font-size: 12px; color: var(--gray-color);">👤 Kunde</div>
                                    <div style="font-weight: bold; margin-top: 5px;"><?php echo htmlspecialchars($project['customer_name'] ?: 'N/A'); ?></div>
                                </div>
                                <div>
                                    <div style="font-size: 12px; color: var(--gray-color);">💰 Budget</div>
                                    <div style="font-weight: bold; margin-top: 5px;"><?php echo number_format($project['budget'] ?? 0, 2); ?>€</div>
                                </div>
                                <div>
                                    <div style="font-size: 12px; color: var(--gray-color);">📝 Einträge</div>
                                    <div style="font-weight: bold; margin-top: 5px;"><?php echo $project['total_entries'] ?? '0'; ?></div>
                                </div>
                                <div>
                                    <div style="font-size: 12px; color: var(--gray-color);">⏰ Stunden</div>
                                    <div style="font-weight: bold; margin-top: 5px;"><?php echo number_format($project['sum_hours'] ?? 0, 2); ?>h</div>
                                </div>
                            </div>

                            <form method="POST" style="display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: flex-end;">
                                <div>
                                    <label style="font-size: 12px; margin-bottom: 5px;">Status ändern:</label>
                                    <select name="status" required style="margin-top: 5px;">
                                        <option value="planning" <?php if($project['status'] == 'planning') echo 'selected'; ?>>📋 Planung</option>
                                        <option value="in_progress" <?php if($project['status'] == 'in_progress') echo 'selected'; ?>>⚙️ In Bearbeitung</option>
                                        <option value="on_hold" <?php if($project['status'] == 'on_hold') echo 'selected'; ?>>⏸️ Pause</option>
                                        <option value="completed" <?php if($project['status'] == 'completed') echo 'selected'; ?>>✅ Abgeschlossen</option>
                                    </select>
                                </div>
                                <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                                <button type="submit" class="btn btn-primary" style="margin: 0;">💾 Speichern</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($projects)): ?>
                    <div style="text-align: center; padding: var(--spacing-2xl); color: var(--gray-color);">
                        <p>📭 Keine Projekte vorhanden</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <footer>
            <p>&copy; 2026 <?php echo APP_NAME; ?> | BBK Modul 307</p>
        </footer>
    </div>

    <script src="../js/validation.js"></script>
    <script src="../js/app.js"></script>
</body>
</html>