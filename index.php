<?php
include "config.php"; // Veritabanı bağlantısı

$sql = "SELECT * FROM kategoriler";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendoteks</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/templatemo-style.css">
</head>
<body>
    <!-- Page Loader -->
    <div id="loader-wrapper">
        <div id="loader"></div>
        <div class="loader-section section-left"></div>
        <div class="loader-section section-right"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="\img\logo.png" alt="logo" style="width: 180px; height: 60px;">
                <!--s Vendoteks -->
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" 
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ml-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link nav-link-1 active" href="index.php">Ana Sayfa</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-2" href="urun.php">Ürünler</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-3" href="about.html">Hakkımızda</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-4" href="contact.html">İletişim</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <div class="tm-hero d-flex justify-content-center align-items-center" data-parallax="scroll" data-image-src="img/hero.jpg">
        <form class="d-flex tm-search-form">
            <input class="form-control tm-search-input" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-success tm-search-btn" type="submit">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <!-- Kategoriler -->
    <div class="container-fluid tm-container-content tm-mt-60">
        <div class="row mb-4">
            <h2 class="col-6 tm-text-primary">Kategoriler</h2>
        </div>

        <div class="row tm-mb-90 tm-gallery">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
            ?>
            <div class="col-6 col-md-6 col-lg-4 col-xl-3 mb-5">
                <h4 style="color: #05A253;"><?= htmlspecialchars($row['kategori_adi']); ?></h4>
                <figure class="effect-ming tm-video-item">
                    <img src="<?= htmlspecialchars($row['kategori_resim']); ?>" 
                        alt="<?= htmlspecialchars($row['kategori_adi']); ?>" 
                        class="img-fluid">
                    <figcaption class="d-flex align-items-center justify-content-center">
                        <h2><?= htmlspecialchars($row['kategori_adi']); ?></h2>
                        <a href="urun.php?kategori=<?= urlencode($row['url']) ?>">View more</a>

                    </figcaption>
                </figure>
                <div class="d-flex justify-content-between tm-text-gray">
                    <span><?= htmlspecialchars($row['aciklama']); ?></span>
                </div>
            </div>
            <?php 
                } // while sonu
            } else {
                echo "<p>Hiç kategori bulunamadı.</p>";
            }
            ?>
        </div>
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
    
    <script src="js/plugins.js"></script>
    <script>
        $(window).on("load", function() {
            $('body').addClass('loaded');
        });
    </script>
</body>
</html>
