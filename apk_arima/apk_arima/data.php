<?php
session_start();
include "config/database.php";
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Proses submit form (add atau update)
$message = "";
$edit_mode = false;
$edit_id = null;
$edit_tahun = $edit_bulan = $edit_penjualan = "";

if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_result = mysqli_query($db, "SELECT * FROM arima_data WHERE id = '$edit_id'");
    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_row = mysqli_fetch_assoc($edit_result);
        $edit_tahun = $edit_row['tahun'];
        $edit_bulan = $edit_row['bulan'];
        $edit_penjualan = $edit_row['penjualan'];
        $edit_mode = true;
    }
}

if (isset($_POST['submit'])) {
    $tahun = $_POST['tahun'];
    $bulan = $_POST['bulan'];
    $penjualan = $_POST['penjualan'];

    if ($edit_mode) {
        // Update
        $sql = "UPDATE arima_data SET tahun='$tahun', bulan='$bulan', penjualan='$penjualan' WHERE id='$edit_id'";
        if (mysqli_query($db, $sql)) {
            $message = "Data berhasil diperbarui!";
            header("Location: data.php?message=" . urlencode($message));
            exit;
        } else {
            $message = "Gagal memperbarui data!";
        }
    } else {
        // Insert
        $sql = "INSERT INTO arima_data (tahun, bulan, penjualan) VALUES ('$tahun', '$bulan', '$penjualan')";
        if (mysqli_query($db, $sql)) {
            $message = "Data berhasil ditambahkan!";
        } else {
            $message = "Gagal menambahkan data!";
        }
    }
}

// Jika ada message dari redirect
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Proses hapus data
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($db, "DELETE FROM arima_data WHERE id='$id'");
    header("Location: data.php");
    exit;
}

$data = mysqli_query($db, "SELECT * FROM arima_data ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data ARIMA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #283850ff, #0d4ea6);
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
            max-width: 1000px;
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

        form {
            display: grid;
            grid-template-columns: repeat(3, 1fr) auto;
            gap: 20px;
            margin-bottom: 30px;
            align-items: end;
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
        }

        input[type="number"], select {
            width: 100%;
            padding: 12px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        input[type="number"]:focus, select:focus {
            border-color: #00ffea;
            outline: none;
        }

        select {
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="white" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
        }

        button[type="submit"] {
            padding: 15px 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .cancel-btn {
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .cancel-btn:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        .message {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            border-radius: 10px;
            font-weight: 500;
            background: rgba(238, 252, 0, 0.2);
            border: 1px solid #eefc00;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        select option {
            background-color: #0d4ea6;
            /* warna biru sesuai tema */
            color: white;
            padding: 10px;
        }

        select option:hover {
            background-color: #0a3c84;
        }

        select option:checked {
            background-color: #092e66;
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

        tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .btn-edit {
            color: #00ffea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .btn-edit:hover {
            color: #00cccc;
        }

        .btn-delete {
            color: #ff4d4d;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .btn-delete:hover {
            color: #cc0000;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 20px;
                margin: 20px;
                padding: 15px;
            }

            .container {
                padding: 20px;
                width: 95%;
            }

            form {
                grid-template-columns: 1fr;
            }

            button[type="submit"], .cancel-btn {
                width: 100%;
                margin-top: 10px;
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
    <h2><?php echo $edit_mode ? 'Edit Data Penjualan (Y)' : 'Input Data Penjualan (Y)'; ?></h2>
    
    <form method="POST">
        <div>
            <label for="tahun">Tahun</label>
            <input type="number" id="tahun" name="tahun" value="<?php echo $edit_tahun; ?>" required>
        </div>
        <div>
            <label for="bulan">Bulan</label>
            <select id="bulan" name="bulan" required>
                <option value="">-- Pilih Bulan --</option>
                <option value="Januari" <?php echo ($edit_bulan == 'Januari') ? 'selected' : ''; ?>>Januari</option>
                <option value="Februari" <?php echo ($edit_bulan == 'Februari') ? 'selected' : ''; ?>>Februari</option>
                <option value="Maret" <?php echo ($edit_bulan == 'Maret') ? 'selected' : ''; ?>>Maret</option>
                <option value="April" <?php echo ($edit_bulan == 'April') ? 'selected' : ''; ?>>April</option>
                <option value="Mei" <?php echo ($edit_bulan == 'Mei') ? 'selected' : ''; ?>>Mei</option>
                <option value="Juni" <?php echo ($edit_bulan == 'Juni') ? 'selected' : ''; ?>>Juni</option>
                <option value="Juli" <?php echo ($edit_bulan == 'Juli') ? 'selected' : ''; ?>>Juli</option>
                <option value="Agustus" <?php echo ($edit_bulan == 'Agustus') ? 'selected' : ''; ?>>Agustus</option>
                <option value="September" <?php echo ($edit_bulan == 'September') ? 'selected' : ''; ?>>September</option>
                <option value="Oktober" <?php echo ($edit_bulan == 'Oktober') ? 'selected' : ''; ?>>Oktober</option>
                <option value="November" <?php echo ($edit_bulan == 'November') ? 'selected' : ''; ?>>November</option>
                <option value="Desember" <?php echo ($edit_bulan == 'Desember') ? 'selected' : ''; ?>>Desember</option>
            </select>
        </div>
        <div>
            <label for="penjualan">Penjualan (Y)</label>
            <input type="number" id="penjualan" name="penjualan" step="0.01" value="<?php echo $edit_penjualan; ?>" required>
        </div>
        <div>
            <button type="submit" name="submit"><?php echo $edit_mode ? 'Perbarui' : 'Simpan'; ?> Data</button>
            <?php if ($edit_mode) { ?>
                <a href="data.php" class="cancel-btn">Batal</a>
            <?php } ?>
        </div>
    </form>

    <?php if ($message != "") { ?>
        <div class="message"><?php echo $message; ?></div>
    <?php } ?>

    <h3>Data Penjualan</h3>

    <table>
        <tr>
            <th>No</th>
            <th>Tahun</th>
            <th>Bulan</th>
            <th>Penjualan (Y)</th>
            <th>Aksi</th>
        </tr>

        <?php 
        $no = 1;
        while ($row = mysqli_fetch_assoc($data)) { ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $row['tahun'] ?></td>
            <td><?= $row['bulan'] ?></td>
            <td><?= $row['penjualan'] ?></td>
            <td>
                <a class="btn-edit" href="data.php?edit=<?= $row['id'] ?>">EDIT</a> |
                <a class="btn-delete" href="data.php?delete=<?= $row['id']; ?>" 
                   onclick="return confirm('Yakin ingin menghapus?')">HAPUS</a>
            </td>
        </tr>
        <?php } ?>
    </table>

</div>

</body>
</html>
