<?php
// Content.php - Detailed anime information page
// This file displays comprehensive information about a specific anime

// Check if anime ID is provided
if (!isset($_GET["content"]) || empty($_GET["content"])) {
    header("Location: index.php");
    exit();
}

$anime_id = intval($_GET["content"]);

// Fetch anime details from database
include_once "./Database/db.php";

$stmt = $conn->prepare("SELECT * FROM `anime` WHERE `id` = ? LIMIT 1");
$stmt->bind_param("i", $anime_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='error-message'>Anime not found.</div>";
    exit();
}

$anime = $result->fetch_assoc();
$stmt->close();

// Check if user has added to favorites or watchlist (if logged in)
$is_favorite = false;
$watchlist_status = null;

if (!empty($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];

    // Check favorites
    $fav_stmt = $conn->prepare(
        "SELECT * FROM `user_favorites` WHERE `user_id` = ? AND `anime_id` = ? LIMIT 1",
    );
    $fav_stmt->bind_param("ii", $user_id, $anime_id);
    $fav_stmt->execute();
    $fav_result = $fav_stmt->get_result();
    $is_favorite = $fav_result->num_rows > 0;
    $fav_stmt->close();

    // Check watchlist
    $watch_stmt = $conn->prepare(
        "SELECT `status`, `episodes_watched`, `score` FROM `user_watchlist` WHERE `user_id` = ? AND `anime_id` = ? LIMIT 1",
    );
    $watch_stmt->bind_param("ii", $user_id, $anime_id);
    $watch_stmt->execute();
    $watch_result = $watch_stmt->get_result();
    if ($watch_result->num_rows > 0) {
        $watchlist_status = $watch_result->fetch_assoc();
    }
    $watch_stmt->close();
}
?>

