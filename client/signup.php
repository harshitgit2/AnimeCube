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
      <h2>Sign Up</h2>

      <?php if (isset($_GET["error"]) && $_GET["error"] === "signup_failed"): ?>
        <div class="alert alert-error">Sign up failed. Please try again.</div>
      <?php endif; ?>

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
        <div class="form-group">
          <label for="address">Address</label>
          <input type="text" id="address" name="address" required />
        </div>
        <button type="submit" name="signup">Sign Up</button>
      </form>

      <p style="text-align:center; margin-top:15px; color:#555;">
        Already have an account? <a href="?login=true" style="color:#5cb85c;">Login</a>
      </p>
    </div>
</div>
