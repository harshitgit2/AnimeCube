
<?php // Card.php - MyAnimeList API dynamic categories component
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

@media (max-width: 768px) {
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
}
</style>
