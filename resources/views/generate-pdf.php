<?php
$invoice_id = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    die("Invoice not found");
}

$stmt = $db->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$stmt->execute([$invoice_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$company = $db->query("SELECT * FROM company_info WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

$currency_symbols = [
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'MKD' => 'ден'
];
$currency_symbol = $currency_symbols[$invoice['currency']] ?? '$';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo htmlspecialchars($invoice['invoice_number']); ?></title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            background: white;
        }
        
        .invoice-container {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 0 auto;
            background: white;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }
        
        .company-info {
            flex: 1;
        }
        
        .logo-placeholder {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .company-info h1 {
            font-size: 24px;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .company-info p {
            font-size: 12px;
            line-height: 1.6;
            color: #666;
        }
        
        .invoice-title {
            text-align: right;
            flex: 1;
        }
        
        .invoice-title h2 {
            font-size: 36px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .invoice-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        
        .client-info, .invoice-details {
            flex: 1;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #667eea;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .client-info p, .invoice-details p {
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 3px;
        }
        
        .invoice-details {
            text-align: right;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .items-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
        }
        
        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }
        
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }
        
        .items-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }
        
        .totals-table {
            width: 300px;
        }
        
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        
        .totals-row.subtotal {
            border-top: 1px solid #e0e0e0;
        }
        
        .totals-row.total {
            border-top: 2px solid #667eea;
            font-weight: bold;
            font-size: 18px;
            color: #667eea;
            padding-top: 12px;
            margin-top: 5px;
        }
        
        .notes-section {
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
        }
        
        .notes-section h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #667eea;
        }
        
        .notes-section p {
            font-size: 13px;
            line-height: 1.6;
            color: #666;
        }
        
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 11px;
            color: #999;
            text-align: center;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        @media print {
            .print-button {
                display: none;
            }
            
            .invoice-container {
                padding: 0;
                width: 100%;
            }
            
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Print / Save as PDF</button>
    
    <div class="invoice-container">
        <!-- header -->
        <div class="header">
            <div class="company-info">
                <?php if (!empty($company['logo_path'])): ?>
                    <img src="<?php echo htmlspecialchars($company['logo_path']); ?>" alt="Company Logo" style="max-width: 150px; max-height: 80px; margin-bottom: 10px;">
                <?php else: ?>
                    <div class="logo-placeholder">LOGO</div>
                <?php endif; ?>
                <h1><?php echo htmlspecialchars($company['company_name']); ?></h1>
                <p><?php echo htmlspecialchars($company['address']); ?><br>
                   <?php echo htmlspecialchars($company['city']); ?></p>
                <p style="margin-top: 10px;">
                    <strong>VAT:</strong> <?php echo htmlspecialchars($company['vat_number']); ?><br>
                    <strong>IBAN:</strong> <?php echo htmlspecialchars($company['iban']); ?><br>
                    <strong>BIC:</strong> <?php echo htmlspecialchars($company['bic']); ?><br>
                    <strong>Bank:</strong> <?php echo htmlspecialchars($company['bank_name']); ?>
                </p>
            </div>
            
            <div class="invoice-title">
                <h2>INVOICE</h2>
            </div>
        </div>
        
        <!-- client and invoice info -->
        <div class="invoice-meta">
            <div class="client-info">
                <div class="section-title">Bill To:</div>
                <p><strong><?php echo htmlspecialchars($invoice['client_name']); ?></strong></p>
                <p><?php echo nl2br(htmlspecialchars($invoice['client_address'])); ?></p>
            </div>
            
            <div class="invoice-details">
                <p><strong>Invoice Number:</strong> <?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
                <p><strong>Invoice Date:</strong> <?php echo date('F d, Y', strtotime($invoice['invoice_date'])); ?></p>
                <p><strong>Due Date:</strong> <?php echo date('F d, Y', strtotime($invoice['due_date'])); ?></p>
            </div>
        </div>
        
        <!-- table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th style="width: 15%;">Quantity</th>
                    <th style="width: 17%;">Unit Price</th>
                    <th style="width: 18%;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td><?php echo number_format($item['quantity'], 2); ?></td>
                    <td><?php echo $currency_symbol . number_format($item['unit_price'], 2); ?></td>
                    <td><?php echo $currency_symbol . number_format($item['total'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- totals -->
        <div class="totals-section">
            <div class="totals-table">
                <div class="totals-row subtotal">
                    <span>Subtotal:</span>
                    <span><?php echo $currency_symbol . number_format($invoice['subtotal'], 2); ?></span>
                </div>
                <div class="totals-row">
                    <span>VAT (<?php echo number_format($invoice['vat_rate'], 2); ?>%):</span>
                    <span><?php echo $currency_symbol . number_format($invoice['vat_amount'], 2); ?></span>
                </div>
                <div class="totals-row total">
                    <span>Total (<?php echo $invoice['currency']; ?>):</span>
                    <span><?php echo $currency_symbol . number_format($invoice['total'], 2); ?></span>
                </div>
                <div class="totals-row" style="color: #e74c3c; font-weight: 600;">
                    <span>Outstanding Balance:</span>
                    <span><?php echo $currency_symbol . number_format($invoice['outstanding_balance'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <!-- notes -->
        <?php if (!empty($invoice['notes'])): ?>
        <div class="notes-section">
            <h3>Notes / Payment Terms</h3>
            <p><?php echo nl2br(htmlspecialchars($invoice['notes'])); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- footer -->
        <div class="footer">
            <p>&nbsp;</p>
        </div>
    </div>
</body>
</html>
