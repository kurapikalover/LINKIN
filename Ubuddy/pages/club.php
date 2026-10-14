
<?php
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/club_functions.php';
require_once __DIR__ . '/../php/club_queries.php';

// Read the club ID from the URL.
$clubId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$club = false;
$socialLinks = [];

if ($clubId !== false && $clubId !== null && $clubId > 0) {
    $club = getClubById($pdo, $clubId);

    if ($club) {
        $socialLinks = getClubSocialLinks($pdo, $clubId);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ÜBuddy</title>
    
    <?php pageIcons(); ?>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/shared.css">
    <link rel="stylesheet" href="../css/club.css">

    
</head>

<body>

<?php pageHeader(); ?>

<div class="sheet">
    <main class="page-content">

      <?php backLink('club_page.php', 'Back to Clubs'); ?>

      <?php if (!$club): ?>

          <div class="club-layout">
              <section class="club-information">
                  <h1>Club not found</h1>
                  <p>This club does not exist or the link is invalid.</p>
                  <a href="club_page.php">Browse all clubs</a>
              </section>
          </div>

      <?php else: ?>

          <?php
          $categoryName = $club['category_name'] ?? 'Uncategorised';
          $categoryClass = getClubCategoryClass($categoryName);

          $activities = getClubActivities($club['activities']);
          ?>

          <div class="club-layout">

              <!-- CLUB INFORMATION -->
              <section class="club-information">

                  <h1><?= clubEscape($club['club_name']) ?></h1>

                  <p class="category <?= clubEscape($categoryClass) ?>">
                      <?= clubEscape($categoryName) ?>
                  </p>

                  <div class="club-circles">
                      <span></span>
                      <span></span>
                      <span></span>
                      <span></span>
                  </div>

                  <!-- ABOUT -->
                  <div class="info-section">
                      <h2>About</h2>
                      <p>
                          <?= nl2br(clubEscape(
                              $club['description']
                              ?: 'No description available yet.'
                          )) ?>
                      </p>
                  </div>

                  <!-- ACTIVITIES -->
                  <div class="info-section">
                      <h2>Activities</h2>

                      <?php if (!empty($activities)): ?>
                          <ul>
                              <?php foreach ($activities as $activity): ?>
                                  <li><?= clubEscape($activity) ?></li>
                              <?php endforeach; ?>
                          </ul>
                      <?php else: ?>
                          <p>No activities listed yet.</p>
                      <?php endif; ?>
                  </div>

                  <!-- MEETING FREQUENCY -->
                  <div class="info-section">
                      <h2>Meeting Frequency</h2>
                      <p>
                          <?= clubEscape(
                              $club['meeting_frequency'] ?? 'Not specified'
                          ) ?>
                      </p>
                  </div>

                  <!-- EXPERIENCE REQUIREMENT -->
                  <div class="info-section">
                      <h2>Experience Requirement</h2>
                      <p>
                          <?= clubEscape(
                              $club['exp_requirement'] ?? 'Not specified'
                          ) ?>
                      </p>
                  </div>

                  <!-- MEMBERSHIP FEE -->
                  <div class="info-section">
                      <h2>Fee</h2>
                      <p>
                          <?= clubEscape(formatClubFee(
                              $club['membership_fee'],
                              $club['fee_currency']
                          )) ?>
                      </p>
                  </div>

                  <!-- SOCIAL MEDIA -->
                  <?php if (!empty($socialLinks)): ?>
                      <div class="info-section">
                          <h2>Social Media</h2>

                          <div class="social-buttons">

                              <?php foreach ($socialLinks as $social): ?>
                                  <a
                                      class="social-button"
                                      href="<?= clubEscape($social['account_url']) ?>"
                                      target="_blank"
                                      rel="noopener noreferrer"
                                  >
                                      <i
                                          class="<?= clubEscape(
                                              getClubSocialIcon($social['platform'])
                                          ) ?>"
                                          aria-hidden="true"
                                      ></i>

                                      <span class="social-button-text">
                                          <span class="social-platform">
                                              <?= clubEscape($social['platform']) ?>
                                          </span>

                                          <?php if (!empty($social['social_media_name'])): ?>
                                              <span class="social-username">
                                                  <?= clubEscape(
                                                      $social['social_media_name']
                                                  ) ?>
                                              </span>
                                          <?php endif; ?>
                                      </span>
                                  </a>
                              <?php endforeach; ?>

                          </div>
                      </div>
                  <?php endif; ?>

              </section>

              <!-- RIGHT SIDEBAR -->
              <aside class="club-sidebar">

                  <div class="club-image">
                      <span>Club Image</span>
                  </div>

                  <button
                      type="button"
                      class="join-button"
                      disabled
                      title="Application functionality has not been connected yet"
                  >
                      JOIN CLUB
                  </button>

                  <div class="contact-card">
                      <h2>Contact Information</h2>
                      <p>Contact information has not been added yet.</p>
                  </div>

              </aside>

          </div>
      <?php endif; ?>

    </main>
</div>

<?php pageFooter(); ?>

</body>
</html>
