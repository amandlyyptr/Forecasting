<?php
session_start();
require 'config/database.php';
$eval_id = $_GET['eval_id'];


// Reset session saat halaman dibuka pertama kali
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    unset($_SESSION['forecast_results']);
    $_POST['num_months'] = 1; // Default 1 bulan
}

// Ambil data hasil observasi
$data = mysqli_query($db,"SELECT * FROM arima_results WHERE evaluation_id='$eval_id' AND y IS NOT NULL");
// ================== DATA UNTUK GRAFIK ==================
$chartData = [];
$chartQuery = mysqli_query($db,"
    SELECT tahun,bulan,y,prediksi_y 
    FROM arima_results 
    WHERE evaluation_id='$eval_id' AND y IS NOT NULL
    ORDER BY tahun ASC, FIELD(bulan,'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember') ASC
");

while($c = mysqli_fetch_assoc($chartQuery)){
    $chartData[] = [
        "label" => $c['bulan'].' '.$c['tahun'],
        "y" => $c['y'] ?? 0,
        "forecast" => $c['prediksi_y'] ?? 0
    ];
}

$eval = mysqli_fetch_assoc(mysqli_query($db,"SELECT * FROM arima_evaluation WHERE id='$eval_id'"));

// Fungsi next month
function next_month($tahun, $bulan) {
    $bulanAssoc = ["Januari"=>1,"Februari"=>2,"Maret"=>3,"April"=>4,"Mei"=>5,"Juni"=>6,"Juli"=>7,"Agustus"=>8,"September"=>9,"Oktober"=>10,"November"=>11,"Desember"=>12];
    $num = $bulanAssoc[$bulan] + 1;
    if($num > 12){ $num = 1; $tahun++; }
    $bulanBalik = array_flip($bulanAssoc);
    return [$tahun, $bulanBalik[$num]];
}

// Proses generate forecast untuk N bulan ke depan
if(isset($_POST['generate_forecast'])){
    $num_months = intval($_POST['num_months']);
    if($num_months < 1 || $num_months > 12) $num_months = 1; // Validasi

    // Ambil data terakhir observasi
    $last = mysqli_fetch_assoc(mysqli_query($db,"SELECT tahun,bulan,y,d_y FROM arima_results WHERE evaluation_id='$eval_id' AND y IS NOT NULL ORDER BY tahun DESC, FIELD(bulan,'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember') DESC LIMIT 1"));

    // ================== AMBIL PHI & C DARI DATABASE ==================
    $coef = mysqli_fetch_assoc(mysqli_query($db,"
        SELECT p.phi, p.c
        FROM arima_evaluation e
        JOIN arima_parameters p ON e.parameter_id = p.id
        WHERE e.id = '$eval_id'
    "));

    if (!$coef) {
        die("⚠ Koefisien ARIMA tidak ditemukan");
    }

    $phi = $coef['phi'];
    $c   = $coef['c'];


    $lastY = $last['y'];
    $lag1  = $last['d_y'];  // ΔY_{t-1} (hanya satu lag untuk ARIMA(1,1,0))

    $current_tahun = $last['tahun'];
    $current_bulan = $last['bulan'];

    $_SESSION['forecast_results'] = []; // Reset hasil sebelumnya

    for($s=1; $s <= $num_months; $s++){
        [$current_tahun,$current_bulan] = next_month($current_tahun,$current_bulan);

        // Cek apakah forecast sudah ada
        $cek = mysqli_query($db,"SELECT * FROM arima_results WHERE evaluation_id='$eval_id' AND tahun='$current_tahun' AND bulan='$current_bulan'");
        if(mysqli_num_rows($cek) > 0){
            $exist = mysqli_fetch_assoc($cek);
            $pred_dY = $exist['prediksi_dy'];
            $pred_Y = $exist['prediksi_y'];
        } else {
            // Rumus ARIMA(1,1,0): ΔY_t = c + φ * ΔY_{t-1}
            $pred_dY = $c + ($phi * $lag1);
            $pred_Y = $lastY + $pred_dY;
            mysqli_query($db,"INSERT INTO arima_results (tahun,bulan,prediksi_dy,prediksi_y,evaluation_id) VALUES ('$current_tahun','$current_bulan','$pred_dY','$pred_Y','$eval_id')");
        }

        $_SESSION['forecast_results'][] = [
            'tahun' => $current_tahun,
            'bulan' => $current_bulan,
            'pred_dy' => formatExcelForecast($pred_dY),
            'pred_y' => formatExcelForecast($pred_Y)
        ];

        // Update lag (hanya satu lag untuk ARIMA(1,1,0))
        $lag1 = $pred_dY;
        $lastY = $pred_Y;
    }
}

// ================= FORMAT FUNCTION ==================
function formatExcel($number){
    if($number === null) return null;
    if(floor($number) == $number){ 
        return number_format($number,0,",",""); 
    }
    return number_format($number,2,",","");
}

// ================= FORMAT FUNCTION UNTUK FORECAST ==================
function formatExcelForecast($number){
    if($number === null) return null;
    $str = (string)$number;
    if($number < 0){
        $abs_str = (string)abs($number);
        return "-" . str_replace('.', '', substr($abs_str, 0, 5));
    } else {
        return str_replace('.', '', substr($str, 0, 5));
    }
}

// ================= FORMAT FUNCTION KHUSUS UNTUK MAPE ==================
function formatMAPE($number){
    if($number === null) return null;
    // Tampilkan dengan 4 desimal untuk presisi seperti Excel
    return number_format($number, 4, ',', '');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARIMA Forecast</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #182538ff, #0d4ea6);
            color: white;
            overflow-x: hidden;
        }
        select option {
            background: #0d4ea6;
            color: white;
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
            background: rgba(0, 40, 90, 0.75); 
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        h2, h3 { text-align: center; margin-bottom: 30px; font-weight: 600; }
        .card {
            background: rgba(202, 6, 6, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(117, 0, 0, 0.2);
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
            padding: 20px;
            text-align: center;
            font-size: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }
        th {
            background: rgba(0, 0, 0, 0.3);
            font-size: 18px;
            font-weight: 600;
        }
        
        /* === FIX YANG DIMINTA === */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        table tr:hover td {
            background: none !important;
        }
        /* ======================== */

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        input, select {
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
        .form-row {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
            align-items: center;
            width: 100%;
            justify-content: center;
        }
        .form-row > * {
            flex: 1;
            max-width: 200px;
        }
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            .navbar {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <a href="home.php"><i class="fas fa-home"></i> BERANDA</a>
    <a href="parameter.php"><i class="fas fa-cogs"></i> INPUT PARAMETER</a>
    <a href="data.php"><i class="fas fa-database"></i> INPUT DATA</a>
    <a href="hitung_arima.php"><i class="fas fa-calculator"></i> HITUNG ARIMA</a>
    <a href="#" onclick="printPDF()"><i class="fas fa-file-pdf"></i> CETAK PDF</a>
</div>

<div class="container">

    <div class="card">
        <h2>📊 GRAFIK AKTUAL vs FORECAST</h2>
        <canvas id="chart" style="max-height:400px;"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx = document.getElementById('chart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($chartData,'label')) ?>,
            datasets: [
                {
                    label: 'Aktual (Y)',
                    data: <?= json_encode(array_column($chartData,'y')) ?>,
                    borderColor: '#ffffff',
                    backgroundColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 2,
                    tension: 0.3
                },
                {
                    label: 'Forecast (Y)',
                    data: <?= json_encode(array_column($chartData,'forecast')) ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderDash: [5,5],
                    borderWidth: 2,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: { beginAtZero: false }
            }
        }
    });
    </script>

    <div class="card">
        <h3>HASIL PERHITUNGAN ARIMA(1,1,0)</h3>

        <!-- === WRAPPER DITAMBAHKAN SESUAI PERINTAH === -->
        <div class="table-wrapper">
        <table>
        <tr>
        <th>Tahun</th><th>Bulan</th><th>Penjualan (Y)</th><th>Y(t-1)</th><th>Diff Y’</th><th>Lag1 Diff</th><th>Pred Diff (Ŷ’)</th><th>Pred Y (Ŷ)</th><th>Error</th><th>MSE</th><th>MAPE</th>
        </tr>
        <?php while($r=mysqli_fetch_assoc($data)){ ?>
        <tr>
        <td><?= $r['tahun'] ?></td>
        <td><?= $r['bulan'] ?></td>
        <td><?= formatExcel($r['y']) ?></td>
        <td><?= formatExcel($r['y_1']) ?></td>
        <td><?= formatExcel($r['d_y']) ?></td>
        <td><?= formatExcel($r['d_y_1']) ?></td>
        <td><?= formatExcel($r['prediksi_dy']) ?></td>
        <td><?= formatExcel($r['prediksi_y']) ?></td>
        <td><?= formatExcel($r['error']) ?></td>
        <td><?= formatExcel($r['mse']) ?></td>
        <td><?= formatMAPE($r['mape']) ?></td>  <!-- GUNAKAN formatMAPE UNTUK PRESISI LEBIH -->
        </tr>
        <?php } ?>
        </table>
        </div>
        <!-- ============================================= -->

    </div>

    <div class="card">
        <h3>RINGKASAN AKURASI MODEL</h3>
        <p><strong>RATA-RATA MSE = <?= number_format($eval['rata_mse'], 2, ',', '') ?></strong></p>
        <p><strong>RATA-RATA MAPE = <?= number_format($eval['rata_mape'], 4, ',', '') ?></strong></p>  <!-- UBAH KE 4 DESIMAL DAN HAPUS /100 -->
    </div>

    <div class="card">
        <h3>HASIL FORECAST</h3>
        <form method="POST">
        <div style="text-align: center; margin-bottom: 20px;">
            <label for="num_months"><strong>Pilih Jumlah Bulan Forecast (1-12):</strong></label><br>
            <select name="num_months" id="num_months" required>
                <?php for($i=1; $i<=12; $i++){ ?>
                    <option value="<?=$i?>" <?=(@$_POST['num_months']==$i ? 'selected' : '')?>><?=$i?></option>
                <?php } ?>
            </select>
        </div>
        <button type="submit" name="generate_forecast">Generate Forecast</button>
        </form>

        <?php if(isset($_SESSION['forecast_results']) && !empty($_SESSION['forecast_results'])){ ?>
        <div class="table-wrapper" style="margin-top: 30px;">
        <table>
        <tr>
        <th>Bulan</th><th>Pred Diff (Ŷ’)</th><th>Pred Penjualan (Ŷ)</th>
        </tr>
        <?php foreach($_SESSION['forecast_results'] as $fr){ ?>
        <tr>
        <td><?= $fr['bulan'] ?> <?= $fr['tahun'] ?></td>
        <td><?= $fr['pred_dy'] ?></td>
        <td><?= $fr['pred_y'] ?></td>
        </tr>
        <?php } ?>
        </table>
        </div>
        <?php } ?>
    </div>

</div>

<!-- Library untuk PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
function printPDF() {

    const { jsPDF } = window.jspdf;   // ← ini WAJIB

    html2canvas(document.body, {
        scale: 2,
        useCORS: true,
        allowTaint: true
    }).then(canvas => {

        let pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
        });

        const imgData = canvas.toDataURL('image/png');

        const pageWidth = 210;
        const pageHeight = 297;
        const imgWidth = pageWidth;
        const imgHeight = canvas.height * imgWidth / canvas.width;

        let heightLeft = imgHeight;
        let position = 0;

        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;

        while (heightLeft > 0) {
            pdf.addPage();
            position = heightLeft - imgHeight;
            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
        }

        pdf.save("hasil_arima.pdf");
    });
}
</script>
