<?php
/**
 * USER STORY 5: Kundenanfrage erfassen
 * BBK Anforderung: Formular mit Validierung und Datenbankenspeicherung
 */

require '../config.php';

if (!isLoggedIn()) redirect('../index.php');

$message = '';
$error = '';

// Kundenanfrage hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_inquiry'])) {
    $customer_id = (int)$_POST['customer_id'];
    $subject = sanitize($_POST['subject'] ?? '');
    $inquiry_message = sanitize($_POST['inquiry_message'] ?? '');
    $priority = sanitize($_POST['priority'] ?? 'medium');
    
    $validationErrors = [];
    
    // Validierung
    if ($customer_id <= 0) $validationErrors[] = 'Kunde ist erforderlich';
    if (empty($subject)) $validationErrors[] = 'Betreff ist erforderlich';
    if (strlen($subject) < 3 || strlen($subject) > 200) $validationErrors[] = 'Betreff: 3-200 Zeichen';
    if (empty($inquiry_message)) $validationErrors[] = 'Nachricht ist erforderlich';
    if (strlen($inquiry_message) < 10) $validationErrors[] = 'Nachricht: mindestens 10 Zeichen';
    if (!in_array($priority, ['low', 'medium', 'high'])) $validationErrors[] = 'Ungültige Priorität';
    
    if (empty($validationErrors)) {
        try {
            $stmt = $pdo->prepare('
                INSERT INTO customer_inquiries (customer_id, subject, message, priority, status) 
                VALUES (?, ?, ?, ?, "new")
            ');
            $stmt->execute([$customer_id, $subject, $inquiry_message, $priority]);
            $message = '✅ Anfrage erfolgreich erfasst!';
            logAction('INQUIRY_CREATE', "Anfrage für Kunde $customer_id erstellt");
        } catch (Exception $e) {
            $error = 'Fehler: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $validationErrors);
    }
}

// Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $inquiry_id = (int)$_POST['inquiry_id'];
    $status = sanitize($_POST['inquiry_status']);
    
    $validStatus = ['new', 'in_progress', 'resolved', 'closed'];
    
    if (in_array($status, $validStatus)) {
        try {
            $stmt = $pdo->prepare('UPDATE customer_inquiries SET status = ? WHERE id = ?');
            $stmt->execute([$status, $inquiry_id]);
            $message = '✅ Status aktualisiert!';
        } catch (Exception $e) {
            $error = 'Fehler: ' . $e->getMessage();
        }
    }
}

// Kunden laden
try {
    $stmt = $pdo->query('SELECT id, name FROM customers ORDER BY name');
    $customers = $stmt->fetchAll();
} catch (Exception $e) {
    $customers = [];
}

