<?php
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/db.php';

$sql = "
    SELECT
        c.club_id,
        c.club_name,
        cat.category_name
    FROM public.club AS c
    LEFT JOIN public.category AS cat
        ON c.category_id = cat.category_id
    ORDER BY c.club_name;
";

$stmt = $pdo->query($sql);
$clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categoryClasses = [
    'Sports' => 'category-sports',
    'Culture' => 'category-culture',
    'Recreational' => 'category-recreational',
    'Arts & Creative' => 'category-arts',
    'Religious' => 'category-religious',
    'Community Service' => 'category-community'
];
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>ÜBuddy</title>

  <?php pageIcons(); ?>

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

      <?php backLink('homepage.php', 'Home'); ?>

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

        <button type="button" class="filter-option" data-category="Culture">
            Culture
        </button>

        <button type="button" class="filter-option" data-category="Recreational">
            Recreational
        </button>

        <button type="button" class="filter-option" data-category="Arts & Creative">
            Arts &amp; Creative
        </button>

        <button type="button" class="filter-option" data-category="Religious">
            Religious
        </button>

        <button type="button" class="filter-option" data-category="Community Service">
            Community Service
        </button>

      </div>


      <div class="club-grid">

          <?php foreach ($clubs as $club): ?>

              <?php
              $clubName = $club['club_name'];
              $categoryName = $club['category_name'] ?? 'Uncategorised';
              $categoryClass = $categoryClasses[$categoryName] ?? '';
              ?>

              <a

              href="club.php?id=<?= (int) $club['club_id'] ?>"
              class="club-card"
              data-category="<?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?>"

              >

                  <div class="club-card-image">
                      Club Image
                  </div>

                  <h2>
                      <?= htmlspecialchars($clubName, ENT_QUOTES, 'UTF-8') ?>
                  </h2>

                  <p class="category <?= htmlspecialchars($categoryClass, ENT_QUOTES, 'UTF-8') ?>">
                      <?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?>
                  </p>

              </a>

          <?php endforeach; ?>

      </div>

    </div>

  </main>

  <?php pageFooter(); ?>

  <script src="../js/club_page.js"></script>

</body>
</html>