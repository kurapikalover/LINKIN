
<?php

// Escape text before displaying it in HTML.
function clubEscape($value)
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Return the CSS class for each club category.
function getClubCategoryClass($categoryName)
{
    $categoryClasses = [
        'Sports' => 'category-sports',
        'Culture' => 'category-culture',
        'Recreational' => 'category-recreational',
        'Arts & Creative' => 'category-arts',
        'Religious' => 'category-religious',
        'Community Service' => 'category-community'
    ];

    return $categoryClasses[$categoryName] ?? '';
}

// Return the Font Awesome icon for a social media platform.
function getClubSocialIcon($platform)
{
    $socialIcons = [
        'instagram' => 'fa-brands fa-instagram',
        'tiktok' => 'fa-brands fa-tiktok',
        'facebook' => 'fa-brands fa-facebook-f',
        'youtube' => 'fa-brands fa-youtube',
        'twitter' => 'fa-brands fa-x-twitter',
        'x' => 'fa-brands fa-x-twitter',
        'linkedin' => 'fa-brands fa-linkedin-in',
        'whatsapp' => 'fa-brands fa-whatsapp'
    ];

    $platform = strtolower(trim($platform));

    return $socialIcons[$platform] ?? 'fa-solid fa-link';
}

// Convert the activities text into an array of activities.
function getClubActivities($activities)
{
    return preg_split(
        '/\r\n|\r|\n/',
        $activities ?? '',
        -1,
        PREG_SPLIT_NO_EMPTY
    );
}

// Format the membership fee for display.
function formatClubFee($fee, $currency)
{
    if ($fee === null) {
        return 'Not specified';
    }

    if ((float) $fee == 0) {
        return 'Free';
    }

    return ($currency ?: 'BND') . ' '
        . number_format((float) $fee, 2);
}

?>
