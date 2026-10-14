
<?php

function pageHeader($studentName = "Student Name", $studentId = "2486023")
{
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

                        <small>
                            <?= htmlspecialchars($studentId, ENT_QUOTES, 'UTF-8') ?>
                            · <a href="#">View Profile</a>
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
?>
