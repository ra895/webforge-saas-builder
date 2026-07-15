<div class="d-flex justify-content-between align-items-center mb-5 flex-wrap">
    <div>
        <h2 class="fw-bold mb-1">Users Management</h2>
        <p class="text-muted mb-0">Suspend or restore subscriber accounts and audit their SaaS subscription levels.</p>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger border-0 mb-4">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success border-0 mb-4"><?= e($success) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm p-4 bg-white">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr class="text-muted small">
                    <th>SUBSCRIBER</th>
                    <th>EMAIL</th>
                    <th>PLAN</th>
                    <th>STATUS</th>
                    <th>REGISTERED ON</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <h6 class="fw-bold mb-0"><?= e($u['name']) ?></h6>
                            <span class="text-muted small">ID: #<?= $u['id'] ?></span>
                        </td>
                        <td><?= e($u['email']) ?></td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary p-2">
                                <?= e($u['plan_name'] ?: 'None') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= ($u['status'] === 'active') ? 'success' : 'danger' ?>-subtle text-<?= ($u['status'] === 'active') ? 'success' : 'danger' ?> p-2 px-3">
                                <?= ucfirst($u['status']) ?>
                            </span>
                        </td>
                        <td class="text-muted small"><?= date('F d, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['status'] === 'active'): ?>
                                <form method="POST" action="<?= APP_URL ?>/admin/users" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="action" value="suspend">
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">Suspend</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="<?= APP_URL ?>/admin/users" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3">Activate</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
