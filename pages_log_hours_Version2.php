<?php
/**
 * USER STORY 3: Stunden in ein Projekt erfassen
 * BBK Anforderung: Datenbankgestütztes Formular mit JavaScript & PHP Validierung
 */

require '../config.php';

if (!isLoggedIn()) redirect('../index.php');
if (!hasPermission('log_hours')) die('❌ Keine Berechtigung!');

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = (int)$_POST['project_id'];
    $hours = (float)$_POST['hours'];
    $date = sanitize($_POST['date'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    
    $validationErrors = [];
    
    // Validierung
    if ($project_id <= 0) $validationErrors[] = 'Projekt ist erforderlich';
    if ($hours <= 0 || $hours > 24) $validationErrors[] = 'Stunden müssen zwischen 0.5 und 24 liegen';
    if (empty($date)) $validationErrors[] = 'Datum ist erforderlich';
    if (!validateDate($date)) $validationErrors[] = 'Ungültiges Datumsformat';
    
    if (empty($validationErrors)) {
        try {
            // Prüfe ob bereits vorhanden
            $checkStmt = $pdo->prepare('SELECT id FROM hours WHERE user_id = ? AND project_id = ? AND date = ?');
            $checkStmt->execute([$_SESSION['user_id'], $project_id, $date]);
            
            if ($checkStmt->rowCount() > 0) {
                $error = 'Für dieses Projekt und Datum existiert bereits ein Eintrag!';
            } else {
                $stmt = $pdo->prepare('
                    INSERT INTO hours (user_id, project_id, hours, date, description) 
                    VALUES (?, ?, ?, ?, ?)
                ');
                $stmt->execute([$_SESSION['user_id'], $project_id, $hours, $date, $description]);
                $message = '✅ Stunden erfolgreich erfasst!';
                logAction('HOURS_LOG', "$hours Stunden zu Projekt $project_id hinzugefügt");
            }
        } catch (Exception $e) {
            $error = 'Fehler: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $validationErrors);
    }
}

// Projekte laden
try {
    $stmt = $pdo->query('SELECT id, name FROM projects WHERE status != "completed" ORDER BY name');
    $projects = $stmt->fetchAll();
} catch (Exception $e) {
    $projects = [];
    $error = 'Fehler beim Laden: ' . $e->getMessage();
}

// Erfasste Stunden laden
try {
    $stmt = $pdo->prepare('
        SELECT h.*, p.name as project_name 
        FROM hours h 
        JOIN projects p ON h.project_id = p.id 
        WHERE h.user_id = ? 
        ORDER BY h.date DESC 
        LIMIT 30
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $hours_log = $stmt->fetchAll();
} catch (Exception $e) {
    $hours_log = [];
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stunden erfassen - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
    <div class="wrapper">
        <nav class="navbar">
            <div class="container">
                <div class="navbar-brand">
                    <span>⏱️</span>
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
                <h1>⏱️ Stunden erfassen</h1>
                <p style="color: var(--gray-color); margin-bottom: var(--spacing-xl);">Erfassen Sie Ihre geleisteten Arbeitsstunden.</p>

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

                <div class="form-section">
                    <h2>➕ Neue Stundenerfassung</h2>
                    
                    <form method="POST" class="needs-validation" id="hours-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="project_id">Projekt <span style="color: var(--danger-color);">*</span></label>
                                <select name="project_id" id="project_id" required>
                                    <option value="">-- Projekt wählen --</option>
                                    <?php foreach ($projects as $project): ?>
                                        <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Projekt ist erforderlich</div>
                            </div>

                            <div class="form-group">
                                <label for="date">Datum <span style="color: var(--danger-color);">*</span></label>
                                <input type="date" name="date" id="date" required value="<?php echo date('Y-m-d'); ?>">
                                <div class="invalid-feedback">Datum ist erforderlich</div>
                            </div>

                            <div class="form-group">
                                <label for="hours">Stunden <span style="color: var(--danger-color);">*</span></label>
                                <input type="number" name="hours" id="hours" min="0.5" max="24" step="0.5" required placeholder="z.B. 8">
                                <div class="invalid-feedback">Stunden zwischen 0.5 und 24</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Beschreibung</label>
                            <textarea name="description" id="description" placeholder="Was haben Sie gemacht?"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <button type="submit" class="btn btn-primary">✅ Stunden speichern</button>
                    </form>
                </div>

                <h2 style="margin-top: var(--spacing-2xl); margin-bottom: var(--spacing-lg);">Meine erfassten Stunden</h2>

                <div class="table-responsive">
                    <table class="card">
                        <thead>
                            <tr>
                                <th>Datum</th>
                                <th>Projekt</th>
                                <th style="width: 80px;">Stunden</th>
                                <th>Beschreibung</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalHours = 0;
                            foreach ($hours_log as $log): 
                                $totalHours += $log['hours'];
                            ?>
                                <tr>
                                    <td><strong><?php echo App::formatDate($log['date']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($log['project_name']); ?></td>
                                    <td style="text-align: center; font-weight: bold;"><?php echo $log['hours']; ?>h</td>
                                    <td><?php echo htmlspecialchars(substr($log['description'], 0, 60)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($hours_log)): ?>
                    <div style="text-align: center; padding: var(--spacing-2xl); color: var(--gray-color);">
                        <p>📭 Keine Einträge vorhanden</p>
                    </div>
                <?php else: ?>
                    <div style="margin-top: var(--spacing-lg); padding: var(--spacing-lg); background: var(--light-color); border-radius: var(--radius-md); text-align: right;">
                        <strong>Gesamtstunden: <?php echo number_format($totalHours, 2); ?>h</strong>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <footer>
            <p>&copy; 2026 <?php echo APP_NAME; ?>