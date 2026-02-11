<?php 
$title = 'Create Invoice';
$current_page = 'create';
include __DIR__ . '/header.php'; 
?>

<div class="page-header">
    <h2>Create New Invoice</h2>
    <p>Fill in the details below to create a new invoice</p>
</div>

<form action="/store-invoice" method="POST" class="invoice-form" id="invoiceForm">
    <div class="form-section">
        <h3>Client Information</h3>
        
        <div class="form-row">
            <div class="form-group">
                <label for="client_name">Client/Company Name *</label>
                <input type="text" id="client_name" name="client_name" required placeholder="Client Company Name">
            </div>
        </div>
        
        <div class="form-group">
            <label for="client_address">Client Address *</label>
            <textarea id="client_address" name="client_address" rows="2" required placeholder="Full address"></textarea>
        </div>
    </div>
    
    <div class="form-section">
        <h3>Invoice Details</h3>
        
        <div class="form-row">
            <div class="form-group">
                <label for="invoice_date">Invoice Date *</label>
                <input type="date" id="invoice_date" name="invoice_date" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label for="due_date">Due Date *</label>
                <input type="date" id="due_date" name="due_date" required value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="currency">Currency *</label>
                <select id="currency" name="currency" required>
                    <option value="USD">USD ($)</option>
                    <option value="EUR">EUR (€)</option>
                    <option value="GBP">GBP (£)</option>
                    <option value="MKD">MKD (ден)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="vat_rate">VAT Rate (%)</label>
                <input type="number" id="vat_rate" name="vat_rate" step="0.01" min="0" max="100" value="0" placeholder="0">
            </div>
        </div>
    </div>
    
    <div class="form-section">
        <h3>Invoice Items</h3>
        
        <div id="invoice-items">
            <div class="invoice-item">
                <div class="form-row item-row">
                    <div class="form-group flex-3">
                        <label>Description *</label>
                        <input type="text" name="item_description[]" required placeholder="Service or product description">
                    </div>
                    
                    <div class="form-group flex-1">
                        <label>Quantity *</label>
                        <input type="number" name="item_quantity[]" step="0.01" min="0.01" required value="1" class="item-quantity">
                    </div>
                    
                    <div class="form-group flex-1">
                        <label>Unit Price *</label>
                        <input type="number" name="item_price[]" step="0.01" min="0" required placeholder="0.00" class="item-price">
                    </div>
                    
                    <div class="form-group flex-1">
                        <label>Total</label>
                        <input type="text" class="item-total" readonly value="0.00">
                    </div>
                    
                    <div class="form-group-btn">
                        <button type="button" class="btn-remove-item" onclick="removeItem(this)" title="Remove item">×</button>
                    </div>
                </div>
            </div>
        </div>
        
        <button type="button" class="btn btn-secondary" onclick="addItem()">+ Add Item</button>
    </div>
    
    <div class="form-section">
        <div class="invoice-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span id="display-subtotal">$0.00</span>
            </div>
            <div class="summary-row">
                <span>VAT (<span id="display-vat-rate">0</span>%):</span>
                <span id="display-vat-amount">$0.00</span>
            </div>
            <div class="summary-row total-row">
                <span>Total:</span>
                <span id="display-total">$0.00</span>
            </div>
            <div class="summary-row">
                <span>Outstanding Balance:</span>
                <span id="display-outstanding">$0.00</span>
            </div>
        </div>
    </div>
    
    <div class="form-section">
        <div class="form-group">
            <label for="notes">Notes / Additional Information</label>
            <textarea id="notes" name="notes" rows="3" placeholder="Payment terms, thank you message, etc."></textarea>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Create Invoice</button>
        <a href="/invoices" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
function addItem() {
    const container = document.getElementById('invoice-items');
    const itemHTML = `
        <div class="invoice-item">
            <div class="form-row item-row">
                <div class="form-group flex-3">
                    <input type="text" name="item_description[]" required placeholder="Service or product description">
                </div>
                
                <div class="form-group flex-1">
                    <input type="number" name="item_quantity[]" step="0.01" min="0.01" required value="1" class="item-quantity">
                </div>
                
                <div class="form-group flex-1">
                    <input type="number" name="item_price[]" step="0.01" min="0" required placeholder="0.00" class="item-price">
                </div>
                
                <div class="form-group flex-1">
                    <input type="text" class="item-total" readonly value="0.00">
                </div>
                
                <div class="form-group-btn">
                    <button type="button" class="btn-remove-item" onclick="removeItem(this)" title="Remove item">×</button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHTML);
    attachItemListeners();
}

function removeItem(button) {
    const items = document.querySelectorAll('.invoice-item');
    if (items.length > 1) {
        button.closest('.invoice-item').remove();
        calculateTotals();
    }
}

function attachItemListeners() {
    document.querySelectorAll('.item-quantity, .item-price').forEach(input => {
        input.removeEventListener('input', calculateItemTotal);
        input.addEventListener('input', calculateItemTotal);
    });
    
    document.getElementById('vat_rate').removeEventListener('input', calculateTotals);
    document.getElementById('vat_rate').addEventListener('input', calculateTotals);
}

function calculateItemTotal(e) {
    const row = e.target.closest('.item-row');
    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const total = quantity * price;
    row.querySelector('.item-total').value = total.toFixed(2);
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll('.item-total').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });
    
    const vatRate = parseFloat(document.getElementById('vat_rate').value) || 0;
    const vatAmount = subtotal * (vatRate / 100);
    const total = subtotal + vatAmount;
    
    const currency = document.getElementById('currency').value;
    const symbol = getCurrencySymbol(currency);
    
    document.getElementById('display-subtotal').textContent = symbol + subtotal.toFixed(2);
    document.getElementById('display-vat-rate').textContent = vatRate.toFixed(2);
    document.getElementById('display-vat-amount').textContent = symbol + vatAmount.toFixed(2);
    document.getElementById('display-total').textContent = symbol + total.toFixed(2);
    document.getElementById('display-outstanding').textContent = symbol + total.toFixed(2);
}

function getCurrencySymbol(currency) {
    const symbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'MKD': 'ден'
    };
    return symbols[currency] || '$';
}

document.getElementById('currency').addEventListener('change', calculateTotals);

attachItemListeners();
</script>

<?php include __DIR__ . '/footer.php'; ?>
