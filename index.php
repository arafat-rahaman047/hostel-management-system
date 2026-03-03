<?php include "includes/header.php"; ?>

<div id="hallCarousel" class="carousel slide carousel-fade shadow-sm" data-bs-ride="carousel">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#hallCarousel" data-bs-slide-to="0" class="active"></button>
    <button type="button" data-bs-target="#hallCarousel" data-bs-slide-to="1"></button>
    <button type="button" data-bs-target="#hallCarousel" data-bs-slide-to="2"></button>
  </div>
  <div class="carousel-inner">
    <div class="carousel-item active" style="height: 500px;">
      <img src="assets/images/hall1.jpg" class="d-block w-100 h-100 object-fit-cover" alt="BU Hall 1">
      <div class="carousel-caption d-none d-md-block p-4"
        style="background: rgba(0, 51, 102, 0.7); border-radius: 15px;">
        <h2 class="fw-bold">Welcome to University of Barishal</h2>
        <p>Comfortable and secure residential facilities for students.</p>
      </div>
    </div>
    <div class="carousel-item" style="height: 500px;">
      <img src="assets/images/hall2.jpg" class="d-block w-100 h-100 object-fit-cover" alt="BU Hall 2">
      <div class="carousel-caption d-none d-md-block p-4"
        style="background: rgba(0, 51, 102, 0.7); border-radius: 15px;">
        <h2 class="fw-bold">Modern Amenities</h2>
        <p>Equipped with high-speed internet and study friendly environment.</p>
      </div>
    </div>
    <div class="carousel-item" style="height: 500px;">
      <img src="assets/images/hall3.jpg" class="d-block w-100 h-100 object-fit-cover" alt="BU Hall 3">
      <div class="carousel-caption d-none d-md-block p-4"
        style="background: rgba(0, 51, 102, 0.7); border-radius: 15px;">
        <h2 class="fw-bold">Comfort Zone</h2>
        <p>Where you can find yourself in nature</p>
      </div>
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#hallCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#hallCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>

<!-- id="halls" added so sidebar "Hostels & Halls" link scrolls here -->
<section class="py-5 bg-light" id="halls">
  <div class="container">
    <div class="text-center mb-5">
      <h6 class="text-primary text-uppercase fw-bold">Accommodation</h6>
      <h2 class="fw-bold display-6">List of Halls of Residence</h2>
      <div class="mx-auto border-bottom border-primary border-3" style="width: 80px;"></div>
    </div>

    <div class="row g-4">
      <?php
      $halls = [
        ['name' => 'Bijoy-24 Hall',            'img' => 'assets/images/logo.png'],
        ['name' => 'Shere Bangla Hall',         'img' => 'assets/images/logo.png'],
        ['name' => 'Kabi Sufia Kamal Hall',     'img' => 'assets/images/logo.png'],
        ['name' => 'Tapashi Rabeya Basri Hall', 'img' => 'assets/images/logo.png'],
      ];

      foreach ($halls as $hall): ?>
        <div class="col-lg-3 col-md-6">
          <div class="card h-100 border-0 shadow-sm hall-card text-center p-4">
            <div class="card-img-container mb-3">
              <img src="<?php echo $hall['img']; ?>" alt="BU Logo" class="img-fluid"
                style="height: 120px; transition: 0.4s;">
            </div>
            <div class="card-body p-0">
              <h5 class="card-title fw-bold mb-4"><?php echo $hall['name']; ?></h5>
              <a href="hall_details.php?name=<?php echo urlencode($hall['name']); ?>"
                class="btn btn-outline-primary rounded-pill px-4 view-btn">
                View <i class="fas fa-arrow-right ms-1" style="font-size: 0.8rem;"></i>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include "includes/footer.php"; ?>