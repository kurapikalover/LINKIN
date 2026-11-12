
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../php/db.php';
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
// CHECK REQUEST METHOD
// =========================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: homepage.php");
    exit();
}


// =========================
// CHECK CSRF TOKEN
// =========================

if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die("Invalid request. Please try again.");
}


// =========================
// GET EVENT ID
// =========================

$eventId = filter_input(
    INPUT_POST,
    'event_id',
    FILTER_VALIDATE_INT
);

if (!$eventId || $eventId <= 0) {
    die("Invalid event.");
}


// =========================
// GET ADMIN CLUBS
// =========================

$adminClubs = getAdminClubs($pdo, $userId);

$allowedClubIds = array_map(
    'intval',
    array_column($adminClubs, 'club_id')
);


// =========================
// CHECK EVENT OWNERSHIP
// =========================

try {

    $stmt = $pdo->prepare("
        SELECT club_id
        FROM event
        WHERE event_id = :event_id
    ");

    $stmt->execute([
        'event_id' => $eventId
    ]);

    $clubId = $stmt->fetchColumn();

    if ($clubId === false) {
        die("Event not found.");
    }

    // Make sure the event belongs to a club
    // administered by the logged-in user.
    if (!in_array((int)$clubId, $allowedClubIds, true)) {
        die("You are not authorised to delete this event.");
    }


    // =========================
    // DELETE EVENT
    // =========================

    $stmt = $pdo->prepare("
        DELETE FROM event
        WHERE event_id = :event_id
          AND club_id = :club_id
    ");

    $stmt->execute([
        'event_id' => $eventId,
        'club_id' => $clubId
    ]);

    if ($stmt->rowCount() === 0) {
        die("Unable to delete the event.");
    }


    // =========================
    // REDIRECT AFTER DELETION
    // =========================

    header(
        "Location: com-admin.php?id=" . (int)$clubId . "&event_deleted=1"
    );

    exit();


} catch (PDOException $e) {

    error_log($e->getMessage());

    // This may happen if another table references this event.
    die("Unable to delete this event. It may be linked to other records.");

}

?>
