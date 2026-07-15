<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page['meta_title'] ?: $website['name']) ?></title>
    <meta name="description" content="<?= e($page['meta_description'] ?: $website['name']) ?>">
    <meta name="keywords" content="<?= e($page['meta_keywords'] ?: '') ?>">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: <?= $website['primary_color'] ?>;
            --secondary-color: <?= $website['secondary_color'] ?>;
        }
        body {
            font-family: '<?= $website['font_family'] ?>', sans-serif;
        }
        .bg-primary { background-color: var(--primary-color) !important; }
        .text-primary { color: var(--primary-color) !important; }
        .btn-primary { background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; }
        
        /* Floating WhatsApp Chat widget style */
        .whatsapp-widget {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background-color: #25d366;
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            z-index: 9999;
            transition: transform 0.2s;
            text-decoration: none;
        }
        .whatsapp-widget:hover {
            transform: scale(1.1);
            color: #fff;
        }
    </style>
    
    <!-- MOCK Google Analytics Tag Injection -->
    <?php 
    $userGaId = Database::query("SELECT setting_value FROM site_settings WHERE setting_key = ?", ['ga_id_' . $website['user_id']])->fetch()['setting_value'] ?? '';
    if (!empty($userGaId)): 
    ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($userGaId) ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= e($userGaId) ?>');
    </script>
    <?php endif; ?>

    <!-- MOCK Facebook Pixel Injection -->
    <?php 
    $userPixelId = Database::query("SELECT setting_value FROM site_settings WHERE setting_key = ?", ['pixel_id_' . $website['user_id']])->fetch()['setting_value'] ?? '';
    if (!empty($userPixelId)): 
    ?>
    <script>
      !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
      n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
      (window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '<?= e($userPixelId) ?>');
      fbq('track', 'PageView');
    </script>
    <?php endif; ?>
</head>
<body>

