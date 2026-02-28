<?= $this->extend('layout/main'); ?>

<?= $this->section('content'); ?>
<?php foreach ($artikel as $a): ?>
    <a href="/artikel/<?= $a['id'] ?>"><?= $a['judul'] ?></a><br />
<?php endforeach ?>
<h1>Hasil:</h1>
<?php if (is_array($hasil)): ?>
    <?php foreach ($hasil as $a): ?>
        <a href="/artikel/<?= $a['id'] ?>">
            <?= esc($a['judul']) ?>
        </a><br />
    <?php endforeach ?>
<?php else: ?>
    <p>Tidak ada data</p>
<?php endif; ?>


<?= $this->endSection(); ?>