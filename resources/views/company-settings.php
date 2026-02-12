<?php 
$title = 'Company Settings';
$current_page = 'settings';

$company = $db->query("SELECT * FROM company_info WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

include __DIR__ . '/header.php'; 
?>

<div class="page-header">
    <h2>Company Settings</h2>
    <p>Update your company information for invoices</p>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?php 
        echo $_SESSION['success']; 
        unset($_SESSION['success']);
        ?>
    </div>
<?php endif; ?>

<form action="/update-company" method="POST" enctype="multipart/form-data" class="invoice-form">
    <div class="form-section">
        <h3>Company Logo</h3>
        
        <?php if (!empty($company['logo_path'])): ?>
            <div class="current-logo">
                <img src="<?php echo htmlspecialchars($company['logo_path']); ?>" alt="Company Logo" style="max-width: 200px; max-height: 100px; margin-bottom: 1rem;">
            </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label for="logo">Upload New Logo (JPG, PNG, GIF, WebP)</label>
            <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif,image/webp">
            <small style="color: #666; display: block; margin-top: 0.5rem;">Leave empty to keep current logo</small>
        </div>
    </div>
    
    <div class="form-section">
        <h3>Company Information</h3>
        
        <div class="form-group">
            <label for="company_name">Company Name *</label>
            <input type="text" id="company_name" name="company_name" required value="<?php echo htmlspecialchars($company['company_name']); ?>">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="city">City *</label>
                <input type="text" id="city" name="city" required value="<?php echo htmlspecialchars($company['city']); ?>">
            </div>
            
            <div class="form-group">
                <label for="address">Address *</label>
                <input type="text" id="address" name="address" required value="<?php echo htmlspecialchars($company['address']); ?>">
            </div>
        </div>
    </div>
    
    <div class="form-section">
        <h3>Tax & Banking Information</h3>
        
        <div class="form-group">
            <label for="vat_number">VAT Number</label>
            <input type="text" id="vat_number" name="vat_number" value="<?php echo htmlspecialchars($company['vat_number']); ?>">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="iban">IBAN</label>
                <input type="text" id="iban" name="iban" value="<?php echo htmlspecialchars($company['iban']); ?>">
            </div>
            
            <div class="form-group">
                <label for="bic">BIC/SWIFT</label>
                <input type="text" id="bic" name="bic" value="<?php echo htmlspecialchars($company['bic']); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="bank_name">Bank Name</label>
            <input type="text" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($company['bank_name']); ?>">
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Settings</button>
        <a href="/" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php include __DIR__ . '/footer.php'; ?>
