<?php

// Escape text before displaying it in HTML.
function escapeHTML($value)
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

?>