// Anfragen laden
try {
    $stmt = $pdo->query('
        SELECT ci.id, ci.subject, ci.message, ci.status, ci.priority, ci.created_at, c.name as customer_name
        FROM customer_inquiries ci
        JOIN customers c ON ci.customer_id = c.id
        ORDER BY ci.created_at DESC
        LIMIT 50
    ');
    $inquiries = $stmt->fetchAll();
} catch (Exception $e) {
    $inquiries = [];
}

$statusTexts = [
    'new' => '🆕 Neu',
    'in_progress' => '⚙️ In Bearbeitung',
    'resolved' => '✅ Gelöst',
    'closed' => '🔒 Geschlossen'
];

$priorityTexts = [
    'low' => '🟢 Niedrig',
    'medium' => '🟡 Mittel',
    'high' => '🔴 Hoch'
];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kundenanfragen - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
    <div class="wrapper">
        <nav class="navbar">
            <div class="container">
                <div class="navbar-brand">
                    <span>📧</span>
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
                <h1>📧 Kundenanfragen</h1>
                <p style="color: var(--gray-color); margin-bottom: var(--spacing-xl);">Verwalten Sie Anfragen von Ihren Kunden.</p>

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
                    <h2>➕ Neue Anfrage erfassen</h2>
                    
                    <form method="POST" class="needs-validation" id="inquiry-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_id">Kunde <span style="color: var(--danger-color);">*</span></label>
                                <select name="customer_id" id="customer_id" required>
                                    <option value="">-- Kunde wählen --</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Kunde ist erforderlich</div>
                            </div>

                            <div class="form-group">
                                <label for="priority">Priorität</label>
                                <select name="priority" id="priority">
                                    <option value="low">🟢 Niedrig</option>
                                    <option value="medium" selected>🟡 Mittel</option>
                                    <option value="high">🔴 Hoch</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="subject">Betreff <span style="color: var(--danger-color);">*</span></label>
                            <input type="text" name="subject" id="subject" required minlength="3" maxlength="200"
                                   placeholder="Kurze Zusammenfassung der Anfrage">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label for="inquiry_message">Nachricht <span style="color: var(--danger-color);">*</span></label>
                            <textarea name="inquiry_message" id="inquiry_message" required minlength="10" 
                                      placeholder="Detaillierte Beschreibung der Anfrage..." style="min-height: 150px;"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <input type="hidden" name="add_inquiry" value="1">
                        <button type="submit" class="btn btn-primary">📤 Anfrage absenden</button>
                    </form>
                </div>

                <h2 style="margin-top: var(--spacing-2xl); margin-bottom: var(--spacing-lg);">Anfrage-Übersicht</h2>

                <div style="margin-bottom: var(--spacing-lg);">
                    <input type="text" id="inquiry-search" data-table-search="inquiries-table"
                           placeholder="🔍 Anfragen durchsuchen..." style="max-width: 300px;">
                </div>

                <div id="inquiries-table">
                    <?php foreach ($inquiries as $inquiry): ?>
                        <div class="card" style="margin-bottom: var(--spacing-lg);">
                            <div style="padding: var(--spacing-lg); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: start;">
                                <div>
                                    <h3 style="margin-bottom: 5px;">📬 <?php echo htmlspecialchars($inquiry['subject']); ?></h3>
                                    <div style="font-size: 12px; color: var(--gray-color);">
                                        👤 <?php echo htmlspecialchars($inquiry['customer_name']); ?> 
                                        • <?php echo date('d.m.Y H:i', strtotime($inquiry['created_at'])); ?>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <span class="status-badge status-<?php echo $inquiry['status']; ?>">
                                        <?php echo $statusTexts[$inquiry['status']]; ?>
                                    </span>
                                    <span class="badge badge-primary">
                                        <?php echo $priorityTexts[$inquiry['priority']]; ?>
                                    </span>
                                </div>
                            </div>

                            <div style="padding: var(--spacing-lg); border-bottom: 1px solid var(--border-color);">
                                <p><?php echo nl2br(htmlspecialchars($inquiry['message'])); ?></p>
                            </div>

                            <div style="padding: var(--spacing-lg); background: var(--light-color);">
                                <form method="POST" style="display: flex; gap: 10px; align-items: flex-end;">
                                    <div style="flex: 1;">
                                        <label style="font-size: 12px; font-weight: 600;">Status aktualisieren:</label>
                                        <select name="inquiry_status" required style="margin-top: 5px;">
                                            <option value="new" <?php if($inquiry['status'] == 'new') echo 'selected'; ?>>🆕 Neu</option>
                                            <option value="in_progress" <?php if($inquiry['status'] == 'in_progress') echo 'selected'; ?>>⚙️ In Bearbeitung</option>
                                            <option value="resolved" <?php if($inquiry['status'] == 'resolved') echo 'selected'; ?>>✅ Gelöst</option>
                                            <option value="closed" <?php if($inquiry['status'] == 'closed') echo 'selected'; ?>>🔒 Geschlossen</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    <button type="submit" class="btn btn-success" style="margin: 0;">💾 Speichern</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($inquiries)): ?>
                    <div style="text-align: center; padding: var(--spacing-2xl); color: var(--gray-color);">
                        <p>📭 Keine Anfragen vorhanden</p>
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