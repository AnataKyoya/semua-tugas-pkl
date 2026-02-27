<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Scraping</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f5f5f5;
        padding: 20px;
    }

    .header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    .header h1 {
        margin-bottom: 10px;
    }

    .info {
        background: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .info span {
        background: #667eea;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        margin-right: 10px;
    }

    table {
        width: 100%;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    th {
        background: #667eea;
        color: white;
        padding: 15px;
        text-align: left;
    }

    td {
        padding: 15px;
        border-bottom: 1px solid #e0e0e0;
    }

    tr:hover {
        background: #f9f9f9;
    }

    .actions {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }

    .btn {
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-success {
        background: #10b981;
        color: white;
    }
    </style>
</head>

<body>
    <div class="header">
        <h1>✅ Scraping Berhasil!</h1>
        <p>Data berhasil diambil dari <?= esc($website) ?></p>
    </div>

    <div class="info">
        <span><?= count($data) ?> data</span>
    </div>

    <?php if (!empty($data)): ?>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <?php foreach (array_keys($data[0]) as $column): ?>
                <th><?= ucfirst($column) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $index => $row): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <?php foreach ($row as $value): ?>
                <td><?= esc($value) ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="actions">
        <a href="<?= base_url('scraper') ?>" class="btn btn-primary">🔙 Kembali</a>

        <form action="<?= base_url('scraper/export') ?>" method="POST" style="display: inline;">
            <input type="hidden" name="website" value="<?= $website ?>">
            <button type="submit" class="btn btn-success">📥 Download CSV</button>
        </form>
    </div>

    <?php else: ?>
    <p>Tidak ada data ditemukan.</p>
    <?php endif; ?>
</body>

</html>