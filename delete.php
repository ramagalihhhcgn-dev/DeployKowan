<?php
require_once 'config/database.php';
require_once 'classes/Produk.php';

// Ambil ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$produkObj = new Produk();
$produk = $produkObj->readOne($id);

if (!$produk) {
    header("Location: index.php");
    exit();
}

$conn = (new Database())->getConnection();

// Hapus foto jika ada
if (!empty($produk['foto']) && file_exists("uploads/" . $produk['foto'])) {
    unlink("uploads/" . $produk['foto']);
}

// Hapus dari database
$query = "DELETE FROM produk WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "<script>alert('Produk berhasil dihapus!'); window.location='index.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus produk!'); window.location='index.php';</script>";
}
?>
