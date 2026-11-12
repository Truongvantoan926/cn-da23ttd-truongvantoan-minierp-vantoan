<?php
$pageTitle = isset($pageTitle) && is_string($pageTitle) ? $pageTitle : 'Mini ERP';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
</head>
<body class="min-vh-100 d-flex flex-column bg-light">
<header class="navbar navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold" href="<?= BASE_URL ?>/dashboard/index">Mini ERP</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="d-flex align-items-center gap-3">
                <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>/profile/index">
                    <i class="fa-solid fa-user me-1" aria-hidden="true"></i>
                    Hồ sơ cá nhân
                </a>
            </div>
        <?php endif; ?>
    </div>
</header>
<div class="d-flex flex-grow-1">
