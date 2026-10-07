<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubuddy - Login</title>

    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <div class="login-page">

        <!-- Navy section -->
        <div class="brand-section">
            
            <div class = "brand-content">
                <h2>ÜBuddy</h2>
                <p class="tagline">Let Ubuddy be your buddy.</p>
                <p class="announcement">Club applications are open.<br>Sign up today!</p>
            </div>
            <svg class="wave-divider" viewBox="0 0 100 1000" preserveAspectRatio="none">
                <path d="M 50 0
                        C 10 150, 10 350, 50 500
                        C 90 650, 90 850, 50 1000
                        L 100 1000
                        L 100 0
                        Z">
                </path>
            </svg>


        </div>

        <!-- White section -->
        <div class="login-section">

            <div class="login-box">
                <h1>Login</h1>

                <form>
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        placeholder="Enter your email"
                    >

                    <label for="password">Password</label>
                    <div class="password-container">
                        <input
                        type="password"
                        id="password"
                        placeholder="Enter your password"
                    >

                     <button type="button" id="toggle-password">👁</button>

                    </div>
                    
                    <button type="submit">LOG IN</button>
                </form>
            </div>

        </div>

        

    </div>

    <script src="script.js"></script>

</body>
</html>