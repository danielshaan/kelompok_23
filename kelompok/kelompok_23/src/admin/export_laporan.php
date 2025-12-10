<?php
require_once __DIR__ . '/_auth_admin.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=laporan_eventhub.csv');

$output = fopen('php://output', 'w');

// Header kolom
fputcsv($output, [
    'Judul Event',
    'Tanggal',
    'Waktu',
    'Lokasi',
    'Kategori',
    'Kuota',
    'Peserta Approved'
]);

// Ambil data event + jumlah peserta
$query = $pdo->query("
    SELECT 
        e.title,
        e.tanggal,
        e.waktu,
        e.lokasi,
        e.kategori,
        e.kuota,
        (SELECT COUNT(*) 
         FROM event_registrations r 
         WHERE r.event_id = e.id AND r.status = 'approved') AS total_approved
    FROM events e
    ORDER BY e.tanggal ASC
");

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['title'],
        $row['tanggal'],
        $row['waktu'],
        $row['lokasi'],
        $row['kategori'],
        $row['kuota'],
        $row['total_approved']
    ]);
}

fclose($output);
exit;