<?php
// Output sections sequentially
foreach ($sections as $sect) {
    $brand = e($sect['content']['brand'] ?? 'Brand');
    $title = e($sect['content']['title'] ?? 'Title');
    $subtitle = e($sect['content']['subtitle'] ?? 'Subtitle');
    $desc = e($sect['content']['desc'] ?? '');

    switch ($sect['type']) {
        case 'navbar':
            echo '<nav class="navbar navbar-expand-lg navbar-light bg-light py-3 border-bottom"><div class="container">';
            echo '<a class="navbar-brand fw-bold" href="#">' . $brand . '</a>';
            echo '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navLive"><span class="navbar-toggler-icon"></span></button>';
            echo '<div class="collapse navbar-collapse" id="navLive"><ul class="navbar-nav ms-auto mb-2 mb-lg-0">';
            
            // Render other dynamic page links
            foreach ($pages as $p) {
                $active = ($p['slug'] === $page['slug']) ? 'active' : '';
                echo '<li class="nav-item"><a class="nav-link ' . $active . '" href="' . APP_URL . '/site/' . $website['subdomain'] . '/' . $p['slug'] . '">' . e($p['title']) . '</a></li>';
            }
            echo '</ul>';
            if (!empty($sect['content']['btn_text'])) {
                echo '<a class="btn btn-primary ms-3" href="' . e($sect['content']['btn_url'] ?? '#') . '">' . e($sect['content']['btn_text']) . '</a>';
            }
            echo '</div></div></nav>';
            break;

        case 'hero':
            $bg = e($sect['content']['bg_color'] ?? '#0d6efd');
            echo '<section class="py-5 text-white" style="background: ' . $bg . ';"><div class="container py-5 text-center">';
            echo '<h1 class="display-3 fw-bold mb-3">' . $title . '</h1>';
            echo '<p class="lead mb-4">' . $subtitle . '</p>';
            if (!empty($sect['content']['btn_primary'])) {
                echo '<a class="btn btn-light btn-lg me-3 px-4" href="' . e($sect['content']['btn_url'] ?? '#contact') . '">' . e($sect['content']['btn_primary']) . '</a>';
            }
            if (!empty($sect['content']['btn_secondary'])) {
                echo '<a class="btn btn-outline-light btn-lg px-4" href="' . e($sect['content']['btn_url'] ?? '#contact') . '">' . e($sect['content']['btn_secondary']) . '</a>';
            }
            echo '</div></section>';
            break;

        case 'about':
            echo '<section id="about" class="py-5 bg-white"><div class="container py-5"><div class="row align-items-center">';
            echo '<div class="col-lg-6"><h2 class="fw-bold mb-4">' . $title . '</h2><p class="text-muted lead">' . nl2br($desc) . '</p></div>';
            echo '<div class="col-lg-6 text-center"><img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80" class="img-fluid rounded shadow" alt="About"></div>';
            echo '</div></div></section>';
            break;

        case 'services':
            echo '<section id="services" class="py-5 bg-light"><div class="container py-5"><h2 class="text-center fw-bold mb-5">' . $title . '</h2><div class="row">';
            if (!empty($sect['content']['items'])) {
                foreach ($sect['content']['items'] as $item) {
                    echo '<div class="col-md-4 mb-4"><div class="card h-100 border-0 shadow-sm p-4 text-center">';
                    echo '<div class="text-primary mb-3"><i class="bi ' . e($item['icon']) . ' display-5"></i></div>';
                    echo '<h4 class="fw-bold">' . e($item['title']) . '</h4>';
                    echo '<p class="text-muted">' . e($item['desc']) . '</p>';
                    echo '</div></div>';
                }
            }
            echo '</div></div></section>';
            break;

        case 'features':
            echo '<section class="py-5 bg-white"><div class="container py-5"><h2 class="text-center fw-bold mb-5">' . $title . '</h2><div class="row">';
            if (!empty($sect['content']['items'])) {
                foreach ($sect['content']['items'] as $item) {
                    echo '<div class="col-md-6 mb-4 d-flex"><div class="me-3 text-primary"><i class="bi ' . e($item['icon']) . ' display-6"></i></div>';
                    echo '<div><h4 class="fw-bold">' . e($item['title']) . '</h4><p class="text-muted">' . e($item['desc']) . '</p></div></div>';
                }
            }
            echo '</div></div></section>';
            break;

        case 'testimonials':
            echo '<section id="testimonials" class="py-5 bg-light"><div class="container py-5"><h2 class="text-center fw-bold mb-5">' . $title . '</h2><div class="row">';
            if (!empty($sect['content']['items'])) {
                foreach ($sect['content']['items'] as $item) {
                    echo '<div class="col-md-6 mb-4"><div class="card border-0 shadow-sm p-4 h-100">';
                    echo '<p class="fst-italic text-muted mb-4">"' . e($item['quote']) . '"</p>';
                    echo '<h5 class="fw-bold mb-0">- ' . e($item['client']) . '</h5>';
                    echo '</div></div>';
                }
            }
            echo '</div></div></section>';
            break;

        case 'faq':
            echo '<section class="py-5 bg-white"><div class="container py-5"><h2 class="text-center fw-bold mb-5">' . $title . '</h2><div class="row justify-content-center"><div class="col-lg-8">';
            if (!empty($sect['content']['items'])) {
                foreach ($sect['content']['items'] as $item) {
                    echo '<div class="border-bottom py-3">';
                    echo '<h4 class="fw-bold mb-2">' . e($item['q']) . '</h4>';
                    echo '<p class="text-muted">' . e($item['a']) . '</p>';
                    echo '</div>';
                }
            }
            echo '</div></div></div></section>';
            break;

        case 'contact':
            // Fetch form ID linked to website
            $formObj = Database::query("SELECT id FROM forms WHERE website_id = ? LIMIT 1", [$website['id']])->fetch();
            $formId = $formObj ? (int)$formObj['id'] : 0;
            
            echo '<section id="contact" class="py-5 bg-light"><div class="container py-5"><div class="row">';
            echo '<div class="col-md-6 mb-4"><h2 class="fw-bold mb-4">' . $title . '</h2>';
            echo '<p><i class="bi bi-geo-alt-fill text-primary me-2"></i> ' . e($sect['content']['address']) . '</p>';
            echo '<p><i class="bi bi-telephone-fill text-primary me-2"></i> ' . e($sect['content']['phone']) . '</p>';
            echo '<p><i class="bi bi-envelope-fill text-primary me-2"></i> ' . e($sect['content']['email']) . '</p></div>';
            echo '<div class="col-md-6"><div class="card p-4 border-0 shadow-sm">';
            echo '<div id="contactFormMsg" class="alert alert-success d-none border-0"></div>';
            echo '<form id="contactFormObj">';
            echo '<input type="hidden" name="form_id" value="' . $formId . '">';
            echo '<div class="mb-3"><input type="text" name="name" class="form-control" placeholder="Your Name" required></div>';
            echo '<div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Your Email" required></div>';
            echo '<div class="mb-3"><textarea name="message" class="form-control" rows="4" placeholder="Your Message" required></textarea></div>';
            echo '<button type="submit" class="btn btn-primary w-100" id="contactSubmitBtn">Send Enquiry</button>';
            echo '</form></div></div>';
            echo '</div></div></section>';
            break;

        case 'footer':
            $copy = e($sect['content']['copyright'] ?? 'All rights reserved.');
            echo '<footer class="bg-dark text-white py-4 text-center"><div class="container"><p class="mb-0">' . $copy . '</p></div></footer>';
            break;
    }
}
?>

<!-- Floating WhatsApp Chat Widget -->
<?php 
$whatsappNo = Database::query("SELECT setting_value FROM site_settings WHERE setting_key = ?", ['whatsapp_' . $website['user_id']])->fetch()['setting_value'] ?? '';
if (!empty($whatsappNo)): 
?>
<a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $whatsappNo)) ?>" target="_blank" class="whatsapp-widget">
    <i class="bi bi-whatsapp"></i>
</a>
<?php endif; ?>

<!-- Client AJAX Form submission -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $('#contactFormObj').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#contactSubmitBtn');
        btn.prop('disabled', true).text('Sending...');
        
        $.ajax({
            url: '<?= APP_URL ?>/api/forms/submit',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                if (resp.success) {
                    $('#contactFormMsg').removeClass('d-none').text(resp.message);
                    $('#contactFormObj').trigger('reset');
                } else {
                    alert('Submission error: ' + resp.message);
                }
                btn.prop('disabled', false).text('Send Enquiry');
            },
            error: function() {
                alert('Connection failure.');
                btn.prop('disabled', false).text('Send Enquiry');
            }
        });
    });
</script>
</body>
</html>
