<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            color: #e2e8f0;
            overflow: hidden;
            height: 100vh;
        }
        /* Top Navigation */
        .editor-topbar {
            height: 60px;
            background-color: #1e293b;
            border-bottom: 1px solid #334155;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 1000;
        }
        .logo-branding {
            font-weight: 800;
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        /* Workspace layout */
        .editor-workspace {
            display: flex;
            height: calc(100vh - 60px);
            position: relative;
        }
        /* Sidebars */
        .editor-sidebar {
            width: 280px;
            background-color: #1e293b;
            border-right: 1px solid #334155;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            z-index: 100;
        }
        .editor-sidebar-right {
            width: 320px;
            background-color: #1e293b;
            border-left: 1px solid #334155;
            border-right: none;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            z-index: 100;
        }
        .sidebar-section {
            padding: 20px;
            border-bottom: 1px solid #334155;
        }
        .sidebar-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            font-weight: 700;
            margin-bottom: 15px;
        }
        /* Canvas Center */
        .editor-canvas-area {
            flex-grow: 1;
            background-color: #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            overflow-y: auto;
            position: relative;
        }
        /* Device Previews */
        .canvas-container {
            width: 100%;
            max-width: 100%;
            background-color: #fff;
            color: #212529;
            min-height: 800px;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .canvas-desktop { max-width: 100%; }
        .canvas-tablet { max-width: 768px; }
        .canvas-mobile { max-width: 390px; }

        /* Block Controls overlay */
        .editor-block {
            position: relative;
            border: 2px solid transparent;
            transition: border-color 0.15s ease-in-out;
        }
        .editor-block:hover, .editor-block.active-block {
            border-color: #0d6efd;
        }
        .block-toolbar {
            position: absolute;
            top: -30px;
            right: 10px;
            background-color: #0d6efd;
            border-radius: 4px 4px 0 0;
            padding: 2px 8px;
            display: none;
            gap: 8px;
            z-index: 99;
        }
        .editor-block:hover .block-toolbar, .editor-block.active-block .block-toolbar {
            display: flex;
            align-items: center;
        }
        .block-toolbar button {
            background: none;
            border: none;
            color: #fff;
            font-size: 0.85rem;
            cursor: pointer;
        }
        .block-toolbar button:hover {
            opacity: 0.8;
        }
        .drag-handle {
            cursor: grab;
        }
        
        /* Sidebar toolbox components */
        .tool-block-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 12px;
            color: #cbd5e1;
            text-align: left;
            width: 100%;
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            cursor: grab;
            transition: background 0.2s;
        }
        .tool-block-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        .tool-block-btn i {
            font-size: 1.25rem;
            margin-right: 12px;
            color: #0dcaf0;
        }
        
        /* Direct edit inputs styles */
        .form-dark {
            background-color: #1e293b;
            border: 1px solid #334155;
            color: #fff;
            border-radius: 8px;
        }
        .form-dark:focus {
            background-color: #1e293b;
            border-color: #0d6efd;
            box-shadow: none;
            color: #fff;
        }
        
        /* Loader overlay */
        #loaderOverlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

<div id="loaderOverlay">
    <div class="text-center text-white">
        <div class="spinner-border text-primary mb-3"></div>
        <h5 class="fw-bold">Saving Page Workspace...</h5>
    </div>
</div>

