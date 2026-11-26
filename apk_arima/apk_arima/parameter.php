<?php
session_start();
include "config/database.php";
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$message = "";
$edit_mode = false;
$edit_id = null;
$edit_p = $edit_d = $edit_q = $edit_phi = $edit_c = "";

// MODE EDIT
if (isset($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($db, $_GET['edit']);
    $stmt = mysqli_prepare($db, "SELECT * FROM arima_parameters WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $edit_id);
    mysqli_stmt_execute($stmt);
    $edit_result = mysqli_stmt_get_result($stmt);
    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_row = mysqli_fetch_assoc($edit_result);
        $edit_p   = $edit_row['p'];
        $edit_d   = $edit_row['d'];
        $edit_q   = $edit_row['q'];
        $edit_phi = $edit_row['phi'];
        $edit_c   = $edit_row['c'];
        $edit_mode = true;
    }
    mysqli_stmt_close($stmt);
}

// SUBMIT FORM PDQ
if (isset($_POST['submit_pdq'])) {
    $p = intval($_POST['p']);
    $d = intval($_POST['d']);
    $q = intval($_POST['q']);

    if ($edit_mode) {
        $stmt = mysqli_prepare($db, "UPDATE arima_parameters SET p=?, d=?, q=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "iiii", $p, $d, $q, $edit_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Parameter PDQ berhasil diperbarui!";
            header("Location: parameter.php?message=" . urlencode($message));
            exit;
        } else {
            $message = "Gagal memperbarui PDQ!";
        }
        mysqli_stmt_close($stmt);
    } else {
        $stmt = mysqli_prepare($db, "INSERT INTO arima_parameters (p, d, q) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iii", $p, $d, $q);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Parameter PDQ berhasil ditambahkan!";
        } else {
            $message = "Gagal menambahkan PDQ!";
        }
        mysqli_stmt_close($stmt);
    }
}

// SUBMIT FORM PHI & C
if (isset($_POST['submit_phi_c'])) {
    $phi = floatval($_POST['phi']);
    $c = floatval($_POST['c']);

    if ($edit_mode) {
        $stmt = mysqli_prepare($db, "UPDATE arima_parameters SET phi=?, c=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ddi", $phi, $c, $edit_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Parameter Phi & C berhasil diperbarui!";
            header("Location: parameter.php?message=" . urlencode($message));
            exit;
        } else {
            $message = "Gagal memperbarui Phi & C!";
        }
        mysqli_stmt_close($stmt);
    } else {
        $stmt = mysqli_prepare($db, "INSERT INTO arima_parameters (phi, c) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "dd", $phi, $c);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Parameter Phi & C berhasil ditambahkan!";
        } else {
            $message = "Gagal menambahkan Phi & C!";
        }
        mysqli_stmt_close($stmt);
    }
}

if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}

// HAPUS DATA
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($db, $_GET['hapus']);
    $stmt = mysqli_prepare($db, "DELETE FROM arima_parameters WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: parameter.php");
    exit;
}

// AMBIL DATA
$result = mysqli_query($db, "SELECT * FROM arima_parameters ORDER BY id DESC");

