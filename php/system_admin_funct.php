
<?php

/* =========================================
   SYSTEM ADMIN DETAILS
========================================= */

function getSystemAdminDetails(PDO $pdo, string $userId): ?array
{
    $sql = "
        SELECT user_id, name, email, faculty, user_type
        FROM public.users
        WHERE user_id = :user_id
          AND user_type = 'System Admin'
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $userId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}


/* =========================================
   GET CLUBS
========================================= */

function getSystemAdminClubs(PDO $pdo): array
{
    $sql = "
        SELECT club_id, club_name
        FROM public.club
        ORDER BY club_name ASC
    ";

    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}


/* =========================================
   USER FEEDBACK
========================================= */

function getSystemAdminFeedback(
    PDO $pdo,
    string $search = '',
    string $status = 'Pending'
): array {

    $allowedStatuses = [
        'Pending',
        'Reviewed',
        'Archived'
    ];

    if (!in_array($status, $allowedStatuses, true)) {
        $status = 'Pending';
    }

    $search = trim($search);

    $sql = "
        SELECT
            f.feedback_id,
            f.user_id,
            f.feedback_title,
            f.feedback_text,
            f.feedback_date,
            f.feedback_status,
            u.name AS student_name,
            COALESCE(fa.attachment_count, 0) AS attachment_count

        FROM public.feedback f

        LEFT JOIN public.users u
            ON f.user_id = u.user_id

        LEFT JOIN (
            SELECT
                feedback_id,
                COUNT(*) AS attachment_count
            FROM public.feedback_attachment
            GROUP BY feedback_id
        ) fa
            ON fa.feedback_id = f.feedback_id

        WHERE f.feedback_status = :status
    ";

    $params = [
        'status' => $status
    ];

    if ($search !== '') {

        $sql .= "
            AND (
                CAST(f.feedback_id AS TEXT) ILIKE :search_id
                OR CAST(f.user_id AS TEXT) ILIKE :search_user_id
                OR f.feedback_title ILIKE :search_title
                OR f.feedback_text ILIKE :search_text
                OR u.name ILIKE :search_name
            )
        ";

        $searchTerm = '%' . $search . '%';

        $params['search_id'] = $searchTerm;
        $params['search_user_id'] = $searchTerm;
        $params['search_title'] = $searchTerm;
        $params['search_text'] = $searchTerm;
        $params['search_name'] = $searchTerm;
    }

    $sql .= "
        ORDER BY f.feedback_date DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/* =========================================
   FEEDBACK STATUS COUNTS
========================================= */

function getSystemAdminFeedbackCounts(PDO $pdo): array
{
    $counts = [
        'Pending' => 0,
        'Reviewed' => 0,
        'Archived' => 0
    ];

    $sql = "
        SELECT
            feedback_status,
            COUNT(*) AS total
        FROM public.feedback
        WHERE feedback_status IN (
            'Pending',
            'Reviewed',
            'Archived'
        )
        GROUP BY feedback_status
    ";

    $stmt = $pdo->query($sql);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $status = $row['feedback_status'];

        if (array_key_exists($status, $counts)) {
            $counts[$status] = (int) $row['total'];
        }
    }

    return $counts;
}


/* =========================================
   FEEDBACK ATTACHMENTS
========================================= */

/*
 * Keep this function for places that need
 * attachments for only one feedback entry.
 */

