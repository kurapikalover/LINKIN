
<?php
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/club_queries.php';
require_once __DIR__ . '/../php/event_queries.php';

$latestNews = getLatestNews($pdo);
$allClubs = getAllClubs($pdo);
$allEvents = getAllEvents($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>ÜBuddy - Home</title>

  <?php pageIcons(); ?>

  <!-- Stylesheets -->
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/shared.css">
  <link rel="stylesheet" href="../css/home.css">
  <link rel="stylesheet" href="../css/news.css">
  <link rel="stylesheet" href="../css/filter.css">
</head>

<body>
 

  <?php pageHeader(); ?>



    <!-- 1. LATEST NEWS BANNER -->
    <section class="news">

      <button
        type="button"
        class="arrow"
        id="newsPrev"
        aria-label="Previous news item"
      >‹</button>

      <div class="news-banner" id="newsBanner">

        <div class="news-content">

          <div class="news-heading">

            <div class="news-icon">
              <span class="material-symbols-outlined">
                campaign
              </span>
            </div>

            <div class="news-title-group">
              <span class="news-eyebrow">
                ÜBUDDY · LATEST NEWS
              </span>

              <h2 id="newsTitle">Latest News</h2>
            </div>

          </div>

          <p id="newsText"></p>

          <div class="news-bottom">

            <span class="news-type" id="newsType">
              Announcement
            </span>

            <button
              type="button"
              class="btn news-details-btn"
              id="newsDetailsBtn"
            >
              View Details

              <span class="material-symbols-outlined">
                arrow_outward
              </span>
            </button>

          </div>

        </div>

      </div>

      <button
        type="button"
        class="arrow"
        id="newsNext"
        aria-label="Next news item"
      >›</button>

    </section>

    <div class="news-pagination">
      <div class="dots" id="newsDots"></div>
      <span id="newsCounter">00 / 00</span>
    </div>


    <!-- 2. SEARCH AND FILTERS -->
    <h1 class="main-title">Check out all the buddies!</h1>

    <div class="content">

      <div class="search-bar">

        <div class="search-input">

          <span class="material-symbols-outlined search-icon">
            search
          </span>

          <input
            type="text"
            id="searchInput"
            placeholder="Search"
            aria-label="Search clubs and events"
          >

        </div>

        <button
          type="button"
          class="filter-btn material-symbols-outlined"
          id="toggleHomeFilter"
          aria-label="Show filters"
          aria-expanded="false"
          aria-controls="filterPanel"
        >
          filter_alt
        </button>

      </div>

      <!-- Shared filter panel -->
      <?php
      $filterPage = 'home';
      require __DIR__ . '/../php/filter.php';
      ?>


      <!-- 3. CLUBS ROW -->
      <div class="row-header">

        <h2>Clubs</h2>

        <a href="../pages/club_page.php" class="btn-small">
          See All
        </a>

      </div>

      <div class="row-wrap">

        <button
          type="button"
          class="arrow"
          data-target="clubsRow"
          data-dir="-1"
          aria-label="Scroll clubs left"
        >‹</button>

        <div class="card-row" id="clubsRow"></div>

        <button
          type="button"
          class="arrow"
          data-target="clubsRow"
          data-dir="1"
          aria-label="Scroll clubs right"
        >›</button>

      </div>


      <!-- 4. EVENTS ROW -->
      <div class="row-header">

        <h2>Events</h2>

        <a href="../pages/event_page.php" class="btn-small">
          See All
        </a>

      </div>

      <div class="row-wrap">

        <button
          type="button"
          class="arrow"
          data-target="eventsRow"
          data-dir="-1"
          aria-label="Scroll events left"
        >‹</button>

        <div class="card-row" id="eventsRow"></div>

        <button
          type="button"
          class="arrow"
          data-target="eventsRow"
          data-dir="1"
          aria-label="Scroll events right"
        >›</button>

      </div>

    </div>


    <!-- 5. FEEDBACK -->
    <section class="feedback">

      <h2>We'd love to hear your voice</h2>

      <p>
        Help us make this website better by voicing your thoughts!
      </p>

      <button type="button" class="btn btn-large">
        Leave Feedback
      </button>

      <small>
        Any suggestions? Complaints? Bugs? We got you, buddy!
      </small>

    </section>

  

  <?php pageFooter(); ?>

 


  <!-- Pass PHP data to JavaScript -->
  <script>
    window.latestNewsItems = <?= json_encode(
        $latestNews,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    window.homeClubs = <?= json_encode(
        $allClubs,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    window.homeEvents = <?= json_encode(
        $allEvents,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;
  </script>

  <script src="../js/news.js"></script>
  <script src="../js/home.js"></script>
  <script src="../js/home_filter.js"></script>
  <script src="../js/home_scroll.js"></script>

</body>
</html>
