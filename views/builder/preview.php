<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - <?= e($website['name']) ?></title>
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
    </style>
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
            echo '<div class="collapse navbar-collapse"><ul class="navbar-nav ms-auto mb-2 mb-lg-0">';
            if (!empty($sect['content']['links'])) {
                foreach ($sect['content']['links'] as $lnk) {
                    echo '<li class="nav-item"><a class="nav-link" href="' . e($lnk['url']) . '">' . e($lnk['text']) . '</a></li>';
                }
            }
            echo '</ul>';
            if (!empty($sect['content']['btn_text'])) {
                echo '<a class="btn btn-primary ms-3" href="#">' . e($sect['content']['btn_text']) . '</a>';
            }
            echo '</div></div></nav>';
            break;

        case 'hero':
            $bg = e($sect['content']['bg_color'] ?? '#0d6efd');
            echo '<section class="py-5 text-white" style="background: ' . $bg . ';"><div class="container py-5 text-center">';
            echo '<h1 class="display-3 fw-bold mb-3">' . $title . '</h1>';
            echo '<p class="lead mb-4">' . $subtitle . '</p>';
            if (!empty($sect['content']['btn_primary'])) {
                echo '<span class="btn btn-light btn-lg me-3 px-4">' . e($sect['content']['btn_primary']) . '</span>';
            }
            if (!empty($sect['content']['btn_secondary'])) {
                echo '<span class="btn btn-outline-light btn-lg px-4">' . e($sect['content']['btn_secondary']) . '</span>';
            }
            echo '</div></section>';
            break;

        case 'about':
            echo '<section class="py-5 bg-white"><div class="container py-5"><div class="row align-items-center">';
            echo '<div class="col-lg-6"><h2 class="fw-bold mb-4">' . $title . '</h2><p class="text-muted lead">' . nl2br($desc) . '</p></div>';
            echo '<div class="col-lg-6 text-center"><img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80" class="img-fluid rounded shadow" alt="About"></div>';
            echo '</div></div></section>';
            break;

        case 'services':
            echo '<section class="py-5 bg-light"><div class="container py-5"><h2 class="text-center fw-bold mb-5">' . $title . '</h2><div class="row">';
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
            echo '<section class="py-5 bg-light"><div class="container py-5"><h2 class="text-center fw-bold mb-5">' . $title . '</h2><div class="row">';
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
            echo '<section class="py-5 bg-light"><div class="container py-5"><div class="row">';
            echo '<div class="col-md-6 mb-4"><h2 class="fw-bold mb-4">' . $title . '</h2>';
            echo '<p><i class="bi bi-geo-alt-fill text-primary me-2"></i> ' . e($sect['content']['address']) . '</p>';
            echo '<p><i class="bi bi-telephone-fill text-primary me-2"></i> ' . e($sect['content']['phone']) . '</p>';
            echo '<p><i class="bi bi-envelope-fill text-primary me-2"></i> ' . e($sect['content']['email']) . '</p></div>';
            echo '<div class="col-md-6"><div class="card p-4 border-0 shadow-sm"><form>';
            echo '<div class="mb-3"><input type="text" class="form-control" placeholder="Your Name" required></div>';
            echo '<div class="mb-3"><input type="email" class="form-control" placeholder="Your Email" required></div>';
            echo '<div class="mb-3"><textarea class="form-control" rows="4" placeholder="Your Message" required></textarea></div>';
            echo '<button type="submit" class="btn btn-primary w-100">Send Enquiry</button>';
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
