<?php
// Features.php - Displays the latest/currently airing anime
$isLoggedIn = !empty($_SESSION["user_id"]) ? "true" : "false";
?>

<div class="features-page-container">
    <div class="features-header">
        <h1>✨ Latest Featured Anime</h1>
        <p>Discover the hottest anime airing right now this season!</p>
    </div>

    <div class="features-grid" id="features-grid">
        <div class="loading-spinner">Fetching the latest anime...</div>
    </div>
</div>

<style>
.features-page-container {
    padding: 30px;
    max-width: 1200px;
    margin: 0 auto;
    min-height: 100vh;
}

.features-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #ddd;
}

.features-header h1 {
    font-size: 32px;
    color: #333;
    margin-bottom: 10px;
}

.features-header p {
    font-size: 16px;
    color: #666;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 25px;
}

.feature-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    display: flex;
    flex-direction: column;
}

.feature-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.feature-img-wrapper {
    position: relative;
    width: 100%;
    height: 350px;
}

.feature-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.feature-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #e53e3e;
    color: white;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.feature-score {
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

.feature-info {
    padding: 15px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.feature-title {
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

.feature-desc {
    font-size: 13px;
    color: #666;
    margin: 0 0 15px 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex-grow: 1;
}

.feature-action {
    width: 100%;
    padding: 10px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    text-transform: uppercase;
    transition: background 0.3s;
    text-align: center;
}

.feature-action:hover {
    background: #5568d3;
}

.feature-action.login-req {
    background: #ff9800;
}

.feature-action.login-req:hover {
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
    padding: 50px;
    font-size: 18px;
    color: #d9534f;
    font-weight: bold;
}

@media (max-width: 768px) {
    .features-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
    .feature-img-wrapper {
        height: 280px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const isLoggedIn = <?php echo $isLoggedIn; ?>;
    const grid = document.getElementById('features-grid');

    // Fetch the latest currently airing anime (Current Season)
    fetch('https://api.jikan.moe/v4/seasons/now?limit=24')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            grid.innerHTML = ''; // Clear loading spinner

            if (!data.data || data.data.length === 0) {
                grid.innerHTML = '<div class="error-message">No featured anime found at the moment.</div>';
                return;
            }

            data.data.forEach(anime => {
                const title = anime.title_english || anime.title;
                const image = anime.images.webp.large_image_url || anime.images.jpg.large_image_url;
                const score = anime.score ? anime.score.toFixed(2) : 'N/A';
                const synopsis = anime.synopsis ? anime.synopsis : 'No description available for this anime.';

                const actionUrl = isLoggedIn ? `?content=${anime.mal_id}&source=mal` : '?login=true';
                const btnText = isLoggedIn ? 'View Details' : 'Login to View';
                const btnClass = isLoggedIn ? 'feature-action' : 'feature-action login-req';

                const cardHTML = `
                    <div class="feature-card" onclick="window.location.href='${actionUrl}'">
                        <div class="feature-img-wrapper">
                            <span class="feature-badge">Latest</span>
                            ${score !== 'N/A' ? `<span class="feature-score">⭐ ${score}</span>` : ''}
                            <img src="${image}" alt="${title}" class="feature-img" loading="lazy">
                        </div>
                        <div class="feature-info">
                            <h3 class="feature-title">${title}</h3>
                            <p class="feature-desc">${synopsis}</p>
                            <button class="${btnClass}">${btnText}</button>
                        </div>
                    </div>
                `;

                grid.insertAdjacentHTML('beforeend', cardHTML);
            });
        })
        .catch(error => {
            console.error('Error fetching features:', error);
            grid.innerHTML = '<div class="error-message">Failed to load the latest anime. Please try again later.</div>';
        });
});
</script>