<!-- Topbar Navigation -->
<header class="editor-topbar">
    <div class="d-flex align-items-center">
        <a href="<?= APP_URL ?>/dashboard/websites" class="btn btn-outline-light btn-sm rounded-pill px-3 me-3"><i class="bi bi-chevron-left"></i> Dashboard</a>
        <h4 class="mb-0 fw-bold logo-branding me-3">WebForge Editor</h4>
        <span class="badge bg-secondary p-2 small"><?= e($website['name']) ?></span>
    </div>
    
    <!-- Responsive View selectors -->
    <div class="btn-group border border-secondary rounded-pill p-1 bg-dark">
        <button class="btn btn-dark btn-sm rounded-pill px-3 active" onclick="setCanvasMode('desktop', this)"><i class="bi bi-laptop"></i></button>
        <button class="btn btn-dark btn-sm rounded-pill px-3" onclick="setCanvasMode('tablet', this)"><i class="bi bi-tablet"></i></button>
        <button class="btn btn-dark btn-sm rounded-pill px-3" onclick="setCanvasMode('mobile', this)"><i class="bi bi-phone"></i></button>
    </div>

    <div>
        <button class="btn btn-outline-info btn-sm rounded-pill px-3 me-2" onclick="undo()"><i class="bi bi-arrow-90deg-left"></i></button>
        <button class="btn btn-outline-info btn-sm rounded-pill px-3 me-2" onclick="redo()"><i class="bi bi-arrow-90deg-right"></i></button>
        <a class="btn btn-outline-light btn-sm rounded-pill px-3 me-2" href="<?= APP_URL ?>/builder/preview?id=<?= $website['id'] ?>&page_id=<?= $currentPage['id'] ?>" target="_blank"><i class="bi bi-eye"></i> Preview</a>
        <button class="btn btn-primary btn-sm rounded-pill px-4" onclick="savePage()"><i class="bi bi-cloud-check-fill"></i> Save Layout</button>
    </div>
</header>

