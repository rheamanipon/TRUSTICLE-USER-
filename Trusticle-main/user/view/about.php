<?php
// Include the header file
include '../includes/header.php';
?>

<!-- Main Content -->
<div class="main-content">
    <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">About Us</h1>
        </div>

    <div class="content-container">
        <div class="about-section-main">
            <h2 class="about-main-title">WHO WE ARE</h2>
            
            <div class="about-main-description">
                <p>
                    Trusticle is a platform developed to help users and administrators identify and manage fake news articles.
                    Our system combines user submissions, admin moderation, and keyword-based detection to promote
                    accurate and trustworthy information online.
                </p>
            </div>
        </div>

        <div class="about-boxes-container">
            <div class="about-box">
                <h3>Our Mission</h3>
                <p>
                    To combat the spread of misinformation by providing an accessible, secure, and efficient tool
                    for detecting fake news articles.
                </p>
            </div>
            
            <div class="about-box">
                <h3>What We Do</h3>
                <p>
                    With Trusticle, users can easily submit and manage their articles, while admins handle reviews and approvals.
                    The system auto-flags fake news using keyword checks and shows simple trends and reports to keep
                    everything transparent and organized.
                </p>
            </div>
            
            <div class="about-box">
                <h3>Why Trusticle?</h3>
                <p>
                    We believe in the importance of credible information. In the age of digital media, it's vital to have systems
                    that filter out fake content while allowing users and admins to work together toward truth and clarity.
                </p>
            </div>
        </div>

        <div class="about-footer-container">
            <div class="about-logo">
                <div class="logo">
                <img src="../assets/images/logo2.png" alt="Trusticle Logo" class="sidebar-logo">
                </div>
            </div>
            
            <div class="about-tagline">
                <h3>Empowering Truth in News</h3>
                <p>Helping users and admins detect fake news through smart analysis, clear reporting, and responsible content management.</p>
            </div>
            
            <div class="about-social">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
            </div>
        </div>
        
        <div class="about-copyright">
            <p>&copy; <?php echo date('Y'); ?> Trusticle. All rights reserved.</p>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../../assets/css/about.css">

<?php
// Include the footer file
include '../includes/footer.php';
?> 