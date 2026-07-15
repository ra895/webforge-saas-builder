<div class="d-flex justify-content-between align-items-center mb-5 flex-wrap">
    <div>
        <h2 class="fw-bold mb-1">Templates Marketplace Editor</h2>
        <p class="text-muted mb-0">Define, modify, and publish JSON layout configurations for user templates.</p>
    </div>
    <div class="mt-3 mt-lg-0">
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#templateModal"><i class="bi bi-plus-circle me-1"></i> Add New Template</button>
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
                    <th>TEMPLATE NAME</th>
                    <th>SLUG</th>
                    <th>CATEGORY</th>
                    <th>CREATED AT</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($templates as $tmpl): ?>
                    <tr>
                        <td>
                            <h6 class="fw-bold mb-0"><?= e($tmpl['name']) ?></h6>
                            <span class="text-muted small text-truncate d-inline-block" style="max-width: 320px;"><?= e($tmpl['description']) ?></span>
                        </td>
                        <td><code><?= e($tmpl['slug']) ?></code></td>
                        <td><span class="badge bg-light text-dark p-2"><?= e($tmpl['category']) ?></span></td>
                        <td class="text-muted small"><?= date('F d, Y', strtotime($tmpl['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="editTemplate(<?= htmlspecialchars(json_encode($tmpl), ENT_QUOTES, 'UTF-8') ?>)">Edit</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add / Edit Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title" id="modalTitle">Add Template</h5>
                <button type="button" class="btn-close" data-bs-close="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="<?= APP_URL ?>/admin/templates">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Template Name</label>
                            <input type="text" name="name" id="tmplName" class="form-control" placeholder="Agency Luxe" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="tmplSlug" class="form-control" placeholder="agency-luxe" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" id="tmplCategory" class="form-control" placeholder="Agency" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Short Description</label>
                            <textarea name="description" id="tmplDesc" class="form-control" rows="2" placeholder="High impact template for creative agencies..."></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Layout Section JSON Array</label>
                            <textarea name="layout_json" id="tmplLayout" class="form-control" rows="8" placeholder="[]" required></textarea>
                            <div class="form-text small text-muted">Copy paste section JSON arrays. Example: <code>[{"type":"hero", "content":{"title":"Heading"}}]</code></div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill mt-3">Save Template Configuration</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function editTemplate(tmpl) {
        $('#modalTitle').text('Edit Template: ' + tmpl.name);
        $('#tmplName').val(tmpl.name);
        // Make slug readonly on edits
        $('#tmplSlug').val(tmpl.slug).prop('readonly', true);
        $('#tmplCategory').val(tmpl.category);
        $('#tmplDesc').val(tmpl.description);
        
        // Format JSON if possible
        try {
            var parsed = JSON.parse(tmpl.layout_json);
            $('#tmplLayout').val(JSON.stringify(parsed, null, 2));
        } catch(e) {
            $('#tmplLayout').val(tmpl.layout_json);
        }

        new bootstrap.Modal(document.getElementById('templateModal')).show();
    }
</script>
