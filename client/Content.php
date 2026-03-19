<?php
// Content.php - Detailed anime information page
// This file displays comprehensive information about a specific anime
// Dynamically fetches from MyAnimeList if requested

// Check if anime ID is provided
if (!isset($_GET["content"]) || empty($_GET["content"])) {
    header("Location: index.php");
    exit();
}

include_once "./Database/db.php";

$anime = null;
$anime_id = intval($_GET["content"]);
$is_mal = isset($_GET["source"]) && $_GET["source"] === "mal";

if ($is_mal) {
    // Fetch from Jikan API
    $api_url = "https://api.jikan.moe/v4/anime/" . $anime_id . "/full";

    // We use stream context to set timeout and user agent
    $context = stream_context_create([
        "http" => [
            "timeout" => 5,
            "user_agent" => "AnimeCube/1.0",
        ],
    ]);

    $json = @file_get_contents($api_url, false, $context);

    if ($json) {
        $data = json_decode($json, true);
        if (isset($data["data"])) {
            $mal_data = $data["data"];

            // Map MAL data to our structure
            $title = $mal_data["title"] ?? "Unknown";

            // Check if it already exists in our DB by title to get the local ID
            $stmt = $conn->prepare(
                "SELECT * FROM `anime` WHERE `title` = ? LIMIT 1",
            );
            $stmt->bind_param("s", $title);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $anime = $result->fetch_assoc();
            } else {
                // Insert into our DB so foreign keys for favorites and watchlist work
                $title_eng = $mal_data["title_english"] ?? null;
                $title_jap = $mal_data["title_japanese"] ?? null;
                $image =
                    $mal_data["images"]["webp"]["large_image_url"] ??
                    ($mal_data["images"]["jpg"]["image_url"] ?? "");
                $description =
                    $mal_data["synopsis"] ?? "No description available.";
                $synopsis = $description;

                $allowed_types = ["TV", "Movie", "OVA", "ONA", "Special"];
                $type =
                    isset($mal_data["type"]) &&
                    in_array($mal_data["type"], $allowed_types)
                        ? $mal_data["type"]
                        : "TV";

                $episodes = $mal_data["episodes"] ?? 0;

                $status_map = [
                    "Currently Airing" => "Airing",
                    "Finished Airing" => "Finished Airing",
                    "Not yet aired" => "Not yet aired",
                ];
                $status =
                    isset($mal_data["status"]) &&
                    isset($status_map[$mal_data["status"]])
                        ? $status_map[$mal_data["status"]]
                        : "Finished Airing";

                $aired_from = !empty($mal_data["aired"]["from"])
                    ? date("Y-m-d", strtotime($mal_data["aired"]["from"]))
                    : null;
                $aired_to = !empty($mal_data["aired"]["to"])
                    ? date("Y-m-d", strtotime($mal_data["aired"]["to"]))
                    : null;

                $premiered =
                    isset($mal_data["season"]) && isset($mal_data["year"])
                        ? ucfirst($mal_data["season"]) . " " . $mal_data["year"]
                        : null;
                $broadcast = $mal_data["broadcast"]["string"] ?? null;

                $producers = isset($mal_data["producers"])
                    ? implode(
                        ", ",
                        array_column($mal_data["producers"], "name"),
                    )
                    : null;
                $licensors = isset($mal_data["licensors"])
                    ? implode(
                        ", ",
                        array_column($mal_data["licensors"], "name"),
                    )
                    : null;
                $studios = isset($mal_data["studios"])
                    ? implode(", ", array_column($mal_data["studios"], "name"))
                    : null;
                $source = $mal_data["source"] ?? null;
                $genres = isset($mal_data["genres"])
                    ? implode(", ", array_column($mal_data["genres"], "name"))
                    : null;
                $themes = isset($mal_data["themes"])
                    ? implode(", ", array_column($mal_data["themes"], "name"))
                    : null;
                $demographic = isset($mal_data["demographics"])
                    ? implode(
                        ", ",
                        array_column($mal_data["demographics"], "name"),
                    )
                    : null;

                $duration = $mal_data["duration"] ?? null;
                $rating = $mal_data["rating"] ?? null;
                $score = $mal_data["score"] ?? 0.0;
                $scored_by = $mal_data["scored_by"] ?? 0;
                $rank = $mal_data["rank"] ?? null;
                $popularity = $mal_data["popularity"] ?? null;
                $members = $mal_data["members"] ?? 0;
                $favorites = $mal_data["favorites"] ?? 0;
                $trailer_url = $mal_data["trailer"]["embed_url"] ?? null;

                // Insert query
                $insert_query = "INSERT INTO `anime` (
                    `title`, `title_english`, `title_japanese`, `image`, `description`, `synopsis`,
                    `type`, `episodes`, `status`, `aired_from`, `aired_to`, `premiered`, `broadcast`,
                    `producers`, `licensors`, `studios`, `source`, `genres`, `themes`, `demographic`,
                    `duration`, `rating`, `score`, `scored_by`, `rank`, `popularity`, `members`, `favorites`, `trailer_url`
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt_in = $conn->prepare($insert_query);
                if ($stmt_in) {
                    $stmt_in->bind_param(
                        "sssssssissssssssssssssdiiiiis",
                        $title,
                        $title_eng,
                        $title_jap,
                        $image,
                        $description,
                        $synopsis,
                        $type,
                        $episodes,
                        $status,
                        $aired_from,
                        $aired_to,
                        $premiered,
                        $broadcast,
                        $producers,
                        $licensors,
                        $studios,
                        $source,
                        $genres,
                        $themes,
                        $demographic,
                        $duration,
                        $rating,
                        $score,
                        $scored_by,
                        $rank,
                        $popularity,
                        $members,
                        $favorites,
                        $trailer_url,
                    );
                    if ($stmt_in->execute()) {
                        $new_id = $stmt_in->insert_id;

                        // Fetch the newly inserted anime
                        $stmt_fetch = $conn->prepare(
                            "SELECT * FROM `anime` WHERE `id` = ?",
                        );
                        $stmt_fetch->bind_param("i", $new_id);
                        $stmt_fetch->execute();
                        $anime = $stmt_fetch->get_result()->fetch_assoc();
                        $stmt_fetch->close();
                    }
                    $stmt_in->close();
                }
            }
            $stmt->close();
        }
    }
}

