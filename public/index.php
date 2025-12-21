<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HireGenius - Smart Video Interview Platform</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="landing-body">
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <!-- Navigation -->
    <nav class="landing-nav">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">
                <i class="fas fa-bolt"></i>
                <span>Hire<span class="accent">Genius</span></span>
            </a>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="../recruiter/login.php" class="btn btn-outline btn-sm">Login</a>
                <a href="../recruiter/register.php" class="btn btn-primary btn-sm">Get Started</a>
            </div>
            <button class="mobile-menu-btn" id="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-star"></i>
                <span>Trusted by 500+ Companies</span>
            </div>
            <h1 class="hero-title">
                Hire Smarter with
                <span class="gradient-text">AI-Powered</span>
                Video Interviews
            </h1>
            <p class="hero-subtitle">
                Streamline your recruitment process with asynchronous video interviews. 
                Save time, reduce bias, and find the perfect candidates faster.
            </p>
            <div class="hero-actions">
                <a href="../recruiter/register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket"></i>
                    Start Free Trial
                </a>
                <a href="#how-it-works" class="btn btn-glass btn-lg">
                    <i class="fas fa-play-circle"></i>
                    Watch Demo
                </a>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <span class="stat-number">10K+</span>
                    <span class="stat-label">Interviews Conducted</span>
                </div>
                <div class="stat">
                    <span class="stat-number">85%</span>
                    <span class="stat-label">Time Saved</span>
                </div>
                <div class="stat">
                    <span class="stat-number">4.9</span>
                    <span class="stat-label">User Rating</span>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <div class="floating-card card-1">
                <div class="mini-video">
                    <i class="fas fa-user"></i>
                </div>
                <div class="card-info">
                    <span class="name">Sarah Johnson</span>
                    <span class="role">Software Engineer</span>
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                </div>
            </div>
            <div class="floating-card card-2">
                <div class="mini-video">
                    <i class="fas fa-user"></i>
                </div>
                <div class="card-info">
                    <span class="name">Mike Chen</span>
                    <span class="role">Product Manager</span>
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
            <div class="hero-mockup">
                <div class="mockup-header">
                    <div class="dots">
                        <span></span><span></span><span></span>
                    </div>
                    <span class="url">hiregenius.com/interview</span>
                </div>
                <div class="mockup-content">
                    <div class="video-preview">
                        <i class="fas fa-video"></i>
                        <span>Recording...</span>
                    </div>
                    <div class="question-preview">
                        <span class="q-badge">Q1</span>
                        <p>Tell us about yourself and your experience...</p>
                    </div>
                    <div class="timer-preview">
                        <i class="fas fa-clock"></i>
                        <span>2:45</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Access Cards -->
    <section class="quick-access" id="get-started">
        <div class="container">
            <div class="access-cards">
                <div class="access-card recruiter">
                    <div class="card-glow"></div>
                    <div class="card-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3>For Recruiters</h3>
                    <p>Create interviews, customize questions, and review candidate responses with powerful analytics.</p>
                    <ul class="features-list">
                        <li><i class="fas fa-check"></i> Unlimited interviews</li>
                        <li><i class="fas fa-check"></i> Custom questions</li>
                        <li><i class="fas fa-check"></i> Video playback</li>
                        <li><i class="fas fa-check"></i> Team collaboration</li>
                    </ul>
                    <div class="card-actions">
                        <a href="../recruiter/login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="../recruiter/register.php" class="btn btn-outline">
                            <i class="fas fa-user-plus"></i> Sign Up
                        </a>
                    </div>
                </div>

                <div class="access-card candidate">
                    <div class="card-glow"></div>
                    <div class="card-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>For Candidates</h3>
                    <p>Take your interview anytime, anywhere. Record your responses at your own pace.</p>
                    <div class="code-input-wrapper">
                        <form action="../candidate/join.php" method="GET" class="code-form">
                            <div class="code-input-group">
                                <i class="fas fa-key"></i>
                                <input type="text" name="code" placeholder="Enter Interview Code" maxlength="6" pattern="[A-Za-z0-9]{6}" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </form>
                    </div>
                    <a href="../candidate/join.php" class="start-link">
                        <i class="fas fa-video"></i> Start Interview Without Code
                    </a>
                </div>

                <div class="access-card admin">
                    <div class="card-glow"></div>
                    <div class="card-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Administration</h3>
                    <p>Manage platform settings, approve recruiters, and monitor system health.</p>
                    <div class="admin-features">
                        <span><i class="fas fa-users-cog"></i> User Management</span>
                        <span><i class="fas fa-cogs"></i> System Settings</span>
                        <span><i class="fas fa-chart-line"></i> Analytics</span>
                    </div>
                    <a href="../admin/login.php" class="btn btn-secondary">
                        <i class="fas fa-lock"></i> Admin Access
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Features</span>
                <h2>Everything You Need for Better Hiring</h2>
                <p>Powerful tools designed to streamline your entire recruitment workflow</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h4>Video Recording</h4>
                    <p>HD quality video responses with automatic compression and secure storage</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h4>Timed Responses</h4>
                    <p>Set custom time limits for each question to simulate real interview pressure</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <h4>Easy Sharing</h4>
                    <p>Share interview links with candidates via email or unique codes</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h4>Analytics Dashboard</h4>
                    <p>Track completion rates, time metrics, and candidate performance</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Team Review</h4>
                    <p>Collaborate with your hiring team to evaluate candidates together</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Secure & Private</h4>
                    <p>Enterprise-grade security with encrypted video storage</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Process</span>
                <h2>How It Works</h2>
                <p>Get started in minutes with our simple 4-step process</p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Create Interview</h4>
                        <p>Set up your interview with custom or default questions</p>
                    </div>
                </div>
                <div class="step-connector"><i class="fas fa-arrow-right"></i></div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>Share Code</h4>
                        <p>Send the unique interview code to your candidates</p>
                    </div>
                </div>
                <div class="step-connector"><i class="fas fa-arrow-right"></i></div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>Record Responses</h4>
                        <p>Candidates record video answers at their convenience</p>
                    </div>
                </div>
                <div class="step-connector"><i class="fas fa-arrow-right"></i></div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4>Review & Decide</h4>
                        <p>Watch responses and make informed hiring decisions</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Testimonials</span>
                <h2>What Our Users Say</h2>
            </div>
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                    <p>"HireGenius has transformed our hiring process. We've reduced time-to-hire by 60% while improving candidate quality."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">JD</div>
                        <div class="author-info">
                            <strong>Jane Doe</strong>
                            <span>HR Director, TechCorp</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                    <p>"The video interview feature is a game-changer. Candidates love the flexibility, and we get better insights into their personalities."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">MS</div>
                        <div class="author-info">
                            <strong>Mike Smith</strong>
                            <span>Talent Manager, StartupXYZ</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                    <p>"Easy to use, beautiful interface, and excellent support. This is exactly what modern recruiting needs."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">AL</div>
                        <div class="author-info">
                            <strong>Anna Lee</strong>
                            <span>CEO, InnovateCo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Transform Your Hiring?</h2>
                <p>Join thousands of companies using HireGenius to find top talent faster.</p>
                <div class="cta-actions">
                    <a href="../recruiter/register.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-rocket"></i> Get Started Free
                    </a>
                    <a href="../candidate/join.php" class="btn btn-glass btn-lg">
                        <i class="fas fa-video"></i> Take Interview
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <a href="index.php" class="footer-logo">
                        <i class="fas fa-bolt"></i>
                        <span>Hire<span class="accent">Genius</span></span>
                    </a>
                    <p>Making hiring smarter, faster, and more human.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <div class="link-group">
                        <h5>Product</h5>
                        <a href="#features">Features</a>
                        <a href="#how-it-works">How It Works</a>
                        <a href="#">Pricing</a>
                    </div>
                    <div class="link-group">
                        <h5>Company</h5>
                        <a href="#">About</a>
                        <a href="#">Careers</a>
                        <a href="#">Contact</a>
                    </div>
                    <div class="link-group">
                        <h5>Legal</h5>
                        <a href="#">Privacy</a>
                        <a href="#">Terms</a>
                        <a href="#">Security</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> HireGenius. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('.landing-nav');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });

        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card, .step, .testimonial-card, .access-card').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>
