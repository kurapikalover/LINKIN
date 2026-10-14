<?php
require_once __DIR__ . '/../php/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>ÜBuddy</title>

  <!-- Home and notification icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=home,notifications,search,filter_alt">

  <!-- Base CSS -->
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/shared.css">
  <link rel="stylesheet" href="../css/club_page.css">
</head>

<body>

  <?php pageHeader(); ?>

  <!-- =========================
        PAGE CONTENT
        Individual pages go here
  ========================== -->
  <main class="page-content">

    <div class="clubs-page">

      <a href="../pages/homepage.php" class="back-button">← Home</a>

      <h1 class="clubs-title">Clubs</h1>

      <div class="search-bar">

        <div class="search-input">

          <span class="material-symbols-outlined search-icon">
            search
          </span>

          <input
            type="text"
            id="searchInput"
            placeholder="Search"
          >

        </div>

        <button
          type="button"
          id="filterBtn"
          class="filter-btn material-symbols-outlined"
          title="Filter by category"
        >
          filter_alt
        </button>

      </div>

      <div class="filter-menu" id="filterMenu">

        <button type="button" class="filter-option active" data-category="all">
          All
        </button>

        <button type="button" class="filter-option" data-category="Sports">
          Sports
        </button>

        <button type="button" class="filter-option" data-category="Arts and Crafts">
          Arts and Crafts
        </button>

        <button type="button" class="filter-option" data-category="Recreational">
          Recreational
        </button>

        <button type="button" class="filter-option" data-category="Culture">
          Culture
        </button>

        <button type="button" class="filter-option" data-category="Religious">
          Religious
        </button>

      </div>


      <div class="club-grid">

        <a href="#" class="club-card">
          <div class="club-card-image">
            Club Image
          </div>

          <h2>Swimming Club</h2>
          <p class="category category-sports">Sports</p>
        </a>

        <a href="#" class="club-card">
          <div class="club-card-image">
            Club Image
          </div>

          <h2>Japan Culture Club</h2>
          <p class="category category-culture">Culture</p>
        </a>

        <a href="#" class="club-card">
          <div class="club-card-image">
            Club Image
          </div>

          <h2>Gardening Club</h2>
          <p class="category category-recreational">Recreational</p>
        </a>

        <a href="#" class="club-card">
          <div class="club-card-image">
            Club Image
          </div>

          <h2>Crochet Society Club</h2>
          <p class="category category-arts">Arts and Crafts</p>
        </a>

        <a href="#" class="club-card">
          <div class="club-card-image">
            Club Image
          </div>

          <h2>BJFA Club</h2>
          <p class="category category-culture">Culture</p>
        </a>

        <a href="../pages/club.php" class="club-card">
          <div class="club-card-image">
            Club Image
          </div>

          <h2>Muslim Youth Club</h2>
          <p class="category category-religious">Religious</p>
        </a>

      </div>

    </div>

  </main>

  <?php pageFooter(); ?>

  <script src="../js/club_page.js"></script>

</body>
</html>