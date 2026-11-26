<?php
session_start();
session_unset();   // menghapus semua session
session_destroy(); // menghancurkan session user
header("Location: login.php"); // redirect ke halaman login
exit;
?>
