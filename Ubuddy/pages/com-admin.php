
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/com_admin_funct.php';


// =========================
// CHECK LOGIN
// =========================

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];


// =========================
// SUCCESS MESSAGE
// =========================

// Retrieve the message once, then remove it from the session.
$successMessage = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);


// =========================
// CSRF TOKEN
// =========================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


// =========================
// GET ADMIN CLUBS
// =========================

$adminClubs = getAdminClubs($pdo, $userId);

$allowedClubIds = array_map(
    'intval',
    array_column($adminClubs, 'club_id')
);

if (empty($adminClubs)) {
    header("Location: homepage.php");
    exit();
}


// =========================
// SELECT CLUB
// =========================

$selectedClubId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

// Select the first administered club by default.
if ($selectedClubId === 0 && !empty($allowedClubIds)) {
    $selectedClubId = $allowedClubIds[0];
}

// Check whether the user is an admin of this club.
if (!in_array($selectedClubId, $allowedClubIds, true)) {
    $selectedClubId = $allowedClubIds[0];
}


// =========================
// INITIALISE VARIABLES
// =========================

$clubName = '';
$admin = [];
$members = [];
$clubEvents = [];

$activeEvents = [];
$pastEvents = [];


// =========================
// GET CLUB INFORMATION
// =========================

if ($selectedClubId > 0) {

    $clubName = getClubName($pdo, $selectedClubId);

    $admin = getAdminDetails(
        $pdo,
        $userId,
        $selectedClubId
    );

    $members = getClubMembers(
        $pdo,
        $selectedClubId
    );

    // Get all events belonging to this club.
    $clubEvents = getClubEvents(
        $pdo,
        $selectedClubId
    );

    // Separate active events from past and cancelled events.
    foreach ($clubEvents as $event) {

        $status = strtolower($event['event_status'] ?? '');

        if (in_array($status, ['upcoming', 'ongoing'], true)) {

            $activeEvents[] = $event;

        } elseif (in_array($status, ['completed', 'cancelled'], true)) {

            $pastEvents[] = $event;

        }
    }
}


// =========================
// SAFE HTML OUTPUT
// =========================

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ÜBuddy - Committee Admin</title>

    <!-- Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Page icons -->
    <?php pageIcons(); ?>

    <!-- CSS -->
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/shared.css">
    <link rel="stylesheet" href="../css/com-admin.css">

    <!-- JavaScript -->
    <script src="../js/com-admin.js" defer></script>

</head>

<body>

<?php

// =========================
// HEADER
// =========================

pageHeader(
    $_SESSION['name'] ?? 'Student',
    $userId,
    $adminClubs
);

?>


<!-- =========================
     PAGE CONTENT
========================= -->

