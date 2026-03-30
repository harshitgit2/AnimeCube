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
// Card.php - MyAnimeList API dynamic categories component
// Checks if the user is logged in for routing

$isLoggedIn = !empty($_SESSION["user_id"]) ? "true" : "false"; ?>


<div class="mal-categories-container">

    <!-- Top Anime Series -->
    <div class="category-section">
        <h2 class="category-title">🏆 Top Anime Series</h2>
        <div class="cards-carousel-wrapper">
            <button class="scroll-btn left">‹</button>
            <div class="cards-carousel" id="cat-top-tv">
                <div class="loading-spinner">Loading top anime...</div>
            </div>
            <button class="scroll-btn right">›</button>
        </div>
    </div>

    <!-- Top Airing Anime -->
    <div class="category-section">
        <h2 class="category-title">📺 Top Airing Anime</h2>
        <div class="cards-carousel-wrapper">
            <button class="scroll-btn left">‹</button>
            <div class="cards-carousel" id="cat-top-airing">
                <div class="loading-spinner">Loading airing anime...</div>
            </div>
            <button class="scroll-btn right">›</button>
        </div>
    </div>

    <!-- Top Upcoming Anime -->
    <div class="category-section">
        <h2 class="category-title">⏳ Top Upcoming Anime</h2>
        <div class="cards-carousel-wrapper">
            <button class="scroll-btn left">‹</button>
            <div class="cards-carousel" id="cat-top-upcoming">
                <div class="loading-spinner">Loading upcoming anime...</div>
            </div>
            <button class="scroll-btn right">›</button>
        </div>
    </div>

    <!-- Top Anime OVA Series -->
    <div class="category-section">
        <h2 class="category-title">💿 Top Anime OVA Series</h2>
        <div class="cards-carousel-wrapper">
            <button class="scroll-btn left">‹</button>
            <div class="cards-carousel" id="cat-top-ova">
                <div class="loading-spinner">Loading OVA series...</div>
            </div>
            <button class="scroll-btn right">›</button>
        </div>
    </div>

    <!-- Top Favorited Anime -->
    <div class="category-section">
        <h2 class="category-title">❤️ Top Favorited Anime</h2>
        <div class="cards-carousel-wrapper">
            <button class="scroll-btn left">‹</button>
            <div class="cards-carousel" id="cat-top-favorite">
                <div class="loading-spinner">Loading favorites...</div>
            </div>
            <button class="scroll-btn right">›</button>
        </div>
    </div>

    <!-- Top Anime by Popularity -->
    <div class="category-section">
        <h2 class="category-title">🔥 Top Anime by Popularity</h2>
        <div class="cards-carousel-wrapper">
            <button class="scroll-btn left">‹</button>
            <div class="cards-carousel" id="cat-top-popularity">
                <div class="loading-spinner">Loading popular anime...</div>
            </div>
            <button class="scroll-btn right">›</button>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const isLoggedIn = <?php echo $isLoggedIn; ?>;

    // Categories and their Jikan API v4 endpoints
    const categories = [
        { id: 'cat-top-tv', url: 'https://api.jikan.moe/v4/top/anime?type=tv&limit=15' },
        { id: 'cat-top-airing', url: 'https://api.jikan.moe/v4/top/anime?filter=airing&limit=15' },
        { id: 'cat-top-upcoming', url: 'https://api.jikan.moe/v4/top/anime?filter=upcoming&limit=15' },
        { id: 'cat-top-ova', url: 'https://api.jikan.moe/v4/top/anime?type=ova&limit=15' },
        { id: 'cat-top-favorite', url: 'https://api.jikan.moe/v4/top/anime?filter=favorite&limit=15' },
        { id: 'cat-top-popularity', url: 'https://api.jikan.moe/v4/top/anime?filter=bypopularity&limit=15' }
    ];

    // Helper to delay execution (prevents Jikan API rate limiting: 3 req/sec max)
    const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

    const renderCards = (containerId, animeList) => {
        const container = document.getElementById(containerId);
        container.innerHTML = ''; // Clear loading

        if (!animeList || animeList.length === 0) {
            container.innerHTML = '<div class="no-anime-message">No anime found for this category.</div>';
            return;
        }

        animeList.forEach(anime => {
            const title = anime.title_english || anime.title;
            const image = anime.images.webp.image_url;
            const score = anime.score ? anime.score.toFixed(2) : 'N/A';
            const status = anime.status;

            // Truncate synopsis
            let synopsis = anime.synopsis ? anime.synopsis : 'No description available.';
            if (synopsis.length > 100) synopsis = synopsis.substring(0, 100) + '...';

            const genres = anime.genres.slice(0, 2).map(g => g.name).join(', ');

            // Determine click action based on login status
            const actionUrl = isLoggedIn ? `?content=${anime.mal_id}&source=mal` : '?login=true';
            const btnText = isLoggedIn ? 'View Details' : 'Login to View';
            const btnClass = isLoggedIn ? 'btn-view-details' : 'btn-view-details btn-login-required';

            const cardHtml = `
                <div class="anime-card-mini" onclick="window.location.href='${actionUrl}'">
                    <div class="card-mini-img-wrapper">
                        <img src="${image}" alt="${title}" class="card-mini-img" loading="lazy">
                        ${score !== 'N/A' ? `<div class="card-mini-score">⭐ ${score}</div>` : ''}
                    </div>
                    <div class="card-mini-info">
                        <h3 class="card-mini-title">${title}</h3>
                        <p class="card-mini-desc">${synopsis}</p>
                        <div class="card-mini-footer">
                            <span class="card-mini-genres">${genres}</span>
                            <button class="${btnClass}">${btnText}</button>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += cardHtml;
        });
    };

    const fetchCategoriesSequentially = async () => {
        for (const cat of categories) {
            try {
                const response = await fetch(cat.url);
                if (response.ok) {
                    const data = await response.json();
                    renderCards(cat.id, data.data);
                } else {
                    document.getElementById(cat.id).innerHTML = '<div class="error-message">Failed to load API data.</div>';
                }
            } catch (error) {
                console.error('Error fetching data:', error);
                document.getElementById(cat.id).innerHTML = '<div class="error-message">Network error occurred.</div>';
            }
            // Delay to respect API rate limits (approx 2 requests per second)
            await delay(600);
        }
    };

    // Initialize fetching
    fetchCategoriesSequentially();

    // Scroll buttons logic
    document.querySelectorAll('.cards-carousel-wrapper').forEach(wrapper => {
        const carousel = wrapper.querySelector('.cards-carousel');
        const leftBtn = wrapper.querySelector('.scroll-btn.left');
        const rightBtn = wrapper.querySelector('.scroll-btn.right');

        leftBtn.addEventListener('click', () => {
            carousel.scrollBy({ left: -600, behavior: 'smooth' });
        });

        rightBtn.addEventListener('click', () => {
            carousel.scrollBy({ left: 600, behavior: 'smooth' });
        });
    });
});
</script>

<style>
.mal-categories-container {
    width: 100%;
    padding: 10px 20px;
}

.category-section {
    margin-bottom: 40px;
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
.category-title {
    font-size: 22px;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #667eea;
    display: inline-block;
}

.cards-carousel-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.cards-carousel {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    padding: 15px 5px;
    scroll-behavior: smooth;
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}

.cards-carousel::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}

.scroll-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    border: none;
    font-size: 24px;
    cursor: pointer;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s;
}

.scroll-btn:hover {
    background: rgba(0, 0, 0, 0.9);
}

.scroll-btn.left {
    left: -15px;
}

.scroll-btn.right {
    right: -15px;
}

.anime-card-mini {
    min-width: 240px;
    max-width: 240px;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
}

.anime-card-mini:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.card-mini-img-wrapper {
    position: relative;
    width: 100%;
    height: 320px;
}

.card-mini-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.card-mini-score {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.8);
    color: #ffd700;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: bold;
}

.card-mini-info {
    padding: 15px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.card-mini-title {
    font-size: 16px;
    font-weight: bold;
    color: #222;
    margin: 0 0 8px 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-mini-desc {
    font-size: 13px;
    color: #666;
    margin: 0 0 15px 0;
    line-height: 1.4;
    flex-grow: 1;
}

.card-mini-footer {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: auto;
}

.card-mini-genres {
    font-size: 12px;
    color: #888;
    font-style: italic;
}

.btn-view-details {
    width: 100%;
    padding: 8px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 13px;
    font-weight: bold;
    cursor: pointer;
    text-transform: uppercase;
    transition: background 0.3s;
}

.btn-view-details:hover {
    background: #5568d3;
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
.loading-spinner {
    padding: 40px;
    color: #666;
    font-style: italic;
    width: 100%;
    text-align: center;
}

.error-message {
    padding: 40px;
    color: #dc3545;
    font-weight: bold;
    width: 100%;
    text-align: center;
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
    .anime-card-mini {
        min-width: 200px;
        max-width: 200px;
    }

    .card-mini-img-wrapper {
        height: 280px;
    }

    .scroll-btn {
        display: none; /* Hide scroll buttons on mobile, allow natural swipe */
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
