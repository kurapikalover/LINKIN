
<?php

function eventStatusClass($status)
{
    $classes = [
        'upcoming'  => 'status-upcoming',
        'ongoing'   => 'status-ongoing',
        'completed' => 'status-completed',
        'cancelled' => 'status-cancelled'
    ];

    return $classes[strtolower($status ?? '')]
        ?? 'status-default';
}



function eventDateLabel($start, $end)
{
    $startDate = date('j M Y', strtotime($start));
    $endDate = date('j M Y', strtotime($end));

    if ($startDate === $endDate) {
        return $startDate;
    }

    return $startDate . ' - ' . $endDate;
}


?>
