<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - ARIMA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #182a46ff, #0d4ea6);
            color: white;
            overflow-x: hidden;
            padding-bottom: 110px; /* Biar ada ruang di bawah */
        }

        .navbar {
            background: rgba(0, 0, 0, 0.25);
            padding: 20px;
            display: flex;
            justify-content: center;
            gap: 60px;
            border-radius: 20px;
            margin: 30px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .navbar a {
            text-decoration: none;
            color: white;
            font-size: 18px;
            font-weight: 500;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .navbar a:hover {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            transform: translateY(-2px);
        }

        .content-box {
            background: rgba(0, 40, 90, 0.45);
            width: 85%;
            max-width: 1200px;
            margin: auto;
            padding: 50px;
            border-radius: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .title {
            font-size: 65px;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .subtitle {
            font-size: 20px;
            line-height: 1.5;
            max-width: 500px;
            font-weight: 400;
        }
 
        .illustration {
            width: 350px;
            opacity: 0.9;
            transition: opacity 0.3s ease;
        }

        .illustration:hover {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 20px;
                margin: 20px;
                padding: 15px;
            }

            .content-box {
                flex-direction: column;
                text-align: center;
                padding: 30px;
                width: 90%;
            }

            .title {
                font-size: 45px;
            }

            .subtitle {
                font-size: 18px;
                max-width: none;
            }

            .illustration {
                width: 250px;
                margin-top: 20px;
            }

            .logout-btn {
                position: static;
                margin-top: 20px;
                display: inline-block;
                width: auto;
            }
        }

        @media (max-width: 480px) {
            .title {
                font-size: 35px;
            }

            .subtitle {
                font-size: 16px;
            }

            .illustration {
                width: 200px;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding-bottom: 40px;
            }

            .logout-btn {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <a href="parameter.php"><i class="fas fa-cogs"></i> INPUT PARAMETER</a>
    <a href="data.php"><i class="fas fa-database"></i> INPUT DATA</a>
    <a href="hitung_arima.php"><i class="fas fa-calculator"></i> HITUNG ARIMA</a>
    <a href="logout.php"><i class=""></i> LOGOUT</a>
</div>

<div class="content-box">
    <div>
        <div class="title">ARIMA<br>Forecasting</div>
        <div class="subtitle">
            ARIMA (AutoRegressive Integrated Moving Average) adalah metode statistik
            untuk melakukan analisis deret waktu dan memprediksi data masa depan.
        </div>
    </div>

    <img class="illustration" src="ikon_grafik.png" alt="ARIMA Illustration">
</div>


</body>
</html>