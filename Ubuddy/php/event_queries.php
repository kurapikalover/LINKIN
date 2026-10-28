
<?php



function getAllEvents(PDO $pdo): array
{
    $sql = "
        SELECT
            e.event_id,
            e.event_name,
            e.description,
            e.start_date,
            e.end_date,
            e.location,
            e.event_status,
            c.club_name,
            ei.image_url AS event_image
        FROM public.event AS e
        LEFT JOIN public.club AS c
            ON e.club_id = c.club_id
        LEFT JOIN LATERAL (
            SELECT image_url
            FROM public.event_image
            WHERE event_id = e.event_id
            ORDER BY event_image_id ASC
            LIMIT 1
        ) AS ei ON TRUE
        ORDER BY e.start_date ASC;
    ";

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



function getLatestNews(PDO $pdo): array
{
    // Get announcements that are currently active.
    $announcementSql = "
        SELECT
            announcement_id AS item_id,
            title,
            message,
            start_at AS item_start,
            'announcement' AS item_type
        FROM public.announcement
        WHERE is_active = TRUE
          AND start_at <= NOW()
          AND end_at > NOW()
    ";

    $announcementStmt = $pdo->query($announcementSql);
    $announcements = $announcementStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get events that have not ended yet.
    $eventSql = "
        SELECT
            e.event_id AS item_id,
            e.event_name AS title,
            e.description AS message,
            e.start_date AS item_start,
            'event' AS item_type
        FROM public.event e
        WHERE e.end_date >= CURRENT_DATE
        ORDER BY e.start_date ASC
    ";

    $eventStmt = $pdo->query($eventSql);
    $events = $eventStmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine announcements and events.
    $items = array_merge($announcements, $events);

    // Sort by start date, newest first.
    usort($items, function ($a, $b) {
        return strtotime($b['item_start']) <=> strtotime($a['item_start']);
    });

    return $items;
}


function getEventById(PDO $pdo, int $eventId): array|false
{
    $sql = "
        SELECT
            e.event_id,
            e.event_name,
            e.description,
            e.start_date,
            e.end_date,
            e.location,
            e.event_status,
            e.club_id,
            c.club_name
        FROM public.event AS e
        LEFT JOIN public.club AS c
            ON e.club_id = c.club_id
        WHERE e.event_id = :event_id
        LIMIT 1;
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'event_id' => $eventId
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}


?>
