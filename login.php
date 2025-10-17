<?php
session_start();

// If already logged in, redirect to home
if (isset($_SESSION['staff_id'])) {
    header('Location: index.php');
    exit;
}

// Optional error message passed via query string
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Blood Donation DMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #87CEEB 0%, #E0F6FF 50%, #FFFFFF 100%);
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(220, 20, 60, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(220, 20, 60, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(220, 20, 60, 0.05) 0%, transparent 50%);
            font-family: 'Arial', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            position: relative;
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }
        
        .login-wrapper { 
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(10px);
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 10;
            background-image: url('images/blooddonation.png');
            background-size: cover;
            background-position: center;
            background-blend-mode: overlay;
        }
        
        .login-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            z-index: -1;
        }
        
        .login-wrapper h2 { 
            margin-top: 0; 
            color: #E21C3D; 
            text-align: center;
            font-size: 2rem;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-group { 
            margin-bottom: 20px; 
        }
        
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600; 
            color: #333;
            font-size: 1.1rem;
        }
        
        input[type="text"], input[type="password"] { 
            width: 100%; 
            padding: 15px; 
            border: 2px solid #E0E0E0; 
            border-radius: 10px; 
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #E21C3D;
            box-shadow: 0 0 10px rgba(226, 28, 61, 0.2);
        }
        
        .btn { 
            width: 100%;
            padding: 15px; 
            background: linear-gradient(135deg, #E21C3D, #8B0000); 
            color: white; 
            border-radius: 10px; 
            text-decoration: none; 
            border: none; 
            cursor: pointer; 
            font-size: 1.1rem;
            font-weight: bold;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(226, 28, 61, 0.3);
        }
        
        .alert { 
            padding: 15px; 
            border-radius: 10px; 
            margin-bottom: 20px; 
            font-weight: 500;
        }
        
        .alert-error { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
        
        .helper { 
            font-size: 0.9em; 
            color: #666; 
            margin-top: 15px; 
            text-align: center;
        }
        
        .helper a {
            color: #E21C3D;
            text-decoration: none;
            font-weight: 600;
        }
        
        .helper a:hover {
            text-decoration: underline;
        }
        
        /* Decorative blood drop elements */
        .blood-drop {
            position: absolute;
            width: 30px;
            height: 30px;
            background: #E21C3D;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        
        .blood-drop:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .blood-drop:nth-child(2) {
            top: 20%;
            right: 15%;
            animation-delay: 2s;
        }
        
        .blood-drop:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        .blood-drop:nth-child(4) {
            bottom: 10%;
            right: 10%;
            animation-delay: 1s;
        }
        
        @keyframes float {
            0%, 100% { transform: rotate(-45deg) translateY(0px); }
            50% { transform: rotate(-45deg) translateY(-20px); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Decorative blood drops -->
        <div class="blood-drop"></div>
        <div class="blood-drop"></div>
        <div class="blood-drop"></div>
        <div class="blood-drop"></div>
        
        <div class="login-wrapper">
            <h2>Sign In</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div style="max-width:400px; margin:0 auto;">
                <h3>Staff Login</h3>
                <p class="helper">Use your Username and Password to access the administrative dashboard.</p>
                    <form action="authenticate.php" method="POST" autocomplete="off">
                        <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-actions">
                        <button type="submit" class="btn">Sign In</button>
                        </div>
                    </form>
                    <p class="helper">If staff accounts are not created yet, add a staff record via <a href="staff.php">Staff Management</a>.</p>
            </div>
        </div>
    </div>
</body>
</html>
