<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Scraper - CI4</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .container {
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-width: 500px;
        width: 100%;
    }

    h1 {
        color: #667eea;
        margin-bottom: 10px;
        font-size: 32px;
    }

    p {
        color: #666;
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 600;
    }

    select,
    input {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s;
    }

    select:focus,
    input:focus {
        outline: none;
        border-color: #667eea;
    }

    button {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s;
    }

    button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
    }

    .info {
        background: #f0f4ff;
        padding: 15px;
        border-radius: 8px;
        margin-top: 20px;
        font-size: 14px;
        color: #555;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>🕷️ Web Scraper</h1>
        <p>Ambil data dari website dengan mudah</p>

        <form action="<?= base_url('scraper/run') ?>" method="POST">

            <div class="form-group">
                <label>Pilih Website:</label>
                <select name="website" required>
                    <option value="">-- Pilih Website --</option>
                    <option value="inaexport">inaexport</option>
                    <option value="kemenperin">kemenperin</option>
                </select>
            </div>

            <button type="submit">🚀 Mulai Scraping</button>
        </form>

        <div class="info">
            💡 <strong>Tips:</strong> Gunakan "Quotes to Scrape" untuk testing. Website ini memang dibuat untuk latihan
            scraping.
        </div>
    </div>
</body>

</html>