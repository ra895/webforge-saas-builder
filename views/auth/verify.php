<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - WebForge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgb(18, 28, 48) 0%, rgb(9, 13, 20) 90%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .verify-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            color: #fff;
            max-width: 420px;
            width: 100%;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="verify-card">
    <?php if ($success): ?>
        <div class="text-success mb-3 display-4"><i class="bi bi-patch-check-fill"></i></div>
        <h4 class="mb-3 fw-bold">Email Verified!</h4>
        <p class="text-muted small mb-4">Your email address has been successfully verified. You can now access your workspace.</p>
        <a href="<?= APP_URL ?>/auth/login" class="btn btn-primary w-100">Sign In Now</a>
    <?php else: ?>
        <div class="text-danger mb-3 display-4"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <h4 class="mb-3 fw-bold">Verification Failed</h4>
        <p class="text-muted small mb-4">The verification token is invalid or has already been used. Please try registering again.</p>
        <a href="<?= APP_URL ?>/auth/register" class="btn btn-outline-light w-100">Register Account</a>
    <?php endif; ?>
</div>

</body>
</html>