function getFeedbackAttachments(PDO $pdo, int $feedbackId): array
{
    $sql = "
        SELECT
            attachment_id,
            feedback_id,
            file_name,
            file_path,
            file_type
        FROM public.feedback_attachment
        WHERE feedback_id = :feedback_id
        ORDER BY attachment_id ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['feedback_id' => $feedbackId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/*
 * Retrieve attachments for multiple feedback
 * entries in one database query.
 *
 * Returns:
 * [
 *     feedback_id => [attachments],
 *     feedback_id => [attachments]
 * ]
 */

function getFeedbackAttachmentsForFeedback(
    PDO $pdo,
    array $feedbackIds
): array {

    $feedbackIds = array_values(array_unique(array_filter(
        array_map('intval', $feedbackIds),
        fn($id) => $id > 0
    )));

    if (empty($feedbackIds)) {
        return [];
    }

    $placeholders = [];
    $params = [];

    foreach ($feedbackIds as $index => $feedbackId) {

        $placeholder = ':feedback_id_' . $index;

        $placeholders[] = $placeholder;
        $params[$placeholder] = $feedbackId;
    }

    $sql = "
        SELECT
            attachment_id,
            feedback_id,
            file_name,
            file_path,
            file_type
        FROM public.feedback_attachment
        WHERE feedback_id IN (
            " . implode(', ', $placeholders) . "
        )
        ORDER BY feedback_id ASC, attachment_id ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $attachmentsByFeedback = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $feedbackId = (int) $row['feedback_id'];

        if (!isset($attachmentsByFeedback[$feedbackId])) {
            $attachmentsByFeedback[$feedbackId] = [];
        }

        $attachmentsByFeedback[$feedbackId][] = $row;
    }

    return $attachmentsByFeedback;
}


/* =========================================
   MARK FEEDBACK AS REVIEWED
========================================= */

function markSystemFeedbackReviewed(
    PDO $pdo,
    int $feedbackId
): bool {

    $sql = "
        UPDATE public.feedback
        SET feedback_status = 'Reviewed'
        WHERE feedback_id = :feedback_id
          AND feedback_status = 'Pending'
        RETURNING feedback_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['feedback_id' => $feedbackId]);

    return (bool) $stmt->fetchColumn();
}


/* =========================================
   ARCHIVE FEEDBACK
========================================= */

function archiveSystemFeedback(
    PDO $pdo,
    int $feedbackId
): bool {

    $sql = "
        UPDATE public.feedback
        SET feedback_status = 'Archived'
        WHERE feedback_id = :feedback_id
          AND feedback_status IN ('Pending', 'Reviewed')
        RETURNING feedback_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['feedback_id' => $feedbackId]);

    return (bool) $stmt->fetchColumn();
}


/* =========================================
   FIND USER FOR ROLE ASSIGNMENT
========================================= */

function findSystemAdminTargetUser(
    PDO $pdo,
    string $search
): array {

    $sql = "
        SELECT user_id, name, user_type
        FROM public.users
        WHERE user_id = :user_id
           OR LOWER(name) = LOWER(:name)
        ORDER BY name
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'user_id' => $search,
        'name' => $search
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/* =========================================
   ASSIGN ROLE
========================================= */

function assignSystemAdminRole(
    PDO $pdo,
    string $userId,
    string $role,
    ?int $clubId = null
): bool {

    if ($role === 'system_admin') {

        $sql = "
            UPDATE public.users
            SET user_type = 'System Admin'
            WHERE user_id = :user_id
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }

    if ($role === 'hep_admin') {

        $sql = "
            UPDATE public.users
            SET user_type = 'Student Affairs'
            WHERE user_id = :user_id
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }

    if ($role === 'committee_admin') {

        if ($clubId === null) {
            return false;
        }

        $sql = "
            UPDATE public.membership
            SET is_admin = TRUE
            WHERE user_id = :user_id
              AND club_id = :club_id
            RETURNING membership_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'user_id' => $userId,
            'club_id' => $clubId
        ]);

        return (bool) $stmt->fetchColumn();
    }

    return false;
}


/* =========================================
   ACCOUNT MANAGEMENT
========================================= */

