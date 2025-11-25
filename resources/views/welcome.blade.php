<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - FJL Lubricants Software</title>
    <!-- Google Fonts (Poppins for modern design) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Bootstrap 4 CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <style>
        /* Background Image and Overlay */
        .welcome-page {
            position: relative;
            background: url('background.jpg');
            /* Replace with your image path */
            background-size: cover;
            background-position: center;
            height: 100vh;
            /* Full height */
            color: white;
            font-family: 'Poppins', sans-serif;
        }

        .welcome-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            /* Darker overlay */
        }

        .welcome-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            padding: 20px;
        }

        .welcome-content h1 {
            font-size: 4rem;
            /* Large font size for welcome text */
            font-weight: 600;
            letter-spacing: 2px;
            margin-bottom: 20px;
        }

        .welcome-content p {
            font-size: 1.4rem;
            margin-bottom: 30px;
            font-weight: 400;
            opacity: 0.8;
        }

        .welcome-buttons .btn {
            font-size: 1.2rem;
            padding: 14px 25px;
            margin: 10px;
            text-transform: uppercase;
            font-weight: 600;
            border-radius: 50px;
            /* Rounded buttons */
            transition: all 0.3s ease;
        }

        .btn-login {
            background-color: #0089fd;
            /* Purple */
            color: white;
        }

        .btn-dashboard {
            background-color: #0089fd;
            /* Pink */
            color: white;
        }

        .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        /* Logo style */
        .logo {
            width: 200px;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>

    <!-- Welcome Page Section -->
    <div class="welcome-page">
        <!-- Overlay -->
        <div class="welcome-overlay"></div>

        <!-- Welcome Content -->
        <div class="welcome-content">
            <!-- Logo -->
            <img src="welcome-logo.png" alt="Logo" class="logo">
            <h1>Welcome to FJ Lubricant Soil Software</h1>
            <!-- Buttons -->
            <div class="welcome-buttons">
                @if (Route::has('login'))
                @auth
                <a href="{{ url('/home') }}" class="btn btn-dashboard">Dashboard</a>
                @else
                <a href="{{ route('login') }}" class="btn btn-login">Login</a>
                @endauth
                @endif

            </div>
        </div>
    </div>

    <!-- Bootstrap 4 JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>