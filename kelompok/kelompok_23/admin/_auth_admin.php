<?php
// file: admin/_auth_admin.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit;
}
require_once __DIR__ . '/../database/db.php';