// If not MAL source or fetching failed, try fetching from local DB by ID
if (!$anime) {
    $stmt = $conn->prepare("SELECT * FROM `anime` WHERE `id` = ? LIMIT 1");
    $stmt->bind_param("i", $anime_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $anime = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$anime) {
    echo "<div class='error-message'>Anime not found. Could not load from MyAnimeList or local database. Please try again later.</div>";
    exit();
}

// Use the local DB ID for favorites/watchlist
$local_anime_id = $anime["id"];

// Check if user has added to favorites or watchlist (if logged in)
$is_favorite = false;
$watchlist_status = null;

if (!empty($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];

    // Check favorites
    $fav_stmt = $conn->prepare(
        "SELECT * FROM `user_favorites` WHERE `user_id` = ? AND `anime_id` = ? LIMIT 1",
    );
    $fav_stmt->bind_param("ii", $user_id, $local_anime_id);
    $fav_stmt->execute();
    $fav_result = $fav_stmt->get_result();
    $is_favorite = $fav_result->num_rows > 0;
    $fav_stmt->close();

    // Check watchlist
    $watch_stmt = $conn->prepare(
        "SELECT `status`, `episodes_watched`, `score` FROM `user_watchlist` WHERE `user_id` = ? AND `anime_id` = ? LIMIT 1",
    );
    $watch_stmt->bind_param("ii", $user_id, $local_anime_id);
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

                <?php if (!empty($anime["title_english"])): ?>
                    <h2 class="content-subtitle"><?php echo htmlspecialchars(
                        $anime["title_english"],
                    ); ?></h2>
                <?php endif; ?>

                <?php if (!empty($anime["title_japanese"])): ?>
                    <h3 class="content-subtitle-jp"><?php echo htmlspecialchars(
                        $anime["title_japanese"],
                    ); ?></h3>
                <?php endif; ?>

                <div class="content-rating">
                    <?php if (
                        !empty($anime["score"]) &&
                        $anime["score"] > 0
                    ): ?>
                        <div class="score-large">
                            <span class="score-icon">⭐</span>
                            <span class="score-number"><?php echo number_format(
                                $anime["score"],
                                2,
                            ); ?></span>
                            <?php if (
                                !empty($anime["scored_by"]) &&
                                $anime["scored_by"] > 0
                            ): ?>
                                <span class="score-users">(<?php echo number_format(
                                    $anime["scored_by"],
                                ); ?> users)</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($anime["rank"])): ?>
                        <div class="rank-badge">Rank #<?php echo number_format(
                            $anime["rank"],
                        ); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($anime["popularity"])): ?>
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
                            : ""; ?>" id="favoriteBtn" data-anime-id="<?php echo $local_anime_id; ?>">
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
                <?php echo !empty($anime["synopsis"])
                    ? nl2br(htmlspecialchars($anime["synopsis"]))
                    : nl2br(htmlspecialchars($anime["description"])); ?>
            </div>
        </div>

        <!-- Information Grid -->
        <div class="content-section">
            <h2 class="section-title">Information</h2>
            <div class="info-grid">
                <?php if (!empty($anime["type"])): ?>
                    <div class="info-item">
                        <span class="info-label">Type:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["type"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($anime["episodes"])): ?>
                    <div class="info-item">
                        <span class="info-label">Episodes:</span>
                        <span class="info-value"><?php echo $anime[
                            "episodes"
                        ]; ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($anime["status"])): ?>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value status-badge-<?php echo strtolower(
                            str_replace(" ", "-", $anime["status"]),
                        ); ?>">
                            <?php echo htmlspecialchars($anime["status"]); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($anime["aired_from"])): ?>
                    <div class="info-item">
                        <span class="info-label">Aired:</span>
                        <span class="info-value">
                            <?php echo date(
                                "M d, Y",
                                strtotime($anime["aired_from"]),
                            ); ?>
                            <?php if (!empty($anime["aired_to"])): ?>
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

                <?php if (!empty($anime["premiered"])): ?>
                    <div class="info-item">
                        <span class="info-label">Premiered:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["premiered"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($anime["broadcast"])): ?>
                    <div class="info-item">
                        <span class="info-label">Broadcast:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["broadcast"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($anime["producers"])): ?>
                    <div class="info-item">
                        <span class="info-label">Producers:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["producers"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($anime["licensors"])): ?>
                    <div class="info-item">
                        <span class="info-label">Licensors:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["licensors"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($anime["studios"])): ?>
                    <div class="info-item">
                        <span class="info-label">Studios:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["studios"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($anime["source"])): ?>
                    <div class="info-item">
                        <span class="info-label">Source:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["source"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($anime["genres"])): ?>
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

                <?php if (!empty($anime["themes"])): ?>
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

                <?php if (!empty($anime["demographic"])): ?>
                    <div class="info-item">
                        <span class="info-label">Demographic:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["demographic"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($anime["duration"])): ?>
                    <div class="info-item">
                        <span class="info-label">Duration:</span>
                        <span class="info-value"><?php echo htmlspecialchars(
                            $anime["duration"],
                        ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($anime["rating"])): ?>
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
        <?php if (!empty($anime["members"]) || !empty($anime["favorites"])): ?>
            <div class="content-section">
                <h2 class="section-title">Statistics</h2>
                <div class="stats-grid">
                    <?php if (!empty($anime["members"])): ?>
                        <div class="stat-item">
                            <div class="stat-label">Members</div>
                            <div class="stat-value"><?php echo number_format(
                                $anime["members"],
                            ); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($anime["favorites"])): ?>
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
        <?php if (!empty($anime["trailer_url"])): ?>
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

        <!-- Recommendations Section -->
        <div class="content-section">
            <h2 class="section-title">Recommendations</h2>
            <div class="recommendations-grid" id="recommendations-container">
                <div class="loading-spinner" style="text-align:center; padding: 20px; font-style: italic; color: #666;">Loading recommendations...</div>
            </div>
        </div>
    </div>
