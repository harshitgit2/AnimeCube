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


</body>
</html>