<div class="content-page">
    <!-- Back Button -->
    <div class="back-button-wrapper">
        <a href="index.php" class="btn-back">← Back to Home</a>
    </div>

    <!-- Hero Section with Background -->
    <div class="content-hero" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.9)), url('<?php echo htmlspecialchars(
        $anime["image"],
    ); ?>') center/cover;">
        <div class="hero-content">
            <div class="hero-poster">
                <img src="<?php echo htmlspecialchars(
                    $anime["image"],
                ); ?>" alt="<?php echo htmlspecialchars($anime["title"]); ?>">
            </div>
            <div class="hero-info">
                <h1 class="content-title"><?php echo htmlspecialchars(
                    $anime["title"],
                ); ?></h1>

                <?php if ($anime["title_english"]): ?>
                    <h2 class="content-subtitle"><?php echo htmlspecialchars(
                        $anime["title_english"],
                    ); ?></h2>
                <?php endif; ?>

                <?php if ($anime["title_japanese"]): ?>
                    <h3 class="content-subtitle-jp"><?php echo htmlspecialchars(
                        $anime["title_japanese"],
                    ); ?></h3>
                <?php endif; ?>

                <div class="content-rating">
                    <?php if ($anime["score"] > 0): ?>
                        <div class="score-large">
                            <span class="score-icon">⭐</span>
                            <span class="score-number"><?php echo number_format(
                                $anime["score"],
                                2,
                            ); ?></span>
                            <?php if ($anime["scored_by"] > 0): ?>
                                <span class="score-users">(<?php echo number_format(
                                    $anime["scored_by"],
                                ); ?> users)</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($anime["rank"]): ?>
                        <div class="rank-badge">Rank #<?php echo number_format(
                            $anime["rank"],
                        ); ?></div>
                    <?php endif; ?>

                    <?php if ($anime["popularity"]): ?>
                        <div class="popularity-badge">Popularity #<?php echo number_format(
                            $anime["popularity"],
                        ); ?></div>
                    <?php endif; ?>
                </div>

                <!-- User Actions -->
                <?php if (!empty($_SESSION["user_id"])): ?>
                    <div class="user-actions">
                        <button class="btn-action <?php echo $is_favorite
                            ? "active"
                            : ""; ?>" id="favoriteBtn" data-anime-id="<?php echo $anime[
    "id"
]; ?>">
                            <span class="icon">❤️</span> <?php echo $is_favorite
                                ? "Remove from Favorites"
                                : "Add to Favorites"; ?>
                        </button>

                        <div class="watchlist-dropdown">
                            <button class="btn-action" id="watchlistBtn">
                                <span class="icon">📝</span>
                                <?php echo $watchlist_status
                                    ? "Update Watchlist"
                                    : "Add to Watchlist"; ?>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="content-main">
        <!-- Synopsis Section -->
        <div class="content-section">
            <h2 class="section-title">Synopsis</h2>
            <div class="synopsis-text">
                <?php echo $anime["synopsis"]
                    ? nl2br(htmlspecialchars($anime["synopsis"]))
                    : nl2br(htmlspecialchars($anime["description"])); ?>
            </div>
        </div>

        <!-- Information Grid -->
        <div class="content-section">
            <h2 class="section-title">Information</h2>
            <div class="info-grid">
                <?php if ($anime["type"]): ?>
                    <div class="info-item">
                        <span class="info-label">Type:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["type"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($anime["episodes"]): ?>
                    <div class="info-item">
                        <span class="info-label">Episodes:</span>
                        <span class="info-value"><?php echo $anime[
                            "episodes"
                        ]; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($anime["status"]): ?>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value status-badge-<?php echo strtolower(
                            str_replace(" ", "-", $anime["status"]),
                        ); ?>">
                            <?php echo htmlspecialchars($anime["status"]); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($anime["aired_from"]): ?>
                    <div class="info-item">
                        <span class="info-label">Aired:</span>
                        <span class="info-value">
                            <?php echo date(
                                "M d, Y",
                                strtotime($anime["aired_from"]),
                            ); ?>
                            <?php if ($anime["aired_to"]): ?>
                                to <?php echo date(
                                    "M d, Y",
                                    strtotime($anime["aired_to"]),
                                ); ?>
                            <?php else: ?>
                                to ?
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($anime["premiered"]): ?>
                    <div class="info-item">
                        <span class="info-label">Premiered:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["premiered"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($anime["broadcast"]): ?>
                    <div class="info-item">
                        <span class="info-label">Broadcast:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["broadcast"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($anime["producers"]): ?>
                    <div class="info-item">
                        <span class="info-label">Producers:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["producers"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($anime["licensors"]): ?>
                    <div class="info-item">
                        <span class="info-label">Licensors:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["licensors"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($anime["studios"]): ?>
                    <div class="info-item">
                        <span class="info-label">Studios:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["studios"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($anime["source"]): ?>
                    <div class="info-item">
                        <span class="info-label">Source:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["source"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($anime["genres"]): ?>
                    <div class="info-item">
                        <span class="info-label">Genres:</span>
                        <span class="info-value">
                            <?php
                            $genres = explode(",", $anime["genres"]);
                            foreach ($genres as $genre): ?>
                                <span class="tag"><?php echo htmlspecialchars(
                                    trim($genre),
                                ); ?></span>
                            <?php endforeach;
                            ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($anime["themes"]): ?>
                    <div class="info-item">
                        <span class="info-label">Themes:</span>
                        <span class="info-value">
                            <?php
                            $themes = explode(",", $anime["themes"]);
                            foreach ($themes as $theme): ?>
                                <span class="tag"><?php echo htmlspecialchars(
                                    trim($theme),
                                ); ?></span>
                            <?php endforeach;
                            ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($anime["demographic"]): ?>
                    <div class="info-item">
                        <span class="info-label">Demographic:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["demographic"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($anime["duration"]): ?>
                    <div class="info-item">
                        <span class="info-label">Duration:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["duration"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($anime["rating"]): ?>
                    <div class="info-item">
                        <span class="info-label">Rating:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["rating"],
                        ); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics Section -->
        <?php if ($anime["members"] || $anime["favorites"]): ?>
            <div class="content-section">
                <h2 class="section-title">Statistics</h2>
                <div class="stats-grid">
                    <?php if ($anime["members"]): ?>
                        <div class="stat-item">
                            <div class="stat-label">Members</div>
                            <div class="stat-value"><?php echo number_format(
                                $anime["members"],
                            ); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($anime["favorites"]): ?>
                        <div class="stat-item">
                            <div class="stat-label">Favorites</div>
                            <div class="stat-value"><?php echo number_format(
                                $anime["favorites"],
                            ); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Trailer Section -->
        <?php if ($anime["trailer_url"]): ?>
            <div class="content-section">
                <h2 class="section-title">Trailer</h2>
                <div class="trailer-wrapper">
                    <iframe
                        src="<?php echo htmlspecialchars(
                            $anime["trailer_url"],
                        ); ?>"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.content-page {
    width: 100%;
    min-height: 100vh;
    background: #f5f5f5;
}

.back-button-wrapper {
    padding: 15px 30px;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-back {
    display: inline-block;
    padding: 8px 16px;
    background: #5cb85c;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
    transition: background 0.3s;
}

.btn-back:hover {
    background: #4cae4c;
}

.content-hero {
    padding: 60px 30px;
    color: white;
}

.hero-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    gap: 40px;
    align-items: flex-start;
}

.hero-poster {
    flex-shrink: 0;
}

.hero-poster img {
    width: 300px;
    height: 420px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
}

.hero-info {
    flex: 1;
}

.content-title {
    font-size: 36px;
    font-weight: bold;
    margin: 0 0 10px 0;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
}

.content-subtitle {
    font-size: 22px;
    color: #ddd;
    margin: 0 0 5px 0;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.8);
}

.content-subtitle-jp {
    font-size: 18px;
    color: #ccc;
    margin: 0 0 20px 0;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.8);
}

.content-rating {
    display: flex;
    gap: 20px;
    align-items: center;
    margin-bottom: 25px;
}

.score-large {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 24px;
    font-weight: bold;
}

.score-icon {
    font-size: 28px;
}

.score-users {
    font-size: 14px;
    color: #ddd;
    font-weight: normal;
}

.rank-badge, .popularity-badge {
    padding: 6px 12px;
    background: rgba(255,255,255,0.2);
    border-radius: 5px;
    font-size: 14px;
    font-weight: 600;
}

.user-actions {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.btn-action {
    padding: 12px 24px;
    background: #5cb85c;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-action:hover {
    background: #4cae4c;
}

.btn-action.active {
    background: #d9534f;
}

.btn-action.active:hover {
    background: #c9302c;
}

.content-main {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 30px;
}

.content-section {
    background: white;
    padding: 30px;
    margin-bottom: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.section-title {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin: 0 0 20px 0;
    padding-bottom: 10px;
    border-bottom: 3px solid #5cb85c;
}

.synopsis-text {
    font-size: 16px;
    line-height: 1.8;
    color: #444;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.info-item {
    display: flex;
    gap: 10px;
}

.info-label {
    font-weight: 600;
    color: #666;
    min-width: 120px;
}

.info-value {
    color: #333;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.tag {
    display: inline-block;
    padding: 4px 12px;
    background: #f0f0f0;
    border-radius: 12px;
    font-size: 13px;
    color: #555;
    border: 1px solid #ddd;
}

.status-badge-airing {
    color: #4caf50;
    font-weight: 600;
}

.status-badge-finished-airing {
    color: #2196f3;
    font-weight: 600;
}

.status-badge-not-yet-aired {
    color: #ff9800;
    font-weight: 600;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-item {
    text-align: center;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.stat-label {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #333;
}

.trailer-wrapper {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
}

.trailer-wrapper iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 8px;
}

.error-message {
    padding: 40px;
    text-align: center;
    font-size: 18px;
    color: #d9534f;
}

@media (max-width: 768px) {
    .hero-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .hero-poster img {
        width: 250px;
        height: 350px;
    }

    .content-title {
        font-size: 28px;
    }

    .content-rating {
        justify-content: center;
    }

    .user-actions {
        flex-direction: column;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Handle favorite button
document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtn = document.getElementById('favoriteBtn');
    const watchlistBtn = document.getElementById('watchlistBtn');

    // Favorite toggle
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', function() {
            const animeId = this.getAttribute('data-anime-id');
            const isActive = this.classList.contains('active');

            // Send AJAX request
            fetch('server/userActions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=toggle_favorite&anime_id=' + animeId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'added') {
                        favoriteBtn.classList.add('active');
                        favoriteBtn.innerHTML = '<span class="icon">❤️</span> Remove from Favorites';
                    } else {
                        favoriteBtn.classList.remove('active');
                        favoriteBtn.innerHTML = '<span class="icon">❤️</span> Add to Favorites';
                    }
                    alert(data.message);
                } else {
                    alert(data.message || 'An error occurred.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update favorites. Please try again.');
            });
        });
    }

    // Watchlist button - for now just show alert (can be expanded to modal)
    if (watchlistBtn) {
        watchlistBtn.addEventListener('click', function() {
            const animeId = <?php echo $anime["id"]; ?>;
            const currentStatus = '<?php echo $watchlist_status
                ? $watchlist_status["status"]
                : "Plan to Watch"; ?>';

            // Simple prompt-based interface
            const statuses = ['Watching', 'Completed', 'On Hold', 'Dropped', 'Plan to Watch'];
            let statusChoice = prompt(
                'Choose watchlist status:\n' +
                '1. Watching\n' +
                '2. Completed\n' +
                '3. On Hold\n' +
                '4. Dropped\n' +
                '5. Plan to Watch\n\n' +
                'Enter number (1-5):',
                statuses.indexOf(currentStatus) + 1
            );

            if (statusChoice === null) return; // User cancelled

            statusChoice = parseInt(statusChoice);
            if (statusChoice < 1 || statusChoice > 5) {
                alert('Invalid choice.');
                return;
            }

            const status = statuses[statusChoice - 1];
            const episodesWatched = prompt('Episodes watched:', '<?php echo $watchlist_status
                ? $watchlist_status["episodes_watched"]
                : "0"; ?>');
            const score = prompt('Your score (1-10, or leave empty):', '<?php echo $watchlist_status &&
            $watchlist_status["score"]
                ? $watchlist_status["score"]
                : ""; ?>');

            if (episodesWatched === null) return; // User cancelled

            // Send AJAX request
            let body = 'action=update_watchlist&anime_id=' + animeId +
                       '&status=' + encodeURIComponent(status) +
                       '&episodes_watched=' + parseInt(episodesWatched || 0);

            if (score && score.trim() !== '') {
                body += '&score=' + parseInt(score);
            }

            fetch('server/userActions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: body
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    watchlistBtn.innerHTML = '<span class="icon">📝</span> Update Watchlist';
                    // Optionally reload page to show updated info
                    location.reload();
                } else {
                    alert(data.message || 'An error occurred.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update watchlist. Please try again.');
            });
        });
    }
});
</script>
