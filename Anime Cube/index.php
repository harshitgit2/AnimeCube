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


</body>
</html>
