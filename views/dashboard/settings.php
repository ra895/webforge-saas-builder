<div class="mb-5">
    <h2 class="fw-bold mb-1">Developer & Integration Settings</h2>
    <p class="text-muted mb-0">Configure your custom lead email relays (SMTP) and embed site tracking pixels (Google Analytics, Facebook Pixel).</p>
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

<form method="POST" action="<?= APP_URL ?>/dashboard/settings">
    <?= csrf_field() ?>
    <div class="row">
        <!-- SMTP Lead Relay -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm p-4 bg-white h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-envelope-at-fill text-primary"></i> SMTP Mail Settings</h5>
                <p class="text-muted small">Relay customer contact enquiries to your mailbox using dedicated SMTP credentials.</p>
                <hr>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">SMTP HOST</label>
                    <input type="text" name="smtp_host" class="form-control" value="<?= e($smtp['host'] ?? '') ?>" placeholder="smtp.mailgun.org">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">PORT</label>
                        <input type="number" name="smtp_port" class="form-control" value="<?= e($smtp['port'] ?? 587) ?>" placeholder="587">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">ENCRYPTION</label>
                        <select name="smtp_encryption" class="form-select">
                            <option value="tls" <?= ($smtp['encryption'] === 'tls') ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= ($smtp['encryption'] === 'ssl') ? 'selected' : '' ?>>SSL</option>
                            <option value="none" <?= ($smtp['encryption'] === 'none') ? 'selected' : '' ?>>None</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">USERNAME</label>
                    <input type="text" name="smtp_username" class="form-control" value="<?= e($smtp['username'] ?? '') ?>" placeholder="postmaster@mg.domain.com">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">PASSWORD</label>
                    <input type="password" name="smtp_password" class="form-control" value="<?= e($smtp['password'] ?? '') ?>" placeholder="••••••••••••">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">FROM EMAIL</label>
                        <input type="email" name="smtp_from_email" class="form-control" value="<?= e($smtp['from_email'] ?? '') ?>" placeholder="leads@domain.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">FROM NAME</label>
                        <input type="text" name="smtp_from_name" class="form-control" value="<?= e($smtp['from_name'] ?? '') ?>" placeholder="My Leads Agent">
                    </div>
                </div>
            </div>
        </div>

        <!-- Marketing and Pixels -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm p-4 bg-white h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-bezier2 text-success"></i> Site Tracking & Chat Pixels</h5>
                <p class="text-muted small">Inject site analytics tools and dynamic floating support widgets directly onto your generated websites.</p>
                <hr>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">GOOGLE ANALYTICS MEASUREMENT ID</label>
                    <input type="text" name="analytics_id" class="form-control" value="<?= e($analytics_id) ?>" placeholder="G-XXXXXXXXXX">
                    <div class="form-text small">Connect Google Analytics 4 tags to audit site traffic metrics.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">FACEBOOK PIXEL ID</label>
                    <input type="text" name="pixel_id" class="form-control" value="<?= e($pixel_id) ?>" placeholder="123456789012345">
                    <div class="form-text small">Embed Facebook/Meta marketing tags to verify advertising conversions.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">WHATSAPP CHAT NUMBER</label>
                    <input type="text" name="whatsapp_no" class="form-control" value="<?= e($whatsapp_no) ?>" placeholder="+1234567890">
                    <div class="form-text small">Provide country code prefix. Inserts a neat floating WhatsApp support chat button to your pages.</div>
                </div>
            </div>
        </div>
        
        <div class="col-12 mt-3 text-end">
            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-semibold">Save Integration Profiles</button>
        </div>
    </div>
</form>
