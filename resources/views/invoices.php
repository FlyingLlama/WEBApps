<?php 
$title = 'All Invoices';
$current_page = 'invoices';
include __DIR__ . '/header.php'; 
?>

<div class="page-header">
    <h2>All Invoices</h2>
    <a href="/create-invoice" class="btn btn-primary">+ New Invoice</a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?php 
        echo $_SESSION['success']; 
        unset($_SESSION['success']);
        ?>
    </div>
<?php endif; ?>

<div class="invoices-table">
    <table>
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Client Name</th>
                <th>Invoice Date</th>
                <th>Due Date</th>
                <th>Total</th>
                <th>Outstanding</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $db->query("SELECT * FROM invoices ORDER BY created_at DESC");
            $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $currency_symbols = [
                'USD' => '$',
                'EUR' => '€',
                'GBP' => '£',
                'MKD' => 'ден'
            ];
            
            if (count($invoices) > 0):
                foreach ($invoices as $invoice):
                    $symbol = $currency_symbols[$invoice['currency']] ?? '$';
            ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong></td>
                    <td><?php echo htmlspecialchars($invoice['client_name']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($invoice['invoice_date'])); ?></td>
                    <td><?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></td>
                    <td><?php echo $symbol . number_format($invoice['total'], 2); ?></td>
                    <td class="<?php echo $invoice['outstanding_balance'] > 0 ? 'text-danger' : 'text-success'; ?>">
                        <?php echo $symbol . number_format($invoice['outstanding_balance'], 2); ?>
                    </td>
                    <td>
                        <form action="/update-status" method="POST" style="display: inline;">
                            <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                            <select name="status" onchange="this.form.submit()" class="status-select status-<?php echo $invoice['status']; ?>">
                                <option value="pending" <?php echo $invoice['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="paid" <?php echo $invoice['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="cancelled" <?php echo $invoice['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <a href="/invoice/<?php echo $invoice['id']; ?>/pdf?id=<?php echo $invoice['id']; ?>" 
                           class="btn-action" target="_blank" title="View/Download PDF">
                            📄 PDF
                        </a>
                    </td>
                </tr>
            <?php 
                endforeach;
            else:
            ?>
                <tr>
                    <td colspan="8" class="no-data">No invoices found. <a href="/create-invoice">Create your first invoice</a></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/footer.php'; ?>
