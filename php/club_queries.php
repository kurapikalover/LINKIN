
<?php

// Retrieve one club using its ID.
function getClubById(PDO $pdo, int $clubId)
{
    $sql = "
        SELECT
            c.club_id,
            c.club_name,
            c.description,
            c.activities,
            c.meeting_frequency,
            c.exp_requirement,
            c.membership_fee,
            c.fee_currency,
            cat.category_name
        FROM public.club AS c
        LEFT JOIN public.category AS cat
            ON c.category_id = cat.category_id
        WHERE c.club_id = :club_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['club_id' => $clubId]);

    $club = $stmt->fetch(PDO::FETCH_ASSOC);

    return $club ?: false;
}

// Retrieve all social media links belonging to a club.
function getClubSocialLinks(PDO $pdo, int $clubId)
{
    $sql = "
        SELECT
            platform,
            social_media_name,
            account_url
        FROM public.club_social_media
        WHERE club_id = :club_id
        ORDER BY platform
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['club_id' => $clubId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



function getAllClubs(PDO $pdo): array
{
    $sql = "
        SELECT
            c.club_id,
            c.club_name,
            c.description,
            cat.category_name,
            ci.storage_path AS image_url
        FROM public.club AS c
        LEFT JOIN public.category AS cat
            ON c.category_id = cat.category_id
        LEFT JOIN LATERAL (
            SELECT storage_path
            FROM public.club_image
            WHERE club_id = c.club_id
            ORDER BY is_primary DESC NULLS LAST,
                     uploaded_at DESC,
                     image_id ASC
            LIMIT 1
        ) AS ci ON TRUE
        ORDER BY c.club_name;
    ";

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>