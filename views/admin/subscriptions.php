<div class="mb-5">
    <h2 class="fw-bold mb-1">Billing & Transactions Log</h2>
    <p class="text-muted mb-0">Platform transactions ledger tracking all Stripe, PayPal, and Razorpay subscription invoices.</p>
</div>

<div class="card border-0 shadow-sm p-4 bg-white">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr class="text-muted small">
                    <th>INVOICE NUMBER</th>
                    <th>CUSTOMER</th>
                    <th>GATEWAY</th>
                    <th>TRANSACTION ID</th>
                    <th>AMOUNT</th>
                    <th>DATE</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No transactions collected.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $pay): ?>
                        <tr>
                            <td><strong class="text-primary"><?= e($pay['invoice_number'] ?: 'N/A') ?></strong></td>
                            <td>
                                <h6 class="fw-bold mb-0"><?= e($pay['user_name']) ?></h6>
                                <span class="text-muted small"><?= e($pay['user_email']) ?></span>
                            </td>
                            <td><span class="badge bg-light text-dark"><?= strtoupper(e($pay['gateway'])) ?></span></td>
                            <td class="text-muted small"><?= e($pay['transaction_id']) ?></td>
                            <td><strong class="text-success">$<?= number_format($pay['amount'], 2) ?></strong></td>
                            <td class="text-muted small"><?= date('F d, Y H:i', strtotime($pay['created_at'])) ?></td>
                            <td>
                                <span class="badge bg-<?= ($pay['status'] === 'completed') ? 'success' : 'danger' ?>-subtle text-<?= ($pay['status'] === 'completed') ? 'success' : 'danger' ?> p-2 px-3">
                                    <?= ucfirst($pay['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
