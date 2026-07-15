<div class="d-flex justify-content-between align-items-center mb-5 flex-wrap">
    <div>
        <h2 class="fw-bold mb-1">My Website Projects</h2>
        <p class="text-muted mb-0">Build websites using pre-made marketplace templates or trigger our instant AI generator wizard.</p>
    </div>
    <div class="mt-3 mt-lg-0">
        <button class="btn btn-outline-dark rounded-pill px-3 me-2" data-bs-toggle="modal" data-bs-target="#createSiteModal"><i class="bi bi-file-earmark-plus"></i> Blank Site</button>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#aiGeneratorModal"><i class="bi bi-cpu-fill me-1"></i> Generate with AI</button>
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

<!-- Websites Grid -->
<div class="row">
    <?php foreach ($websites as $web): ?>
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                <div style="height: 160px; background: linear-gradient(135deg, <?= $web['primary_color'] ?> 0%, <?= $web['secondary_color'] ?> 100%);" class="d-flex align-items-center justify-content-center text-white">
                    <div class="text-center">
                        <i class="bi bi-layout-text-window-reverse display-5 mb-2"></i>
                        <h5 class="fw-bold mb-0"><?= e($web['name']) ?></h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary p-2 rounded-3"><?= e($web['category']) ?></span>
                        <span class="badge bg-<?= ($web['status'] === 'published') ? 'success' : 'warning' ?> p-1 px-2 text-white small"><?= ucfirst($web['status']) ?></span>
                    </div>
                    <p class="text-muted small mb-3">
                        Subdomain: <strong><?= e($web['subdomain']) ?>.local</strong>
                        <?php if (!empty($web['custom_domain'])): ?>
                            <br>Domain: <strong class="text-success"><?= e($web['custom_domain']) ?></strong>
                        <?php endif; ?>
                    </p>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="<?= APP_URL ?>/builder/editor?id=<?= $web['id'] ?>" class="btn btn-outline-primary btn-sm w-100 rounded-pill"><i class="bi bi-pencil-square"></i> Open Editor</a>
                        </div>
                        <div class="col-6">
                            <a href="<?= APP_URL ?>/site/<?= e($web['subdomain']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm w-100 rounded-pill"><i class="bi bi-eye"></i> View Live</a>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm w-100 rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">Site Settings & Export</button>
                                <ul class="dropdown-menu w-100 border-0 shadow-lg p-2">
                                    <li><a class="dropdown-item rounded-3" href="#" onclick="openRenameModal(<?= $web['id'] ?>, '<?= e($web['name']) ?>')"><i class="bi bi-cursor-text me-2"></i> Rename Project</a></li>
                                    <li><a class="dropdown-item rounded-3" href="#" onclick="openDuplicateModal(<?= $web['id'] ?>, '<?= e($web['name']) ?>')"><i class="bi bi-files me-2"></i> Duplicate Site</a></li>
                                    <li><a class="dropdown-item rounded-3" href="#" onclick="openDomainModal(<?= $web['id'] ?>, '<?= e($web['custom_domain'] ?? '') ?>')"><i class="bi bi-link-45deg me-2 text-primary"></i> Custom Domain</a></li>
                                    <li><a class="dropdown-item rounded-3" href="#" onclick="openSeoModal(<?= $web['id'] ?>)"><i class="bi bi-search me-2 text-info"></i> SEO Settings</a></li>
                                    <li><a class="dropdown-item rounded-3" href="<?= APP_URL ?>/api/export/zip?id=<?= $web['id'] ?>"><i class="bi bi-file-earmark-zip-fill me-2 text-success"></i> Download static ZIP</a></li>
                                    <li><a class="dropdown-item rounded-3" href="#" onclick="openGithubModal(<?= $web['id'] ?>)"><i class="bi bi-github me-2 text-dark"></i> Deploy to Github</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger rounded-3" href="#" onclick="openDeleteModal(<?= $web['id'] ?>)"><i class="bi bi-trash3 me-2"></i> Delete Site</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- =====================================================
     MODALS
     ===================================================== -->

