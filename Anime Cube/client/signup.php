<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Sign Up Form</title>
  <link rel="stylesheet" href="../public/style.css" />
</head>
<body class="SignUp-form">


  <div class="signup-wrapper">
    <div class="video-container">
      <iframe
          width="700" height="550"
          src="https://www.youtube.com/embed/BwdMT-OiARI?si=-enWlXFm43FJhL0K&amp;start=1"
          title="YouTube video player" frameborder="0"
          allow="autoplay;"
          referrerpolicy="strict-origin-when-cross-origin" allowfullscreen>

      </iframe>
    </div>

    <div class="container">
      <h2>Sign Up</h2>
      <form id="signupForm" action="server/requests.php" method="POST">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required />
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required />
        </div>
        <div class="form-group">
          <label for="confirmPassword">Confirm Password</label>
          <input type="password" id="confirmPassword" name="confirmPassword" required />
        </div>
        <button type="submit">Sign Up</button>
      </form>
    </div>
  </div>


</body>
</html>