<div class="editor-workspace">
    <!-- Left Sidebar: Section Blocks -->
    <div class="editor-sidebar">
        <div class="sidebar-section">
            <h6 class="sidebar-title">Canvas Navigation</h6>
            <label class="form-label text-muted small">ACTIVE PAGE</label>
            <select class="form-select form-dark mb-2" onchange="switchPage(this.value)">
                <?php foreach ($pages as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($p['id'] == $currentPage['id']) ? 'selected' : '' ?>><?= e($p['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="sidebar-section">
            <h6 class="sidebar-title">Drag & Drop Blocks</h6>
            <div id="toolbox">
                <button class="tool-block-btn" onclick="addNewSection('hero')"><i class="bi bi-star-fill"></i> Hero Banner</button>
                <button class="tool-block-btn" onclick="addNewSection('about')"><i class="bi bi-file-earmark-person"></i> About Us</button>
                <button class="tool-block-btn" onclick="addNewSection('services')"><i class="bi bi-briefcase-fill"></i> Services</button>
                <button class="tool-block-btn" onclick="addNewSection('features')"><i class="bi bi-check-circle-fill"></i> Features</button>
                <button class="tool-block-btn" onclick="addNewSection('testimonials')"><i class="bi bi-chat-quote-fill"></i> Testimonials</button>
                <button class="tool-block-btn" onclick="addNewSection('faq')"><i class="bi bi-question-circle-fill"></i> FAQ Block</button>
                <button class="tool-block-btn" onclick="addNewSection('contact')"><i class="bi bi-envelope-at-fill"></i> Contact Details</button>
                <button class="tool-block-btn" onclick="addNewSection('footer')"><i class="bi bi-c-circle-fill"></i> Simple Footer</button>
            </div>
        </div>
    </div>

    <!-- Center Canvas Workspace -->
    <div class="editor-canvas-area">
        <div id="canvas" class="canvas-container canvas-desktop">
            <!-- Dynamic Sections get populated here -->
        </div>
    </div>

    <!-- Right Sidebar: Attributes and properties panel -->
    <div class="editor-sidebar-right">
        <div class="sidebar-section p-4" id="propertiesPanel">
            <h6 class="sidebar-title">Property Manager</h6>
            <p class="text-muted small">Click any block in the canvas workspace to modify details or colors.</p>
        </div>
    </div>
</div>

<!-- jQuery, SortableJS, and Custom editor state script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    // State storage arrays
    let activePageId = <?= $currentPage['id'] ?>;
    let pageSections = <?= json_encode($sections) ?>;
    let selectedBlockIndex = null;
    
    // Undo/Redo stacks
    let undoHistory = [];
    let redoHistory = [];

    // Push local state to history
    function pushState() {
        undoHistory.push(JSON.stringify(pageSections));
        redoHistory = []; // Reset redo stack on new action
    }

    function undo() {
        if (undoHistory.length > 0) {
            redoHistory.push(JSON.stringify(pageSections));
            pageSections = JSON.parse(undoHistory.pop());
            renderCanvas();
        }
    }

    function redo() {
        if (redoHistory.length > 0) {
            undoHistory.push(JSON.stringify(pageSections));
            pageSections = JSON.parse(redoHistory.pop());
            renderCanvas();
        }
    }

    // Set preview scaling device size
    function setCanvasMode(mode, btn) {
        $('.btn-group button').removeClass('active');
        $(btn).addClass('active');
        $('#canvas').removeClass('canvas-desktop canvas-tablet canvas-mobile').addClass('canvas-' + mode);
    }

    // Switch dynamic builder pages
    function switchPage(pageId) {
        window.location.href = '<?= APP_URL ?>/builder/editor?id=<?= $website['id'] ?>&page_id=' + pageId;
    }

    // Initialize builder Sortable JS canvas
    $(function() {
        renderCanvas();
        
        var canvasEl = document.getElementById('canvas');
        new Sortable(canvasEl, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: function (evt) {
                pushState();
                // Reorder section state array matching index sorting changes
                var item = pageSections.splice(evt.oldIndex, 1)[0];
                pageSections.splice(evt.newIndex, 0, item);
                renderCanvas();
            }
        });
    });

    // Render workspace blocks
    function renderCanvas() {
        var canvas = $('#canvas');
        canvas.empty();

        if (pageSections.length === 0) {
            canvas.html('<div class="text-center py-5 text-muted"><p>Canvas workspace empty. Append blocks from the left sidebar toolbox.</p></div>');
            return;
        }

        pageSections.forEach(function(sect, index) {
            var activeClass = (selectedBlockIndex === index) ? 'active-block' : '';
            var htmlBlock = $('<div class="editor-block ' + activeClass + '" onclick="selectBlock(' + index + ', event)"></div>');
            
            // Build custom controls Toolbar Overlay
            var toolbar = $('<div class="block-toolbar">' +
                '<span class="drag-handle text-white me-1"><i class="bi bi-arrows-move"></i></span>' +
                '<button type="button" onclick="moveBlockUp(' + index + ', event)"><i class="bi bi-arrow-up"></i></button>' +
                '<button type="button" onclick="moveBlockDown(' + index + ', event)"><i class="bi bi-arrow-down"></i></button>' +
                '<button type="button" onclick="duplicateBlock(' + index + ', event)"><i class="bi bi-files"></i></button>' +
                '<button type="button" onclick="deleteBlock(' + index + ', event)"><i class="bi bi-trash-fill text-danger"></i></button>' +
            '</div>');

            htmlBlock.append(toolbar);
            
            // Append compiled static layout content based on type
            htmlBlock.append(compilePreviewBlock(sect.type, sect.content));
            canvas.append(htmlBlock);
        });
    }

    // Maps section parameters to display elements inside the builder canvas
    function compilePreviewBlock(type, content) {
        var brand = content.brand || 'Company';
        var title = content.title || 'Click to edit text';
        var subtitle = content.subtitle || 'Click to edit subtitle';
        var desc = content.desc || 'Provide detail description.';
        
        switch (type) {
            case 'navbar':
                var links = '';
                if(content.links) {
                    content.links.forEach(l => { links += '<li class="nav-item"><span class="nav-link text-dark small" style="pointer-events:none;">'+l.text+'</span></li>'; });
                }
                return '<nav class="navbar navbar-expand bg-light border-bottom p-3"><div class="container-fluid">' +
                    '<span class="navbar-brand fw-bold mb-0 text-dark">'+brand+'</span>' +
                    '<ul class="navbar-nav ms-auto mb-0">' + links + '</ul>' +
                    (content.btn_text ? '<span class="btn btn-primary btn-sm ms-3" style="pointer-events:none;">'+content.btn_text+'</span>' : '') +
                    '</div></nav>';
            
            case 'hero':
                var bg = content.bg_color || '#0d6efd';
                return '<div class="py-5 text-white text-center" style="background: ' + bg + ';">' +
                    '<h1 class="display-5 fw-bold mb-2">' + title + '</h1>' +
                    '<p class="lead small mb-4">' + subtitle + '</p>' +
                    (content.btn_primary ? '<span class="btn btn-light btn-sm me-2">'+content.btn_primary+'</span>' : '') +
                    (content.btn_secondary ? '<span class="btn btn-outline-light btn-sm">'+content.btn_secondary+'</span>' : '') +
                    '</div>';

            case 'about':
                return '<div class="p-5 bg-white"><div class="row align-items-center">' +
                    '<div class="col-md-7"><h2 class="fw-bold mb-3">' + title + '</h2><p class="text-muted small">' + desc.replace(/\n/g, '<br>') + '</p></div>' +
                    '<div class="col-md-5 text-center"><img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=300&q=80" class="img-fluid rounded shadow-sm" alt="About"></div>' +
                    '</div></div>';

            case 'services':
                var cards = '';
                if(content.items) {
                    content.items.forEach(item => {
                        cards += '<div class="col-md-4 mb-2"><div class="card p-3 border text-center h-100">' +
                            '<div class="text-primary mb-2"><i class="bi '+item.icon+' fs-3"></i></div>' +
                            '<h6 class="fw-bold">'+item.title+'</h6><p class="text-muted small mb-0">'+item.desc+'</p>' +
                            '</div></div>';
                    });
                }
                return '<div class="p-5 bg-light"><h3 class="text-center fw-bold mb-4">'+title+'</h3><div class="row">'+cards+'</div></div>';

            case 'features':
                var rows = '';
                if(content.items) {
                    content.items.forEach(item => {
                        rows += '<div class="col-md-6 mb-2 d-flex"><div class="me-2 text-primary"><i class="bi '+item.icon+'"></i></div>' +
                            '<div><h6 class="fw-bold mb-1">'+item.title+'</h6><p class="text-muted small mb-0">'+item.desc+'</p></div></div>';
                    });
                }
                return '<div class="p-5 bg-white"><h3 class="text-center fw-bold mb-4">'+title+'</h3><div class="row">'+rows+'</div></div>';

            case 'testimonials':
                var list = '';
                if(content.items) {
                    content.items.forEach(item => {
                        list += '<div class="col-md-6 mb-3"><div class="card p-3 border-0 shadow-sm">' +
                            '<p class="fst-italic text-muted small">"'+item.quote+'"</p>' +
                            '<h6 class="fw-bold mb-0">- '+item.client+'</h6>' +
                            '</div></div>';
                    });
                }
                return '<div class="p-5 bg-light"><h3 class="text-center fw-bold mb-4">'+title+'</h3><div class="row">'+list+'</div></div>';

            case 'faq':
                var faqs = '';
                if(content.items) {
                    content.items.forEach(item => {
                        faqs += '<div class="border-bottom py-2"><h6 class="fw-bold mb-1">'+item.q+'</h6><p class="text-muted small mb-0">'+item.a+'</p></div>';
                    });
                }
                return '<div class="p-5 bg-white"><h3 class="text-center fw-bold mb-3">'+title+'</h3><div>'+faqs+'</div></div>';

            case 'contact':
                return '<div class="p-5 bg-light"><div class="row">' +
                    '<div class="col-md-6"><h3 class="fw-bold mb-3">'+title+'</h3>' +
                    '<p class="small mb-1"><i class="bi bi-geo-alt text-primary me-2"></i>'+(content.address || '')+'</p>' +
                    '<p class="small mb-1"><i class="bi bi-telephone text-primary me-2"></i>'+(content.phone || '')+'</p>' +
                    '<p class="small mb-1"><i class="bi bi-envelope text-primary me-2"></i>'+(content.email || '')+'</p></div>' +
                    '<div class="col-md-6"><div class="card p-3 border-0 shadow-sm"><form><input type="text" class="form-control form-control-sm mb-2" placeholder="Your Name" disabled><textarea class="form-control form-control-sm mb-2" placeholder="Your Message" disabled></textarea><button type="button" class="btn btn-primary btn-sm w-100" disabled>Send</button></form></div></div>' +
                    '</div></div>';

            case 'footer':
                return '<footer class="bg-dark text-white py-3 text-center"><p class="mb-0 small">' + (content.copyright || 'All rights reserved.') + '</p></footer>';

            default:
                return '<div class="p-4 border text-center">Section: ' + type + '</div>';
        }
    }

    // Select active editing block
    function selectBlock(index, event) {
        event.stopPropagation();
        selectedBlockIndex = index;
        $('.editor-block').removeClass('active-block');
        $(event.currentTarget).addClass('active-block');

        // Populate Properties Sidebar dynamically based on block keys
        var sect = pageSections[index];
        var panel = $('#propertiesPanel');
        panel.empty();
        
        panel.append('<h6 class="sidebar-title">Block Properties: ' + sect.type.toUpperCase() + '</h6>');
        
        // Render inputs depending on keys in content
        for (var key in sect.content) {
            var val = sect.content[key];
            
            if (typeof val === 'string') {
                panel.append('<div class="mb-3"><label class="form-label text-muted small text-uppercase">' + key.replace('_', ' ') + '</label>');
                if (key.includes('color')) {
                    panel.append('<input type="color" class="form-control form-dark form-control-color w-100" value="' + val + '" oninput="updateProperty(\'' + key + '\', this.value)">');
                } else if (key === 'desc' || key === 'desc_long') {
                    panel.append('<textarea class="form-control form-dark" rows="4" oninput="updateProperty(\'' + key + '\', this.value)">' + val + '</textarea>');
                } else {
                    panel.append('<input type="text" class="form-control form-dark" value="' + val + '" oninput="updateProperty(\'' + key + '\', this.value)">');
                }
                panel.append('</div>');
            }
        }
    }

    // Live sync inputs to preview canvas state
    function updateProperty(key, val) {
        if (selectedBlockIndex !== null) {
            pageSections[selectedBlockIndex].content[key] = val;
            renderCanvas();
        }
    }

    // Add block triggers
    function addNewSection(type) {
        pushState();
        var defaults = {};
        if (type === 'navbar') defaults = { brand: 'My Brand', btn_text: 'Contact Us', btn_url: '#contact' };
        else if (type === 'hero') defaults = { title: 'Empower Your Visions', subtitle: 'Stunning designs engineered dynamically.', btn_primary: 'Get Started', bg_color: '#0d6efd' };
        else if (type === 'about') defaults = { title: 'Our Core Story', desc: 'Over 10 years of business experience...' };
        else if (type === 'contact') defaults = { title: 'Contact Us', email: 'leads@local.com', phone: '+1 555-0000', address: '123 Avenue, NY' };
        else if (type === 'footer') defaults = { copyright: '© ' + new Date().getFullYear() + ' Company Inc. All rights reserved.' };
        else if (type === 'services') defaults = { title: 'Our Services', items: [{ icon: 'bi-cpu', title: 'Consulting', desc: 'Expert strategy guide' }] };
        else if (type === 'features') defaults = { title: 'Core Advantages', items: [{ icon: 'bi-shield-check', title: 'Secured Data', desc: 'Fully encrypted logs' }] };
        
        pageSections.push({
            type: type,
            content: defaults
        });
        
        renderCanvas();
        // Auto-select newly added section
        selectBlock(pageSections.length - 1, { stopPropagation: function(){} });
    }

    // Move, duplicate, or delete canvas components
    function moveBlockUp(index, event) {
        event.stopPropagation();
        if (index > 0) {
            pushState();
            var tmp = pageSections[index];
            pageSections[index] = pageSections[index - 1];
            pageSections[index - 1] = tmp;
            renderCanvas();
        }
    }

    function moveBlockDown(index, event) {
        event.stopPropagation();
        if (index < pageSections.length - 1) {
            pushState();
            var tmp = pageSections[index];
            pageSections[index] = pageSections[index + 1];
            pageSections[index + 1] = tmp;
            renderCanvas();
        }
    }

    function duplicateBlock(index, event) {
        event.stopPropagation();
        pushState();
        var cloned = JSON.parse(JSON.stringify(pageSections[index]));
        pageSections.splice(index + 1, 0, cloned);
        renderCanvas();
    }

    function deleteBlock(index, event) {
        event.stopPropagation();
        pushState();
        pageSections.splice(index, 1);
        selectedBlockIndex = null;
        $('#propertiesPanel').empty().append('<h6 class="sidebar-title">Property Manager</h6><p class="text-muted small">Click any block in the canvas workspace to modify details or colors.</p>');
        renderCanvas();
    }

    // Save page layout state via API endpoint
    function savePage() {
        $('#loaderOverlay').css('display', 'flex');
        
        $.ajax({
            url: '<?= APP_URL ?>/api/builder/save',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                page_id: activePageId,
                sections: pageSections
            }),
            success: function(resp) {
                $('#loaderOverlay').hide();
                alert(resp.message);
            },
            error: function() {
                $('#loaderOverlay').hide();
                alert('Ajax communication failed during save.');
            }
        });
    }
</script>

</body>
</html>
