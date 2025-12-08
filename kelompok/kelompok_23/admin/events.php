<?php
require_once __DIR__ . '/_auth_admin.php';

// ===========================
// HANDLE CREATE EVENT
// ===========================
if (isset($_POST['create_event'])) {

    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $tanggal     = $_POST['tanggal'];
    $waktu       = $_POST['waktu'];
    $lokasi      = trim($_POST['lokasi']);
    $kuota       = (int)$_POST['kuota'];
    $kategori    = trim($_POST['kategori']);
    $created_by  = $_SESSION['user_id'];
    $thumbnail   = null;

    // upload file jika ada
    if (!empty($_FILES['thumbnail']['name'])) {

        $upload_dir = __DIR__ . '/../uploads/event_images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES['thumbnail']['name']);
        $target   = $upload_dir . $filename;

        $allowed   = ['jpg', 'jpeg', 'png'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($extension, $allowed)) {
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) {
                $thumbnail = $filename;
            }
        }
    }

    // insert event
    $stmt = $pdo->prepare("INSERT INTO events 
        (title, description, tanggal, waktu, lokasi, kuota, kategori, thumbnail, created_by) 
        VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$title, $description, $tanggal, $waktu, $lokasi, $kuota, $kategori, $thumbnail, $created_by]);

    header("Location: events.php?msg=created");
    exit;
}

// ===========================
// HANDLE DELETE EVENT
// ===========================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // hapus file poster
    $stmt = $pdo->prepare("SELECT thumbnail FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $event = $stmt->fetch();

    if ($event && $event['thumbnail']) {
        $file = __DIR__ . '/../uploads/event_images/' . $event['thumbnail'];
        if (file_exists($file)) unlink($file);
    }

    $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$id]);

    header("Location: events.php?msg=deleted");
    exit;
}