function getSystemAdminUsers(
    PDO $pdo,
    string $search = ''
): array {

    $sql = "
        SELECT
            user_id,
            name,
            email,
            faculty,
            user_type
        FROM public.users
    ";

    $params = [];

    $search = trim($search);

    if ($search !== '') {

        $sql .= "
            WHERE
                user_id ILIKE :search_user_id
                OR name ILIKE :search_name
                OR email ILIKE :search_email
                OR user_type ILIKE :search_user_type
        ";

        $searchTerm = '%' . $search . '%';

        $params['search_user_id'] = $searchTerm;
        $params['search_name'] = $searchTerm;
        $params['search_email'] = $searchTerm;
        $params['search_user_type'] = $searchTerm;
    }

    $sql .= " ORDER BY name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getSystemAdminUser(
    PDO $pdo,
    string $userId
): ?array {

    $sql = "
        SELECT
            user_id,
            name,
            email,
            faculty,
            user_type
        FROM public.users
        WHERE user_id = :user_id
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $userId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}


function addSystemAdminUser(
    PDO $pdo,
    string $userId,
    string $name,
    string $email,
    string $password,
    ?string $faculty,
    string $userType
): bool {

    $allowedTypes = [
        'Student',
        'Student Affairs',
        'System Admin'
    ];

    if (!in_array($userType, $allowedTypes, true)) {
        return false;
    }

    $sql = "
        INSERT INTO public.users (
            user_id,
            name,
            email,
            password,
            faculty,
            user_type
        )
        VALUES (
            :user_id,
            :name,
            :email,
            :password,
            :faculty,
            :user_type
        )
        RETURNING user_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'user_id' => $userId,
        'name' => $name,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'faculty' => $faculty,
        'user_type' => $userType
    ]);

    return (bool) $stmt->fetchColumn();
}


function updateSystemAdminUser(
    PDO $pdo,
    string $userId,
    string $name,
    string $email,
    ?string $faculty,
    string $userType,
    ?string $newPassword = null
): bool {

    $allowedTypes = [
        'Student',
        'Student Affairs',
        'System Admin'
    ];

    if (!in_array($userType, $allowedTypes, true)) {
        return false;
    }

    if ($newPassword !== null && $newPassword !== '') {

        $sql = "
            UPDATE public.users
            SET
                name = :name,
                email = :email,
                faculty = :faculty,
                user_type = :user_type,
                password = :password
            WHERE user_id = :user_id
            RETURNING user_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'faculty' => $faculty,
            'user_type' => $userType,
            'password' => password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            )
        ]);

    } else {

        $sql = "
            UPDATE public.users
            SET
                name = :name,
                email = :email,
                faculty = :faculty,
                user_type = :user_type
            WHERE user_id = :user_id
            RETURNING user_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'faculty' => $faculty,
            'user_type' => $userType
        ]);
    }

    return (bool) $stmt->fetchColumn();
}


function deleteSystemAdminUser(
    PDO $pdo,
    string $userId,
    string $currentAdminId
): bool {

    if ($userId === $currentAdminId) {
        return false;
    }

    $sql = "
        DELETE FROM public.users
        WHERE user_id = :user_id
        RETURNING user_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $userId]);

    return (bool) $stmt->fetchColumn();
}


/*
|--------------------------------------------------------------------------
| Compatibility functions for account_process.php
|--------------------------------------------------------------------------
| The processor hashes passwords before calling these functions.
*/

function getSystemAdminAccounts(PDO $pdo): array
{
    return getSystemAdminUsers($pdo);
}


function addSystemAdminAccount(PDO $pdo, array $account): bool
{
    $sql = "
        INSERT INTO public.users (
            user_id,
            name,
            email,
            password,
            faculty,
            user_type
        )
        VALUES (
            :user_id,
            :name,
            :email,
            :password,
            :faculty,
            :user_type
        )
        RETURNING user_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'user_id' => $account['user_id'],
        'name' => $account['name'],
        'email' => $account['email'],
        'password' => $account['password'],
        'faculty' => $account['faculty'],
        'user_type' => $account['user_type']
    ]);

    return (bool) $stmt->fetchColumn();
}


