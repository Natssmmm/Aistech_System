<?php
/**
 * Printable Official Receipt for Ferry's Restobar
 * Used in the admin Payment Settlement screen after marking an order as PAID.
 *
 * Requires passed variables:
 *   $receipt_order  array  - order details
 *   $receipt_items  array  - order_items rows
 *   $receipt_cashier string - the admin/staff who processed payment
 *   $receipt_paid_at string - paid datetime
 *   $receipt_change  float  - change amount
 */
$receipt_change = $receipt_change ?? 0;
$receipt_paid_at = $receipt_paid_at ?? date('Y-m-d H:i:s');
$receipt_cashier = $receipt_cashier ?? 'Cashier';
$receipt_redirect_url = $receipt_redirect_url ?? '';

// Receipt amounts. Product prices and order totals are stored as the final
// amount paid (there is no separate tax column), i.e. VAT-inclusive.
$receipt_total   = (float)($receipt_order['total_amount'] ?? 0);
$receipt_subtotal = 0.0;
foreach ($receipt_items as $ri) {
    $receipt_subtotal += (float)($ri['subtotal'] ?? 0);
}

// VAT breakdown — standard 12% Philippines VAT on VAT-inclusive sales.
// VATable Sales = VAT-inclusive amount / 1.12 ; VAT = amount - VATable Sales.
// VAT is computed as total - VATable so VATable + VAT always equals the total.
$receipt_vat_sales = round($receipt_total / 1.12, 2);
$receipt_vat_amount = round($receipt_total - $receipt_vat_sales, 2);
?>
<style>
    /* Receipt layout — applies to both the on-screen preview and printed output */
    #receipt-print-area { color: #000; background: #fff; }
    #receipt-print-area .r-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: baseline;
        gap: .25rem;
        line-height: 1.5;
    }
    #receipt-print-area .r-item-name {
        flex: 1 1 auto;
        min-width: 0;
        overflow-wrap: break-word;
        word-wrap: break-word;
    }
    #receipt-print-area .r-item-qtyprice {
        flex-shrink: 0;
        text-align: right;
        white-space: nowrap;
    }
    #receipt-print-area .r-dashed {
        border-top: 1px dashed #000;
        margin: .55rem 0;
    }
    #receipt-print-area .r-total .r-value { font-weight: 800; }
    #receipt-print-area .r-head {
        text-align: center;
        border-bottom: 2px dashed #000;
        padding-bottom: .55rem;
        margin-bottom: .55rem;
    }
</style>
<div id="receipt-print-area" class="receipt" style="font-family:'Courier New',Courier,monospace; font-size:.82rem;">
    <!-- Header — centered -->
    <div class="r-head">
        <div style="font-size:1.25rem;font-weight:800;letter-spacing:.5px;"><?= e(BUSINESS_NAME) ?></div>
        <div style="font-size:.8rem;"><?= e(BUSINESS_ADDRESS) ?></div>
        <div style="font-size:.8rem;">Contact: <?= e(BUSINESS_PHONE) ?></div>
        <div style="font-size:.8rem;">Email: <?= e(BUSINESS_EMAIL) ?></div>
    </div>

    <!-- Order information — left-aligned -->
    <div style="font-size:.82rem;">
        <div class="r-row"><span><?= e('Order No:') ?></span><span style="font-weight:800;"><?= e($receipt_order['order_number']) ?></span></div>
        <div class="r-row"><span><?= e('Cashier:') ?></span><span><?= e($receipt_cashier) ?></span></div>
        <div class="r-row"><span><?= e('Date:') ?></span><span><?= date('M d, Y h:i A', strtotime($receipt_paid_at)) ?></span></div>
        <div class="r-row"><span><?= e('Served:') ?></span><span><?= ucfirst(str_replace('_',' ',$receipt_order['order_type'])) ?></span></div>
        <div class="r-row"><span><?= e('Customer:') ?></span><span><?= e($receipt_order['customer_name']) ?></span></div>
    </div>

    <div class="r-dashed"></div>

    <!-- Items and prices -->
    <div style="font-size:.82rem;">
        <div class="r-row r-item-head" style="font-weight:700; text-transform:uppercase;">
            <span class="r-item-name"><?= e('Item') ?></span>
            <span class="r-item-qtyprice">Qty &times; Price</span>
        </div>
        <?php foreach ($receipt_items as $ri): ?>
            <div class="r-row r-item-line">
                <span class="r-item-name"><?= e($ri['product_name'] ?? 'Item') ?></span>
                <span class="r-item-qtyprice"><?= (int)$ri['quantity'] ?> &times; <?= number_format((float)$ri['price'],2) ?></span>
            </div>
            <div class="r-row r-item-amt" style="font-weight:700;">
                <span class="r-item-name"></span>
                <span class="r-item-qtyprice">= &nbsp;&#8369;<?= number_format((float)$ri['subtotal'],2) ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="r-dashed"></div>

    <!-- Transaction summary -->
    <div style="font-size:.85rem;">
        <div class="r-row"><span><?= e('Subtotal') ?></span><span class="r-value">&#8369;&nbsp;<?= number_format($receipt_subtotal,2) ?></span></div>
        <div class="r-row"><span><?= e('VATable Sales') ?></span><span class="r-value">&#8369;&nbsp;<?= number_format($receipt_vat_sales,2) ?></span></div>
        <div class="r-row"><span><?= e('VAT (12%)') ?></span><span class="r-value">&#8369;&nbsp;<?= number_format($receipt_vat_amount,2) ?></span></div>
        <div class="r-row r-total"><span><?= e('TOTAL') ?></span><span class="r-value">&#8369;&nbsp;<?= number_format($receipt_total,2) ?></span></div>
    </div>

    <div class="r-dashed"></div>

    <!-- Payment information -->
    <div style="font-size:.82rem;">
        <div class="r-row"><span><?= e('Payment') ?></span><span class="r-value"><?= e($receipt_order['payment_method'] ?? 'Cash') ?></span></div>
        <div class="r-row"><span><?= e('Cash') ?></span><span class="r-value">&#8369;&nbsp;<?= number_format((float)($receipt_cash_received ?? $receipt_total),2) ?></span></div>
        <div class="r-row"><span><?= e('Change') ?></span><span class="r-value">&#8369;&nbsp;<?= number_format((float)$receipt_change,2) ?></span></div>
    </div>

    <div class="r-dashed"></div>

    <!-- Footer — centered -->
    <div style="border-top:2px dashed #000; margin-top:.6rem; padding-top:.6rem; text-align:center; font-size:.8rem; line-height:1.5;">
        <div><?= e('This serves as your official receipt.') ?></div>
        <div><?= e('Thank you, and please come again.') ?></div>
        <div><?= e('For feedback, please call or text us at:') ?></div>
        <div style="font-weight:700;"><?= e(BUSINESS_PHONE) ?></div>
        <div><?= e('Email:') ?> <?= e(BUSINESS_EMAIL) ?></div>
    </div>
</div>

<?php if (!isset($receipt_no_print)): ?>
<script>
function printReceipt() {
    if (typeof fully !== 'undefined' && fully.print) {
        fully.print();
    } else {
        window.print();
    }
}
</script>
<?php endif; ?>
