<?php
session_start();
include 'config/database.php';

if (isset($_SESSION['username'])) {
    header('Location: home.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM pengguna WHERE username = '$username'";
    $result = mysqli_query($db, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if ($user['password'] === md5($password)) {
            $_SESSION['username'] = $user['username'];
            header('Location: home.php');
            exit();
        } else {
            $message = 'Password salah!';
        }
    } else {
        $message = 'Username tidak ditemukan!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ARIMA Forecasting – Login</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    body {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        background: #262d42ff;  /* Dark mode */
        overflow: hidden;
        color: white;
    }

    /* Animated background graph */
    .animated-bg {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: -1;
    }

    .line {
        position: absolute;
        width: 150%;
        height: 2px;
        background: rgba(255, 255, 255, 0.1);
        animation: moveLine 6s linear infinite;
    }

    @keyframes moveLine {
        0% { transform: translateX(-10%); }
        100% { transform: translateX(30%); }
    }

    /* Wrapper */
    .wrapper {
        display: flex;
        height: 100vh;
        width: 100%;
    }

    /* Left Panel */
    .left-panel {
        flex: 1.3;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
        position: relative;
    }

    .arima-box {
        text-align: center;
    }

    .logo {
        width: 120px;
        height: auto;
        margin-bottom: 20px;
    }

    .title {
        font-size: 38px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .sub {
        font-size: 18px;
        opacity: 0.8;
        margin-bottom: 40px;
    }

    /* Moving Chart Bars */
    .bar {
        width: 18px;
        background: rgba(255,255,255,0.25);
        border-radius: 5px;
        display: inline-block;
        margin: 0 4px;
        animation: barMove 1.8s ease-in-out infinite alternate;
    }

    @keyframes barMove {
        0% { height: 40px; }
        100% { height: 130px; }
    }

    /* Right Panel (Login) */
    .login-panel {
        flex: 1;
        background: #11182D;
        border-radius: 30px 0 0 30px;
        padding: 60px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        color: #fff;
        position: relative;
    }

    h2 {
        font-size: 30px;
        margin-bottom: 10px;
    }

    .subtitle {
        margin-bottom: 25px;
        opacity: .7;
        font-size: 15px;
    }

    input {
        width: 100%;
        padding: 15px;
        margin: 10px 0;
        border: 2px solid #4e8cff;
        border-radius: 10px;
        background: #0F1527;
        color: white;
        font-size: 16px;
        outline: none;
    }

    input:focus {
        border-color: #9dbdff;
    }

    /* Button */
    button {
        width: 100%;
        padding: 15px;
        background: #4e8cff;
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 17px;
        cursor: pointer;
        transition: .2s;
    }

    button:hover {
        background: #2765d3;
    }

    /* ERROR */
    .error-message {
        color: #ff6b6b;
        background: rgba(255, 0, 0, 0.1);
        padding: 10px;
        border-left: 4px solid #ff6b6b;
        margin-bottom: 15px;
        border-radius: 5px;
    }

    a {
        margin-top: 20px;
        color: #6e9cff;
        text-decoration: none;
        font-weight: 600;
    }

    a:hover {
        text-decoration: underline;
    }

    /* Loading Wave Animation */
    .loading {
        display: none;
        margin: 20px auto 0;
        width: 60px;
        height: 20px;
        display: flex;
        justify-content: space-between;
    }

    .wave {
        width: 8px;
        background: #4e8cff;
        animation: waveAnim 0.8s infinite ease-in-out;
    }

    .wave:nth-child(2) { animation-delay: .1s; }
    .wave:nth-child(3) { animation-delay: .2s; }
    .wave:nth-child(4) { animation-delay: .3s; }
    .wave:nth-child(5) { animation-delay: .4s; }

    @keyframes waveAnim {
        0%, 100% { height: 15px; }
        50% { height: 35px; }
    }
</style>

<script>
function showLoading() {
    document.getElementById("loading").style.display = "flex";
}
</script>

</head>
<body>

<!-- Animated Lines -->
<div class="animated-bg">
    <?php for ($i=0; $i<10; $i++): ?>
    <div class="line" style="top: <?= rand(0,100) ?>vh; animation-duration: <?= rand(4,10) ?>s;"></div>
    <?php endfor; ?>
</div>

<div class="wrapper">

    <!-- LEFT PANEL -->
    <div class="left-panel">
        <div class="arima-box">
            <img src="logoawal.png" class="logo">

            <div class="title">ARIMA Forecasting</div>
            <div class="sub">Time Series • Trend Analysis • Forecasting</div>

            <!-- Animated Chart Bars -->
            <?php for ($i=0; $i<18; $i++): ?>
                <div class="bar" style="animation-duration: <?= rand(1,3) ?>s;"></div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- RIGHT PANEL LOGIN -->
    <div class="login-panel">
        <h2>Masuk</h2>
        <div class="subtitle">Login untuk mengakses Dashboard ARIMA</div>

        <?php if ($message != '') { echo '<p class="error-message">'.$message.'</p>'; } ?>

        <form method="POST" onsubmit="showLoading()">
            <input type="text" name="username" placeholder="Username" required autofocus>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit">Login</button>

            <!-- Loading Waves -->
            <div class="loading" id="loading">
                <div class="wave"></div>
                <div class="wave"></div>
                <div class="wave"></div>
                <div class="wave"></div>
                <div class="wave"></div>
            </div>
        </form>

        <a href="register.php">Belum punya akun? Daftar disini</a>
    </div>

</div>

</body>
</html>
