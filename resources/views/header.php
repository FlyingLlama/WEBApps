<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Invoice Management'; ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="container">
                <div class="logo">
                    <h1>Invoicinator3000</h1>
                </div>
                <ul class="nav-links">
                    <li><a href="/" class="<?php echo ($current_page ?? '') === 'home' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="/create-invoice" class="<?php echo ($current_page ?? '') === 'create' ? 'active' : ''; ?>">Create Invoice</a></li>
                    <li><a href="/invoices" class="<?php echo ($current_page ?? '') === 'invoices' ? 'active' : ''; ?>">Invoices</a></li>
                    <li><a href="/company-settings" class="<?php echo ($current_page ?? '') === 'settings' ? 'active' : ''; ?>">Settings</a></li>
                </ul>
            </div>
        </nav>
    </header>
    <main class="container">
