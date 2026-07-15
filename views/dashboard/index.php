<div class="row mb-5">
    <div class="col-12">
        <div class="card card-glass border-0 p-4 shadow-sm text-white" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <div>
                    <h2 class="fw-bold mb-1">Welcome back, <?= e($currentUser['name']) ?>!</h2>
                    <p class="text-muted mb-0">Manage your digital assets, build new SaaS landing pages, and track client submissions.</p>
                </div>
                <div class="mt-3 mt-lg-0">
                    <span class="badge bg-primary fs-6 p-2 px-3 rounded-pill"><?= e($sub['plan_name']) ?> Plan Active</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards Grid -->
<div class="row mb-5">
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm p-4 h-100 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold">WEBSITES CREATED</span>
                    <h3 class="fw-bold text-dark mt-2 mb-0"><?= $stats['websites'] ?> / <?= $sub['website_limit'] ?></h3>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                    <i class="bi bi-globe display-6"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm p-4 h-100 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold">STORAGE RESOURCE</span>
                    <h3 class="fw-bold text-dark mt-2 mb-0"><?= $stats['storage'] ?> / <?= $sub['storage_limit_mb'] ?> MB</h3>
                </div>
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                    <i class="bi bi-hdd-network display-6"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm p-4 h-100 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold">DYNAMIC VISITORS</span>
                    <h3 class="fw-bold text-dark mt-2 mb-0"><?= $stats['visitors'] ?></h3>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                    <i class="bi bi-people-fill display-6"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm p-4 h-100 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold">FORM ENQUIRIES</span>
                    <h3 class="fw-bold text-dark mt-2 mb-0"><?= $stats['enquiries'] ?></h3>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                    <i class="bi bi-envelope-open-fill display-6"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Websites and Action Logs -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm p-4 bg-white mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Active Websites</h4>
                <a class="btn btn-primary btn-sm rounded-pill px-3" href="<?= APP_URL ?>/dashboard/websites"><i class="bi bi-plus-circle me-1"></i> Manage Sites</a>
            </div>
            
            <?php if (empty($websites)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-grid-3x3-gap display-4 text-muted"></i>
                    <p class="mt-3 text-muted">No websites generated yet. Launch the builder now!</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-muted small">
                                <th>NAME</th>
                                <th>SUBDOMAIN</th>
                                <th>CUSTOM DOMAIN</th>
                                <th>STATUS</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($websites as $web): ?>
                                <tr>
                                    <td>
                                        <h6 class="fw-bold mb-0"><?= e($web['name']) ?></h6>
                                        <span class="text-muted small"><?= e($web['category']) ?></span>
                                    </td>
                                    <td>
                                        <a href="<?= APP_URL ?>/site/<?= e($web['subdomain']) ?>" target="_blank" class="text-decoration-none">
                                            <?= e($web['subdomain']) ?>.local
                                        </a>
                                    </td>
                                    <td>
                                        <span class="text-muted small"><?= e($web['custom_domain'] ?: 'Not connected') ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($web['status'] === 'published') ? 'success' : 'secondary' ?>-subtle text-<?= ($web['status'] === 'published') ? 'success' : 'secondary' ?> p-2 px-3">
                                            <?= ucfirst($web['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= APP_URL ?>/builder/editor?id=<?= $web['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill"><i class="bi bi-pencil-square"></i> Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- User Log Stream -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm p-4 bg-white">
            <h4 class="fw-bold mb-4">Recent Activity Logs</h4>
            <?php if (empty($logs)): ?>
                <p class="text-muted small mb-0">No actions recorded in this billing cycle.</p>
            <?php else: ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($logs as $log): ?>
                        <li class="d-flex mb-3">
                            <div class="me-3 text-muted"><i class="bi bi-dot display-6"></i></div>
                            <div>
                                <h6 class="mb-1 text-dark small fw-bold"><?= e($log['action']) ?></h6>
                                <p class="text-muted small mb-1"><?= e($log['description']) ?></p>
                                <span class="text-muted" style="font-size: 0.75rem;"><?= date('M d, H:i', strtotime($log['created_at'])) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
