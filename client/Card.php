<?php
// Card.php - Anime card component with search and filtering
// Fetch anime data from database
include_once "./Database/db.php";

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$genre_filter = isset($_GET['genre']) ? trim($_GET['genre']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$type_filter = isset($_GET['type']) ? trim($_GET['type']) : '';
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'score_desc';

// Build the query with filters
$query = "SELECT * FROM `anime` WHERE 1=1";
$params = [];
$types = "";

// Search filter
if (!empty($search)) {
    $query .= " AND (title LIKE ? OR title_english LIKE ? OR title_japanese LIKE ? OR description LIKE ? OR synopsis LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
    $types .= "sssss";
}

// Genre filter
if (!empty($genre_filter)) {
    $query .= " AND genres LIKE ?";
    $params[] = "%$genre_filter%";
    $types .= "s";
}

// Status filter
if (!empty($status_filter)) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// Type filter
if (!empty($type_filter)) {
    $query .= " AND type = ?";
    $params[] = $type_filter;
    $types .= "s";
}

// Sorting
switch ($sort_by) {
    case 'score_desc':
        $query .= " ORDER BY score DESC, created_at DESC";
        break;
    case 'score_asc':
        $query .= " ORDER BY score ASC, created_at DESC";
        break;
    case 'title_asc':
        $query .= " ORDER BY title ASC";
        break;
    case 'title_desc':
        $query .= " ORDER BY title DESC";
        break;
    case 'popularity':
        $query .= " ORDER BY popularity DESC, score DESC";
        break;
    case 'newest':
        $query .= " ORDER BY created_at DESC";
        break;
    case 'oldest':
        $query .= " ORDER BY created_at ASC";
        break;
    default:
        $query .= " ORDER BY score DESC, created_at DESC";
}

// Execute query
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

$animes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $animes[] = $row;
    }
}

// Get unique genres for filter dropdown
$genres_query = "SELECT genres FROM anime WHERE genres IS NOT NULL AND genres != ''";
$genres_result = $conn->query($genres_query);
$available_genres = [];
if ($genres_result) {
    while ($row = $genres_result->fetch_assoc()) {
        $genres = explode(',', $row['genres']);
        foreach ($genres as $genre) {
            $genre = trim($genre);
            if (!empty($genre)) {
                $available_genres[] = $genre;
            }
        }
    }
}
$available_genres = array_unique($available_genres);
sort($available_genres);

// Get unique statuses and types for filter dropdowns
$statuses_query = "SELECT DISTINCT status FROM anime WHERE status IS NOT NULL AND status != '' ORDER BY status";
$statuses_result = $conn->query($statuses_query);
$available_statuses = [];
if ($statuses_result) {
    while ($row = $statuses_result->fetch_assoc()) {
        $available_statuses[] = $row['status'];
    }
}

$types_query = "SELECT DISTINCT type FROM anime WHERE type IS NOT NULL AND type != '' ORDER BY type";
$types_result = $conn->query($types_query);
$available_types = [];
if ($types_result) {
    while ($row = $types_result->fetch_assoc()) {
        $available_types[] = $row['type'];
    }
}
?>

