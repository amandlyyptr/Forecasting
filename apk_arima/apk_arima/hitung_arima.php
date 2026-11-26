<?php 
session_start();
require 'config/database.php';

// Cek Login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Simpan data penjualan ke array
$data = [];
$dataQuery = mysqli_query($db, "
    SELECT * FROM arima_data 
    ORDER BY tahun ASC,
    FIELD(bulan,
        'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    ) ASC
");

while ($r = mysqli_fetch_assoc($dataQuery)) {
    $data[] = $r;
}

// Ambil parameter ARIMA
$paramQuery = mysqli_query($db, "SELECT * FROM arima_parameters ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hitung ARIMA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #253245ff, #0d4ea6);
            color: white;
            overflow-x: hidden;
        }

        /* FIX LIST SELECT AGAR TULISAN MUNCUL */
        select option {
            background: #0d4ea6; /* warna list */
            color: white;        /* warna teks */
        }

        /* Saat hover lebih jelas */
        select option:hover {
            background: #083c81;
        }


        .navbar {
            background: rgba(0, 0, 0, 0.25);
            padding: 20px;
            display: flex;
            justify-content: center;
            gap: 60px;
            border-radius: 20px;
            margin: 30px;
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
        .container {
            width: 90%;
            max-width: 1200px;
            margin: auto;
            margin-top: 20px;
            background: rgba(0, 40, 90, 0.45);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        h2, h3 { text-align: center; margin-bottom: 30px; font-weight: 600; }
        .card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        th, td {
            padding: 15px;
            text-align: center;
            font-size: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }
        th {
            background: rgba(0, 0, 0, 0.3);
            font-size: 18px;
            font-weight: 600;
        }
        tr:hover { background: rgba(255, 255, 255, 0.05); }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        select {
            width: 100%;
            max-width: 300px;
            padding: 12px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        button {
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>

<body>

<div class="navbar">
    <a href="home.php"><i class="fas fa-home"></i> BERANDA</a>
    <a href="parameter.php"><i class="fas fa-cogs"></i> INPUT PARAMETER</a>
    <a href="data.php"><i class="fas fa-database"></i> INPUT DATA</a>
    <a href="hitung_arima.php"><i class="fas fa-calculator"></i> HITUNG ARIMA</a>
</div>

<div class="container">

    <div class="card">
        <h3>Data Penjualan</h3>

        <?php if(count($data) < 5): ?>
        <p style="color: yellow; text-align:center;">
            Minimal 5 data untuk perhitungan ARIMA!
        </p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tahun</th>
                    <th>Bulan</th>
                    <th>Penjualan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($data as $row) {
                    echo "<tr>
                        <td>".$no++."</td>
                        <td>".$row['tahun']."</td>
                        <td>".$row['bulan']."</td>
                        <td>".$row['penjualan']."</td>
                    </tr>";
                } ?>
            </tbody>
        </table>
    </div>

    <form action="proses_hitung_arima.php" method="POST" class="card">
        <h3>Pilih Parameter ARIMA</h3>

        <select name="parameter_id" id="parameterSelect" required>
            <option value="">-- Pilih (p,d,q,φ,c) --</option>
            <?php while ($param = mysqli_fetch_assoc($paramQuery)) : ?>
                <option value="<?= $param['id']; ?>" 
                        data-p="<?= $param['p']; ?>" 
                        data-d="<?= $param['d']; ?>" 
                        data-q="<?= $param['q']; ?>"
                        data-phi="<?= $param['phi']; ?>"
                        data-c="<?= $param['c']; ?>">
                    (<?= $param['p']; ?>,<?= $param['d']; ?>,<?= $param['q']; ?>,
                    φ=<?= $param['phi']; ?>, c=<?= $param['c']; ?>)
                </option>
            <?php endwhile; ?>
        </select>


        <input type="hidden" name="p" id="inputP">
        <input type="hidden" name="d" id="inputD">
        <input type="hidden" name="q" id="inputQ">
        <input type="hidden" name="phi" id="inputPhi">
        <input type="hidden" name="c" id="inputC">


        <button type="submit" <?= (count($data) < 5) ? "disabled" : ""; ?>>
            Hitung ARIMA
        </button>
    </form>

</div>

<script>
document.getElementById("parameterSelect").addEventListener("change", function(){
    const opt = this.options[this.selectedIndex];

    document.getElementById("inputP").value   = opt.dataset.p;
    document.getElementById("inputD").value   = opt.dataset.d;
    document.getElementById("inputQ").value   = opt.dataset.q;
    document.getElementById("inputPhi").value = opt.dataset.phi;
    document.getElementById("inputC").value   = opt.dataset.c;
});
</script>


</body>
</html>
