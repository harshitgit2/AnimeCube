<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login Form</title>
  <link rel="stylesheet" href="./public/style.css" />
</head>
<body class="SignUp-form">

  <div class="signup-wrapper">
    <div class="video-container">
      <iframe
          width="700" height="550"
          src="https://www.youtube.com/embed/BwdMT-OiARI?si=-enWlXFm43FJhL0K&start=1"
          title="YouTube video player" frameborder="0"
          allow="autoplay;"
          referrerpolicy="strict-origin-when-cross-origin" allowfullscreen>
      </iframe>
    </div>

    <div class="container">
      <h2>Login</h2>

      <?php if (isset($_GET["signup"]) && $_GET["signup"] === "success"): ?>
        <div class="alert alert-success">Account created successfully! Please log in.</div>
      <?php endif; ?>

      <?php if (isset($_GET["error"])): ?>
        <?php if ($_GET["error"] === "invalid_password"): ?>
          <div class="alert alert-error">Incorrect password. Please try again.</div>
        <?php elseif ($_GET["error"] === "user_not_found"): ?>
          <div class="alert alert-error">No account found with that username.</div>
        <?php endif; ?>
      <?php endif; ?>

      <form id="loginForm" action="server/requests.php" method="POST">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required />
        </div>

        <button type="submit" name="login">Login</button>
      </form>

      <p style="text-align:center; margin-top:15px; color:#555;">
        Don't have an account? <a href="?signup=true" style="color:#5cb85c;">Sign Up</a>
      </p>
    </div>
  </div>

</body>
</html>
