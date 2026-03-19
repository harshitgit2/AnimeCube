<?php
session_start();
include "../Database/db.php";

// Set JSON response header
header("Content-Type: application/json");

// Check if user is logged in
if (empty($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "You must be logged in to perform this action.",
    ]);
    exit();
}

$user_id = $_SESSION["user_id"];

// ─── ADD/REMOVE FAVORITE ─────────────────────────────────────────────────────
if (isset($_POST["action"]) && $_POST["action"] === "toggle_favorite") {
    if (!isset($_POST["anime_id"])) {
        echo json_encode([
            "success" => false,
            "message" => "Anime ID is required.",
        ]);
        exit();
    }

    $anime_id = intval($_POST["anime_id"]);

    // Verify anime exists in local database first
    $verify_stmt = $conn->prepare(
        "SELECT `id` FROM `anime` WHERE `id` = ? LIMIT 1",
    );
    $verify_stmt->bind_param("i", $anime_id);
    $verify_stmt->execute();
    if ($verify_stmt->get_result()->num_rows === 0) {
        echo json_encode([
            "success" => false,
            "message" => "Anime not found in local database.",
        ]);
        $verify_stmt->close();
        exit();
    }
    $verify_stmt->close();

    // Check if already favorited
    $check_stmt = $conn->prepare(
        "SELECT `id` FROM `user_favorites` WHERE `user_id` = ? AND `anime_id` = ? LIMIT 1",
    );
    $check_stmt->bind_param("ii", $user_id, $anime_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $is_favorite = $check_result->num_rows > 0;
    $check_stmt->close();

    if ($is_favorite) {
        // Remove from favorites
        $delete_stmt = $conn->prepare(
            "DELETE FROM `user_favorites` WHERE `user_id` = ? AND `anime_id` = ?",
        );
        $delete_stmt->bind_param("ii", $user_id, $anime_id);

        if ($delete_stmt->execute()) {
            echo json_encode([
                "success" => true,
                "action" => "removed",
                "message" => "Removed from favorites.",
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to remove from favorites.",
            ]);
        }
        $delete_stmt->close();
    } else {
        // Add to favorites
        $insert_stmt = $conn->prepare(
            "INSERT INTO `user_favorites` (`user_id`, `anime_id`) VALUES (?, ?)",
        );
        $insert_stmt->bind_param("ii", $user_id, $anime_id);

        if ($insert_stmt->execute()) {
            echo json_encode([
                "success" => true,
                "action" => "added",
                "message" => "Added to favorites!",
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to add to favorites.",
            ]);
        }
        $insert_stmt->close();
    }
}

// ─── ADD/UPDATE WATCHLIST ────────────────────────────────────────────────────
elseif (isset($_POST["action"]) && $_POST["action"] === "update_watchlist") {
    if (!isset($_POST["anime_id"])) {
        echo json_encode([
            "success" => false,
            "message" => "Anime ID is required.",
        ]);
        exit();
    }

    $anime_id = intval($_POST["anime_id"]);
    $status = isset($_POST["status"])
        ? trim($_POST["status"])
        : "Plan to Watch";
    $episodes_watched = isset($_POST["episodes_watched"])
        ? intval($_POST["episodes_watched"])
        : 0;
    $score =
        isset($_POST["score"]) && $_POST["score"] !== ""
            ? intval($_POST["score"])
            : null;

    // Verify anime exists in local database first
    $verify_stmt = $conn->prepare(
        "SELECT `id` FROM `anime` WHERE `id` = ? LIMIT 1",
    );
    $verify_stmt->bind_param("i", $anime_id);
    $verify_stmt->execute();
    if ($verify_stmt->get_result()->num_rows === 0) {
        echo json_encode([
            "success" => false,
            "message" => "Anime not found in local database.",
        ]);
        $verify_stmt->close();
        exit();
    }
    $verify_stmt->close();

    // Validate status
    $valid_statuses = [
        "Watching",
        "Completed",
        "On Hold",
        "Dropped",
        "Plan to Watch",
    ];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid status.",
        ]);
        exit();
    }

    // Validate score (1-10 or null)
    if ($score !== null && ($score < 1 || $score > 10)) {
        echo json_encode([
            "success" => false,
            "message" => "Score must be between 1 and 10.",
        ]);
        exit();
    }

    // Check if already in watchlist
    $check_stmt = $conn->prepare(
        "SELECT `id` FROM `user_watchlist` WHERE `user_id` = ? AND `anime_id` = ? LIMIT 1",
    );
    $check_stmt->bind_param("ii", $user_id, $anime_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $exists = $check_result->num_rows > 0;
    $check_stmt->close();

    if ($exists) {
        // Update existing watchlist entry
        if ($score !== null) {
            $update_stmt = $conn->prepare(
                "UPDATE `user_watchlist` SET `status` = ?, `episodes_watched` = ?, `score` = ? WHERE `user_id` = ? AND `anime_id` = ?",
            );
            $update_stmt->bind_param(
                "siiii",
                $status,
                $episodes_watched,
                $score,
                $user_id,
                $anime_id,
            );
        } else {
            $update_stmt = $conn->prepare(
                "UPDATE `user_watchlist` SET `status` = ?, `episodes_watched` = ?, `score` = NULL WHERE `user_id` = ? AND `anime_id` = ?",
            );
            $update_stmt->bind_param(
                "siii",
                $status,
                $episodes_watched,
                $user_id,
                $anime_id,
            );
        }

        if ($update_stmt->execute()) {
            echo json_encode([
                "success" => true,
                "action" => "updated",
                "message" => "Watchlist updated successfully!",
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to update watchlist.",
            ]);
        }
        $update_stmt->close();
    } else {
        // Insert new watchlist entry
        if ($score !== null) {
            $insert_stmt = $conn->prepare(
                "INSERT INTO `user_watchlist` (`user_id`, `anime_id`, `status`, `episodes_watched`, `score`) VALUES (?, ?, ?, ?, ?)",
            );
            $insert_stmt->bind_param(
                "iisii",
                $user_id,
                $anime_id,
                $status,
                $episodes_watched,
                $score,
            );
        } else {
            $insert_stmt = $conn->prepare(
                "INSERT INTO `user_watchlist` (`user_id`, `anime_id`, `status`, `episodes_watched`) VALUES (?, ?, ?, ?)",
            );
            $insert_stmt->bind_param(
                "iisi",
                $user_id,
                $anime_id,
                $status,
                $episodes_watched,
            );
        }

        if ($insert_stmt->execute()) {
            echo json_encode([
                "success" => true,
                "action" => "added",
                "message" => "Added to watchlist!",
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to add to watchlist.",
            ]);
        }
        $insert_stmt->close();
    }
}

// ─── REMOVE FROM WATCHLIST ───────────────────────────────────────────────────
elseif (isset($_POST["action"]) && $_POST["action"] === "remove_watchlist") {
    if (!isset($_POST["anime_id"])) {
        echo json_encode([
            "success" => false,
            "message" => "Anime ID is required.",
        ]);
        exit();
    }

    $anime_id = intval($_POST["anime_id"]);

    $delete_stmt = $conn->prepare(
        "DELETE FROM `user_watchlist` WHERE `user_id` = ? AND `anime_id` = ?",
    );
    $delete_stmt->bind_param("ii", $user_id, $anime_id);

    if ($delete_stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Removed from watchlist.",
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to remove from watchlist.",
        ]);
    }
    $delete_stmt->close();
}

// ─── INVALID ACTION ──────────────────────────────────────────────────────────
else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid action.",
    ]);
}

$conn->close();
?>
