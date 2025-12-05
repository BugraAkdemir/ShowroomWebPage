<?php
// Veritabanı bağlantısı
require_once "../config.php";

// Resimlerin yükleneceği klasör
$uploadDir = "../uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Slug oluşturma fonksiyonu (URL için)
function slugify($string) {
    $string = mb_strtolower($string, 'UTF-8');
    $string = preg_replace('/[^\p{L}\p{Nd}]+/u', '-', $string);
    $string = trim($string, '-');
    return $string;
}

// Mesajlar
$success = $error = "";

// Kategori ekleme
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['kategori_adi'])) {
    $kategori_adi = trim($_POST["kategori_adi"]);
    $aciklama     = trim($_POST["aciklama"]);

    if (!empty($kategori_adi) && isset($_FILES["kategori_resim"])) {
        // Aynı kategori var mı kontrol et
        $check = $conn->prepare("SELECT id FROM kategoriler WHERE kategori_adi = ?");
        $check->bind_param("s", $kategori_adi);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Bu kategori zaten mevcut!";
        } else {
            // Resim yükleme
            $fileName = basename($_FILES["kategori_resim"]["name"]);
            $targetFile = $uploadDir . time() . "_" . $fileName;

            if (move_uploaded_file($_FILES["kategori_resim"]["tmp_name"], $targetFile)) {
                $kategori_resim = "uploads/" . basename($targetFile); // DB için düzgün yol
                $url = slugify($kategori_adi);

                // DB kaydı
                $stmt = $conn->prepare("INSERT INTO kategoriler (kategori_adi, kategori_resim, url, aciklama) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $kategori_adi, $kategori_resim, $url, $aciklama);

                if ($stmt->execute()) {
                    $success = "Kategori başarıyla eklendi ✅";
                } else {
                    $error = "Hata: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Resim yüklenirken bir hata oluştu!";
            }
        }
        $check->close();
    } else {
        $error = "Lütfen kategori adı ve resim seçiniz!";
    }
}

// Kategori silme
if (isset($_GET['delete_kategori'])) {
    $id = intval($_GET['delete_kategori']);
    $conn->query("DELETE FROM kategoriler WHERE id = $id");
    header("Location: index.php");
    exit;
}

// Ürün silme
if (isset($_GET['delete_urun'])) {
    $id = intval($_GET['delete_urun']);

    // Önce resim kayıtlarını çek
    $resimler = $conn->query("SELECT resim_yolu FROM urun_resimler WHERE urun_id = $id");
    while ($row = $resimler->fetch_assoc()) {
        $filePath = "../" . $row['resim_yolu']; // uploads/ yolunu bul
        if (file_exists($filePath)) {
            unlink($filePath); // Dosyayı sil
        }
    }

    // DB'den resim kayıtlarını sil
    $conn->query("DELETE FROM urun_resimler WHERE urun_id = $id");

    // Sonra ürünü sil
    $conn->query("DELETE FROM urunler WHERE id = $id");

    header("Location: index.php?msg=urun_silindi");
    exit;
}


// Ürün ekleme
if(isset($_POST['urun_ekle'])){
    $kategori_id = intval($_POST['kategori_id']);
    $urun_adi = trim($_POST['urun_adi']);
    $aciklama = trim($_POST['aciklama']);

    $stmt = $conn->prepare("INSERT INTO urunler (kategori_id, urun_adi, aciklama) VALUES (?,?,?)");
    $stmt->bind_param("iss", $kategori_id, $urun_adi, $aciklama);
    $stmt->execute();
    $urun_id = $stmt->insert_id;

    foreach($_FILES['urun_resimler']['tmp_name'] as $i => $tmp){
    if(is_uploaded_file($tmp)){
        $fileName = time()."_".$_FILES['urun_resimler']['name'][$i];
        
        // Fiziksel yükleme yolu (sunucuya)
        $targetPath = "../uploads/".$fileName;
        
        // Veritabanı için yol (tarayıcıdan erişim için)
        $dbPath = "uploads/".$fileName;

        if(move_uploaded_file($tmp, $targetPath)){
            $r = $conn->prepare("INSERT INTO urun_resimler (urun_id, resim_yolu) VALUES (?,?)");
            $r->bind_param("is", $urun_id, $dbPath);
            $r->execute();
        }
    }
}


    echo "<div class='alert alert-success'>Ürün eklendi ✅</div>";
}




// Kategorileri çek
$kategoriler = $conn->query("SELECT * FROM kategoriler ORDER BY id DESC");

// Ürünleri çek
$urunler = $conn->query("SELECT ur.*, kat.kategori_adi FROM urunler ur LEFT JOIN kategoriler kat ON ur.kategori_id = kat.id ORDER BY ur.id DESC");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel</title>
<link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">

    <div class="card shadow p-4 mb-5">
        <h2 class="mb-4 text-center">Kategori Ekle</h2>

        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Kategori Adı</label>
                <input type="text" name="kategori_adi" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Kategori Resmi</label>
                <input type="file" name="kategori_resim" class="form-control" accept="image/*" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Açıklama</label>
                <textarea name="aciklama" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Kategori Ekle</button>
        </form>
    </div>

    <h3>Kategoriler</h3>
    <table class="table table-bordered mb-5">
        <tr>
            <th>ID</th>
            <th>Adı</th>
            <th>Resim</th>
            <th>URL</th>
            <th>Açıklama</th>
            <th>İşlemler</th>
        </tr>
        <?php while ($row = $kategoriler->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['kategori_adi']) ?></td>
            <td><img src="../<?= $row['kategori_resim'] ?>" width="80"></td>
            <td><?= htmlspecialchars($row['url']) ?></td>
            <td><?= htmlspecialchars($row['aciklama']) ?></td>
            <td>
                <a href="index.php?delete_kategori=<?= $row['id'] ?>" class="btn btn-sm btn-danger" >Sil</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h3>Ürün Ekle</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Kategori Seç</label>
            <select name="kategori_id" class="form-control" required>
                <option value="">Seçiniz</option>
                <?php
                $catRes = $conn->query("SELECT * FROM kategoriler ORDER BY kategori_adi ASC");
                while ($cat = $catRes->fetch_assoc()):
                ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['kategori_adi']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
        <label>Ürün Adı</label>
        <input type="text" name="urun_adi" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Ürün Resimleri</label>
        <input type="file" name="urun_resimler[]" class="form-control" accept="image/*" multiple required>
    </div>
    <div class="mb-3">
        <label>Açıklama</label>
        <textarea name="aciklama" class="form-control"></textarea>
    </div>
    <button type="submit" name="urun_ekle" class="btn btn-primary">Ürün Ekle</button>
    </form>

    <h3 class="mt-5">Ürünler</h3>
    <table class="table table-bordered mb-5">
        <tr>
            <th>ID</th>
            <th>Ürün Adı</th>
            <th>Kategori</th>
            <th>Resim</th>
            <th>Açıklama</th>
            <th>İşlemler</th>
        </tr>
        <?php while($u = $urunler->fetch_assoc()): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['urun_adi']) ?></td>
            <td><?= htmlspecialchars($u['kategori_adi']) ?></td>
            <td><img src="../<?= $u['urun_resim'] ?>" width="80"></td>
            <td><?= htmlspecialchars($u['aciklama']) ?></td>
            <td>
                <a href="index.php?delete_urun=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Silmek istediğine emin misin?')">Sil</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

</div>
</body>
</html>
