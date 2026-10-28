
<?php

// ========================================
// GET STUDENT PROFILE
// ========================================
function getStudentProfile(PDO $pdo, $userId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT user_id, name, email, faculty, user_type
         FROM "users"
         WHERE user_id = :user_id
           AND user_type = :user_type'
    );

    $stmt->execute([
        'user_id' => $userId,
        'user_type' => 'Student'
    ]);

    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    return $profile ?: null;
}


// ========================================
// GET STUDENT CLUBS
// ========================================
function getStudentClubs(PDO $pdo, $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT
            c.club_id,
            c.club_name,
            m.membership_type
         FROM membership m
         JOIN club c
           ON c.club_id = m.club_id
         WHERE m.user_id = :user_id'
    );

    $stmt->execute([
        'user_id' => $userId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// ========================================
// GET RECENT APPLICATIONS
// ========================================
function getRecentApplications(PDO $pdo, $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT
            a.application_id,
            a.application_status,
            c.club_name
         FROM application a
         JOIN club c
           ON c.club_id = a.club_id
         WHERE a.user_id = :user_id
         ORDER BY a.application_id DESC
         LIMIT 3'
    );

    $stmt->execute([
        'user_id' => $userId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// ========================================
// GET STUDENT ACHIEVEMENTS
// ========================================
function getStudentAchievements(PDO $pdo, $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT
            achievement_id,
            user_id,
            club_id,
            achievement_title,
            achievement_description,
            achievement_date,
            achievement_type,
            achievement_status,
            review_feedback,
            evidence_url
         FROM achievement
         WHERE user_id = :user_id
         ORDER BY achievement_date DESC'
    );

    $stmt->execute([
        'user_id' => $userId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// ========================================
// ESCAPE OUTPUT FOR HTML
// ========================================
function profileEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}


// ========================================
// GET CSS CLASS FOR STATUS
// ========================================
function profileStatusClass($status): string
{
    $status = strtolower(trim((string) $status));

    if (in_array(
        $status,
        ['approved', 'accepted', 'successful'],
        true
    )) {
        return 'approved';
    }

    if (in_array(
        $status,
        ['rejected', 'declined', 'unsuccessful'],
        true
    )) {
        return 'rejected';
    }

    if (in_array(
        $status,
        ['pending', 'under review'],
        true
    )) {
        return 'pending';
    }

    return 'default';
}


function updateStudentEmail(PDO $pdo, $userId, string $email): void
{
    $stmt = $pdo->prepare(
        'UPDATE "users"
         SET email = :email
         WHERE user_id = :user_id
           AND usertype = :usertype'
    );

    $stmt->execute([
        'email' => $email,
        'user_id' => $userId,
        'usertype' => 'Student'
    ]);
}
