<?php
// Search.php - Dynamic search queries via Jikan API v4
$isLoggedIn = !empty($_SESSION["user_id"]) ? "true" : "false";
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
?>

<div class="search-page-container">
    <div class="search-header">
        <h1>🔍 Search Results</h1>
        <p>Showing relative matches for: <strong class="text-primary"><?php echo htmlspecialchars($searchQuery); ?></strong></p>
    </div>

    <!-- Results Section -->
    <div class="results-container mt-4">
        <div class="category-grid" id="search-grid">
            <div class="loading-spinner">Searching for anime...</div>
        </div>
    </div>
</div>

<style>
.search-page-container {
    padding: 30px;
    max-width: 1200px;
    margin: 0 auto;
    min-height: 100vh;
}
.search-header {
    text-align: center;
    margin-bottom: 30px;
}
.search-header h1 {
    font-size: 32px;
    color: #333;
}
.search-header p {
    font-size: 18px;
    color: #666;
}
.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 25px;
}
.cat-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    display: flex;
    flex-direction: column;
}
.cat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}
.cat-img-wrapper {
    position: relative;
    width: 100%;
    height: 310px;
}
.cat-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cat-score {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.8);
    color: #ffd700;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: bold;
}
.cat-info {
    padding: 15px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.cat-title {
    font-size: 15px;
    font-weight: bold;
    color: #222;
    margin: 0 0 8px 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.cat-action {
    margin-top: auto;
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
    text-align: center;
}
.cat-action:hover {
    background: #5568d3;
}
.cat-action.login-req {
    background: #ff9800;
}
.cat-action.login-req:hover {
    background: #f57c00;
}
.loading-spinner {
    grid-column: 1 / -1;
    text-align: center;
    padding: 50px;
    font-size: 18px;
    color: #666;
    font-style: italic;
}
.error-message {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px;
    font-size: 16px;
    color: #d9534f;
    font-weight: bold;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const isLoggedIn = <?php echo $isLoggedIn; ?>;
    const query = <?php echo json_encode($searchQuery); ?>;
    const searchGrid = document.getElementById('search-grid');

    if (!query || query.trim() === '') {
        searchGrid.innerHTML = '<div class="error-message">Please enter a valid search term.</div>';
        return;
    }

    fetch(`https://api.jikan.moe/v4/anime?q=${encodeURIComponent(query)}&sfw=true&limit=24`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            searchGrid.innerHTML = '';
            
            if (!data.data || data.data.length === 0) {
                searchGrid.innerHTML = '<div class="loading-spinner">No anime found matching your search.</div>';
                return;
            }

            data.data.forEach(anime => {
                const title = anime.title_english || anime.title;
                const image = anime.images.webp.large_image_url || anime.images.jpg.image_url;
                const score = anime.score ? anime.score.toFixed(2) : 'N/A';
                
                const actionUrl = isLoggedIn ? `?content=${anime.mal_id}&source=mal` : '?login=true';
                const btnText = isLoggedIn ? 'View Details' : 'Login to View';
                const btnClass = isLoggedIn ? 'cat-action' : 'cat-action login-req';

                const cardHTML = `
                    <div class="cat-card" onclick="window.location.href='${actionUrl}'">
                        <div class="cat-img-wrapper">
                            ${score !== 'N/A' ? `<span class="cat-score">⭐ ${score}</span>` : ''}
                            <img src="${image}" alt="${title}" class="cat-img" loading="lazy">
                        </div>
                        <div class="cat-info">
                            <h3 class="cat-title" title="${title}">${title}</h3>
                            <button class="${btnClass}">${btnText}</button>
                        </div>
                    </div>
                `;
                
                searchGrid.insertAdjacentHTML('beforeend', cardHTML);
            });
        })
        .catch(error => {
            console.error('Error fetching search results:', error);
            searchGrid.innerHTML = '<div class="error-message">Failed to fetch search results. Try again later.</div>';
        });
});
</script>
