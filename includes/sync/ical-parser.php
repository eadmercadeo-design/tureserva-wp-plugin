<?php
/**
 * ==========================================================
 * PARSER iCal — Lectura de calendarios externos (Airbnb, Booking)
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

function tureserva_parse_ical($ical_data) {
    $lines = explode("\n", $ical_data);
    $events = [];
    $event = [];

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === 'BEGIN:VEVENT') {
            $event = [];
        } elseif ($line === 'END:VEVENT') {
            if (!empty($event)) $events[] = $event;
        } elseif (strpos($line, 'DTSTART') === 0) {
            $event['start'] = substr($line, strpos($line, ':') + 1);
        } elseif (strpos($line, 'DTEND') === 0) {
            $event['end'] = substr($line, strpos($line, ':') + 1);
        } elseif (strpos($line, 'SUMMARY') === 0) {
            $event['summary'] = substr($line, strpos($line, ':') + 1);
        }
    }

    return $events;
}
