<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HireGenius - Smart Video Interview Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="landing-container">
        <header class="landing-header">
            <div class="logo">
                <h1>Hire<span class="accent">Genius</span></h1>
            </div>
            <p class="tagline">Smart Video Interview Platform</p>
        </header>

        <main class="landing-main">
            <section class="hero-section">
                <h2>Streamline Your Hiring Process</h2>
                <p>Conduct video interviews efficiently. Create custom questions, review candidate responses, and make informed hiring decisions.</p>
            </section>

            <section class="action-cards">
                <div class="card recruiter-card">
                    <div class="card-icon">👔</div>
                    <h3>For Recruiters</h3>
                    <p>Create interviews, manage candidates, and review responses all in one place.</p>
                    <div class="card-buttons">
                        <a href="recruiter/login.php" class="btn btn-primary">Login</a>
                        <a href="recruiter/register.php" class="btn btn-outline">Sign Up</a>
                    </div>
                </div>

                <div class="card candidate-card">
                    <div class="card-icon">🎯</div>
                    <h3>For Candidates</h3>
                    <p>Take your interview anytime, anywhere. Just enter your interview code to begin.</p>
                    <div class="card-buttons">
                        <a href="candidate/join.php" class="btn btn-primary">Start Interview</a>
                    </div>
                </div>

                <div class="card admin-card">
                    <div class="card-icon">⚙️</div>
                    <h3>Administration</h3>
                    <p>Manage recruiters, approve accounts, and configure system settings.</p>
                    <div class="card-buttons">
                        <a href="admin/login.php" class="btn btn-secondary">Admin Login</a>
                    </div>
                </div>
            </section>
        </main>

        <footer class="landing-footer">
            <p>&copy; <?= date('Y') ?> HireGenius. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
