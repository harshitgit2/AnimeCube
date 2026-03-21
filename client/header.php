<nav class="navbar navbar-expand-lg navbar-light  bg-dark ">

  <div class="container-fluid">

    <a class="navbar-brand" href="./">
        <img src="./public/logo.jpg" alt="Logo" width="125px" height="80px">
    </a>

    <div class="collapse navbar-collapse justify-content-center " id="navbarNavDropdown">
      <ul class="navbar-nav ">
        <li class="nav-item">
          <a class="nav-link active text-white" aria-current="page" href="./">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#">Features</a>
        </li>

        <li class="nav-item">
          <a class="nav-link text-white" href="#">Category</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#">Latest Q&A</a>
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
    </div>
  </div>
</nav>
