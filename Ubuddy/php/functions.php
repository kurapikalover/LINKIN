
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================
   PAGE HEADER
========================= */


/* =========================
   PAGE HEADER
========================= */

function pageHeader($studentName = null, $studentId = null)
{
    // Use the logged-in user's details from the session.
    $studentName = $studentName ?? $_SESSION['name'] ?? 'Student Name';
    $studentId = $studentId ?? $_SESSION['user_id'] ?? 'User ID';

    // Identify the current page for active-tab styling.
    $currentPage = basename($_SERVER['PHP_SELF'] ?? '');

    $isHome = $currentPage === 'homepage.php';

    $isNotifications = in_array($currentPage, [
        'notification.php',
        'notifications.php'
    ], true);

    $isClubs = $currentPage === 'club_page.php';
    $isEvents = $currentPage === 'event_page.php';

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
            <a
                href="#"
                class="tab-icon-bell <?= $isNotifications ? 'active' : '' ?>"
                aria-label="Notifications"
                <?= $isNotifications ? 'aria-current="page"' : '' ?>
            >
                <span class="material-symbols-outlined">notifications</span>
            </a>

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
                href="#"
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
