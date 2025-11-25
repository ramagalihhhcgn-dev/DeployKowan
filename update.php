<?php
require_once 'config/database.php';
require_once 'classes/Produk.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$produkObj = new Produk();
$produk = $produkObj->readOne($id);

if (!$produk) {
    header("Location: index.php");
    exit();
}

$message = "";
$error = "";

// Jika form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $produkObj->setNama($_POST['nama']);
    $produkObj->setDeskripsi($_POST['deskripsi']);
    $produkObj->setHarga($_POST['harga']);

    // Handle upload foto baru
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $foto_baru = $produkObj->uploadFoto($_FILES['foto']);

        if ($foto_baru) {
            // Hapus foto lama jika ada
            if (!empty($produk['foto']) && file_exists("uploads/" . $produk['foto'])) {
                unlink("uploads/" . $produk['foto']);
            }

            $produkObj->setFoto($foto_baru);
        } else {
            $error = "Gagal upload foto! Pastikan file adalah JPG/PNG/GIF & < 2MB.";
        }
    } else {
        // Foto tetap pakai yang lama
        $produkObj->setFoto($produk['foto']);
    }

    if (empty($error)) {
        // Update query manual
        $conn = (new Database())->getConnection();
        $query = "UPDATE produk SET nama=?, deskripsi=?, harga=?, foto=?, updated_at=NOW() WHERE id=?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdsi", $_POST['nama'], $_POST['deskripsi'], $_POST['harga'], $produkObj->getFoto(), $id);

        if ($stmt->execute()) {
            $message = "Produk berhasil diperbarui!";
            header("refresh:2;url=detail.php?id=" . $id);
        } else {
            $error = "Gagal memperbarui produk!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1>Edit Produk ✏</h1>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form">

            <div class="form-group">
                <label>Nama Produk:</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($produk['nama']) ?>" required>
            </div>

            <div class="form-group">
                <label>Deskripsi:</label>
                <textarea name="deskripsi" rows="5"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Harga (Rp):</label>
                <input type="number" name="harga" step="0.01" value="<?= $produk['harga'] ?>" required>
            </div>

            <div class="form-group">
                <label>Foto Saat Ini:</label>
                <?php if ($produk['foto']): ?>
                    <img src="uploads/<?= $produk['foto'] ?>" width="200" style="border-radius:8px;">
                <?php else: ?>
                    <p>Tidak ada foto</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Upload Foto Baru (opsional):</label>
                <input type="file" name="foto" accept="image/*">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Update</button>
                <a href="detail.php?id=<?= $id ?>" class="btn btn-secondary">Batal</a>
            </div>

        </form>
    </div>
</body>

</html>
