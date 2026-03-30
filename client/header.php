<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container-fluid px-4">

    <!-- Logo -->
    <a class="navbar-brand" href="./">
      <img src="./public/logo.jpg" alt="Logo" width="125px" height="80px">
    </a>

    <!-- Mobile Toggle Button -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-center" id="navbarNavDropdown">
      <!-- Nav Links -->
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link active text-white" href="./">Home</a>
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
      </ul>

      <!-- Search Bar -->
      <form class="d-flex mx-1" role="search">
        <input class="form-control me-2" type="search" placeholder="Search anime..." aria-label="Search">
        <button class="btn btn-outline-light mx-1" type="submit">
          <i class="fas fa-search">
              <img src="./public/search.svg" alt="Search" width="24" height="24">
          </i>
        </button>
      </form>

      <!-- Right Side Icons -->
      <ul class="navbar-nav ms-auto align-items-center gap-2">

        <!-- Dark/Light Mode Toggle -->
        <li class="nav-item">
          <button class="btn btn-outline-light btn-sm" id="themeToggle" title="Toggle theme">
            <i class="fas fa-moon" id="themeIcon">
                <img src="./public/moon.svg" alt="Moon" width="24" height="24">
            </i>
          </button>
        </li>

        <!-- Notifications -->
        <!--<li class="nav-item">
          <button class="btn btn-outline-light btn-sm position-relative" title="Notifications">
            <i class="fas fa-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              3
            </span>
          </button>
        </li>-->

        <!-- User Profile Dropdown -->
        <?php if (!empty($_SESSION["user_id"])) { ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
              <img src="./public/default-avatar.png" alt="Avatar" width="32" height="32" class="rounded-circle border border-light">
              <span><?php echo htmlspecialchars(
                  $_SESSION["username"] ?? "User",
              ); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
              <li><a class="dropdown-item" href="?profile=true"><i class="fas fa-user me-2"></i>Profile</a></li>
              <li><a class="dropdown-item" href="?settings=true"><i class="fas fa-cog me-2"></i>Settings</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="?logout=true"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
          </li>
        <?php } ?>

        <!-- Login / Signup  -->
        <?php if (empty($_SESSION["user_id"])) { ?>
          <li class="nav-item">
            <a class="btn btn-outline-light btn-sm" href="?login=true">Login</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-light text-dark btn-sm" href="?signup=true">Sign Up</a>
          </li>
        <?php } ?>

      </ul>
    </div>
  </div>
</nav>


<!-- Dark/Light Mode Script -->
<script>
  const themeToggle = document.getElementById('themeToggle');
  const themeIcon = document.getElementById('themeIcon');

  // Load saved theme
  if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    themeIcon.classList.replace('fa-moon', 'fa-sun');
  }

  themeToggle.addEventListener('click', () => {
    document.body.classList.toggle('light-mode');
    const isLight = document.body.classList.contains('light-mode');
    themeIcon.classList.toggle('fa-moon', !isLight);
    themeIcon.classList.toggle('fa-sun', isLight);
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
  });
</script>

<!-- Light Mode CSS (add in <head> or a CSS file) -->
<style>
  body.light-mode {
    background-color: hsl(210, 17%, 98%) !important;
    color: hsl(0, 0%, 0%) !important;
  }
  body.light-mode .navbar {
    background-color: hsl(0, 0%, 100%) !important;
  }
  body.light-mode .nav-link,
  body.light-mode .navbar-brand {
    color: hsl(0, 0%, 0%) !important;
  }


  /* Hover effect on navbar links */
  .navbar a:hover {
    background-color: grey;
    box-shadow: 2px 2px 5px black;
    border-radius: 5px;
  }
</style>

</style>
