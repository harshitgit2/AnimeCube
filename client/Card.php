<?php
// Card.php - Anime card component
// Fetch anime data from database
include_once "./Database/db.php";

$query = "SELECT * FROM `anime` ORDER BY `score` DESC, `created_at` DESC";
$result = $conn->query($query);

$animes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $animes[] = $row;
    }
}
?>

<div class="anime-cards-container">
    <div class="cards-grid">
        <?php if (empty($animes)): ?>
            <div class="no-anime-message">
                <p>No anime available at the moment. Please check back later!</p>
            </div>
        <?php else: ?>
            <?php foreach ($animes as $anime): ?>
                <div class="anime-card" data-anime-id="<?php echo $anime['id']; ?>">
                    <div class="card-image-wrapper">
                        <img
                            src="<?php echo htmlspecialchars($anime['image']); ?>"
                            alt="<?php echo htmlspecialchars($anime['title']); ?>"
                            class="card-image"
                            loading="lazy"
                        >
                        <?php if ($anime['score'] > 0): ?>
                            <div class="card-score">
                                <span class="score-icon">⭐</span>
                                <span class="score-value"><?php echo number_format($anime['score'], 2); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($anime['status']): ?>
                            <div class="card-status status-<?php echo strtolower(str_replace(' ', '-', $anime['status'])); ?>">
                                <?php echo htmlspecialchars($anime['status']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-content">
                        <h3 class="card-title">
                            <?php echo htmlspecialchars($anime['title']); ?>
                        </h3>

                        <?php if ($anime['title_english'] && $anime['title_english'] !== $anime['title']): ?>
                            <p class="card-subtitle">
                                <?php echo htmlspecialchars($anime['title_english']); ?>
                            </p>
                        <?php endif; ?>

                        <div class="card-meta">
                            <?php if ($anime['type']): ?>
                                <span class="meta-badge"><?php echo htmlspecialchars($anime['type']); ?></span>
                            <?php endif; ?>

                            <?php if ($anime['episodes'] > 0): ?>
                                <span class="meta-badge"><?php echo $anime['episodes']; ?> Episodes</span>
                            <?php endif; ?>

                            <?php if ($anime['premiered']): ?>
                                <span class="meta-badge"><?php echo htmlspecialchars($anime['premiered']); ?></span>
                            <?php endif; ?>
                        </div>

                        <p class="card-description">
                            <?php
                                $description = htmlspecialchars($anime['description']);
                                echo strlen($description) > 120
                                    ? substr($description, 0, 120) . '...'
                                    : $description;
                            ?>
                        </p>

                        <?php if ($anime['genres']): ?>
                            <div class="card-genres">
                                <?php
                                    $genres = explode(',', $anime['genres']);
                                    $displayGenres = array_slice($genres, 0, 3);
                                    foreach ($displayGenres as $genre):
                                ?>
                                    <span class="genre-tag"><?php echo htmlspecialchars(trim($genre)); ?></span>
                                <?php endforeach; ?>

                                <?php if (count($genres) > 3): ?>
                                    <span class="genre-tag">+<?php echo count($genres) - 3; ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="card-actions">
                            <?php if (!empty($_SESSION['user_id'])): ?>
                                <!-- User is logged in, allow access to content -->
                                <a href="?content=<?php echo $anime['id']; ?>" class="btn-view-details">
                                    View Details
                                </a>
                            <?php else: ?>
                                <!-- User not logged in, redirect to login -->
                                <a href="?login=true" class="btn-view-details btn-login-required">
                                    Login to View
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.anime-cards-container {
    width: 100%;
    padding: 20px;
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
    margin-top: 20px;
}

.anime-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    display: flex;
    flex-direction: column;
}

.anime-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.card-image-wrapper {
    position: relative;
    width: 100%;
    height: 380px;
    overflow: hidden;
    background: #f0f0f0;
}

.card-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.anime-card:hover .card-image {
    transform: scale(1.05);
}

.card-score {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.8);
    color: #ffd700;
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.score-icon {
    font-size: 16px;
}

.card-status {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 5px 12px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-airing {
    background: #4caf50;
    color: white;
}

.status-finished-airing {
    background: #2196f3;
    color: white;
}

.status-not-yet-aired {
    background: #ff9800;
    color: white;
}

.card-content {
    padding: 15px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.card-title {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    margin: 0 0 5px 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-subtitle {
    font-size: 13px;
    color: #666;
    margin: 0 0 10px 0;
    font-style: italic;
}

.card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}

.meta-badge {
    font-size: 11px;
    background: #e0e0e0;
    color: #555;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 500;
}

.card-description {
    font-size: 14px;
    color: #555;
    line-height: 1.5;
    margin: 0 0 12px 0;
    flex-grow: 1;
}

.card-genres {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 15px;
}

.genre-tag {
    font-size: 11px;
    background: #f5f5f5;
    color: #333;
    padding: 4px 10px;
    border-radius: 12px;
    border: 1px solid #ddd;
}

.card-actions {
    margin-top: auto;
}

.btn-view-details {
    display: block;
    width: 100%;
    padding: 10px;
    background: #5cb85c;
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: background 0.3s ease;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.5px;
}

.btn-view-details:hover {
    background: #4cae4c;
}

.btn-login-required {
    background: #ff9800;
}

.btn-login-required:hover {
    background: #f57c00;
}

.no-anime-message {
    text-align: center;
    padding: 60px 20px;
    color: #777;
    font-size: 18px;
    grid-column: 1 / -1;
}

@media (max-width: 768px) {
    .cards-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 15px;
    }

    .card-image-wrapper {
        height: 300px;
    }
}

@media (max-width: 480px) {
    .cards-grid {
        grid-template-columns: 1fr;
    }
}
</style>
