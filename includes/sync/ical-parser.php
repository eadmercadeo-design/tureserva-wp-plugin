<?php
/**
 * ==========================================================
 * PARSER iCal — Lectura de calendarios externos (Airbnb, Booking, Google)
 * ==========================================================
 * Convierte el contenido de un archivo .ics en un array estructurado
 * con los eventos encontrados (inicio, fin, resumen, UID, etc.).
 * ==========================================================
 */

if (!defined('ABSPATH')) exit;

/**
 * Parsea datos iCal (ICS) y retorna un array de eventos.
 *
 * @param string $ical_data Contenido completo del archivo .ics
 * @return array Lista de eventos parseados
 */
function tureserva_parse_ical($ical_data) {

    // Normalizar saltos de línea (importante para compatibilidad multiplataforma)
    $ical_data = str_replace(["\r\n", "\r"], "\n", $ical_data);

    // Separar líneas y unir aquellas que están partidas (continuación con espacio o tab)
    $lines = explode("\n", $ical_data);
    $normalized = [];

    foreach ($lines as $line) {
        if (preg_match('/^[ \t]/', $line) && !empty($normalized)) {
            // Continuación de línea anterior
            $normalized[count($normalized) - 1] .= trim($line);
        } else {
            $normalized[] = trim($line);
        }
    }

    $events = [];
    $event  = [];

    foreach ($normalized as $line) {
        if ($line === 'BEGIN:VEVENT') {
            $event = [];
            continue;
        }

        if ($line === 'END:VEVENT') {
            if (!empty($event)) {
                // Normalizar fechas a formato ISO 8601 si es posible
                if (isset($event['start'])) {
                    $event['start'] = tureserva_normalize_ical_date($event['start']);
                }
                if (isset($event['end'])) {
                    $event['end'] = tureserva_normalize_ical_date($event['end']);
                }
                $events[] = $event;
            }
            continue;
        }

        // Extraer claves y valores
        if (strpos($line, ':') !== false) {
            [$key, $value] = explode(':', $line, 2);
            $key = strtoupper(trim($key));

            if (strpos($key, 'DTSTART') === 0) {
                $event['start'] = trim($value);
            } elseif (strpos($key, 'DTEND') === 0) {
                $event['end'] = trim($value);
            } elseif (strpos($key, 'SUMMARY') === 0) {
                $event['summary'] = trim($value);
            } elseif (strpos($key, 'UID') === 0) {
                $event['uid'] = trim($value);
            } elseif (strpos($key, 'DESCRIPTION') === 0) {
                $event['description'] = trim($value);
            } elseif (strpos($key, 'LOCATION') === 0) {
                $event['location'] = trim($value);
            }
        }
    }

    return $events;
}

/**
 * Convierte fechas iCal a formato legible o ISO 8601
 *
 * @param string $ical_date Fecha en formato iCal (p. ej. 20250131T150000Z)
 * @return string Fecha normalizada (Y-m-d H:i:s)
 */
function tureserva_normalize_ical_date($ical_date) {
    // Ejemplo: 20250131T150000Z o 20250131
    $ical_date = trim($ical_date);
    $formats = ['Ymd\THis\Z', 'Ymd\THis', 'Ymd'];

    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $ical_date, new DateTimeZone('UTC'));
        if ($dt !== false) {
            return $dt->format('Y-m-d H:i:s');
        }
    }

    // Si no se puede convertir, retornar el valor original
    return $ical_date;
}
