<div class="mb-5">
    <h2 class="fw-bold mb-1">Global System Settings</h2>
    <p class="text-muted mb-0">Configure payment gateways, OAuth configurations, and AI developer API keys.</p>
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

<form method="POST" action="<?= APP_URL ?>/admin/settings">
    <?= csrf_field() ?>
    <div class="row">
        <!-- Site Configuration -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm p-4 bg-white h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-gear-fill text-primary"></i> General Settings</h5>
                <hr>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">SAAS PORTAL NAME</label>
                    <input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? 'WebForge') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">ALLOW PUBLIC REGISTRATIONS</label>
                    <select name="allow_registration" class="form-select">
                        <option value="1" <?= (($settings['allow_registration'] ?? '1') === '1') ? 'selected' : '' ?>>Enabled</option>
                        <option value="0" <?= (($settings['allow_registration'] ?? '1') === '0') ? 'selected' : '' ?>>Disabled</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">PAYMENT SYSTEM MODE</label>
                    <select name="payment_mode" class="form-select">
                        <option value="sandbox" <?= (($settings['payment_mode'] ?? 'sandbox') === 'sandbox') ? 'selected' : '' ?>>Sandbox / Mock Mode</option>
                        <option value="live" <?= (($settings['payment_mode'] ?? 'sandbox') === 'live') ? 'selected' : '' ?>>Live production Mode</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- AI credentials -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm p-4 bg-white h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-cpu-fill text-warning"></i> AI API Access Profiles</h5>
                <hr>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">GEMINI API KEY</label>
                    <input type="password" name="gemini_api_key" class="form-control" value="<?= e($settings['gemini_api_key'] ?? '') ?>" placeholder="AIzaSy...">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">OPENAI API KEY</label>
                    <input type="password" name="openai_api_key" class="form-control" value="<?= e($settings['openai_api_key'] ?? '') ?>" placeholder="sk-...">
                </div>
            </div>
        </div>

        <!-- Payment integrations credentials -->
        <div class="col-lg-12 mb-4">
            <div class="card border-0 shadow-sm p-4 bg-white">
                <h5 class="fw-bold mb-3"><i class="bi bi-credit-card-2-back-fill text-success"></i> Payment Gateway Gate credentials</h5>
                <hr>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">STRIPE PUBLIC KEY</label>
                        <input type="text" name="stripe_key" class="form-control" value="<?= e($settings['stripe_key'] ?? '') ?>" placeholder="pk_test_...">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">STRIPE SECRET KEY</label>
                        <input type="password" name="stripe_secret" class="form-control" value="<?= e($settings['stripe_secret'] ?? '') ?>" placeholder="sk_test_...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small fw-bold">PAYPAL CLIENT ID</label>
                        <input type="text" name="paypal_client_id" class="form-control" value="<?= e($settings['paypal_client_id'] ?? '') ?>" placeholder="PayPal_Client_...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small fw-bold">RAZORPAY KEY ID</label>
                        <input type="text" name="razorpay_key_id" class="form-control" value="<?= e($settings['razorpay_key_id'] ?? '') ?>" placeholder="rzp_test_...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small fw-bold">GITHUB OAUTH CLIENT ID</label>
                        <input type="text" name="github_client_id" class="form-control" value="<?= e($settings['github_client_id'] ?? '') ?>" placeholder="Client_ID">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-semibold">Save Global Configurations</button>
        </div>
    </div>
</form>
