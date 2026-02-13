<?php
/**
 * USER STORY 2: Auf Kundendaten (Telefon / Adresse) zugreifen
 * BBK Anforderung: Formulardesign und Validierung
 */

require '../config.php';

if (!isLoggedIn()) redirect('../index.php');
if (!hasPermission('view_customer_data')) die('❌ Keine Berechtigung!');

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $postal_code = sanitize($_POST['postal_code'] ?? '');
    $contact_person = sanitize($_POST['contact_person'] ?? '');
    
    $validationErrors = [];
    
    // Server-seitige Validierung
    if (empty($name)) $validationErrors[] = 'Name ist erforderlich';
    if (empty($email)) $validationErrors[] = 'E-Mail ist erforderlich';
    elseif (!validateEmail($email)) $validationErrors[] = 'Ungültige E-Mail';
    if (!empty($phone) && !validatePhone($phone)) $validationErrors[] = 'Ungültige Telefon';
    if (strlen($name) < 3 || strlen($name) > 150) $validationErrors[] = 'Name ungültig (3-150 Zeichen)';
    
    if (empty($validationErrors)) {
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare('
                    INSERT INTO customers (name, email, phone, address, city, postal_code, contact_person) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([$name, $email, $phone, $address, $city, $postal_code, $contact_person]);
                $message = '✅ Kunde hinzugefügt!';
                logAction('CUSTOMER_ADD', "Kunde '$name' hinzugefügt");
            } 
            elseif ($action === 'edit') {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare('
                    UPDATE customers SET name = ?, email = ?, phone = ?, address = ?, city = ?, postal_code = ?, contact_person = ? 
                    WHERE id = ?
                ');
                $stmt->execute([$name, $email, $phone, $address, $city, $postal_code, $contact_person, $id]);
                $message = '✅ Kunde aktualisiert!';
                logAction('CUSTOMER_EDIT', "Kunde '$name' aktualisiert");
            } 
            elseif ($action === 'delete') {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare('DELETE FROM customers WHERE id = ?');
                $stmt->execute([$id]);
                $message = '✅ Kunde gelöscht!';
                logAction('CUSTOMER_DELETE', "Kunde gelöscht");
            }
        } catch (Exception $e) {
            $error = 'Fehler: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $validationErrors);
    }
}

// Kunden laden
try {
    $stmt = $pdo->query('SELECT * FROM customers ORDER BY name');
    $customers = $stmt->fetchAll();
} catch (Exception $e) {
    $customers = [];
    $error = 'Fehler beim Laden: ' . $e->getMessage();
}

$editCustomer = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM customers WHERE id = ?');
    $stmt->execute([$id]);
    $editCustomer = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kundenverwaltung - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
    <div class="wrapper">
        <nav class="navbar">
            <div class="container">
                <div class="navbar-brand">
                    <span>👥</span>
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
                <h1>👥 Kundenverwaltung</h1>
                <p style="color: var(--gray-color); margin-bottom: var(--spacing-xl);">Verwalten Sie Ihre Kundendaten zentral.</p>

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
                    <h2><?php echo $editCustomer ? '✏️ Kunde bearbeiten' : '➕ Neuer Kunde'; ?></h2>
                    
                    <form method="POST" class="needs-validation" id="customer-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Name <span style="color: var(--danger-color);">*</span></label>
                                <input type="text" name="name" id="name" required minlength="3" maxlength="150"
                                       value="<?php echo $editCustomer['name'] ?? ''; ?>">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group">
                                <label for="email">E-Mail <span style="color: var(--danger-color);">*</span></label>
                                <input type="email" name="email" id="email" required
                                       value="<?php echo $editCustomer['email'] ?? ''; ?>">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group">
                                <label for="phone">Telefon</label>
                                <input type="tel" name="phone" id="phone"
                                       value="<?php echo $editCustomer['phone'] ?? ''; ?>"
                                       placeholder="+49 30 123456">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="address">Adresse</label>
                                <input type="text" name="address" id="address"
                                       value="<?php echo $editCustomer['address'] ?? ''; ?>">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group">
                                <label for="city">Stadt</label>
                                <input type="text" name="city" id="city"
                                       value="<?php echo $editCustomer['city'] ?? ''; ?>">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group">
                                <label for="postal_code">Postleitzahl</label>
                                <input type="text" name="postal_code" id="postal_code" maxlength="10"
                                       value="<?php echo $editCustomer['postal_code'] ?? ''; ?>">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact_person">Kontaktperson</label>
                            <input type="text" name="contact_person" id="contact_person"
                                   value="<?php echo $editCustomer['contact_person'] ?? ''; ?>">
                            <div class="invalid-feedback"></div>
                        </div>

                        <input type="hidden" name="action" value="<?php echo $editCustomer ? 'edit' : 'add'; ?>">
                        <?php if ($editCustomer): ?>
                            <input type="hidden" name="id" value="<?php echo $editCustomer['id']; ?>">
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary">
                            <?php echo $editCustomer ? '💾 Aktualisieren' : '✅ Hinzufügen'; ?>
                        </button>
                        <?php if ($editCustomer): ?>
                            <a href="manage_customers.php" class="btn btn-secondary">Abbrechen</a>
                        <?php endif; ?>
                    </form>
                </div>

                <h2 style="margin-top: var(--spacing-2xl); margin-bottom: var(--spacing-lg);">Kundenliste</h2>
                <input type="text" id="customer-search" data-table-search="customers-table" 
                       placeholder="🔍 Kunden durchsuchen..." style="max-width: 300px; margin-bottom: var(--spacing-lg);">

                <div class="table-responsive">
                    <table id="customers-table" class="card">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>E-Mail</th>
                                <th>Telefon</th>
                                <th>Adresse</th>
                                <th>Stadt</th>
                                <th style="width: 150px;">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($customer['name']); ?></strong></td>
                                    <td><a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>"><?php echo htmlspecialchars($customer['email']); ?></a></td>
                                    <td><a href="tel:<?php echo htmlspecialchars($customer['phone']); ?>"><?php echo htmlspecialchars($customer['phone']); ?></a></td>
                                    <td><?php echo htmlspecialchars($customer['address']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['city']); ?></td>
                                    <td>
                                        <a href="?edit=<?php echo $customer['id']; ?>" class="btn btn-warning btn-small">✏️ Bearbeiten</a>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $customer['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-danger btn-small" 
                                                    onclick="return confirmDelete('diesen Kunden');">🗑️ Löschen</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($customers)): ?>
                    <div style="text-align: center; padding: var(--spacing-2xl); color: var(--gray-color);">
                        <p>📭 Keine Kunden vorhanden</p>
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