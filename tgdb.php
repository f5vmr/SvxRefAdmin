<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/* talkgroup / number alias database */
$tgdb_array = [
    '0'     => 'Idle',
    '91'   => 'FM Worldwide',
    '9090' => 'FM USA East Coast Net',
    '235'   => 'FM United Kingdom',
    '2350'   => 'FM UK Chat 0',
    '2351'   => 'FM UK Chat 1',
    '23450' => 'FM Yorkshire Net',
    '23500' => 'FM UK South West',
    '23510' => 'FM UK South East',
    '23520' => 'FM UK North East',
    '23525' => 'FM UK Freedom Nets',
    '23526' => 'FM UK HubNet',
    '23530' => 'FM UK Yorks & Humberside',
    '23540' => 'FM UK Wales',
    '23550' => 'FM UK Scotland',
    '23556' => 'DVS Scotland',
    '23560' => 'FM UK North East',
    '23561' => 'FM UK Engineering Channel',
    '23570' => 'FM UK Northern Ireland',
    '23580' => 'FM UK West Midlands',
    '23590' => 'FM UK East Midlands',
    '240'   => 'FM Sweden',
    '260'    => 'FM Poland',
    '262'   => 'FM Germany',
    'Any TG'   => 'can be used anywhere even if unlisted',
    ];
?>
