<?= $this->extend('layout/main'); ?>

<?= $this->section('content'); ?>

<h1><?= $artikel['judul'] ?></h1>
<h1><?= $artikel['kategori'] ?></h1>
<h1><?= $artikel['isi'] ?></h1>

<script>
    const activityUrl = "<?= base_url() . 'api/v1/aktivitas' ?>";

    // setTimeout(() => {
    fetch(activityUrl, {
        method: "POST",
        credentials: 'same-origin',
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            article_kategori: "<?= $artikel['kategori'] ?>",
            count: true,
        })
    }).catch(err => console.error("Gagal kirim aktivitas:", err));
    // }, 9500)

    document.addEventListener("DOMContentLoaded", function() {
        let startTime = Date.now();
        let maxScroll = 0;

        window.addEventListener("scroll", function() {
            let scrollTop = window.scrollY;
            let docHeight = document.documentElement.scrollHeight - window.innerHeight;
            let scrollPercent = (scrollTop / docHeight) * 100;
            maxScroll = Math.max(maxScroll, scrollPercent);
        });

        function sendActivity(type, value) {
            fetch(activityUrl, {
                method: "POST",
                credentials: 'same-origin',
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    article_kategori: "<?= $artikel['kategori'] ?>",
                    count: false,
                    type: type,
                    value: value
                })
            }).catch(err => console.error("Gagal kirim aktivitas:", err));
        }

        setInterval(function() {
            let timeSpent = Math.floor((Date.now() - startTime) / 1000);

            sendActivity("scroll_percentage", maxScroll.toFixed(2));
            sendActivity("time_spent_seconds", timeSpent);
        }, 10000);

        window.addEventListener("beforeunload", function() {
            let timeSpent = Math.floor((Date.now() - startTime) / 1000);

            let timeData = new Blob(
                [JSON.stringify({
                    article_kategori: "<?= $artikel['kategori'] ?>",
                    count: false,
                    type: "time_spent_seconds",
                    value: timeSpent
                })], {
                    type: 'application/json'
                }
            );

            let scrollData = new Blob(
                [JSON.stringify({
                    article_kategori: "<?= $artikel['kategori'] ?>",
                    count: false,
                    type: "scroll_percentage",
                    value: maxScroll.toFixed(2)
                })], {
                    type: 'application/json'
                }
            );

            navigator.sendBeacon(activityUrl, timeData);
            navigator.sendBeacon(activityUrl, scrollData);
        });
    });
</script>

<?= $this->endSection(); ?>