<!-- Create site modal -->
<div class="modal fade" id="createSiteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title">Create Site</h5>
                <button type="button" class="btn-close" data-bs-close="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="<?= APP_URL ?>/dashboard/websites">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Project Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Acme Corp" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subdomain (e.g. acme)</label>
                        <input type="text" name="subdomain" class="form-control" placeholder="acme" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Starting Template</label>
                        <select name="template" class="form-select">
                            <option value="">Blank canvas</option>
                            <?php foreach ($templates as $tmpl): ?>
                                <option value="<?= e($tmpl['slug']) ?>"><?= e($tmpl['name']) ?> (<?= e($tmpl['category']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill mt-3">Generate Draft Canvas</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- AI Generator modal -->
<div class="modal fade" id="aiGeneratorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-cpu-fill text-primary"></i> AI Site Builder Wizard</h5>
                <button type="button" class="btn-close" data-bs-close="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="aiForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Business Name</label>
                            <input type="text" name="business_name" class="form-control" placeholder="Le Gourmet Bistro" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Business Category</label>
                            <select name="category" class="form-select" required>
                                <option value="Restaurant">Restaurant / Food</option>
                                <option value="Gym">Gym & Fitness</option>
                                <option value="Agency">Consulting / Business Agency</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="info@gourmet.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+1 (555) 019-2834" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" placeholder="789 Foodie Lane, San Francisco, CA" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Website Project Name</label>
                            <input type="text" name="website_name" class="form-control" placeholder="Le Gourmet Bistro" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Describe your Business details & objectives</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="A cozy French bistro serving organic ingredients cooked with age-old recipes in downtown." required></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Primary Color Theme</label>
                            <input type="color" name="primary_color" class="form-control form-control-color w-100" value="#2c1a11">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Secondary Color Theme</label>
                            <input type="color" name="secondary_color" class="form-control form-control-color w-100" value="#a57c5b">
                        </div>
                    </div>
                    <div id="aiMsg" class="alert alert-info border-0 d-none mt-3"></div>
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill mt-4" id="aiSubmitBtn"><i class="bi bi-magic"></i> Generate Website and Content Now</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Github Deploy modal -->
<div class="modal fade" id="githubModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-github"></i> Deploy to Github</h5>
                <button type="button" class="btn-close" data-bs-close="modal"></button>
            </div>
            <div class="modal-body">
                <form id="githubForm">
                    <input type="hidden" name="website_id" id="githubWebId">
                    <div class="mb-3">
                        <label class="form-label">GitHub Repository Name</label>
                        <input type="text" name="repo_name" class="form-control" placeholder="my-awesome-site" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">GitHub Personal Access Token (OAuth)</label>
                        <input type="password" name="github_token" class="form-control" placeholder="ghp_••••••••••••" required>
                    </div>
                    <div id="gitMsg" class="alert alert-info border-0 d-none"></div>
                    <button type="submit" class="btn btn-dark w-100 py-2 rounded-pill mt-3">Push Static Pages to Main</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Rename Modal -->
<div class="modal fade" id="renameModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title">Rename Project</h5>
                <button type="button" class="btn-close" data-bs-close="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="<?= APP_URL ?>/dashboard/websites">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="rename">
                    <input type="hidden" name="website_id" id="renameWebId">
                    <div class="mb-3">
                        <label class="form-label">New Website Name</label>
                        <input type="text" name="name" id="renameWebName" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill mt-3">Rename Website</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Duplicate Modal -->
<div class="modal fade" id="duplicateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title">Duplicate Website</h5>
                <button type="button" class="btn-close" data-bs-close="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="<?= APP_URL ?>/dashboard/websites">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="duplicate">
                    <input type="hidden" name="website_id" id="duplicateWebId">
                    <div class="mb-3">
                        <label class="form-label">Duplicate Name</label>
                        <input type="text" name="name" id="duplicateWebName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Subdomain</label>
                        <input type="text" name="subdomain" class="form-control" placeholder="new-subdomain" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill mt-3">Duplicate Project</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Custom Domain modal -->
<div class="modal fade" id="domainModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-link-45deg text-primary"></i> Custom Domain Manager</h5>
                <button type="button" class="btn-close" data-bs-close="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- DNS Info Card -->
                <div class="card bg-light border-0 p-3 mb-4 rounded-3">
                    <h6 class="fw-bold mb-2 small text-uppercase text-muted"><i class="bi bi-info-circle-fill me-1"></i> DNS Configuration Instructions</h6>
                    <p class="small text-muted mb-2">Configure these DNS records at your domain registrar (e.g. GoDaddy, Namecheap) to map traffic:</p>
                    <table class="table table-sm table-borderless mb-0 style-table small text-muted">
                        <tbody>
                            <tr>
                                <td><strong>Record Type:</strong></td>
                                <td>CNAME</td>
                            </tr>
                            <tr>
                                <td><strong>Host:</strong></td>
                                <td><code>www</code></td>
                            </tr>
                            <tr>
                                <td><strong>Points to:</strong></td>
                                <td><code>cname.webforge.local</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="<?= APP_URL ?>/dashboard/websites">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="connect_domain">
                    <input type="hidden" name="website_id" id="domainWebId">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">CUSTOM DOMAIN NAME</label>
                        <input type="text" name="domain_name" id="domainNameInput" class="form-control" placeholder="www.mycompany.com" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill mb-2">Save Domain Connect</button>
                </form>

                <form method="POST" action="<?= APP_URL ?>/dashboard/websites" id="disconnectDomainForm" class="d-none">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="disconnect_domain">
                    <input type="hidden" name="website_id" id="disconnectWebId">
                    <button type="submit" class="btn btn-outline-danger w-100 py-2 rounded-pill mt-1">Disconnect Current Domain</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SEO settings modal -->
<div class="modal fade" id="seoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-search text-info"></i> SEO Metadata Settings</h5>
                <button type="button" class="btn-close" data-bs-close="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="<?= APP_URL ?>/dashboard/websites">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="save_seo">
                    <input type="hidden" name="website_id" id="seoWebId">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">META TITLE</label>
                        <input type="text" name="meta_title" id="seoTitleInput" class="form-control" placeholder="Acme Services - Premier Growth Agency" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">META DESCRIPTION</label>
                        <textarea name="meta_description" id="seoDescInput" class="form-control" rows="3" placeholder="Acme offers high-performance IT consulting and low energy compute cloud optimization..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">META KEYWORDS</label>
                        <input type="text" name="meta_keywords" id="seoKeywordsInput" class="form-control" placeholder="consulting, low energy compute, optimization">
                    </div>
                    <div id="seoLoading" class="alert alert-info border-0 p-2 small mb-3 text-center d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span> Loading current metadata...
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill mt-3">Save SEO Config</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-body p-4 text-center">
                <i class="bi bi-exclamation-triangle-fill text-danger display-4 mb-3"></i>
                <h4 class="fw-bold">Are you absolutely sure?</h4>
                <p class="text-muted">Deleting this website is permanent. All pages, sections, and collected form submissions will be deleted.</p>
                <form method="POST" action="<?= APP_URL ?>/dashboard/websites">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="website_id" id="deleteWebId">
                    <button type="submit" class="btn btn-danger py-2 rounded-pill px-4 me-2">Yes, delete it</button>
                    <button type="button" class="btn btn-light py-2 rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- jQuery (Required for Bootstrap AJAX Modals and wizard interactions) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Set parameters in actions modals
    function openRenameModal(id, name) {
        $('#renameWebId').val(id);
        $('#renameWebName').val(name);
        new bootstrap.Modal(document.getElementById('renameModal')).show();
    }
    
    function openDuplicateModal(id, name) {
        $('#duplicateWebId').val(id);
        $('#duplicateWebName').val(name + ' (Copy)');
        new bootstrap.Modal(document.getElementById('duplicateModal')).show();
    }
    
    function openDeleteModal(id) {
        $('#deleteWebId').val(id);
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    function openGithubModal(id) {
        $('#githubWebId').val(id);
        new bootstrap.Modal(document.getElementById('githubModal')).show();
    }

    // AI Generation AJAX Form Submit
    $('#aiForm').on('submit', function(e) {
        e.preventDefault();
        $('#aiSubmitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Assembling Content & Design...');
        $('#aiMsg').removeClass('d-none').addClass('alert-info').text('The AI is dynamically building your homepage, about sections, custom testimonials, FAQ cards, and writing your copy...');
        
        $.ajax({
            url: '<?= APP_URL ?>/api/ai/generate',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                if (resp.success) {
                    $('#aiMsg').removeClass('alert-info').addClass('alert-success').text('Website generated successfully! Redirecting...');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    $('#aiMsg').removeClass('alert-info').addClass('alert-danger').text(resp.message);
                    $('#aiSubmitBtn').prop('disabled', false).html('<i class="bi bi-magic"></i> Generate Website and Content Now');
                }
            },
            error: function() {
                $('#aiMsg').removeClass('alert-info').addClass('alert-danger').text('Server error occurred during generation.');
                $('#aiSubmitBtn').prop('disabled', false).html('<i class="bi bi-magic"></i> Generate Website and Content Now');
            }
        });
    });

    // Github Deploy AJAX Form
    $('#githubForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Deploying...');
        $('#gitMsg').removeClass('d-none').addClass('alert-info').text('Configuring repository and pushing static files...');
        
        $.ajax({
            url: '<?= APP_URL ?>/api/github/push',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                if (resp.success) {
                    $('#gitMsg').removeClass('alert-info').addClass('alert-success').text(resp.message);
                } else {
                    $('#gitMsg').removeClass('alert-info').addClass('alert-danger').text(resp.message);
                }
                btn.prop('disabled', false).text('Push Static Pages to Main');
            },
            error: function() {
                $('#gitMsg').removeClass('alert-info').addClass('alert-danger').text('GitHub connection error.');
                btn.prop('disabled', false).text('Push Static Pages to Main');
            }
        });
    });

    function openDomainModal(id, domain) {
        $('#domainWebId').val(id);
        $('#disconnectWebId').val(id);
        
        if (domain) {
            $('#domainNameInput').val(domain);
            $('#disconnectDomainForm').removeClass('d-none');
        } else {
            $('#domainNameInput').val('');
            $('#disconnectDomainForm').addClass('d-none');
        }
        
        new bootstrap.Modal(document.getElementById('domainModal')).show();
    }

    function openSeoModal(id) {
        $('#seoWebId').val(id);
        $('#seoTitleInput').val('');
        $('#seoDescInput').val('');
        $('#seoKeywordsInput').val('');
        
        $('#seoLoading').removeClass('d-none');
        
        // Fetch current SEO settings via AJAX
        $.ajax({
            url: '<?= APP_URL ?>/api/builder/seo',
            method: 'GET',
            data: { website_id: id },
            success: function(resp) {
                $('#seoLoading').addClass('d-none');
                if (resp.success && resp.data) {
                    $('#seoTitleInput').val(resp.data.meta_title || '');
                    $('#seoDescInput').val(resp.data.meta_description || '');
                    $('#seoKeywordsInput').val(resp.data.meta_keywords || '');
                }
            },
            error: function() {
                $('#seoLoading').addClass('d-none');
                alert('Failed to load SEO metadata.');
            }
        });
        
        new bootstrap.Modal(document.getElementById('seoModal')).show();
    }
</script>
