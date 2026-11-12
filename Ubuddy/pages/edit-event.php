
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/com_admin_funct.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$adminClubs = getAdminClubs($pdo, $userId);

$allowedClubIds = array_map(
    'intval',
    array_column($adminClubs, 'club_id')
);

// Get event ID
$eventId = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);

if (!$eventId) {
    header('Location: com-admin.php');
    exit;
}

// Get event and its club
$stmt = $pdo->prepare("
    SELECT
        event_id,
        event_name,
        description,
        start_date,
        end_date,
        location,
        event_status,
        club_id
    FROM public.event
    WHERE event_id = :event_id
");

$stmt->execute(['event_id' => $eventId]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    http_response_code(404);
    exit('Event not found.');
}

$clubId = (int)$event['club_id'];

// Make sure this user administers the event's club
if (!in_array($clubId, $allowedClubIds, true)) {
    http_response_code(403);
    exit('You do not have permission to edit this event.');
}

$errors = [];

$eventName = $event['event_name'];
$description = $event['description'] ?? '';
$startDate = $event['start_date'];
$endDate = $event['end_date'];
$location = $event['location'] ?? '';
$eventStatus = $event['event_status'];

$allowedStatuses = [
    'upcoming',
    'ongoing',
    'completed',
    'cancelled'
];

// Create CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventName = trim($_POST['event_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
    $endDate = trim($_POST['end_date'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $eventStatus = $_POST['event_status'] ?? 'upcoming';

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    if ($eventName === '') {
        $errors[] = 'Please enter an event name.';
    }

    $start = DateTime::createFromFormat('!Y-m-d', $startDate);
    $end = DateTime::createFromFormat('!Y-m-d', $endDate);

    if (!$start || $start->format('Y-m-d') !== $startDate) {
        $errors[] = 'Please enter a valid start date.';
    }

    if (!$end || $end->format('Y-m-d') !== $endDate) {
        $errors[] = 'Please enter a valid end date.';
    }

    if ($start && $end && $end < $start) {
        $errors[] = 'The end date cannot be earlier than the start date.';
    }

    if (!in_array($eventStatus, $allowedStatuses, true)) {
        $errors[] = 'Please select a valid event status.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE public.event
                SET
                    event_name = :event_name,
                    description = :description,
                    start_date = :start_date,
                    end_date = :end_date,
                    location = :location,
                    event_status = :event_status
                WHERE event_id = :event_id
                  AND club_id = :club_id
            ");

            $stmt->execute([
                'event_name' => $eventName,
                'description' => $description !== '' ? $description : null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'location' => $location !== '' ? $location : null,
                'event_status' => $eventStatus,
                'event_id' => $eventId,
                'club_id' => $clubId
            ]);

            header('Location: com-admin.php?id=' . $clubId . '&event_updated=1');
            exit;

        } catch (PDOException $e) {
            $errors[] = 'Unable to update the event. Please try again.';
        }
    }
}

pageHeader(
    $_SESSION['name'] ?? 'Student',
    $userId,
    $adminClubs
);
?>

<link rel="stylesheet" href="../css/event-form.css">

<div class="event-form-container">
    <a href="com-admin.php?id=<?= $clubId ?>" class="back-link">
        &larr; Back to Dashboard
    </a>

    <h2>Edit Event</h2>
    <p class="form-subtitle">
        Update the details of your event.
    </p>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
        >

        <div class="form-group">
            <label for="event_name">Event Name *</label>
            <input
                type="text"
                id="event_name"
                name="event_name"
                maxlength="150"
                value="<?= htmlspecialchars($eventName) ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea
                id="description"
                name="description"
                rows="4"
            ><?= htmlspecialchars($description) ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="start_date">Start Date *</label>
                <input
                    type="date"
                    id="start_date"
                    name="start_date"
                    value="<?= htmlspecialchars($startDate) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="end_date">End Date *</label>
                <input
                    type="date"
                    id="end_date"
                    name="end_date"
                    value="<?= htmlspecialchars($endDate) ?>"
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="location">Location</label>
            <input
                type="text"
                id="location"
                name="location"
                maxlength="255"
                value="<?= htmlspecialchars($location) ?>"
            >
        </div>

        <div class="form-group">
            <label for="event_status">Event Status *</label>
            <select id="event_status" name="event_status" required>
                <?php foreach ($allowedStatuses as $status): ?>
                    <option
                        value="<?= htmlspecialchars($status) ?>"
                        <?= $eventStatus === $status ? 'selected' : '' ?>
                    >
                        <?= ucfirst($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <a
                href="com-admin.php?id=<?= $clubId ?>"
                class="cancel-button"
            >
                Cancel
            </a>

            <button type="submit" class="submit-button">
                Save Changes
            </button>
        </div>
    </form>
</div>

<?php pageFooter(); ?>
