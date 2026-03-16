<?php
include "../Database/db.php";

// ─── SIGN UP ───────────────────────────────────────────────────────────────
if (isset($_POST["signup"])) {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $address = trim($_POST["address"]);

    $stmt = $conn->prepare(
        "INSERT INTO `users`(`username`, `email`, `password`, `address`) VALUES (?, ?, ?, ?)",
    );

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssss", $username, $email, $password, $address);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: ../index.php?login=true&signup=success");
        exit();
    } else {
        echo "Error: " . $stmt->error;
        $stmt->close();
        $conn->close();
    }
}

// ─── LOGIN ─────────────────────────────────────────────────────────────────
elseif (isset($_POST["login"])) {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT `id`, `username`, `password` FROM `users` WHERE `username` = ? LIMIT 1",
    );

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            session_start();
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $stmt->close();
            $conn->close();
            header("Location: ../index.php");
            exit();
        } else {
            $stmt->close();
            $conn->close();
            header("Location: ../index.php?login=true&error=invalid_password");
            exit();
        }
    } else {
        $stmt->close();
        $conn->close();
        header("Location: ../index.php?login=true&error=user_not_found");
        exit();
    }
}

// ─── NO VALID POST ──────────────────────────────────────────────────────────
else {
    header("Location: ../index.php");
    exit();
}
?>
