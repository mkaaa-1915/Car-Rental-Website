<div class="modal-container" id="auth-modal">
    <div class="modal">
        <span class="close-btn" id="close-modal">&times;</span>

        <div class="auth-form" id="login-form">
            <div class="form-header">
                <h2>Welcome Back 👋</h2>
                <p>Log in to your account.</p>
            </div>
            <form action="process_login.php" method="POST">
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="auth-btn">Log In</button>
            </form>

            <div class="separator">OR</div>

            <div class="social-login">
                <button class="social-btn gmail"><i class='bx bxl-google'></i> Continue with Google</button>
                <button class="social-btn facebook"><i class='bx bxl-facebook'></i> Continue with Facebook</button>
                <button class="social-btn apple"><i class='bx bxl-apple'></i> Continue with Apple</button>
            </div>

            <p class="switch-auth">Don't have an account? <a href="#" id="show-signup">Sign Up</a></p>
        </div>

        <div class="auth-form hidden" id="signup-form">
            <div class="form-header">
                <h2>Create Account 🚀</h2>
                <p>Join RentGo for exclusive rentals.</p>
            </div>
            <form action="process_signup.php" method="POST">
                <div class="input-group">
                    <input type="text" name="fullname" placeholder="Full Name" required>
                </div>
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password (min 6 characters)" required minlength="6">
                </div>
                <div class="input-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <button type="submit" class="auth-btn">Sign Up</button>
            </form>
            <p class="switch-auth">Already have an account? <a href="#" id="show-login">Log In</a></p>
        </div>
    </div>
</div>

