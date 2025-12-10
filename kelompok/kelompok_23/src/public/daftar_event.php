<?php
session_start();
require_once __DIR__ . '/../database/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'anggota') {
    header('Location: login.php');
    exit;
}

$user_id  = $_SESSION['user_id'];
$event_id = $_POST['event_id'] ?? null;

if (!$event_id) {
    header('Location: index.php');
    exit;
}

// cek apakah sudah daftar
$stmt = $pdo->prepare("SELECT id FROM event_registrations WHERE user_id = ? AND event_id = ?");
$stmt->execute([$user_id, $event_id]);
$existing = $stmt->fetch();

if ($existing) {
    header('Location: index.php?msg=sudah_daftar');
    exit;
}

// insert pendaftaran
$stmt = $pdo->prepare("INSERT INTO event_registrations (user_id, event_id, status) VALUES (?,?, 'pending')");
$stmt->execute([$user_id, $event_id]);

header('Location: index.php?msg=daftar_sukses');
exit;