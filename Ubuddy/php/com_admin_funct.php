
<?php

/* =========================
   GET ADMIN CLUBS
========================= */

function getAdminClubs($pdo, $userId)
{
    $sql = "
        SELECT
            c.club_id,
            c.club_name
        FROM membership m
        JOIN club c ON m.club_id = c.club_id
        WHERE m.user_id = :user_id
          AND m.membership_status = 'Enrolled'
          AND m.is_admin = TRUE
        ORDER BY c.club_name
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/* =========================
   GET ADMIN DETAILS
========================= */

function getAdminDetails($pdo, $userId, $clubId)
{
    $sql = "
        SELECT
            u.name,
            u.user_id,
            u.faculty,
            p.position_name
        FROM membership m
        JOIN users u ON m.user_id = u.user_id
        LEFT JOIN position p ON m.position_id = p.position_id
        WHERE m.user_id = :user_id
          AND m.club_id = :club_id
          AND m.membership_status = 'Enrolled'
          AND m.is_admin = TRUE
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $userId,
        'club_id' => $clubId
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}


/* =========================
   GET CLUB MEMBERS
========================= */

function getClubMembers($pdo, $clubId)
{
    $sql = "
        SELECT
            u.name,
            u.user_id,
            u.faculty,
            m.is_admin,
            p.position_name
        FROM membership m
        JOIN users u ON m.user_id = u.user_id
        LEFT JOIN position p ON m.position_id = p.position_id
        WHERE m.club_id = :club_id
          AND m.membership_status = 'Enrolled'
        ORDER BY u.name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['club_id' => $clubId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/* =========================
   GET UPCOMING EVENTS
========================= */

function getUpcomingEvents($pdo, $clubId)
{
    $sql = "
        SELECT
            event_id,
            event_name,
            start_date,
            end_date,
            location
        FROM event
        WHERE club_id = :club_id
          AND event_status = 'upcoming'
          AND start_date >= CURRENT_DATE
        ORDER BY start_date ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['club_id' => $clubId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/* =========================
   GET CLUB NAME
========================= */

function getClubName($pdo, $clubId)
{
    $sql = "
        SELECT club_name
        FROM club
        WHERE club_id = :club_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['club_id' => $clubId]);

    return $stmt->fetchColumn();
}

?>