<main class="page-content">

    <div class="committee-admin-page">

        <?php backLink('homepage.php', 'Home'); ?>

        <h1 class="committee-admin-title">
            Committee Admin
        </h1>


        <?php if (empty($adminClubs)): ?>

            <p>You are not currently a committee admin of any club.</p>

        <?php else: ?>


            <h2 class="club-name">
                <?= e($clubName) ?>
            </h2>


            <!-- =========================
                 SUCCESS MESSAGE
            ========================== -->

            <?php if (!empty($successMessage)): ?>

                <div class="event-success" id="eventSuccess">

                    <span>
                        <?= e($successMessage) ?>
                    </span>

                    <button
                        type="button"
                        class="close-success"
                        onclick="document.getElementById('eventSuccess').remove()"
                        aria-label="Close success message"
                        title="Close"
                    >
                        &times;
                    </button>

                </div>

            <?php endif; ?>


            <!-- =========================
                 COMMITTEE CARD
            ========================== -->

            <section class="committee-card">

                <span class="material-symbols-outlined committee-icon">
                    diversity_3
                </span>

                <div class="committee-details">

                    <h3>
                        <?= e($admin['name'] ?? '') ?>
                    </h3>

                    <p class="p1">
                        <?= e($admin['user_id'] ?? '') ?>
                    </p>

                    <p class="p2">
                        <?= e($admin['faculty'] ?? '') ?>
                    </p>

                    <p class="p3">
                        <?= e($admin['position_name'] ?? 'Committee Admin') ?>
                    </p>

                </div>

            </section>


            <div class="committee-admin-layout">


                <!-- =========================
                     MEMBER LIST
                ========================== -->

                <section class="member-list">

                    <h2>Member List</h2>


                    <!-- SEARCH AND FILTERS -->

                    <div class="member-controls">

                        <input
                            type="text"
                            id="memberSearch"
                            placeholder="Search by name or registration number..."
                            aria-label="Search members"
                        >

                        <select
                            id="positionFilter"
                            aria-label="Filter by position"
                        >

                            <option value="all">All Positions</option>

                            <?php

                            // Get unique positions from club members.
                            $positions = [];

                            foreach ($members as $member) {

                                $position = $member['position_name'] ?? 'Member';

                                if (!in_array($position, $positions, true)) {
                                    $positions[] = $position;
                                }
                            }

                            sort($positions);

                            foreach ($positions as $position):

                            ?>

                                <option value="<?= e(strtolower($position)) ?>">
                                    <?= e($position) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>


                        <select
                            id="adminFilter"
                            aria-label="Filter by admin status"
                        >

                            <option value="all">All Members</option>
                            <option value="admin">Admins Only</option>
                            <option value="non-admin">Non-admins Only</option>

                        </select>

                    </div>


                    <!-- MEMBER TABLE -->

                    <div class="table-wrap">

                        <table>

                            <thead>

                                <tr>
                                    <th>Name</th>
                                    <th>Reg. No</th>
                                </tr>

                            </thead>


                            <tbody id="memberRows">

                                <?php if (!empty($members)): ?>

                                    <?php foreach ($members as $member): ?>

                                        <?php

                                        $position = $member['position_name'] ?? 'Member';

                                        $isAdmin = in_array(
                                            strtolower((string) $member['is_admin']),
                                            ['1', 't', 'true', 'yes'],
                                            true
                                        );

                                        ?>

                                        <tr
                                            class="member-row"
                                            data-name="<?= e(strtolower($member['name'])) ?>"
                                            data-user-id="<?= e(strtolower($member['user_id'])) ?>"
                                            data-position="<?= e(strtolower($position)) ?>"
                                            data-admin="<?= $isAdmin ? 'admin' : 'non-admin' ?>"
                                        >

                                            <td>
                                                <?= e($member['name']) ?>
                                            </td>

                                            <td>
                                                <?= e($member['user_id']) ?>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>
                                        <td colspan="2">
                                            No members found.
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </section>


                <!-- =========================
                     RIGHT SIDE
                ========================== -->

                <aside class="admin-side">


                    <!-- VIEW CLUB DETAILS -->

                    <a
                        href="club.php?id=<?= (int) $selectedClubId ?>&from=com-admin&admin_club_id=<?= (int) $selectedClubId ?>"
                        class="side-btn"
                    >

                        <i class="fa-solid fa-eye"></i>
                        View Club Details

                    </a>


                    <!-- VIEW APPLICATIONS -->

                    <a
                        href="com-applications.php?club_id=<?= (int) $selectedClubId ?>"
                        class="side-btn"
                    >

                        View Applications

                    </a>


                    <!-- =========================
                         ALL EVENTS
                    ========================== -->

                    <section class="upcoming-events">

                        <!-- EVENTS HEADER -->

                        <div class="events-header">

                            <h2>Events</h2>

                            <a
                                href="add-event.php?club_id=<?= (int) $selectedClubId ?>"
                                class="addbtn"
                                aria-label="Add event"
                                title="Add Event"
                            >

                                +

                            </a>

                        </div>


                        <!-- SHARED EVENT SEARCH -->

                        <div class="event-search">

                            <span class="material-symbols-outlined">
                                search
                            </span>

                            <input
                                type="text"
                                id="eventSearch"
                                placeholder="Search events by name..."
                                aria-label="Search all events"
                            >

                        </div>


                        <!-- =========================
                             ACTIVE EVENTS
                        ========================== -->

                        <div class="event-section">

                            <h3 class="event-section-title">
                                Active Events
                            </h3>


                            <div
                                class="events-list event-scroll-list"
                                id="activeEventsList"
                            >

                                <?php if (!empty($activeEvents)): ?>

                                    <?php foreach ($activeEvents as $event): ?>

                                        <article
                                            class="event-details event-card"
                                            data-event-name="<?= e(strtolower($event['event_name'])) ?>"
                                        >

                                            <!-- EVENT NAME -->

                                            <h3>
                                                <?= e($event['event_name']) ?>
                                            </h3>


                                            <!-- EVENT DATE AND LOCATION -->

                                            <p>

                                                <?php

                                                $startDate = date(
                                                    'd M Y',
                                                    strtotime($event['start_date'])
                                                );

                                                $endDate = date(
                                                    'd M Y',
                                                    strtotime($event['end_date'])
                                                );

                                                echo e(
                                                    $event['start_date'] === $event['end_date']
                                                        ? $startDate
                                                        : $startDate . ' - ' . $endDate
                                                );

                                                ?>

                                                <br>

                                                <?= e(
                                                    $event['location'] ?? 'Location not specified'
                                                ) ?>

                                            </p>


                                            <!-- EVENT DESCRIPTION -->

                                            <?php if (!empty($event['description'])): ?>

                                                <p class="event-description">
                                                    <?= e($event['description']) ?>
                                                </p>

                                            <?php endif; ?>


                                            <!-- EVENT STATUS -->

                                            <span class="event-status <?= e($event['event_status']) ?>">
                                                <?= e(ucfirst($event['event_status'])) ?>
                                            </span>


                                            <!-- EVENT ACTION BUTTONS -->

                                            <div class="event-actions">

                                                <a
                                                    href="event_detail.php?id=<?= (int) $event['event_id'] ?>&from=com-admin&admin_club_id=<?= (int) $selectedClubId ?>"
                                                    class="view-eventbtn"
                                                >
                                                    View
                                                </a>


                                                <form
                                                    action="delete-event.php"
                                                    method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this event?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="event_id"
                                                        value="<?= (int) $event['event_id'] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="csrf_token"
                                                        value="<?= e($_SESSION['csrf_token']) ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="delete-eventbtn"
                                                    >
                                                        Delete
                                                    </button>

                                                </form>

                                            </div>

                                        </article>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <p class="no-events">
                                        No upcoming or ongoing events.
                                    </p>

                                <?php endif; ?>

                            </div>

                        </div>


                        <!-- =========================
                             PAST & CANCELLED EVENTS
                        ========================== -->

                        <div class="event-section past-events-section">

                            <button
                                type="button"
                                class="past-events-toggle"
                                id="pastEventsToggle"
                                aria-expanded="false"
                                aria-controls="pastEventsContent"
                            >

                                <span>Past & Cancelled Events</span>

                                <span class="material-symbols-outlined dropdown-arrow">
                                    expand_more
                                </span>

                            </button>


                            <!-- COLLAPSIBLE CONTENT -->

                            <div
                                id="pastEventsContent"
                                class="past-events-content"
                                hidden
                            >

                                <div
                                    class="events-list event-scroll-list"
                                    id="pastEventsList"
                                >

                                    <?php if (!empty($pastEvents)): ?>

                                        <?php foreach ($pastEvents as $event): ?>

                                            <article
                                                class="event-details event-card"
                                                data-event-name="<?= e(strtolower($event['event_name'])) ?>"
                                            >

                                                <!-- EVENT NAME -->

                                                <h3>
                                                    <?= e($event['event_name']) ?>
                                                </h3>


                                                <!-- EVENT DATE AND LOCATION -->

                                                <p>

                                                    <?php

                                                    $startDate = date(
                                                        'd M Y',
                                                        strtotime($event['start_date'])
                                                    );

                                                    $endDate = date(
                                                        'd M Y',
                                                        strtotime($event['end_date'])
                                                    );

                                                    echo e(
                                                        $event['start_date'] === $event['end_date']
                                                            ? $startDate
                                                            : $startDate . ' - ' . $endDate
                                                    );

                                                    ?>

                                                    <br>

                                                    <?= e(
                                                        $event['location'] ?? 'Location not specified'
                                                    ) ?>

                                                </p>


                                                <!-- EVENT DESCRIPTION -->

                                                <?php if (!empty($event['description'])): ?>

                                                    <p class="event-description">
                                                        <?= e($event['description']) ?>
                                                    </p>

                                                <?php endif; ?>


                                                <!-- EVENT STATUS -->

                                                <span class="event-status <?= e($event['event_status']) ?>">
                                                    <?= e(ucfirst($event['event_status'])) ?>
                                                </span>


                                                <!-- EVENT ACTION BUTTONS -->

                                                <div class="event-actions">

                                                    <a
                                                          href="event_detail.php?id=<?= (int) $event['event_id'] ?>&from=com-admin&admin_club_id=<?= (int) $selectedClubId ?>"
                                                        class="view-eventbtn"
                                                    >
                                                        View
                                                    </a>


                                                    <form
                                                        action="delete-event.php"
                                                        method="POST"
                                                        onsubmit="return confirm('Are you sure you want to delete this event?');"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="event_id"
                                                            value="<?= (int) $event['event_id'] ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="csrf_token"
                                                            value="<?= e($_SESSION['csrf_token']) ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="delete-eventbtn"
                                                        >
                                                            Delete
                                                        </button>

                                                    </form>

                                                </div>

                                            </article>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <p class="no-events">
                                            No completed or cancelled events.
                                        </p>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>


                        <!-- SHARED SEARCH RESULTS MESSAGE -->

                        <p
                            class="event-no-results"
                            id="eventNoResults"
                            hidden
                        >
                            No matching events found.
                        </p>

                    </section>

                </aside>

            </div>

        <?php endif; ?>

    </div>

</main>


<?php pageFooter(); ?>

</body>
</html>