</div>

<style>
.recommendations-grid {
    display: flex;
    gap: 15px;
    overflow-x: auto;
    padding-bottom: 15px;
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.recommendations-grid::-webkit-scrollbar {
    display: none;
}
.recommendation-card {
    min-width: 150px;
    max-width: 150px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: transform 0.2s;
    background: white;
    flex-shrink: 0;
}
.recommendation-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.recommendation-img {
    width: 100%;
    height: 210px;
    object-fit: cover;
}
.recommendation-title {
    padding: 10px;
    font-size: 13px;
    font-weight: bold;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #333;
}
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
    background: #667eea;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
    transition: background 0.3s;
}

.btn-back:hover {
    background: #5568d3;
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
    background: #667eea;
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
    background: #5568d3;
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
    border-bottom: 3px solid #667eea;
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
    font-weight: bold;
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
            const animeId = <?php echo $local_anime_id; ?>;
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

// Fetch Recommendations
document.addEventListener('DOMContentLoaded', function() {
    const malId = <?php echo $is_mal ? $anime_id : "null"; ?>;

    // If we have a MAL ID, we can fetch recommendations directly
    if (malId) {
        fetchRecommendations(malId);
    } else {
        document.getElementById('recommendations-container').innerHTML = '<div style="padding: 20px; color: #666; text-align: center;">Recommendations not available for local-only anime.</div>';
    }

    function fetchRecommendations(id) {
        fetch('https://api.jikan.moe/v4/anime/' + id + '/recommendations')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('recommendations-container');
                container.innerHTML = '';

                if (data.data && data.data.length > 0) {
                    const recs = data.data.slice(0, 15); // show top 15
                    recs.forEach(rec => {
                        const anime = rec.entry;
                        const card = document.createElement('div');
                        card.className = 'recommendation-card';
                        card.onclick = () => window.location.href = '?content=' + anime.mal_id + '&source=mal';

                        card.innerHTML = `
                            <img src="${anime.images.webp.image_url}" alt="${anime.title}" class="recommendation-img" loading="lazy">
                            <div class="recommendation-title" title="${anime.title}">${anime.title}</div>
                        `;
                        container.appendChild(card);
                    });
                } else {
                    container.innerHTML = '<div style="padding: 20px; color: #666; text-align: center;">No recommendations found.</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching recommendations:', error);
                document.getElementById('recommendations-container').innerHTML = '<div style="padding: 20px; color: #d9534f; text-align: center;">Failed to load recommendations.</div>';
            });
    }
});
</script>
