<?php
/**
 * Shared Header Layout Template
 */
$currentUser = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'WebForge Builder') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Fonts (Outfit / Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8f9fc;
        }
        .navbar-brand {
            font-weight: 800;
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card-glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 16px;
        }
        .nav-link.active {
            font-weight: 600;
            color: #0d6efd !important;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3">
    <div class="container">
        <a class="navbar-brand fs-4" href="<?= APP_URL ?>/dashboard">WebForge</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="topNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
                <li class="nav-item">
                    <a class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'dashboard') && !str_contains($_SERVER['REQUEST_URI'], 'websites') && !str_contains($_SERVER['REQUEST_URI'], 'settings')) ? 'active' : '' ?>" href="<?= APP_URL ?>/dashboard">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'websites') ? 'active' : '' ?>" href="<?= APP_URL ?>/dashboard/websites">My Websites</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'settings') ? 'active' : '' ?>" href="<?= APP_URL ?>/dashboard/settings">Integrations</a>
                </li>
                <?php if (Auth::isAdmin()): ?>
                <li class="nav-item dropdown ms-lg-3">
                    <a class="nav-link dropdown-toggle text-danger fw-bold" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                        Super Admin Panel
                    </a>
                    <ul class="dropdown-menu border-0 shadow-lg">
                        <li><a class="dropdown-menu-item dropdown-item" href="<?= APP_URL ?>/admin">Overview</a></li>
                        <li><a class="dropdown-menu-item dropdown-item" href="<?= APP_URL ?>/admin/users">Users Manager</a></li>
                        <li><a class="dropdown-menu-item dropdown-item" href="<?= APP_URL ?>/admin/subscriptions">Billing Log</a></li>
                        <li><a class="dropdown-menu-item dropdown-item" href="<?= APP_URL ?>/admin/templates">Marketplace Templates</a></li>
                        <li><a class="dropdown-menu-item dropdown-item" href="<?= APP_URL ?>/admin/settings">Platform Settings</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center">
                <span class="text-muted me-3 d-none d-lg-inline">Welcome, <strong><?= e($currentUser['name']) ?></strong></span>
                <a class="btn btn-outline-danger btn-sm rounded-pill px-3" href="<?= APP_URL ?>/auth/logout">Sign Out</a>
            </div>
        </div>
    </div>
</nav>
<div class="container py-5">
