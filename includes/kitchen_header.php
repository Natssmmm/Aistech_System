<?php
/**
 * Kitchen header - requires login (kitchen role)
 * Manages ONLY customer orders coming from the kiosk.
 */
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/auth.php';
require_login();
require_role('kitchen');
$user = current_user();

$pageTitle = $pageTitle ?? 'Kitchen Order Management';
$activeTab = $_GET['tab'] ?? 'pending';

// Live order counts (only PAID orders - unpaid orders stay hidden until admin settles)
$counts = [
    'pending'   => (int)db()->query("SELECT COUNT(*) c FROM orders WHERE status='pending' AND payment_status='paid'")->fetch()['c'],
    'preparing' => (int)db()->query("SELECT COUNT(*) c FROM orders WHERE status='preparing' AND payment_status='paid'")->fetch()['c'],
    'ready'     => (int)db()->query("SELECT COUNT(*) c FROM orders WHERE status='ready' AND payment_status='paid'")->fetch()['c'],
    'completed' => (int)db()->query("SELECT COUNT(*) c FROM orders WHERE status='completed' AND payment_status='paid'")->fetch()['c'],
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?> Kitchen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>assets/css/style.css?v=3.0" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/dashboard.css?v=3.0" rel="stylesheet">
    <style>
/* Kitchen tabs */
.kitchen-tabs { overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; }
.kitchen-tabs::-webkit-scrollbar { display: none; }
@media (max-width: 992px) and (orientation: landscape) and (max-height: 500px) {
  .kitchen-topbar { padding: .5rem .8rem; }
  .kitchen-topbar h1 { font-size: 1rem; }
  .kitchen-tabs { padding: .4rem .6rem; }
  .kitchen-content { padding: .5rem; }
  .k-order-card .k-body { padding: .4rem; }
  .k-order-card .k-actions { padding: 0 .4rem .4rem; }
}
@media (max-width: 320px) and (orientation: portrait) {
  .kitchen-topbar .d-flex.align-items-center.gap-2 { gap: .25rem !important; flex-wrap: wrap; }
  .kitchen-topbar .btn { padding: .3rem .5rem; font-size: .75rem; }
}

/* Kitchen topbar (red header) — preserved exactly */
body { background: #000000; }
.kitchen-topbar {
    background: linear-gradient(135deg, #e63946, #c1121f);
    color: #fff; padding: .9rem 1.5rem;
    display: flex; align-items: center; justify-content: space-between;
    box-shadow: 0 4px 16px rgba(0,0,0,.3);
}
.kitchen-topbar .logo { width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,.2); display: grid; place-items: center; font-size: 1.4rem; }
.kitchen-topbar h1 { font-size: 1.3rem; margin: 0; color: #fff; }
.kitchen-topbar small { opacity: .85; }

/* Kitchen tabs — dark theme */
.kitchen-tabs {
    display: flex; gap: .4rem; background: #121212; padding: .7rem 1.5rem;
    border-bottom: 1px solid #334158; overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.kitchen-tabs .tab {
    flex-shrink: 0; text-decoration: none; color: #94a3b8;
    border: 2px solid #334158; border-radius: 50px; padding: .4rem 1rem;
    font-weight: 700; display: flex; align-items: center; gap: .4rem; transition: all .2s;
}
.kitchen-tabs .tab:hover { border-color: #e63946; color: #e63946; }
.kitchen-tabs .tab.active { background: #e63946; border-color: #e63946; color: #fff; }
.kitchen-tabs .tab .count {
    background: rgba(230,57,70,.12); border-radius: 50px; min-width: 20px; height: 20px;
    display: grid; place-items: center; font-size: .72rem; padding: 0 .35rem;
    color: #e63946;
}
.kitchen-tabs .tab.active .count { background: rgba(255,255,255,.3); color: #fff; }

/* Content area */
.kitchen-content { padding: 1.25rem; }

/* Order grid — CSS Grid, left-aligned, 5+ cards per row on wide screens */
.k-order-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
    width: 100%;
}

/* Order cards — compact dark boxes, consistent fixed height */
.k-order-card {
    background: #1e293b; border: 1px solid #334158; border-radius: 10px;
    overflow: hidden; transition: transform .2s, box-shadow .2s;
    display: flex; flex-direction: column; min-height: 280px; max-height: 420px;
}
.k-order-card:hover { transform: translateY(-2px); box-shadow: 0 4px 14px rgba(0,0,0,.25); }
.k-order-card .k-head {
    background: #293548; padding: .35rem .55rem;
    display: flex; justify-content: space-between; align-items: center;
    border-bottom: 1px solid #334158; gap: .25rem;
    flex-shrink: 0;
}
.k-order-card .k-head h6 { margin: 0; color: #e2e8f0; font-weight: 700; font-size: .88rem; overflow-wrap:anywhere; word-break:break-word; }
.k-order-card .k-body {
    padding: .4rem; flex: 1 1 auto; overflow-y: auto; min-height: 0;
    display: flex; flex-direction: column;
}
.k-order-card .k-body > .k-item:first-child { margin-top: 0; }
.k-order-card .k-item { display: flex; justify-content: space-between; padding: .15rem 0; border-bottom: 1px dashed #334158; color: #94a3b8; gap: .25rem; font-size: .82rem; flex-shrink: 0; }
.k-order-card .k-item span { overflow-wrap:anywhere; word-break:break-word; }
.k-order-card .k-actions { flex-shrink: 0; display: flex; gap: .25rem; padding: 0 .4rem .4rem; flex-wrap: wrap; }
.k-order-card .k-total { display: flex; justify-content: space-between; font-weight: 800; color: #f1f5f9; padding-top: .25rem; flex-shrink: 0; }
.k-order-card .k-time { font-size: .7rem; color: #6474a1; margin-bottom: .1rem; flex-shrink: 0; }
.k-order-card hr { border-color: #334158 !important; opacity: .5 !important; margin: .2rem 0 !important; }
.k-order-card .alert.alert-info { background: #1e3a5f; color: #93c5fd; border: 1px solid #334158; }
.badge-k { padding: .25em .6em; border-radius: 50px; font-weight: 700; font-size: .72rem; flex-shrink: 0; }
.st-pending { background: #f4a261; color: #1a1f2b; }
.st-preparing { background: #457b9d; color: #fff; }
.st-ready { background: #2a9d8f; color: #fff; }
.st-completed { background: #6c757d; color: #fff; }
.st-cancelled { background: #e76f51; color: #fff; }

/* Empty state */
.empty-state { text-align: center; padding: 2.5rem 1rem; color: #94a3b8; }
.empty-state i { font-size: 2.5rem; opacity: .4; }

/* Detail items (order_details.php) — light */
.k-detail-item { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: .5rem; }
.k-detail-item .label { color: #94a3b8; font-size: .8rem; }
.k-detail-item .value { color: #1e293b; font-weight: 700; overflow-wrap:anywhere; word-break:break-word; }

/* Tables — use Bootstrap defaults for light card backgrounds */
.table { color: #334155; }
.table thead th { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
.table tbody td { border-color: #e2e8f0; }
.table-hover tbody tr:hover { background: #f1f5f9; }

/* Scrollbar — light */
::-webkit-scrollbar { width: 10px; height: 10px; }
::-webkit-scrollbar-track { background: #f1f5f9; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

/* Ultra-small phones */
@media (max-width: 359.98px) {
    .kitchen-topbar { padding: .6rem .8rem; gap: .4rem; }
    .kitchen-topbar h1 { font-size: .95rem; }
    .kitchen-topbar small { font-size: .7rem; }
    .kitchen-topbar .logo { width: 38px; height: 38px; font-size: 1.2rem; }
    .kitchen-tabs { padding: .5rem .7rem; gap: .3rem; }
    .kitchen-tabs .tab { padding: .4rem .75rem; font-size: .75rem; }
    .kitchen-content { padding: .6rem; }
    .k-order-card .k-head h6 { font-size: .88rem; }
    .k-order-card .k-body { padding: .5rem; }
    .k-order-card .k-actions { padding: 0 .5rem .5rem; }
    .k-order-card .k-item { font-size: .8rem; }
    .k-order-card .k-total { font-size: .85rem; }
    .k-order-card .k-actions .btn { font-size: .78rem; }
    .table { font-size: .78rem; }
}

/* 320px baseline */
@media (max-width: 320px) {
    .kitchen-tabs .tab { font-size: .7rem; padding: .3rem .5rem; }
    .kitchen-tabs .tab .count { min-width: 16px; height: 16px; font-size: .6rem; padding: 0 .25rem; }
    .k-order-card .k-actions { gap: .25rem; }
    .k-order-card .k-actions .btn { flex: 1 1 100%; width: 100%; }
}

/* Tablet / small desktop */
@media (max-width: 767.98px) {
    .kitchen-topbar { flex-wrap: wrap; gap: .5rem; padding: .7rem 1rem; }
    .kitchen-topbar h1 { font-size: 1.05rem; }
    .kitchen-tabs { padding: .6rem 1rem; }
    .kitchen-content { padding: .8rem; }
    .k-order-card .k-actions { flex-wrap: wrap; }
    .k-order-card .k-actions .btn { flex: 1 1 auto; }
    .k-order-card .k-head { flex-wrap: wrap; }
}

/* Kitchen touch targets */
@media (pointer: coarse) {
  .kitchen-tabs .tab { min-height: 44px; }
  .k-order-card .k-actions .btn { min-height: 44px; }
  .btn { min-height: 44px; }
  .kitchen-topbar .btn-light { min-height: 44px; min-width: 44px; }
}
@media (max-width: 767.98px) and (orientation: portrait) {
  .kitchen-topbar { min-height: 56px; }
  .kitchen-tabs { position: sticky; top: 56px; z-index: 1025; }
}
@media (orientation: portrait) {
  .kitchen-tabs .tab,
  .k-order-card .k-actions .btn,
  .kitchen-topbar .btn {
    display: inline-flex !important;
    align-items: center !important;
    visibility: visible !important;
    opacity: 1 !important;
    pointer-events: auto !important;
  }
}

/* Override Bootstrap utility spacing on order cards for compactness */
.k-order-card .mb-2,
.k-order-card .mb-3 { margin-bottom: .25rem !important; }
.k-order-card .mt-2,
.k-order-card .mt-3 { margin-top: .25rem !important; }
.k-order-card hr.my-2,
.k-order-card hr.my-3 { margin: .25rem 0 !important; }
.k-order-card .alert.mt-2 { margin-top: .35rem !important; }
    </style>
</head>
<body>
<div class="kitchen-topbar">
    <div class="d-flex align-items-center gap-3">
        <div class="logo"><i class="bi bi-egg-fried"></i></div>
        <div>
            <h1><i class="bi bi-fire me-1"></i>Kitchen - <?= e(SITE_NAME) ?></h1>
            <small>Customer Order Management</small>
        </div>
    </div>
<div class="d-flex align-items-center gap-2">
        <span class="d-none d-md-inline-flex align-items-center gap-2 me-1" style="background:rgba(255,255,255,.15);padding:.3rem .8rem;border-radius:50px;font-size:.8rem;">
            <i class="bi bi-person"></i> <?= e($user['fullname'] ?? 'Kitchen') ?>
        </span>
        <button class="btn btn-sm btn-light" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
            <i class="bi bi-box-arrow-right"></i> Logout
        </button>
        <form id="logout-form" action="<?= BASE_URL ?>authentication/logout.php" method="POST" class="d-none"></form>
    </div>
</div>

<div class="kitchen-tabs">
    <a class="tab <?= $activeTab === 'pending' ? 'active' : '' ?>" href="?tab=pending">
        <i class="bi bi-hourglass-split"></i> Pending <span class="count"><?= $counts['pending'] ?></span>
    </a>
    <a class="tab <?= $activeTab === 'preparing' ? 'active' : '' ?>" href="?tab=preparing">
        <i class="bi bi-fire"></i> Preparing <span class="count"><?= $counts['preparing'] ?></span>
    </a>
    <a class="tab <?= $activeTab === 'ready' ? 'active' : '' ?>" href="?tab=ready">
        <i class="bi bi-bell"></i> Ready to Serve <span class="count"><?= $counts['ready'] ?></span>
    </a>
    <a class="tab <?= $activeTab === 'completed' ? 'active' : '' ?>" href="?tab=completed">
        <i class="bi bi-check2-circle"></i> Completed <span class="count"><?= $counts['completed'] ?></span>
    </a>
</div>

 <div class="kitchen-content">
