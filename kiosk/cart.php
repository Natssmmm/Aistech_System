<?php
/**
 * Kiosk - Shopping cart page
 */
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

$pageTitle = 'Your Cart';
$items = cart_items();
$total = array_sum(array_column($items, 'subtotal'));

require_once __DIR__ . '/../includes/kiosk_header.php';
?>
<div class="kiosk-topbar">
    <div class="k-brand">
        <div class="logo"><i class="bi bi-cart3"></i></div>
        <div>
            <h1>Your Cart</h1>
            <small><?= e(SITE_NAME) ?></small>
        </div>
    </div>
    <a href="<?= BASE_URL ?>kiosk/index.php?start=1" class="btn btn-light">
        <i class="bi bi-arrow-left"></i> Continue Shopping
    </a>
</div>

<div class="checkout-page">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-basket me-2"></i>Order Summary
            <span class="badge bg-primary ms-2"><?= cart_count() ?> item(s)</span>
        </div>
        <div class="card-body">
            <?php if (empty($items)): ?>
                <div class="empty-state">
                    <i class="bi bi-cart-x"></i>
                    <h5>Your cart is empty</h5>
                    <p>Browse the menu and add items to your order.</p>
                    <a href="<?= BASE_URL ?>kiosk/index.php?start=1" class="btn btn-primary">
                        <i class="bi bi-cup-straw"></i> Browse Menu
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td data-label="Product">
                                    <div class="cart-product-name fw-bold"><?= e($item['product_name']) ?></div>
                                    <small class="text-muted"><?= e($item['category_name']) ?></small>
                                    <div class="small text-muted">
                                        <i class="bi bi-box-seam"></i> Available: <strong><?= (int)$item['stock'] ?></strong>
                                    </div>
                                </td>
                                <td class="text-center" data-label="Price"><?= format_currency($item['price']) ?></td>
                                <td class="text-center" data-label="Qty">
                                    <div class="qty-control">
                                        <button class="cart-update" data-id="<?= (int)$item['product_id'] ?>" data-qty="<?= (int)$item['cart_qty'] - 1 ?>">−</button>
                                        <span><?= (int)$item['cart_qty'] ?></span>
                                        <button class="cart-update" data-id="<?= (int)$item['product_id'] ?>" data-qty="<?= (int)$item['cart_qty'] + 1 ?>"
                                            <?= (int)$item['cart_qty'] >= (int)$item['stock'] ? 'disabled' : '' ?>>+</button>
                                    </div>
                                </td>
                                <td class="text-end cart-line-total fw-bold" data-label="Subtotal" data-total="<?= $item['subtotal'] ?>"><?= format_currency($item['subtotal']) ?></td>
                                <td class="text-end" data-label="Action">
                                    <button class="btn btn-sm btn-danger cart-remove" data-id="<?= (int)$item['product_id'] ?>">
                                        <i class="bi bi-trash"></i> <span class="d-none d-sm-inline">Remove</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-3 mt-3">
                    <div>
                        <h4 class="fw-bold">Total: <span id="cart-total" data-amount="<?= $total ?>"><?= format_currency($total) ?></span></h4>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2" style="min-width:220px;">
                        <a href="<?= BASE_URL ?>kiosk/index.php?start=1" class="btn btn-light flex-fill">
                            <i class="bi bi-arrow-left"></i> Add More
                        </a>
                        <a href="<?= BASE_URL ?>kiosk/checkout.php" class="btn btn-success btn-lg flex-fill">
                            <i class="bi bi-credit-card"></i> Checkout
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/kiosk_footer.php'; ?>
