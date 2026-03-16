<html>
<head>
    <title>Anime Cube</title>
    <?php include "client/commonFile.php"; ?>


</head>
<body>
    <?php
    include "client/header.php";

    if (isset($_GET["login"])) {
        include "client/login.php";
    } elseif (isset($_GET["signup"])) {
        include "client/signup.php";
    } else {
        //
    }
    ?>

    <span class="text-center mt-4px text-2xl ">
        <p class="tile "> Watch any anime you want</p>
        <a href="/Dashboard-project/AnimeCube/index.php?login=true" class="btn btn-primary"><img src="./public/anime1.jpg" alt=""></a>
    </span>




<?php include "client/apiCall.php"; ?>
    <?php
    // Store fetched data in session for fallback
    session_start();
    if (isset($content) && isset($anime_name) && isset($character_name)) {
        $_SESSION["last_quote"] = $content;
        $_SESSION["last_anime"] = $anime_name;
        $_SESSION["last_character"] = $character_name;
    } else {
        // Fallback to last fetched data from session
        $content = $_SESSION["last_quote"] ?? "No quote available";
        $anime_name = $_SESSION["last_anime"] ?? "N/A";
        $character_name = $_SESSION["last_character"] ?? "N/A";
    }
    ?>


    <span class="QuBody ">
        Quote: <span class="content"><?php echo $content; ?></span><br>
        Anime: <span class="anime_name"><?php echo $anime_name; ?></span><br>
        Character: <span class="character_name"><?php echo $character_name; ?></span>
        <span id="api-timer" class="mt-2 text-center text-sm text-gray-500">
            Next refresh in: <span id="countdown">5:00</span>
        </span>
    </span>



    <script>
        // Countdown Timer for API refresh every 5 minutes
        let timeLeft = 300; // 5 minutes in seconds

        function updateCountdown() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('countdown').textContent =
                `${minutes}:${seconds.toString().padStart(2, '0')}`;

            if (timeLeft <= 0) {
                location.reload(); // Reload page to fetch new API data
            } else {
                timeLeft--;
                setTimeout(updateCountdown, 1000);

            }
        }

        updateCountdown();
    </script>




</body>
</html>
