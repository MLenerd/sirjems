<?php
session_start();
include "config/config.php";

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql = "DELETE FROM stocks WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: stock-tracking.php");
        exit;
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
} else {
    header("Location: stock-tracking.php");
    exit;
}
?>