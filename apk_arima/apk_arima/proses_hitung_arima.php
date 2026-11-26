<?php
session_start();
require 'config/database.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// ==========================
// VALIDASI PARAMETER ARIMA
// ==========================
$parameter_id = $_POST['parameter_id'];

$param = mysqli_fetch_assoc(
    mysqli_query($db, "SELECT * FROM arima_parameters WHERE id = '$parameter_id'")
);

if (!$param) {
    die("⚠ Parameter ARIMA tidak ditemukan!");
}

// Sistem masih dibatasi ARIMA(1,1,0)
if ($param['p'] != 1 || $param['d'] != 1 || $param['q'] != 0) {
    die("⚠ Sistem hanya mendukung ARIMA(1,1,0)");
}

// ==========================
// AMBIL KOEFISIEN DARI DATABASE
// ==========================
$phi = $param['phi'];   // φ dari DB
$c   = $param['c'];     // c dari DB

// ==========================
// AMBIL DATA BERURUT BULAN
// ==========================
$data = [];
$q = mysqli_query($db,"
    SELECT * FROM arima_data
    ORDER BY tahun ASC,
    FIELD(bulan,
        'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    )
");

while ($r = mysqli_fetch_assoc($q)) {
    $data[] = $r;
}

if (count($data) < 3) {
    die("⚠ Data minimal sampai Maret!");
}

$tahun_awal = $data[0]['tahun'];

// ==========================
// VARIABEL PERHITUNGAN
// ==========================
$result   = [];
$mapeSum  = 0;
$mseSum   = 0;
$n        = 0;

// ==========================
// PROSES PERHITUNGAN ARIMA(1,1,0)
// ==========================
for ($i = 0; $i < count($data); $i++) {

    $id    = $data[$i]['id'];
    $tahun = $data[$i]['tahun'];
    $bulan = $data[$i]['bulan'];
    $Y     = $data[$i]['penjualan'];

    if (is_null($Y) || $Y == 0) {
        continue;
    }

    $Y_prev = ($i > 0) ? $data[$i-1]['penjualan'] : null;

    // ΔY
    $dY = ($i > 0) ? $Y - $Y_prev : null;

    // ΔY(t-1)
    $dY_1 = ($i > 0 && isset($result[$i-1])) ? $result[$i-1]['d_y'] : null;

    // Kosongkan Januari & Februari tahun awal
    $awal_kosong = (
        $tahun == $tahun_awal &&
        ($bulan == 'Januari' || $bulan == 'Februari')
    );

    if (!$awal_kosong && $i > 0 && $dY_1 !== null) {

        // ======================
        // RUMUS ARIMA(1,1,0)
        // ======================
        $pred_dY = $c + ($phi * $dY_1);
        $pred_Y  = $Y_prev + $pred_dY;

        $error = $Y - $pred_Y;
        $mape  = abs($error / $Y);
        $mse   = pow($error, 2);

        $mapeSum += $mape;
        $mseSum  += $mse;
        $n++;

    } else {
        $pred_dY = null;
        $pred_Y  = null;
        $error   = null;
        $mape    = null;
        $mse     = null;
    }

    $result[$i] = [
        "id"      => $id,
        "y_1"     => $Y_prev,
        "d_y"     => $dY,
        "d_y_1"   => $dY_1,
        "pred_dy" => $pred_dY,
        "pred_y"  => $pred_Y,
        "error"   => $error,
        "mape"    => $mape,
        "mse"     => $mse
    ];
}

// ==========================
// RATA-RATA ERROR
// ==========================
$avgMAPE = ($n > 0) ? $mapeSum / $n : 0;
$avgMSE  = ($n > 0) ? $mseSum / $n : 0;

// ==========================
// SIMPAN KE EVALUATION
// ==========================
mysqli_query($db, "
    INSERT INTO arima_evaluation
    (mape, mse, rata_mape, rata_mse, parameter_id, p, d, q)
    VALUES
    ('$mapeSum', '$mseSum', '$avgMAPE', '$avgMSE',
     '$parameter_id', '{$param['p']}', '{$param['d']}', '{$param['q']}')
");

$eval_id = mysqli_insert_id($db);

// ==========================
// UPDATE arima_results
// ==========================
foreach ($result as $r) {

    mysqli_query($db, "
        UPDATE arima_results SET
            y_1 = " . (is_null($r['y_1']) ? "NULL" : $r['y_1']) . ",
            d_y = " . (is_null($r['d_y']) ? "NULL" : $r['d_y']) . ",
            d_y_1 = " . (is_null($r['d_y_1']) ? "NULL" : $r['d_y_1']) . ",
            prediksi_dy = " . (is_null($r['pred_dy']) ? "NULL" : $r['pred_dy']) . ",
            prediksi_y  = " . (is_null($r['pred_y']) ? "NULL" : $r['pred_y']) . ",
            error = " . (is_null($r['error']) ? "NULL" : $r['error']) . ",
            mape  = " . (is_null($r['mape']) ? "NULL" : $r['mape']) . ",
            mse   = " . (is_null($r['mse']) ? "NULL" : $r['mse']) . ",
            evaluation_id = '$eval_id'
        WHERE data_id = '{$r['id']}'
    ");
}

// ==========================
// REDIRECT
// ==========================
header("Location: hasil_perhitungan.php?eval_id=$eval_id");
exit();
?>
