<?php
session_start();

// Handle logout
if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime Cube - Watch Any Anime You Want</title>
    <?php include "client/commonFile.php"; ?>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .main-layout {
            display: flex;
            min-height: calc(100vh - 80px);
            gap: 0;
        }

        .quotes-sidebar {
            width: 350px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .quotes-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .quotes-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
        }

        .quotes-header h2 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .quotes-header p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .quote-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .quote-icon {
            font-size: 40px;
            opacity: 0.5;
            margin-bottom: 15px;
        }

        .quote-text {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
            font-style: italic;
        }

        .quote-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
        }

        .quote-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .quote-meta-label {
            font-weight: 600;
            opacity: 0.8;
        }

        .quote-meta-value {
            font-weight: 400;
        }

        .api-timer {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            font-size: 13px;
        }

        .countdown {
            font-weight: bold;
            font-size: 18px;
            color: #ffd700;
        }

        .cards-main {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 30px;
            padding: 40px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .welcome-section h1 {
            font-size: 36px;
            color: #333;
            margin: 0 0 15px 0;
        }

        .welcome-section p {
            font-size: 18px;
            color: #666;
            margin: 0;
        }

        @media (max-width: 1024px) {
            .main-layout {
                flex-direction: column;
            }

            .quotes-sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
        }

        @media (max-width: 768px) {
            .quotes-sidebar {
                padding: 20px;
            }

            .cards-main {
                padding: 20px;
            }

            .welcome-section h1 {
                font-size: 28px;
            }

            .welcome-section p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <?php include "client/header.php"; ?>

    <?php // Route handling

if (isset($_GET["login"])) {
        // Show login page
        include "client/login.php";
    } elseif (isset($_GET["signup"])) {
        // Show signup page
        include "client/signup.php";
    } elseif (isset($_GET["content"])) {
        // Show detailed content page (only if logged in)
        if (!empty($_SESSION["user_id"])) {
            include "client/Content.php";
        } else {
            // Redirect to login if not logged in
            header("Location: index.php?login=true");
            exit();
        }
    } else {
         ?>
        <!-- Show main homepage with quotes and cards -->
        <div class="main-layout">
            <!-- Left Sidebar: Quotes -->
            <aside class="quotes-sidebar">
                <div class="quotes-content">
                    <div class="quotes-header">
                        <h2>✨ Anime Quotes</h2>
                        <p>Daily inspiration from your favorite anime</p>
                    </div>

                    <?php
                    include "client/apiCall.php";

                    // Store fetched data in session for fallback
                    if (
                        isset($content) &&
                        isset($anime_name) &&
                        isset($character_name)
                    ) {
                        $_SESSION["last_quote"] = $content;
                        $_SESSION["last_anime"] = $anime_name;
                        $_SESSION["last_character"] = $character_name;
                    } else {
                        // Fallback to last fetched data from session
                        $content =
                            $_SESSION["last_quote"] ?? "No quote available";
                        $anime_name = $_SESSION["last_anime"] ?? "N/A";
                        $character_name = $_SESSION["last_character"] ?? "N/A";
                    }
                    ?>

                    <div class="quote-card">
                        <div class="quote-icon">💬</div>
                        <div class="quote-text">
                            "<?php echo htmlspecialchars($content); ?>"
                        </div>
                        <div class="quote-meta">
                            <div class="quote-meta-item">
                                <span class="quote-meta-label">📺 Anime:</span>
                                <span class="quote-meta-value"><?php echo htmlspecialchars(
                                    $anime_name,
                                ); ?></span>
                            </div>
                            <div class="quote-meta-item">
                                <span class="quote-meta-label">👤 Character:</span>
                                <span class="quote-meta-value"><?php echo htmlspecialchars(
                                    $character_name,
                                ); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="api-timer">
                        <div>Next refresh in:</div>
                        <div class="countdown" id="countdown">5:00</div>
                    </div>
                </div>
            </aside>

            <!-- Right Main Content: Anime Cards -->
            <main class="cards-main">
                <div class="welcome-section">
                    <h1>🎬 Welcome to Anime Cube</h1>
                    <p>Watch any anime you want - Explore our collection below</p>
                    <?php if (empty($_SESSION["user_id"])): ?>
                        <p style="margin-top: 15px; color: #ff9800;">
                            <strong>👉 Please <a href="?login=true" style="color: #5cb85c; text-decoration: underline;">login</a> to view detailed anime information</strong>
                        </p>
                    <?php endif; ?>
                </div>

                <?php include "client/Card.php"; ?>
            </main>
        </div>

        <script>
            // Countdown Timer for API refresh every 5 minutes
            let timeLeft = 300; // 5 minutes in seconds

            function updateCountdown() {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                const countdownElement = document.getElementById('countdown');

                if (countdownElement) {
                    countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                }

                if (timeLeft <= 0) {
                    location.reload(); // Reload page to fetch new API data
                } else {
                    timeLeft--;
                    setTimeout(updateCountdown, 1000);
                }
            }

            updateCountdown();
        </script>
<?php
    }
// End else - main homepage
?>

</body>
</html>