<div class="anime-cards-container">
    <!-- Search and Filter Controls -->
    <div class="search-filter-section">
        <form method="GET" action="" class="search-filter-form" id="searchFilterForm">
            <div class="search-row">
                <!-- Search Bar -->
                <div class="search-input-group">
                    <input
                        type="text"
                        name="search"
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search anime by title, description..."
                        class="search-input"
                        id="searchInput"
                    >
                    <button type="submit" class="search-btn">
                        <span class="search-icon">🔍</span>
                    </button>
                </div>

                <!-- Clear Filters Button -->
                <button type="button" class="clear-filters-btn" id="clearFiltersBtn">
                    Clear Filters
                </button>
            </div>

            <div class="filter-row">
                <!-- Genre Filter -->
                <div class="filter-group">
                    <label for="genre">Genre:</label>
                    <select name="genre" id="genre" class="filter-select">
                        <option value="">All Genres</option>
                        <?php foreach ($available_genres as $genre): ?>
                            <option value="<?php echo htmlspecialchars($genre); ?>" <?php echo $genre_filter === $genre ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($genre); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="filter-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status" class="filter-select">
                        <option value="">All Statuses</option>
                        <?php foreach ($available_statuses as $status): ?>
                            <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($status); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Type Filter -->
                <div class="filter-group">
                    <label for="type">Type:</label>
                    <select name="type" id="type" class="filter-select">
                        <option value="">All Types</option>
                        <?php foreach ($available_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $type_filter === $type ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sort By -->
                <div class="filter-group">
                    <label for="sort">Sort by:</label>
                    <select name="sort" id="sort" class="filter-select">
                        <option value="score_desc" <?php echo $sort_by === 'score_desc' ? 'selected' : ''; ?>>Highest Rated</option>
                        <option value="score_asc" <?php echo $sort_by === 'score_asc' ? 'selected' : ''; ?>>Lowest Rated</option>
                        <option value="popularity" <?php echo $sort_by === 'popularity' ? 'selected' : ''; ?>>Most Popular</option>
                        <option value="title_asc" <?php echo $sort_by === 'title_asc' ? 'selected' : ''; ?>>Title A-Z</option>
                        <option value="title_desc" <?php echo $sort_by === 'title_desc' ? 'selected' : ''; ?>>Title Z-A</option>
                        <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Recently Added</option>
                        <option value="oldest" <?php echo $sort_by === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="results-summary">
        <p>
            <?php if (!empty($search) || !empty($genre_filter) || !empty($status_filter) || !empty($type_filter)): ?>
                Found <strong><?php echo count($animes); ?></strong> anime
                <?php if (!empty($search)): ?> matching "<strong><?php echo htmlspecialchars($search); ?></strong>"<?php endif; ?>
                <?php if (!empty($genre_filter)): ?> in genre "<strong><?php echo htmlspecialchars($genre_filter); ?></strong>"<?php endif; ?>
                <?php if (!empty($status_filter)): ?> with status "<strong><?php echo htmlspecialchars($status_filter); ?></strong>"<?php endif; ?>
                <?php if (!empty($type_filter)): ?> of type "<strong><?php echo htmlspecialchars($type_filter); ?></strong>"<?php endif; ?>
            <?php else: ?>
                Showing all <strong><?php echo count($animes); ?></strong> anime
            <?php endif; ?>
        </p>
    </div>

    <div class="cards-grid">
        <?php if (empty($animes)): ?>
            <div class="no-anime-message">
                <p>No anime found matching your criteria. Try adjusting your search or filters.</p>
                <a href="index.php" class="btn-back">← Back to All Anime</a>
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

/* Search and Filter Section */
.search-filter-section {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.search-filter-form {
    width: 100%;
}

.search-row {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    align-items: center;
}

.search-input-group {
    flex: 1;
    position: relative;
    display: flex;
    max-width: 500px;
}

.search-input {
    flex: 1;
    padding: 12px 45px 12px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 25px;
    font-size: 16px;
    transition: border-color 0.3s ease;
    background: #f8f9fa;
}

.search-input:focus {
    outline: none;
    border-color: #5cb85c;
    background: white;
}

.search-btn {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    background: #5cb85c;
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s ease;
}

.search-btn:hover {
    background: #4cae4c;
}

.search-icon {
    font-size: 16px;
}

.clear-filters-btn {
    background: #6c757d;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.3s ease;
    white-space: nowrap;
}

.clear-filters-btn:hover {
    background: #5a6268;
}

.filter-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 150px;
}

.filter-group label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    font-size: 14px;
}

.filter-select {
    padding: 10px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    cursor: pointer;
    transition: border-color 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #5cb85c;
}

/* Results Summary */
.results-summary {
    margin-bottom: 20px;
    padding: 15px 20px;
    background: #e8f5e8;
    border-radius: 8px;
    border-left: 4px solid #5cb85c;
}

.results-summary p {
    margin: 0;
    color: #2d5016;
    font-size: 15px;
}

.results-summary strong {
    color: #1b3a0a;
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

.btn-back {
    display: inline-block;
    padding: 10px 20px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    margin-top: 15px;
    transition: background 0.3s ease;
}

.btn-back:hover {
    background: #5a6268;
}

.no-anime-message {
    text-align: center;
    padding: 60px 20px;
    color: #777;
    font-size: 18px;
    grid-column: 1 / -1;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .filter-row {
        gap: 15px;
    }

    .filter-group {
        min-width: 130px;
    }
}

@media (max-width: 768px) {
    .search-filter-section {
        padding: 20px;
    }

    .search-row {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }

    .search-input-group {
        max-width: none;
    }

    .clear-filters-btn {
        align-self: center;
        min-width: 120px;
    }

    .filter-row {
        flex-direction: column;
        gap: 15px;
    }

    .filter-group {
        width: 100%;
        min-width: unset;
    }

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

    .anime-cards-container {
        padding: 15px;
    }

    .search-filter-section {
        padding: 15px;
    }
}
</style>

<script>
// Clear filters functionality
document.getElementById('clearFiltersBtn').addEventListener('click', function() {
    // Reset all form inputs
    document.getElementById('searchInput').value = '';
    document.getElementById('genre').value = '';
    document.getElementById('status').value = '';
    document.getElementById('type').value = '';
    document.getElementById('sort').value = 'score_desc';

    // Submit the form to reset results
    document.getElementById('searchFilterForm').submit();
});

// Auto-submit form when filters change (for better UX)
document.querySelectorAll('.filter-select').forEach(select => {
    select.addEventListener('change', function() {
        document.getElementById('searchFilterForm').submit();
    });
});

// Optional: Debounced search (submit after user stops typing)
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.getElementById('searchFilterForm').submit();
    }, 800); // Wait 800ms after user stops typing
});
</script>
