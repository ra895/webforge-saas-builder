<div class="row mb-5">
    <div class="col-12">
        <div class="card card-glass border-0 p-4 shadow-sm text-white" style="background: linear-gradient(135deg, #7c2d12 0%, #451a03 100%);">
            <h2 class="fw-bold mb-1"><i class="bi bi-shield-lock-fill me-2"></i> Platform Control Panel</h2>
            <p class="text-white-50 mb-0">Platform Overview, user registrations, subscription revenue collection, and site compliance approvals.</p>
        </div>
    </div>
</div>

<!-- Admin KPIs -->
<div class="row mb-5">
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold">TOTAL SUBSCRIBERS</span>
                    <h3 class="fw-bold text-dark mt-2 mb-0"><?= $stats['users'] ?></h3>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle"><i class="bi bi-people display-6"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold">TOTAL SITES HOSTED</span>
                    <h3 class="fw-bold text-dark mt-2 mb-0"><?= $stats['websites'] ?></h3>
                </div>
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle"><i class="bi bi-window-sidebar display-6"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold">GROSS REVENUE</span>
                    <h3 class="fw-bold text-dark mt-2 mb-0">$<?= $stats['revenue'] ?></h3>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle"><i class="bi bi-currency-dollar display-6"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold">OPEN TICKETS</span>
                    <h3 class="fw-bold text-dark mt-2 mb-0"><?= $stats['tickets'] ?></h3>
                </div>
                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle"><i class="bi bi-question-circle display-6"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Approvals Queue -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm p-4 bg-white h-100">
            <h4 class="fw-bold mb-4">Websites Awaiting Approval</h4>
            <?php if (empty($approvals)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-patch-check display-5"></i>
                    <p class="mt-2 small">All sites verified. The queue is clean!</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-muted small">
                                <th>SITE NAME</th>
                                <th>OWNER</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvals as $app): ?>
                                <tr>
                                    <td>
                                        <h6 class="fw-bold mb-0"><?= e($app['name']) ?></h6>
                                        <span class="text-muted small"><?= e($app['subdomain']) ?>.local</span>
                                    </td>
                                    <td><?= e($app['owner_name']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-success rounded-pill px-3">Approve</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Billing ledger -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm p-4 bg-white h-100">
            <h4 class="fw-bold mb-4">Recent Payment Records</h4>
            <?php if (empty($payments)): ?>
                <p class="text-muted small py-4 text-center">No payment entries collected yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-muted small">
                                <th>USER</th>
                                <th>GATEWAY</th>
                                <th>AMOUNT</th>
                                <th>DATE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td><?= e($pay['user_name']) ?></td>
                                    <td><span class="badge bg-light text-dark"><?= ucfirst(e($pay['gateway'])) ?></span></td>
                                    <td><strong class="text-success">$<?= number_format($pay['amount'], 2) ?></strong></td>
                                    <td class="text-muted small"><?= date('M d, Y', strtotime($pay['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
