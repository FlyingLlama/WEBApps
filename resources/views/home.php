<?php 
$title = 'Home - Invoice Management';
$current_page = 'home';
include __DIR__ . '/header.php'; 
?>

<div class="hero">
    <h2>Welcome to the Invoiceinator3000</h2>
    <p>A place where you can create and manage all your invoices</p>
    
    <div class="stats">
        <?php
        $total_invoices = $db->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
        $total_amount = $db->query("SELECT SUM(total) FROM invoices")->fetchColumn() ?: 0;
        $pending = $db->query("SELECT COUNT(*) FROM invoices WHERE status = 'pending'")->fetchColumn();
        ?>
        
        <div class="stat-card">
            <h3><?php echo $total_invoices; ?></h3>
            <p>Total Invoices</p>
        </div>
        
        <div class="stat-card">
            <h3>$<?php echo number_format($total_amount, 2); ?></h3>
            <p>Total Amount</p>
        </div>
        
        <div class="stat-card">
            <h3><?php echo $pending; ?></h3>
            <p>Pending Invoices</p>
        </div>
    </div>
    
    <div class="actions">
        <a href="/create-invoice" class="btn btn-primary">Create New Invoice</a>
        <a href="/invoices" class="btn btn-secondary">View All Invoices</a>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
