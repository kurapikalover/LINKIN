<?php
require_once __DIR__ . '/../php/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>UBuddy - Home</title>

  <?php pageIcons(); ?>

  <!-- Your own styles (separate from the login page's style.css) -->
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/shared.css">
  <link rel="stylesheet" href="../css/home.css">

</head>


<body>
  <div class="page">

  <?php pageHeader(); ?>

  <div class="sheet">

    <!-- 3. LATEST NEWS BANNER -->
    <section class="news">
      <button class="arrow" id="newsPrev">‹</button>

      <div class="news-banner" id="newsBanner">
        <h2>Latest News</h2>
        <p id="newsText"></p>
        <button class="btn">View Details</button>
      </div>

      <button class="arrow" id="newsNext">›</button>
    </section>
    <div class="dots" id="newsDots"></div>

    <!-- 4. SEARCH -->
    <h1 class="main-title">Check out all the buddies!</h1>
    <div class="content">
      <div class="search-bar">
        <div class="search-input">
          <span class="material-symbols-outlined search-icon">
            search
          </span>
          <input type="text" id="searchInput" placeholder="Search">
        </div>
        <button class="filter-btn material-symbols-outlined">
          filter_alt
        </button>
      </div>
      
      <!-- 5. CLUBS ROW -->
      <div class="row-header">
        <h2>Clubs</h2>
        <a href="../pages/club_page.php" class="btn-small">See All</a>
      </div>
      <div class="row-wrap">
        <button class="arrow" data-target="clubsRow" data-dir="-1">‹</button>
        <div class="card-row" id="clubsRow"></div>
        <button class="arrow" data-target="clubsRow" data-dir="1">›</button>
      </div>

      <!-- 6. EVENTS ROW -->
      <div class="row-header">
        <h2>Events</h2>
        <a href="../pages/event_page.php" class="btn-small">See All</a>
      </div>
      <div class="row-wrap">
        <button class="arrow" data-target="eventsRow" data-dir="-1">‹</button>
        <div class="card-row" id="eventsRow"></div>
        <button class="arrow" data-target="eventsRow" data-dir="1">›</button>
      </div>
    </div>

    <!-- 7. FEEDBACK -->
    <section class="feedback">
      <h2>We'd love to hear your voice</h2>
      <p>Help us make this website better by voicing your thoughts!</p>
      <button class="btn btn-large">Leave Feedback</button>
      <small>Any suggestions? Complaints? Bugs? We got you, buddy!</small>
    </section>

<?php pageFooter(); ?>

  <!-- Your own script (separate from the login page's script.js) -->
  <script src="../js/home.js"></script>
</body>
</html>