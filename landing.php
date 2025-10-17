<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation Database Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Landing Page Specific Styles */
        .landing-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #E21C3D 0%, #8B0000 100%);
            color: white;
            font-family: 'Arial', sans-serif;
        }

        .header {
            background: rgba(0, 0, 0, 0.8);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #E21C3D;
            font-size: 1.2rem;
        }

        .system-title {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .nav-btn {
            background: #E21C3D;
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-btn:hover {
            background: white;
            color: #E21C3D;
            transform: translateY(-2px);
        }

        .welcome-banner {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            margin-top: 80px;
            overflow: hidden;
            white-space: nowrap;
        }

        .scrolling-text {
            display: inline-block;
            animation: scroll 20s linear infinite;
            font-size: 1.2rem;
            font-weight: bold;
        }

        @keyframes scroll {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        .hero-section {
            padding: 4rem 2rem;
            text-align: center;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .image-carousel {
            position: relative;
            max-width: 800px;
            margin: 2rem auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .carousel-container {
            position: relative;
            width: 100%;
            height: 400px;
        }

        .carousel-slide {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            background-size: cover;
            background-position: center;
        }

        .carousel-slide.active {
            opacity: 1;
        }

        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 1rem;
            cursor: pointer;
            font-size: 1.5rem;
            transition: background 0.3s ease;
        }

        .carousel-nav:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .carousel-prev {
            left: 0;
            border-radius: 0 10px 10px 0;
        }

        .carousel-next {
            right: 0;
            border-radius: 10px 0 0 10px;
        }

        .content-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            max-width: 1200px;
            margin: 4rem auto;
            padding: 0 2rem;
        }

        .mission-section {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .mission-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #FFD700;
        }

        .mission-text {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .impact-text {
            font-size: 1.2rem;
            font-weight: bold;
            color: #FFD700;
            text-align: center;
            padding: 1rem;
            background: rgba(255, 215, 0, 0.1);
            border-radius: 10px;
            border-left: 4px solid #FFD700;
        }

        .promo-section {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .blood-drop-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, #E21C3D, #8B0000);
            border-radius: 50% 50% 50% 0;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .promo-text {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: #E21C3D;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .social-link:hover {
            transform: scale(1.2);
        }

        .footer {
            background: rgba(0, 0, 0, 0.8);
            padding: 2rem 0;
            text-align: center;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .content-section {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        /* About Us Section */
        .about-section {
            background: white;
            padding: 60px 0;
            color: #333;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: #E21C3D;
            margin-bottom: 40px;
            font-weight: bold;
        }

        .about-content {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .about-text p {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 20px;
            text-align: justify;
        }

        .mission-vision {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 40px;
        }

        .mission, .vision {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            border-left: 4px solid #E21C3D;
        }

        .mission h3, .vision h3 {
            color: #E21C3D;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .mission p, .vision p {
            font-size: 1rem;
            line-height: 1.6;
            margin: 0;
        }

        /* Contact Section */
        .contact-section {
            background: linear-gradient(135deg, #E21C3D, #8B0000);
            color: white;
            padding: 60px 0;
        }

        .contact-section .section-title {
            color: white;
        }

        .contact-content {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .contact-item:hover {
            transform: translateY(-5px);
        }

        .contact-details h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: white;
        }

        .contact-details p {
            margin: 5px 0;
            font-size: 1rem;
        }

        .contact-details a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .contact-details a:hover {
            color: #ffeb3b;
        }

        /* Additional Responsive Design */
        @media (max-width: 768px) {
            .mission-vision {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .contact-info {
                grid-template-columns: 1fr;
            }
            
            .contact-item {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="logo-section">
                    <div class="logo">🩸</div>
                    <div class="system-title">BLOOD DONATION DATABASE MANAGEMENT SYSTEM</div>
                </div>
                <div class="nav-buttons">
                    <a href="login.php" class="nav-btn">
                        Login
                    </a>
                </div>
            </div>
        </header>

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="scrolling-text">
                Hi, Welcome to our Blood Donation Database Management System - Save Lives, Donate Blood! 🩸
            </div>
        </div>

        <!-- Hero Section -->
        <section class="hero-section">
            <h1 class="hero-title">Blood Donation Management System</h1>
            
            <!-- Image Carousel -->
            <div class="image-carousel">
                <div class="carousel-container">
                    <div class="carousel-slide active" style="background-image: url('images/ABbload.png');"></div>
                    <div class="carousel-slide" style="background-image: url('images/blood.png');"></div>
                    <div class="carousel-slide" style="background-image: url('images/savelife.png');"></div>
                    <div class="carousel-slide" style="background-image: url('images/letsdonate.png');"></div>
                    <div class="carousel-slide" style="background-image: url('images/donation.png');"></div>
                </div>
                <button class="carousel-nav carousel-prev" onclick="changeSlide(-1)">❮</button>
                <button class="carousel-nav carousel-next" onclick="changeSlide(1)">❯</button>
            </div>
        </section>

        <!-- Content Section -->
        <section class="content-section">
            <!-- Mission Section -->
            <div class="mission-section">
                <h2 class="mission-title">Our Mission</h2>
                <p class="mission-text">
                    To provide a comprehensive and efficient blood donation management system that ensures the safe collection, testing, storage, and distribution of blood products. We are committed to building resilient communities through well-trained and dedicated staff, equipped with the necessary technology and logistics to save lives.
                </p>
                <div class="impact-text">
                    "The blood you donate gives someone another chance of life. One day that someone may be a close relative, a friend, a loved one—or even you."
                </div>
            </div>

            <!-- Promo Section -->
            <div class="promo-section">
                <div class="blood-drop-icon">🩸</div>
                <h3 class="promo-text">Give Blood... Save Lives...</h3>
                <p>Every donation counts. Every life matters.</p>
                <div class="social-links">
                    <a href="#" class="social-link">Facebook</a>
                    <a href="#" class="social-link">Instagram</a>
                    <a href="#" class="social-link">Website</a>
                </div>
            </div>
        </section>

        <!-- About Us Section -->
        <section class="about-section">
            <div class="container">
                <h2 class="section-title">About Us</h2>
                <div class="about-content">
                    <div class="about-text">
                        <p>We are Group G from MAKERERE UNIVERSITY, dedicated to developing innovative solutions for healthcare management. Our Blood Donation Database Management System represents our commitment to leveraging technology to save lives and improve healthcare efficiency.</p>
                        
                        <p>This comprehensive system streamlines the entire blood donation process, from donor registration to blood inventory management, ensuring that every drop of blood reaches those who need it most. Our mission is to bridge the gap between blood donors and recipients through technology.</p>
                        
                        <div class="mission-vision">
                            <div class="mission">
                                <h3>Our Mission</h3>
                                <p>To create a seamless, efficient, and user-friendly platform that connects blood donors with recipients, ensuring timely access to life-saving blood supplies.</p>
                            </div>
                            <div class="vision">
                                <h3>Our Vision</h3>
                                <p>To become the leading blood donation management platform that revolutionizes healthcare through innovative technology and compassionate service.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="contact-section">
            <div class="container">
                <h2 class="section-title">Contact Us</h2>
                <div class="contact-content">
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-details">
                                <h3>Email</h3>
                                <p><a href="mailto:info.groupg.aits@gmail.com">info.groupg.aits@gmail.com</a></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-details">
                                <h3>Phone</h3>
                                <p><a href="tel:+256200905814">+256 20 090 5814</a></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-details">
                                <h3>Institution</h3>
                                <p>MAKERERE UNIVERSITY</p>
                                <p>Group G - Software Development Team</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <p>&copy; 2025 Blood Donation Database Management System. All rights reserved.</p>
                <p>Powered by Group G</p>
            </div>
        </footer>
    </div>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const totalSlides = slides.length;

        function showSlide(n) {
            slides.forEach(slide => slide.classList.remove('active'));
            currentSlide = (n + totalSlides) % totalSlides;
            slides[currentSlide].classList.add('active');
        }

        function changeSlide(direction) {
            showSlide(currentSlide + direction);
        }

        // Auto-advance slides every 5 seconds
        setInterval(() => {
            changeSlide(1);
        }, 5000);

        // Pause auto-advance on hover
        const carousel = document.querySelector('.image-carousel');
        carousel.addEventListener('mouseenter', () => {
            clearInterval(autoAdvance);
        });
    </script>
</body>
</html>
