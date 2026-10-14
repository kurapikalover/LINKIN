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
  <link rel="stylesheet" href="../css/event_page.css">
  
</head>

<body>

  <?php pageHeader(); ?>

  <div class="sheet">
    <!-- =========================
          PAGE CONTENT
          Individual pages go here
    ========================== -->
    <main class="page-content">

      <div class="events-page">

        <a href="../pages/homepage.php" class="back-button">← Home</a>

        <h1 class="events-title">Events</h1>

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
            class="filter-btn material-symbols-outlined"
            title="Filter by category"
          >
            filter_alt
          </button>

        </div>

        <div class="event-grid">

          <a href="#" class="event-card">
            <div class="event-card-image">
              Event Image
            </div>

            <h2>Opening Ceremony</h2>
          </a>

          <a href="#" class="event-card">
            <div class="event-card-image">
              Event Image
            </div>

            <h2>Yukata Workshop</h2>
          </a>

          <a href="#" class="event-card">
            <div class="event-card-image">
              Event Image
            </div>

            <h2>Silat Workshop</h2>
          </a>

          <a href="#" class="event-card">
            <div class="event-card-image">
              Event Image
            </div>

            <h2>Origami Workshop</h2>
          </a>

          <a href="#" class="event-card">
            <div class="event-card-image">
              Event Image
            </div>

            <h2>Movie Night</h2>
          </a>

        </div>

      </div>

    </main>

  <?php pageFooter(); ?>
  <script src="../js/event_page.js"></script>
</body>
</html>