// ===========================
// HANDLE EDIT EVENT (UPDATE)
// ===========================
if (isset($_POST['update_event'])) {

    $id          = (int)$_POST['id'];
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $tanggal     = $_POST['tanggal'];
    $waktu       = $_POST['waktu'];
    $lokasi      = trim($_POST['lokasi']);
    $kuota       = (int)$_POST['kuota'];
    $kategori    = trim($_POST['kategori']);
    $thumbnail   = $_POST['old_thumbnail'];

    // cek jika ada upload file baru
    if (!empty($_FILES['thumbnail']['name'])) {

        $upload_dir = __DIR__ . '/../uploads/event_images/';
        $filename = time() . "_" . basename($_FILES['thumbnail']['name']);
        $target   = $upload_dir . $filename;

        $allowed   = ['jpg', 'jpeg', 'png'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($extension, $allowed)) {
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) {
                // hapus file lama
                if ($thumbnail && file_exists($upload_dir . $thumbnail)) {
                    unlink($upload_dir . $thumbnail);
                }
                $thumbnail = $filename;
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE events SET 
            title=?, description=?, tanggal=?, waktu=?, lokasi=?, kuota=?, kategori=?, thumbnail=? 
            WHERE id=?");
    $stmt->execute([$title, $description, $tanggal, $waktu, $lokasi, $kuota, $kategori, $thumbnail, $id]);

    header("Location: events.php?msg=updated");
    exit;
}

// ===========================
// GET ALL EVENTS
// ===========================
$events = $pdo->query("SELECT * FROM events ORDER BY tanggal DESC")->fetchAll();

// GET ONE EVENT IF EDITING
$edit_event = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $edit_event = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Event - EventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 flex">

<?php include 'sidebar.php'; ?>

<main class="flex-1 ml-64 p-8">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-3xl font-bold text-slate-800">Manajemen Event</h2>
            <p class="text-slate-500 text-sm">Buat, ubah, dan kelola semua event.</p>
        </div>

        <a href="events.php#form-tambah"
           class="px-5 py-3 bg-emerald-600 text-white rounded-lg font-semibold shadow hover:bg-emerald-700 transition">
            <i class="fa-solid fa-circle-plus mr-2"></i>Tambah Event
        </a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="mb-6 p-4 rounded-lg text-white
            <?= $_GET['msg'] === 'created' ? 'bg-emerald-600' : '' ?>
            <?= $_GET['msg'] === 'updated' ? 'bg-blue-600' : '' ?>
            <?= $_GET['msg'] === 'deleted' ? 'bg-red-600' : '' ?>">
            <?=
                $_GET['msg'] === 'created' ? 'Event berhasil dibuat!' :
                ($_GET['msg'] === 'updated' ? 'Event berhasil diperbarui!' :
                'Event berhasil dihapus!')
            ?>
        </div>
    <?php endif; ?>

    <!-- ===================== -->
    <!-- FORM TAMBAH / EDIT -->
    <!-- ===================== -->

    <div id="form-tambah" class="bg-white p-6 rounded-2xl shadow border border-gray-200 mb-10">

        <h3 class="text-xl font-bold text-slate-800 mb-4">
            <?= $edit_event ? "Edit Event" : "Tambah Event Baru" ?>
        </h3>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">

            <?php if ($edit_event): ?>
                <input type="hidden" name="id" value="<?= $edit_event['id'] ?>">
                <input type="hidden" name="old_thumbnail" value="<?= $edit_event['thumbnail'] ?>">
            <?php endif; ?>

            <div>
                <label class="block text-sm font-medium mb-1">Judul Event</label>
                <input type="text" name="title" required
                       class="w-full px-4 py-2 border rounded-lg"
                       value="<?= $edit_event['title'] ?? '' ?>">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-2 border rounded-lg"><?= $edit_event['description'] ?? '' ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Tanggal</label>
                    <input type="date" name="tanggal" required
                           class="w-full px-4 py-2 border rounded-lg"
                           value="<?= $edit_event['tanggal'] ?? '' ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Waktu</label>
                    <input type="time" name="waktu" required
                           class="w-full px-4 py-2 border rounded-lg"
                           value="<?= $edit_event['waktu'] ?? '' ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Kuota Peserta</label>
                    <input type="number" name="kuota" required
                           class="w-full px-4 py-2 border rounded-lg"
                           value="<?= $edit_event['kuota'] ?? '' ?>">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Lokasi</label>
                <input type="text" name="lokasi" required
                       class="w-full px-4 py-2 border rounded-lg"
                       value="<?= $edit_event['lokasi'] ?? '' ?>">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Kategori</label>
                <input type="text" name="kategori"
                       class="w-full px-4 py-2 border rounded-lg"
                       value="<?= $edit_event['kategori'] ?? '' ?>">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Poster / Thumbnail</label>

                <?php if ($edit_event && $edit_event['thumbnail']): ?>
                    <img src="../uploads/event_images/<?= $edit_event['thumbnail'] ?>" class="h-28 rounded-lg mb-2">
                <?php endif; ?>

                <input type="file" name="thumbnail" accept=".jpg,.jpeg,.png"
                       class="w-full px-4 py-2 border rounded-lg">
            </div>

            <button type="submit"
                    name="<?= $edit_event ? 'update_event' : 'create_event' ?>"
                    class="px-6 py-3 bg-slate-900 text-white rounded-lg font-semibold hover:bg-slate-800">
                <?= $edit_event ? "Perbarui Event" : "Tambah Event" ?>
            </button>

            <?php if ($edit_event): ?>
                <a href="events.php"
                   class="px-5 py-3 bg-gray-200 rounded-lg font-semibold ml-2">Batal Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- ===================== -->
    <!-- TABLE LIST EVENT -->
    <!-- ===================== -->

    <div class="bg-white p-6 rounded-2xl shadow border border-gray-200">
        <h3 class="text-xl font-bold text-slate-800 mb-4">Daftar Semua Event</h3>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-gray-100 text-slate-700">
                        <th class="py-3 px-4">Poster</th>
                        <th class="py-3 px-4">Judul</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Lokasi</th>
                        <th class="py-3 px-4">Kuota</th>
                        <th class="py-3 px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>

                <?php if (!$events): ?>
                    <tr>
                        <td colspan="6" class="text-center py-6 text-slate-500">
                            Belum ada event tersedia.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($events as $ev): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4">
                            <?php if ($ev['thumbnail']): ?>
                                <img src="../uploads/event_images/<?= $ev['thumbnail'] ?>"
                                     class="h-14 w-20 rounded-lg object-cover">
                            <?php else: ?>
                                <span class="text-slate-400 text-sm">Tidak ada gambar</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 font-semibold"><?= htmlspecialchars($ev['title']) ?></td>
                        <td class="py-3 px-4"><?= date('d M Y', strtotime($ev['tanggal'])) ?></td>
                        <td class="py-3 px-4"><?= htmlspecialchars($ev['lokasi']) ?></td>
                        <td class="py-3 px-4"><?= $ev['kuota'] ?></td>
                        <td class="py-3 px-4">
                            <a href="events.php?edit=<?= $ev['id'] ?>"
                               class="text-blue-600 font-semibold hover:underline mr-3">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>

                            <a href="events.php?delete=<?= $ev['id'] ?>"
                               onclick="return confirm('Yakin ingin menghapus event ini?')"
                               class="text-red-600 font-semibold hover:underline">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>

</main>
</body>
</html>