// ================= FORMAT FUNCTION ==================
function formatExcel($number){
    if($number === null) return null;
    if(floor($number) == $number){ 
        return number_format($number,0,",",""); 
    }
    return number_format($number,4,",","");  // 4 desimal untuk presisi seperti phi dan c
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Parameter ARIMA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- ================= CSS DIPERBAIKI UNTUK JARIK DAN TAMPILAN ================= -->
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #27374eff, #0d4ea6);
            color: white;
            overflow-x: hidden;
            padding-bottom: 110px;
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

        .container {
            width: 90%;
            max-width: 1200px;  /* Diperbesar untuk tampilan lebih luas */
            margin: auto;
            margin-top: 20px;
            background: rgba(0, 40, 90, 0.45);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        h2, h3 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }

        /* Pesan Sukses/Error */
        .message {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .message.success {
            background: rgba(0, 255, 0, 0.2);
            color: #d4edda;
        }
        .message.error {
            background: rgba(255, 0, 0, 0.2);
            color: #f8d7da;
        }

        /* Form PDQ */
        .form-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
        }

        .form-row {
            display: flex;
            gap: 30px;  /* Jarak antar kolom diperbesar */
            align-items: end;
            flex-wrap: wrap;  /* Agar wrap jika perlu */
        }

        .form-row > div {
            flex: 1;
            min-width: 150px;  /* Minimal lebar untuk kolom */
        }

        .form-row > div:last-child {
            flex: none;  /* Tombol tidak stretch */
            margin-left: auto;  /* Dorong ke kanan */
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
        }

        input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input[type="number"]:focus {
            border-color: #667eea;
            outline: none;
        }

        button[type="submit"] {
            padding: 15px 30px;  /* Lebar diperbesar untuk tombol */
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
            white-space: nowrap;  /* Agar teks tidak wrap */
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
        }

        .cancel-btn {
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 10px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s ease;
        }

        .cancel-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Tabel diperbaiki untuk tampilan rapi */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        th, td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        th {
            background: rgba(0, 0, 0, 0.3);
            font-weight: 600;
            font-size: 16px;
        }

        tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.05);
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Link aksi diperbaiki */
        td a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            margin: 0 5px;
            transition: color 0.3s ease;
        }

        td a:hover {
            color: #764ba2;
        }

        td a:first-child {
            color: #28a745;  /* Hijau untuk edit */
        }

        td a:last-child {
            color: #dc3545;  /* Merah untuk hapus */
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 20px;
            }
            .form-row {
                flex-direction: column;
                gap: 15px;  /* Jarak lebih kecil di mobile */
            }
            .form-row > div:last-child {
                margin-left: 0;  /* Reset margin di mobile */
                align-self: center;  /* Center tombol */
            }
            table {
                font-size: 14px;
            }
            th, td {
                padding: 10px;
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
</div>

<div class="container">
    <h2><?= $edit_mode ? 'Edit Parameter ARIMA' : 'Input Parameter ARIMA'; ?></h2>

    <?php if ($message) { 
        $class = strpos($message, 'berhasil') !== false ? 'success' : 'error';
        echo "<div class='message $class'>$message</div>"; 
    } ?>

    <!-- Form PDQ -->
    <div class="form-section">
        <h3>Parameter PDQ</h3>
        <form method="POST">
            <div class="form-row">
                <div>
                    <label>P (AutoRegressive)</label>
                    <input type="number" name="p" value="<?= $edit_p ?>" required>
                </div>
                <div>
                    <label>D (Differencing)</label>
                    <input type="number" name="d" value="<?= $edit_d ?>" required>
                </div>
                <div>
                    <label>Q (Moving Average)</label>
                    <input type="number" name="q" value="<?= $edit_q ?>" required>
                </div>
                <div>
                    <button type="submit" name="submit_pdq">
                        <?= $edit_mode ? 'Perbarui PDQ' : 'Simpan PDQ'; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Form Phi & C -->
    <div class="form-section">
        <h3>Parameter Phi & Konstanta C</h3>
        <form method="POST">
            <div class="form-row">
                <div>
                    <label>Phi (φ)</label>
                    <input type="number" step="0.0001" name="phi" value="<?= $edit_phi ?>" required>
                </div>
                <div>
                    <label>Konstanta (c)</label>
                    <input type="number" step="0.0001" name="c" value="<?= $edit_c ?>" required>
                </div>
                <div>
                    <button type="submit" name="submit_phi_c">
                        <?= $edit_mode ? 'Perbarui Phi & C' : 'Simpan Phi & C'; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($edit_mode) { ?>
        <div style="text-align: center; margin-bottom: 20px;">
            <a href="parameter.php" class="cancel-btn">Batal Edit</a>
        </div>
    <?php } ?>

    <h3>Data Parameter ARIMA</h3>

    <table>
        <tr>
            <th>No</th>
            <th>P</th>
            <th>D</th>
            <th>Q</th>
            <th>Phi</th>
            <th>c</th>
            <th>Aksi</th>
        </tr>

        <?php $no = 1; while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $row['p']; ?></td>
            <td><?= $row['d']; ?></td>
            <td><?= $row['q']; ?></td>
            <td><?= formatExcel($row['phi']); ?></td>  <!-- FORMAT PHI -->
            <td><?= formatExcel($row['c']); ?></td>    <!-- FORMAT C -->
            <td>
                <a href="parameter.php?edit=<?= $row['id']; ?>"><i class="fas fa-edit"></i> Edit</a> |
                <a href="parameter.php?hapus=<?= $row['id']; ?>"
                   onclick="return confirm('Yakin ingin menghapus?')"><i class="fas fa-trash"></i> Hapus</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
