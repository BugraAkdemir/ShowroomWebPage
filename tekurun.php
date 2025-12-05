<?php
require_once "config.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ürün bilgisi
$stmt = $conn->prepare("SELECT u.*, k.kategori_adi 
                        FROM urunler u 
                        LEFT JOIN kategoriler k ON u.kategori_id = k.id 
                        WHERE u.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$urun = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$urun){
    die("Ürün bulunamadı!");
}

// Ürün fotoğrafları
$res = $conn->prepare("SELECT resim_yolu FROM urun_resimler WHERE urun_id = ? ORDER BY id ASC");
$res->bind_param("i", $id);
$res->execute();
$result = $res->get_result();
$resimler = [];
while($r = $result->fetch_assoc()){
    $resimler[] = $r['resim_yolu'];
}
$res->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog-Z Photo Detail Page</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/templatemo-style.css">

</head>
<body>
    <!-- Page Loader -->
    <!-- <div id="loader-wrapper">
        <div id="loader"></div>

        <div class="loader-section section-left"></div>
        <div class="loader-section section-right"></div>

    </div> -->
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

    <!-- <div class="tm-hero d-flex justify-content-center align-items-center" data-parallax="scroll" data-image-src="img/hero.jpg">
        <form class="d-flex tm-search-form">
            <input class="form-control tm-search-input" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-success tm-search-btn" type="submit">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div> -->

<style>
.img-thumbnail.active {
    border: 2px solid #009999 !important;
}

</style>


    <div class="container-fluid tm-container-content tm-mt-60">
    <div class="row mb-4">
        <h2 class="col-12 tm-text-primary"><?= htmlspecialchars($urun['urun_adi']) ?></h2>
    </div>
    <div class="row tm-mb-90">
        <!-- Sol taraf: Resimler -->
        <div class="col-xl-8 col-lg-7 col-md-6 col-sm-12">

            <?php if(count($resimler) > 0): ?>
            <!-- Carousel -->
            <div id="urunCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach($resimler as $i=>$resim): ?>
                        <div class="carousel-item <?= $i==0 ? 'active' : '' ?>">
                            <img src="<?= htmlspecialchars($resim) ?>" 
                                 alt="<?= htmlspecialchars($urun['urun_adi']) ?>" 
                                 class="d-block w-100 img-fluid">
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if(count($resimler) > 1): ?>
                <!-- Oklar -->
                <button class="carousel-control-prev" type="button" data-bs-target="#urunCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#urunCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
                <?php endif; ?>
            </div>

            <?php if(count($resimler) > 1): ?>
            <!-- Thumbnail'ler -->
            <div class="d-flex justify-content-center mt-3 flex-wrap gap-2">
                <?php foreach($resimler as $j=>$thumb): ?>
                    <img src="<?= htmlspecialchars($thumb) ?>" 
                         style="width:80px; height:60px; object-fit:cover; cursor:pointer;" 
                         data-bs-target="#urunCarousel" 
                         data-bs-slide-to="<?= $j ?>"
                         class="img-thumbnail <?= $j==0?'active':'' ?>">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php else: ?>
                <p>Bu ürüne ait resim bulunamadı.</p>
            <?php endif; ?>

        </div>

        <!-- Sağ taraf: Açıklama -->
        <div class="col-xl-4 col-lg-5 col-md-6 col-sm-12">
            <div class="tm-bg-gray tm-video-details p-3">
                <p class="mb-4"><?= nl2br(htmlspecialchars($urun['aciklama'])) ?></p>
                <div class="mb-4">
                    <h3 class="tm-text-gray-dark mb-3">Kategori</h3>
                    <p><?= htmlspecialchars($urun['kategori_adi'] ?? '-') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>



        <div class="row mb-4">
    <h2 class="col-12 tm-text-primary">Related Photos</h2>
</div>
<div class="row mb-3 tm-gallery">
    <?php
    // Bu ürünü hariç tutarak rastgele 8 ürün getir
    $related = $conn->query("
        SELECT u.id, u.urun_adi,
               (SELECT resim_yolu FROM urun_resimler WHERE urun_id = u.id LIMIT 1) AS resim
        FROM urunler u
        WHERE u.id != {$urun['id']}
        ORDER BY RAND()
        LIMIT 8
    ");

    if ($related && $related->num_rows > 0) {
        while ($urun2 = $related->fetch_assoc()) {
            $resim = !empty($urun2['resim']) ? htmlspecialchars($urun2['resim']) : 'img/defaulturun.png';
    ?>
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-5">
            <figure class="tm-video-item" style="text-align:center;">
                <img src="<?= $resim ?>" alt="<?= htmlspecialchars($urun2['urun_adi']) ?>" class="img-fluid" style="border-radius:5px;">
                <a href="tekurun.php?id=<?= $urun2['id'] ?>" class="stretched-link"></a>
            </figure>
            <div class="mt-2 text-center">
                <h5 class="text-dark" style="font-size:1rem; font-weight:600;">
                    <?= htmlspecialchars($urun2['urun_adi']) ?>
                </h5>
            </div>
        </div>
    <?php
        }
    } else {
        // Eğer hiç ürün yoksa 4 varsayılan ürün göster
        for ($i = 0; $i < 4; $i++) {
            echo '
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-5">
                <figure class="tm-video-item">
                    <img src="img/defaulturun.png" alt="Varsayılan Ürün" class="img-fluid">
                </figure>
                <div class="mt-2 text-center">
                    <h5 class="text-muted" style="font-size:1rem;">Varsayılan Ürün</h5>
                </div>
            </div>';
        }
    }
    ?>
</div>


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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/plugins.js"></script>
    <script>
        $(window).on("load", function() {
            $('body').addClass('loaded');
        });
    </script>

    <script>
// Thumbnail aktif class güncelleme
document.addEventListener('DOMContentLoaded', function () {
    const thumbs = document.querySelectorAll('.img-thumbnail[data-bs-slide-to]');
    const carousel = document.getElementById('urunCarousel');
    thumbs.forEach(t => {
        t.addEventListener('click', function () {
            thumbs.forEach(x => x.classList.remove('active'));
            this.classList.add('active');
        });
    });
    if (carousel) {
        carousel.addEventListener('slid.bs.carousel', function (e) {
            thumbs.forEach(x => x.classList.remove('active'));
            if (thumbs[e.to]) thumbs[e.to].classList.add('active');
        });
    }
});
</script>
<style>
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
<a href="https://wa.me/905392632614" target="_blank" id="whatsapp-btn"><i class="fab fa-whatsapp"></i></a>
</body>
</html>