
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function pageHeader($studentName = null, $studentId = null)
{
    // Use the logged-in user's details from the session.
    $studentName = $studentName ?? $_SESSION['name'] ?? 'Student Name';
    $studentId = $studentId ?? $_SESSION['user_id'] ?? 'User ID';
    ?>
    <div class="page">

        <nav class="tabs">
            <a href="../pages/homepage.php" class="tab-icon material-symbols-outlined">
                home
            </a>

            <a href="#" class="tab-icon-bell material-symbols-outlined">
                notifications
            </a>

            <span class="spacer"></span>

            <a href="#" class="tab">My Activity</a>
            <a href="#" class="tab">My Applications</a>
            <a href="#" class="tab">My Achievements</a>
        </nav>

        <div class="sheet">

            <header class="header">
                <div class="logo">
                    ÜBuddy
                    <small>Let UBuddy be your buddy</small>
                </div>

                <div class="user">
                    <div class="user-info">
                        <strong>
                            <?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?>
                        </strong>

                        <small class="user-id">
                            <?= htmlspecialchars($studentId, ENT_QUOTES, 'UTF-8') ?>
                        </small>

                        <small class="profile-link">
                            <a href="profile.php">View Profile</a>
                        </small>
                    </div>

                    <div class="avatar"></div>
                </div>
            </header>

    <?php
}




function pageFooter()
{
    ?>
        </div> <!-- .sheet -->
    </div> <!-- .page -->
    <?php
}



function pageIcons() {
    echo '
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700">
    ';
}


function backLink($url, $label) {
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
