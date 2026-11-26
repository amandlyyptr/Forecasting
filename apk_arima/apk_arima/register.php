<?php
include('config/database.php');

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = trim($_POST['username']);
    $password = md5(trim($_POST['password']));

    $check = mysqli_query($db, "SELECT * FROM pengguna WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $msg = "Username sudah digunakan!";
    } else {
        $query = mysqli_query($db, "INSERT INTO pengguna (username, password) VALUES ('$username', '$password')");
        if ($query) {
            $msg = "Registrasi berhasil! Mengalihkan ke login...";
            header("refresh:2; url=login.php");
        } else {
            $msg = "Gagal registrasi!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrasi ARIMA</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    body {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        background: #0A0F1F; 
        color: white;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
    }

    /* Animated background lines */
    .animated-bg .line {
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

    .register-box {
        width: 380px;
        background: rgba(255, 255, 255, 0.05);
        padding: 40px;
        border-radius: 20px;
        text-align: center;
        box-shadow: 0 0 20px rgba(0,0,0,0.4);
        backdrop-filter: blur(10px);
        position: relative;
        z-index: 2;
    }

    .register-box img {
        width: 90px;
        margin-bottom: 15px;
    }

    h2 {
        margin-bottom: 15px;
        font-size: 28px;
        font-weight: 600;
    }

    .input-group {
        width: 100%;
        text-align: left;
        margin-bottom: 18px;
    }

    label {
        font-size: 14px;
        font-weight: 600;
    }

    input {
        width: 100%;
        padding: 13px;
        margin-top: 5px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.08);
        color: white;
        font-size: 15px;
        outline: none;
        transition: 0.3s;
    }

    input:focus {
        border-color: #4e8cff;
    }

    button {
        width: 100%;
        padding: 14px;
        background: #4e8cff;
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 17px;
        cursor: pointer;
        transition: 0.2s;
    }
    button:hover {
        background: #2765d3;
    }

    .msg {
        margin-bottom: 15px;
        padding: 10px;
        background: rgba(255, 50, 50, 0.15);
        border-left: 4px solid #ff4d4d;
        border-radius: 5px;
        font-size: 14px;
    }

    a {
        color: #6e9cff;
        font-weight: 600;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    /* Loading wave */
    .loading {
        display: none;
        margin: 15px auto 0;
        width: 60px;
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
        0%, 100% { height: 12px; }
        50% { height: 32px; }
    }
</style>

<script>
function showLoading() {
    document.getElementById("loading").style.display = "flex";
}
</script>
</head>
<body>

<!-- Animated background lines -->
<div class="animated-bg">
    <?php for ($i=0; $i<12; $i++): ?>
        <div class="line" style="top: <?= rand(0,100) ?>vh; animation-duration: <?= rand(4,10) ?>s;"></div>
    <?php endfor; ?>
</div>

<div class="register-box">
    <img src="logoawal.png" alt="Logo">

    <h2>Registrasi ARIMA</h2>

    <?php if ($msg != "") { ?>
        <div class="msg"><?= $msg ?></div>
    <?php } ?>

    <form method="POST" onsubmit="showLoading()">

        <div class="input-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Masukkan username" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>
        </div>

        <button type="submit">Daftar</button>

        <div class="loading" id="loading">
            <div class="wave"></div>
            <div class="wave"></div>
            <div class="wave"></div>
            <div class="wave"></div>
            <div class="wave"></div>
        </div>

    </form>

    <p style="margin-top: 18px;">Sudah punya akun? <a href="login.php">Login</a></p>
</div>

</body>
</html>
