
<?php

require_once __DIR__ . '/notification_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================
   PAGE HEADER
========================= */

function pageHeader($studentName = null, $studentId = null, $adminClubs = [])
{
    // Use the logged-in user's details from the session.
    $studentName = $studentName ?? $_SESSION['name'] ?? 'Student Name';
    $studentId = $studentId ?? $_SESSION['user_id'] ?? 'User ID';

    // Identify the current page for active-tab styling.
    $currentPage = basename($_SERVER['PHP_SELF'] ?? '');

    // Get the number of unread notifications.
    global $pdo;

    $unreadCount = 0;

    if (isset($pdo) && isset($_SESSION['user_id'])) {
        $unreadCount = getUnreadNotificationCount(
            $pdo,
            $_SESSION['user_id']
        );
    }

    $isHome = $currentPage === 'homepage.php';

    $isNotifications = in_array($currentPage, [
        'notification.php',
        'notifications.php'
    ], true);

    $isClubs = $currentPage === 'club_page.php';
    $isEvents = $currentPage === 'event_page.php';

    $isCommitteeAdmin = $currentPage === 'com-admin.php';

    $isMyActivity = in_array($currentPage, [
        'my_activity.php',
        'myActivity.php'
    ], true);

    $isMyApplications = in_array($currentPage, [
        'my_applications.php',
        'my_application.php',
        'myApplications.php'
    ], true);
    ?>

    <div class="page">

        <nav class="tabs">

            <!-- Home -->
            <a
                href="../pages/homepage.php"
                class="tab-icon <?= $isHome ? 'active' : '' ?>"
                aria-label="Home"
                <?= $isHome ? 'aria-current="page"' : '' ?>
            >
                <span class="material-symbols-outlined">home</span>
            </a>

            <!-- Notifications -->
            <!-- Notifications -->
            <a
                href="../pages/notification.php"
                class="tab-icon-bell <?= $isNotifications ? 'active' : '' ?>"
                aria-label="Notifications"
                <?= $isNotifications ? 'aria-current="page"' : '' ?>
            >
                <span class="material-symbols-outlined">notifications</span>

                <?php if ($unreadCount > 0): ?>
                    <span class="notification-dot"></span>
                <?php endif; ?>
            </a>

            <!-- Committee Admin Dropdown -->
            <?php if (!empty($adminClubs)): ?>

                <div class="committee-admin-dropdown">

                    <button
                        type="button"
                        class="tab-icon <?= $isCommitteeAdmin ? 'active' : '' ?>"
                        aria-label="Committee Admin"
                        id="committeeAdminButton"
                        aria-expanded="false"
                        aria-controls="committeeAdminMenu"
                    >
                        <span class="material-symbols-outlined">
                            diversity_3
                        </span>
                    </button>

                    <div
                        class="committee-admin-menu"
                        id="committeeAdminMenu"
                        hidden
                    >

                        <p>Select a Club</p>

                        <?php foreach ($adminClubs as $club): ?>

                            <a href="../pages/com-admin.php?id=<?= urlencode($club['club_id']) ?>">
                                <?= htmlspecialchars(
                                    $club['club_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </a>

                        <?php endforeach; ?>

                    </div>

                </div>

            <?php endif; ?>

            <span class="spacer"></span>

            <!-- Clubs -->
            <a
                href="../pages/club_page.php"
                class="tab <?= $isClubs ? 'active' : '' ?>"
                <?= $isClubs ? 'aria-current="page"' : '' ?>
            >
                Clubs
            </a>

            <!-- Events -->
            <a
                href="../pages/event_page.php"
                class="tab <?= $isEvents ? 'active' : '' ?>"
                <?= $isEvents ? 'aria-current="page"' : '' ?>
            >
                Events
            </a>

            <!-- My Activity -->
            <a
                href="../pages/myactivity.php"
                class="tab <?= $isMyActivity ? 'active' : '' ?>"
                <?= $isMyActivity ? 'aria-current="page"' : '' ?>
            >
                My Activity
            </a>

            <!-- My Applications -->
            <a
                href="#"
                class="tab <?= $isMyApplications ? 'active' : '' ?>"
                <?= $isMyApplications ? 'aria-current="page"' : '' ?>
            >
                My Applications
            </a>

        </nav>

        <div class="sheet">

            <header class="header">

                <!-- Logo -->
                <div class="logo">
                    ÜBuddy
                    <small>Let UBuddy be your buddy</small>
                </div>

                <!-- User information and profile dropdown -->
                <div class="user">

                    <div class="user-info">

                        <strong>
                            <?= htmlspecialchars(
                                $studentName,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>

                        <small class="user-id">
                            <?= htmlspecialchars(
                                $studentId,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </small>

                    </div>

                    <div class="profile-dropdown" id="profileDropdown">

                        <button
                            type="button"
                            class="avatar"
                            id="profileDropdownButton"
                            aria-label="Open profile menu"
                            aria-expanded="false"
                            aria-controls="profileDropdownMenu"
                        >
                            <span class="material-symbols-outlined">
                                account_circle
                            </span>
                        </button>

                        <!-- Dropdown menu -->
                        <div
                            class="profile-dropdown-menu"
                            id="profileDropdownMenu"
                            hidden
                        >

                            <a href="../pages/stu_profile.php">
                                <span class="material-symbols-outlined">
                                    person
                                </span>
                                My Profile
                            </a>

                            <a
                                href="../pages/logout.php"
                                class="logout-option"
                            >
                                <span class="material-symbols-outlined">
                                    logout
                                </span>
                                Log Out
                            </a>

                        </div>

                    </div>

                </div>

            </header>

    <?php
}


/* =========================
   PAGE FOOTER
========================= */

function pageFooter()
{
    ?>

        </div> <!-- .sheet -->

    </div> <!-- .page -->

        <script src="../js/profile_dropdown.js"></script>

    <?php
}


/* =========================
   PAGE ICONS
========================= */

function pageIcons()
{
    echo '
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700">
    ';
}


/* =========================
   BACK LINK
========================= */

function backLink($url, $label)
{
    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

    echo '
        <a href="' . $safeUrl . '" class="back-link">
            <span class="back-icon">
                <span class="material-symbols-outlined">arrow_back</span>
            </span>
            <span class="back-text">' . $safeLabel . '</span>
        </a>
    ';
}

?>
