<?php
require_once "config.php";

$kategori_slug = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$allKategori = $conn->query("SELECT * FROM kategoriler ORDER BY kategori_adi ASC");

$sql = "SELECT u.*, k.kategori_adi, k.url AS kategori_url, 
        (SELECT resim_yolu FROM urun_resimler r WHERE r.urun_id = u.id ORDER BY r.id ASC LIMIT 1) AS kapak_resim
        FROM urunler u 
        INNER JOIN kategoriler k ON u.kategori_id = k.id";


$params = [];
if ($kategori_slug) {
    $sql .= " WHERE k.url = ?";
    $params[] = $kategori_slug;
}
if ($search) {
    $sql .= $kategori_slug ? " AND u.urun_adi LIKE ?" : " WHERE u.urun_adi LIKE ?";
    $params[] = "%$search%";
}
$sql .= " ORDER BY u.id DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ürünler</title>
<link rel="stylesheet" href="css/bootstrap.min.css">
 <link rel="stylesheet" href="css/templatemo-style.css">
 <link rel="stylesheet" href="fontawesome/css/all.min.css"> 
<style>

body{
    background-color: #fff;
}



.tm-video-item img { width:100%; transition: none; }
.tm-video-item figcaption { display:none; }

.product-name { font-weight:bold; margin-top:10px; color:#009999; }
.product-desc { font-size:0.9rem; color:#666; }

.tm-text-primary { color:#009999; font-weight:bold; margin-bottom:15px; }


.tm-search-input {
    width: 100%;          /* her ekrana uysun */
    max-width: 300px;     /* PC’de 300px’i geçmesin */
    border-radius: 5px 0 0 5px;
    border: 1px solid #ccc;
    padding: 10px;
    box-sizing: border-box; /* taşmayı tamamen engeller */
}

.tm-search-wrapper {
    display: flex;
    justify-content: flex-end; /* varsayılan: sağa */
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 20px;
}

/* Mobilde ortala */
@media (max-width: 767px) {
    .tm-search-wrapper {
        justify-content: center;
    }
}

@media (max-width: 991px) {
    .navbar-collapse {
        position: right;
        top: 56px; /* navbar yüksekliği */
        right: 0;
        left: 0;
        background: #fff;
        z-index: 1050;
        padding: 10px;
    }
}


/* .tm-search-btn {
    border: none;
    border-radius: 0 5px 5px 0;
    background: #009999;
    color: white;
    padding: 10px 20px;
    flex-shrink: 0; /* mobilde daralmasını engeller */
} */



.list-group-item a { text-decoration:none; color:#05A253; display:block; }
.list-group-item.active a { color:#fff; background-color: ; font-weight:bold; }


#whatsapp-btn {
    position:fixed;
    bottom:20px;
    right:20px;
    width:60px;
    height:60px;
    border-radius:50%;
    background:#25D366;
    display:flex;
    justify-content:center;
    align-items:center;
    box-shadow:0 4px 6px rgba(0,0,0,0.3);
    z-index:1000;
}
#whatsapp-btn i { color:white; font-size:28px; }
#whatsapp-btn:hover { transform:scale(1.1); transition:0.3s; }
</style>
</head>
<body>


<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #ccc;">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      <img src="img/logo.png" alt="logo" style="width: 175px; height: 55px;">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
      data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" 
      aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="index.php">Ana Sayfa</a></li>
        <li class="nav-item"><a class="nav-link" href="urun.php">Ürünler</a></li>
        <li class="nav-item"><a class="nav-link" href="about.html">Hakkımızda</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.html">İletişim</a></li>
      </ul>
    </div>
  </div>
</nav>



<div class="container-fluid tm-container-content tm-mt-60">
    <div class="row">
        <!-- Kategori -->
        <div class="col-lg-3 mb-4">
            <h4 class="tm-text-primary">Kategoriler</h4>
            <ul class="list-group">
                <li class="list-group-item <?= $kategori_slug==''?'active':'' ?>"><a href="urun.php">Tüm Kategoriler</a></li>
                <?php while($cat = $allKategori->fetch_assoc()): ?>
                <li class="list-group-item <?= $kategori_slug==$cat['url']?'active':'' ?>">
                    <a href="urun.php?kategori=<?= htmlspecialchars($cat['url']) ?>" style=><?= htmlspecialchars($cat['kategori_adi']) ?></a>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <!-- Ürünler ve arama -->
        <div class="col-lg-9">
            <div class="tm-search-wrapper">
                <form method="GET" class="d-flex">
                    <?php if($kategori_slug): ?>
                        <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori_slug) ?>">
                    <?php endif; ?>
                    <input type="text" name="search" class="tm-search-input" placeholder="Ürün ara..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="tm-search-btn"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="row">
    <?php if($result->num_rows>0): ?>
        <?php while($urun=$result->fetch_assoc()): ?>
           <div class="col-6 col-md-4 mb-4">
    <div class="product-card">
        <a href="tekurun.php?id=<?= $urun['id'] ?>">
           <?php if(!empty($urun['kapak_resim'])): ?>
    <img src="<?= htmlspecialchars($urun['kapak_resim']) ?>" alt="<?= htmlspecialchars($urun['urun_adi']) ?>" class="img-fluid">
<?php else: ?>
    <img src="img/no-image.png" alt="Resim Yok" class="img-fluid"> <!-- yedek resim -->
<?php endif; ?>

        </a>
        <div class="product-name"><?= htmlspecialchars($urun['urun_adi']) ?></div>
        <div class="product-desc"><?= htmlspecialchars($urun['aciklama']) ?></div>
    </div>
</div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Hiç ürün bulunamadı.</p>
    <?php endif; ?>
</div>

        </div>
    </div>
</div>

<!-- WhatsApp -->
<a href="https://wa.me/905392632614" target="_blank" id="whatsapp-btn"><i class="fab fa-whatsapp"></i></a>

<script src="js/plugins.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Footer -->
    <footer class="tm-bg-gray pt-5 pb-3 tm-text-gray tm-footer">
        <div class="container-fluid tm-container-small">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-12 px-5 mb-5">
                    <h3 class="tm-text-primary mb-4 tm-footer-title">Vendoteks Hakkında</h3>
                    <p> 

2014 yılında başladığımız konfeksiyon üretimi, 2020 yılında ÇATI markası ile birlikte medikal iş kıyafetleri imalatına evrildi.

Çatı Medikal olarak asıl amacımız; ünvan, eğitim, cinsiyet farketmeksizin tüm sağlık çalışanlarına yüksek kaliteyi uygun fiyatlarla sunmaktır. 

Yıllarca edindiğimiz tecrübe, uzman personel ve kaliteli hammadde ile buluşarak sizlerin beğenisine sunuldu.

Vendoteks' de kalite bir seçenek değil ZORUNLULUKTUR!</p>
                </div>
                <!-- <div class="col-lg-3 col-md-6 col-sm-6 col-12 px-5 mb-5">
                    <h3 class="tm-text-primary mb-4 tm-footer-title">Our Links</h3>
                    <ul class="tm-footer-links pl-0">
                        <li><a href="#">Advertise</a></li>
                        <li><a href="#">Support</a></li>
                        <li><a href="#">Our Company</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div> -->
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 px-5 mb-5">
                    <ul class="tm-social-links d-flex justify-content-end pl-0 mb-5">
                        <li class="mb-2"><a href="https://facebook.com"><i class="fab fa-facebook"></i></a></li>
                        <li class="mb-2"><a href="https://twitter.com"><i class="fab fa-twitter"></i></a></li>
                        <li class="mb-2"><a href="https://instagram.com"><i class="fab fa-instagram"></i></a></li>
                        <li class="mb-2"><a href="https://pinterest.com"><i class="fab fa-pinterest"></i></a></li>
                    </ul>
                    <!-- <a href="#" class="tm-text-gray text-right d-block mb-2">Terms of Use</a>
                    <a href="#" class="tm-text-gray text-right d-block">Privacy Policy</a> -->
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 col-md-7 col-12 px-5 mb-3">
                    Copyright 2025 Vendoteks Company. All rights reserved.
                </div>
                <div class="col-lg-4 col-md-5 col-12 px-5 text-right">
                    Designed by <a href="" class="tm-text-gray" rel="sponsored" target="_parent">Bugra Akdemir</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
