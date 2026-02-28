<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h1>Halo</h1>
    <?= $this->renderSection('content'); ?>

    <!-- <script>
        const activityUrl = "https://trending-only.com/id/artikel/activity";
        document.addEventListener("DOMContentLoaded", function() {
            let startTime = Date.now();
            let maxScroll = 0;

            window.addEventListener("scroll", function() {
                let scrollTop = window.scrollY;
                let docHeight = document.documentElement.scrollHeight - window.innerHeight;
                let scrollPercent = (scrollTop / docHeight) * 100;
                maxScroll = Math.max(maxScroll, scrollPercent);
                console.log(maxScroll)
            });

            function sendActivity(type, value) {
                fetch(activityUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        article_id: 2287,
                        type: type,
                        value: value
                    })
                }).catch(err => console.error("Gagal kirim aktivitas:", err));
            }

            setInterval(function() {
                let timeSpent = Math.floor((Date.now() - startTime) / 1000);
                sendActivity("scroll_percentage", maxScroll.toFixed(2));
                sendActivity("time_spent_seconds", timeSpent);
                console.log(timeSpent)
            }, 10000);

            window.addEventListener("beforeunload", function() {
                let timeSpent = Math.floor((Date.now() - startTime) / 1000);

                let timeData = new Blob(
                    [JSON.stringify({
                        article_id: 2287,
                        type: "time_spent_seconds",
                        value: timeSpent
                    })], {
                        type: 'application/json'
                    }
                );

                let scrollData = new Blob(
                    [JSON.stringify({
                        article_id: 2287,
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
    </script> -->
</body>

</html>