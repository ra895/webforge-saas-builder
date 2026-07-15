<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - WebForge</title>
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
        .forgot-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            color: #fff;
            max-width: 420px;
            width: 100%;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }
        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 10px;
            padding: 12px;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #0d6efd;
            box-shadow: none;
            color: #fff;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
        }
        a {
            color: #0dcaf0;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="forgot-card text-center">
    <h4 class="mb-3 fw-bold">Forgot Password?</h4>
    <p class="text-muted small mb-4">Enter your registered email address, and we'll dispatch a link to reset your credentials.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger border-0 text-start" style="background: rgba(220,53,69,0.2); color: #f8d7da;">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success border-0 text-start" style="background: rgba(40,167,69,0.2); color: #d4edda;">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/auth/forgot">
        <?= csrf_field() ?>
        <div class="mb-4 text-start">
            <label class="form-label text-muted small">EMAIL ADDRESS</label>
            <input type="email" name="email" class="form-control" placeholder="name@domain.com" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 mb-3">Send Reset Link</button>
    </form>

    <div class="text-muted small">
        <a href="<?= APP_URL ?>/auth/login">Back to Sign In</a>
    </div>
</div>

</body>
</html>
