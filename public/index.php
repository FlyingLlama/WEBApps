<?php
session_start();

$db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("
    CREATE TABLE IF NOT EXISTS company_info (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        company_name TEXT NOT NULL,
        logo_path TEXT,
        city TEXT,
        address TEXT,
        vat_number TEXT,
        iban TEXT,
        bic TEXT,
        bank_name TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS invoices (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_number TEXT NOT NULL UNIQUE,
        client_name TEXT NOT NULL,
        client_address TEXT,
        invoice_date DATE NOT NULL,
        due_date DATE NOT NULL,
        currency TEXT DEFAULT 'USD',
        subtotal DECIMAL(10,2) NOT NULL,
        vat_rate DECIMAL(5,2) DEFAULT 0,
        vat_amount DECIMAL(10,2) DEFAULT 0,
        total DECIMAL(10,2) NOT NULL,
        outstanding_balance DECIMAL(10,2) NOT NULL,
        status TEXT DEFAULT 'pending',
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS invoice_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_id INTEGER NOT NULL,
        description TEXT NOT NULL,
        quantity DECIMAL(10,2) NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (invoice_id) REFERENCES invoices(id)
    )
");

$company_check = $db->query("SELECT COUNT(*) FROM company_info")->fetchColumn();
if ($company_check == 0) {
    $db->exec("INSERT INTO company_info (company_name, city, address, vat_number, iban, bic, bank_name) 
               VALUES ('Your Company Name', 'Your City', 'Your Address', 'VAT123456', 'IBAN123456789', 'BIC123', 'Your Bank')");
}

$request_uri = $_SERVER['REQUEST_URI'];
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$path = str_replace($script_name, '', $request_uri);
$path = parse_url($path, PHP_URL_PATH);

switch ($path) {
    case '/':
    case '/home':
        include __DIR__ . '/../resources/views/home.php';
        break;
    
    case '/create-invoice':
        include __DIR__ . '/../resources/views/create-invoice.php';
        break;
    
    case '/invoices':
        include __DIR__ . '/../resources/views/invoices.php';
        break;
    
    case '/company-settings':
        include __DIR__ . '/../resources/views/company-settings.php';
        break;
    
    case '/store-invoice':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subtotal = 0;
            $items = [];
            
            if (isset($_POST['item_description']) && is_array($_POST['item_description'])) {
                foreach ($_POST['item_description'] as $index => $description) {
                    if (!empty($description)) {
                        $quantity = floatval($_POST['item_quantity'][$index]);
                        $unit_price = floatval($_POST['item_price'][$index]);
                        $item_total = $quantity * $unit_price;
                        $subtotal += $item_total;
                        
                        $items[] = [
                            'description' => $description,
                            'quantity' => $quantity,
                            'unit_price' => $unit_price,
                            'total' => $item_total
                        ];
                    }
                }
            }
            
            $vat_rate = floatval($_POST['vat_rate']);
            $vat_amount = $subtotal * ($vat_rate / 100);
            $total = $subtotal + $vat_amount;
            $outstanding_balance = $total;
            
            $year = date('Y');
            $count = $db->query("SELECT COUNT(*) FROM invoices WHERE strftime('%Y', invoice_date) = '$year'")->fetchColumn();
            $invoice_number = ($count + 1) . '/' . $year;
            $stmt = $db->prepare("INSERT INTO invoices (invoice_number, client_name, client_address, invoice_date, due_date, currency, subtotal, vat_rate, vat_amount, total, outstanding_balance, notes) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $invoice_number,
                $_POST['client_name'],
                $_POST['client_address'],
                $_POST['invoice_date'],
                $_POST['due_date'],
                $_POST['currency'],
                $subtotal,
                $vat_rate,
                $vat_amount,
                $total,
                $outstanding_balance,
                $_POST['notes'] ?? ''
            ]);
            
            $invoice_id = $db->lastInsertId();
            
            foreach ($items as $item) {
                $stmt = $db->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$invoice_id, $item['description'], $item['quantity'], $item['unit_price'], $item['total']]);
            }
            
            $_SESSION['success'] = 'Invoice created successfully!';
            header('Location: /invoices');
            exit;
        }
        break;
    
    case '/update-company':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $db->prepare("UPDATE company_info SET company_name = ?, city = ?, address = ?, vat_number = ?, iban = ?, bic = ?, bank_name = ? WHERE id = 1");
            $stmt->execute([
                $_POST['company_name'],
                $_POST['city'],
                $_POST['address'],
                $_POST['vat_number'],
                $_POST['iban'],
                $_POST['bic'],
                $_POST['bank_name']
            ]);
            
            $_SESSION['success'] = 'Company information updated successfully!';
            header('Location: /company-settings');
            exit;
        }
        break;
    
    case '/update-status':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $invoice_id = $_POST['invoice_id'];
            $status = $_POST['status'];
            
            $outstanding = ($status === 'paid') ? 0 : null;
            
            if ($outstanding !== null) {
                $stmt = $db->prepare("UPDATE invoices SET status = ?, outstanding_balance = ? WHERE id = ?");
                $stmt->execute([$status, $outstanding, $invoice_id]);
            } else {
                $stmt = $db->prepare("UPDATE invoices SET status = ? WHERE id = ?");
                $stmt->execute([$status, $invoice_id]);
            }
            
            $_SESSION['success'] = 'Invoice status updated successfully!';
            header('Location: /invoices');
            exit;
        }
        break;
    
    case (preg_match('/^\/invoice\/(\d+)\/pdf$/', $path, $matches) ? true : false):
        $invoice_id = $matches[1];
        include __DIR__ . '/../resources/views/generate-pdf.php';
        break;
    
    default:
        http_response_code(404);
        echo "404 - Page not found";
        break;
}
