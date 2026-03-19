<?php
// Category.php - Browse anime by genres
$isLoggedIn = !empty($_SESSION["user_id"]) ? "true" : "false";
?>

<div class="category-page-container">
    <div class="category-header">
        <h1>📚 Anime Categories</h1>
        <p>Explore anime by your favorite genres and themes!</p>
    </div>

    <!-- Genre Tags Section -->
    <div class="genre-container">
        <div class="genre-list" id="genre-list">
            <div class="loading-text">Loading genres...</div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="results-container">
        <h2 class="results-title" id="results-title">Popular Action Anime</h2>
        <div class="category-grid" id="category-grid">
            <div class="loading-spinner">Select a category to view anime.</div>
        </div>
    </div>
</div>

<style>
.category-page-container {
    padding: 30px;
    max-width: 1200px;
    margin: 0 auto;
    min-height: 100vh;
}

.category-header {
    text-align: center;
    margin-bottom: 30px;
}

.category-header h1 {
    font-size: 32px;
    color: #333;
    margin-bottom: 10px;
}

.category-header p {
    font-size: 16px;
    color: #666;
}

.genre-container {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    margin-bottom: 40px;
}

.genre-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    max-height: 200px;
    overflow-y: auto;
    padding: 10px;
    /* Custom scrollbar for genre list */
    scrollbar-width: thin;
    scrollbar-color: #667eea #f0f0f0;
}

.genre-list::-webkit-scrollbar {
    width: 8px;
}
.genre-list::-webkit-scrollbar-track {
    background: #f0f0f0;
    border-radius: 10px;
}
.genre-list::-webkit-scrollbar-thumb {
    background-color: #667eea;
    border-radius: 10px;
}

.genre-btn {
    padding: 8px 18px;
    background: #f0f2f5;
    color: #444;
    border: 1px solid #e1e4e8;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.genre-btn:hover {
    background: #e2e6ea;
    transform: translateY(-2px);
}

.genre-btn.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
}

.results-title {
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #667eea;
    display: inline-block;
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

.loading-text {
    color: #666;
    font-style: italic;
    padding: 20px;
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
    const genreList = document.getElementById('genre-list');
    const categoryGrid = document.getElementById('category-grid');
    const resultsTitle = document.getElementById('results-title');

    // Fetch all anime genres
    fetch('https://api.jikan.moe/v4/genres/anime')
        .then(response => response.json())
        .then(data => {
            genreList.innerHTML = '';

            if (data.data) {
                // Sort genres alphabetically
                const genres = data.data.sort((a, b) => a.name.localeCompare(b.name));

                genres.forEach(genre => {
                    // Filter out explicit/adult genres just in case
                    if(genre.name === 'Hentai' || genre.name === 'Erotica') return;

                    const btn = document.createElement('button');
                    btn.className = 'genre-btn';
                    btn.textContent = genre.name;
                    btn.dataset.id = genre.mal_id;

                    btn.addEventListener('click', () => {
                        // Remove active class from all
                        document.querySelectorAll('.genre-btn').forEach(b => b.classList.remove('active'));
                        // Add active to clicked
                        btn.classList.add('active');
                        // Fetch anime for this genre
                        fetchAnimeByGenre(genre.mal_id, genre.name);
                    });

                    genreList.appendChild(btn);
                });

                // Trigger click on first genre (usually Action: id 1) to load initial data
                const defaultGenreBtn = document.querySelector('.genre-btn[data-id="1"]') || document.querySelector('.genre-btn');
                if (defaultGenreBtn) {
                    defaultGenreBtn.click();
                }
            }
        })
        .catch(error => {
            console.error('Error fetching genres:', error);
            genreList.innerHTML = '<div class="error-message">Failed to load genres.</div>';
        });

    // Fetch anime by genre ID
    function fetchAnimeByGenre(genreId, genreName) {
        resultsTitle.textContent = `Popular ${genreName} Anime`;
        categoryGrid.innerHTML = '<div class="loading-spinner">Fetching anime... Please wait.</div>';

        // Fetch top anime for the specific genre
        fetch(`https://api.jikan.moe/v4/anime?genres=${genreId}&order_by=popularity&sort=asc&limit=24`)
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                categoryGrid.innerHTML = '';

                if (!data.data || data.data.length === 0) {
                    categoryGrid.innerHTML = '<div class="error-message">No anime found for this category.</div>';
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

                    categoryGrid.insertAdjacentHTML('beforeend', cardHTML);
                });
            })
            .catch(error => {
                console.error('Error fetching category anime:', error);
                categoryGrid.innerHTML = '<div class="error-message">Failed to load anime for this category. Try again.</div>';
            });
    }
});
</script>