function updateSystemAdminAccount(
    PDO $pdo,
    string $userId,
    array $account
): bool {

    if ($account['password'] !== null) {

        $sql = "
            UPDATE public.users
            SET
                name = :name,
                email = :email,
                password = :password,
                faculty = :faculty,
                user_type = :user_type
            WHERE user_id = :user_id
            RETURNING user_id
        ";

    } else {

        $sql = "
            UPDATE public.users
            SET
                name = :name,
                email = :email,
                faculty = :faculty,
                user_type = :user_type
            WHERE user_id = :user_id
            RETURNING user_id
        ";
    }

    $stmt = $pdo->prepare($sql);

    $params = [
        'user_id' => $userId,
        'name' => $account['name'],
        'email' => $account['email'],
        'faculty' => $account['faculty'],
        'user_type' => $account['user_type']
    ];

    if ($account['password'] !== null) {
        $params['password'] = $account['password'];
    }

    $stmt->execute($params);

    return (bool) $stmt->fetchColumn();
}


function deleteSystemAdminAccount(
    PDO $pdo,
    string $userId
): bool {

    $sql = "
        DELETE FROM public.users
        WHERE user_id = :user_id
        RETURNING user_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $userId]);

    return (bool) $stmt->fetchColumn();
}


/* =========================================
   CATEGORY MANAGEMENT
========================================= */

function getSystemAdminCategories(
    PDO $pdo,
    string $search = ''
): array {

    $sql = "
        SELECT category_id, category_name
        FROM public.category
    ";

    $params = [];

    if ($search !== '') {
        $sql .= " WHERE category_name ILIKE :search";
        $params['search'] = '%' . $search . '%';
    }

    $sql .= " ORDER BY category_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function addSystemAdminCategory(
    PDO $pdo,
    string $categoryName
): bool {

    $sql = "
        INSERT INTO public.category (category_name)
        VALUES (:category_name)
        RETURNING category_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['category_name' => $categoryName]);

    return (bool) $stmt->fetchColumn();
}


function updateSystemAdminCategory(
    PDO $pdo,
    int $categoryId,
    string $categoryName
): bool {

    $sql = "
        UPDATE public.category
        SET category_name = :category_name
        WHERE category_id = :category_id
        RETURNING category_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'category_id' => $categoryId,
        'category_name' => $categoryName
    ]);

    return (bool) $stmt->fetchColumn();
}


function deleteSystemAdminCategory(
    PDO $pdo,
    int $categoryId
): bool {

    $sql = "
        DELETE FROM public.category
        WHERE category_id = :category_id
        RETURNING category_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['category_id' => $categoryId]);

    return (bool) $stmt->fetchColumn();
}


/* =========================================
   SYSTEM NOTIFICATIONS
========================================= */

function getNotificationRecipients(PDO $pdo): array
{
    $sql = "
        SELECT user_id, name, email
        FROM public.users
        ORDER BY name ASC
    ";

    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}


function sendSystemAdminNotification(
    PDO $pdo,
    string $audience,
    string $targetUserId,
    string $title,
    string $message,
    string $notificationType,
    ?string $linkUrl = null
): int {

    if ($audience === 'individual') {

        $sql = "
            INSERT INTO public.notification (
                user_id,
                title,
                message,
                notif_date,
                notif_type,
                is_read,
                link_url
            )
            SELECT
                user_id,
                :title,
                :message,
                CURRENT_TIMESTAMP,
                :notif_type,
                FALSE,
                :link_url
            FROM public.users
            WHERE user_id = :user_id
            RETURNING notif_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'user_id' => $targetUserId,
            'title' => $title,
            'message' => $message,
            'notif_type' => $notificationType,
            'link_url' => $linkUrl
        ]);

        return $stmt->rowCount();
    }

    if ($audience === 'all') {

        $sql = "
            INSERT INTO public.notification (
                user_id,
                title,
                message,
                notif_date,
                notif_type,
                is_read,
                link_url
            )
            SELECT
                user_id,
                :title,
                :message,
                CURRENT_TIMESTAMP,
                :notif_type,
                FALSE,
                :link_url
            FROM public.users
            RETURNING notif_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'title' => $title,
            'message' => $message,
            'notif_type' => $notificationType,
            'link_url' => $linkUrl
        ]);

        return $stmt->rowCount();
    }

    return 0;
}
