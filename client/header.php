<nav class="navbar navbar-expand-lg navbar-dark bg-dark">

  <div class="container-fluid">

    <a class="navbar-brand" href="./">
        <img src="./public/logo.jpg" alt="Logo" width="125px" height="80px">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-between" id="navbarNavDropdown">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active text-white" aria-current="page" href="./">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="?features=true">Features</a>
        </li>

        <li class="nav-item">
          <a class="nav-link text-white" href="?category=true">Category</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="?qa=true">Latest Q&A</a>
        </li>

        <?php if (!empty($_SESSION["user_id"])) { ?>
            <li class="nav-item">
              <a class="nav-link text-white" href="?logout=true">Logout</a>
            </li>
        <?php } ?>

        <?php if (empty($_SESSION["user_id"])) { ?>
            <li class="nav-item">
              <a class="nav-link text-white" href="?login=true">Login</a>
            </li>
            <li class="nav-item text-white">
              <a class="nav-link text-white" href="?signup=true">SignUp</a>
            </li>
        <?php } ?>

      </ul>

      <div class="d-flex align-items-center">
        <!-- Search Form -->
        <form class="d-flex me-3" action="index.php" method="GET">
            <input class="form-control me-2" type="search" placeholder="Search Anime" aria-label="Search" name="search" required>
            <button class="btn btn-outline-light" type="submit">Search</button>
        </form>

        <!-- Theme Toggle -->
        <button id="themeToggleBtn" class="btn btn-outline-light" title="Toggle Theme">
          🌙
        </button>
      </div>

    </div>
  </div>
</nav>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.getElementById("themeToggleBtn");
    const currentTheme = localStorage.getItem("theme") || "light";

    // Apply exact initial theme
    if (currentTheme === "dark") {
        document.body.classList.add("dark-mode");
        toggleBtn.textContent = "☀️";
    }

    toggleBtn.addEventListener("click", () => {
        document.body.classList.toggle("dark-mode");
        let theme = "light";
        if (document.body.classList.contains("dark-mode")) {
            theme = "dark";
            toggleBtn.textContent = "☀️";
        } else {
            toggleBtn.textContent = "🌙";
        }
        // Save user preference
        localStorage.setItem("theme", theme);
    });
});
</script>
