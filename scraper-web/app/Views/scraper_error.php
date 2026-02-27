<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 50px;
            text-align: center;
        }

        .error-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .error-box h1 {
            color: #e74c3c;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="error-box">
        <h1>❌ Error!</h1>
        <p><?= esc($error) ?></p>
        <a href="<?= base_url('scraper') ?>" class="btn">Kembali</a>
    </div>
</body>

</html>