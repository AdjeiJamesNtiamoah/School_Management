<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Flawless | Digital School Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #020617;
            --accent: linear-gradient(135deg, #a855f7 0%, #6366f1 100%);
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
            --text: #f8fafc;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Animated Background Blobs */
        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: #6366f1;
            filter: blur(120px);
            opacity: 0.15;
            z-index: -1;
            border-radius: 50%;
            animation: move 20s infinite alternate;
        }

        @keyframes move {
            from { transform: translate(-10%, -10%); }
            to { transform: translate(20%, 20%); }
        }

        /* Navigation */
        nav {
            padding: 30px 10%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            background: var(--accent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
        }

        /* Hero Section */
        .hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0 20px;
        }

        h1 {
            font-size: clamp(2.5rem, 8vw, 5rem);
            font-weight: 800;
            margin: 0;
            line-height: 1.1;
        }

        p {
            color: #94a3b8;
            font-size: 1.2rem;
            max-width: 600px;
            margin: 25px 0 40px 0;
        }

        /* Portal Cards */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            width: 100%;
            max-width: 1100px;
            margin-bottom: 50px;
        }

        .card {
            background: var(--glass);
            border: 1px solid var(--border);
            padding: 40px;
            border-radius: 30px;
            text-decoration: none;
            color: white;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(10px);
        }

        .card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.07);
            border-color: #a855f7;
        }

        .card h3 { margin: 0 0 10px 0; font-size: 1.5rem; }
        .card p { font-size: 0.9rem; margin: 0; }

        .btn-main {
            background: var(--accent);
            padding: 18px 40px;
            border-radius: 100px;
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
            transition: 0.3s;
        }

        .btn-main:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.5);
        }

        footer {
            padding: 40px;
            text-align: center;
            font-size: 0.9rem;
            color: #475569;
        }
    </style>
</head>
<body>

    <div class="blob" style="top: -100px; left: -100px;"></div>
    <div class="blob" style="bottom: -100px; right: -100px; background: #a855f7;"></div>

    <nav>
        <div class="logo">FLAWLESS</div>
        <div>
            <a href="login.php" class="btn-main">Admin Login</a>
        </div>
    </nav>

    <main class="hero">
        <h1>Simplify Your <br><span style="color: #6366f1;">Education.</span></h1>
        <p>A unified platform for students, teachers, and administrators to communicate, track progress, and manage finance in real-time.</p>

        <div class="grid">
            <a href="login.php" class="card">
                <h3>👨‍🎓 Students</h3>
                <p>Access assignments, check grades, chat with teachers, Manage classrooms, input marks, broadcast alerts, Monitor tuition payments and generate financial reports.</p>
            </a>
            <a href="login.php" class="card">
                <h3>👩‍🏫Teachers</h3>
                <p>Manage classrooms, input marks, and broadcast alerts.</p>
            </a>
            <a href="login.php" class="card">
                <h3>💰 Finance</h3>
                <p>Monitor tuition payments and generate financial reports.</p>
            </a>
        </div>
        
    </main>

    <footer>
        &copy; 2026 Flawless School Management System. Built for the future of learning.
    </footer>

</body>
</html>