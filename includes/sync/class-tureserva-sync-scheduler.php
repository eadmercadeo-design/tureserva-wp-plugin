<?php
/**
 * Clase Scheduler para la Sincronización iCal
 *
 * Se encarga de definir los intervalos de cron personalizados y programar
 * el evento recurrente de sincronización según la configuración.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TuReserva_Sync_Scheduler {

    const CRON_HOOK   = 'tureserva_cron_sync_calendars';
    const OPTION_NAME = 'tureserva_sync_frequency';
    const DEFAULT_FREQ = '30min';

    public function __construct() {
        // Registrar intervalos personalizados
        add_filter( 'cron_schedules', array( $this, 'add_custom_intervals' ) );

        // Asegurar que el evento esté programado al cargar (si no existe)
        add_action( 'init', array( $this, 'ensure_scheduled_event' ) );
    }

    /**
     * Define los intervalos de tiempo disponibles para el cron.
     */
    public function add_custom_intervals( $schedules ) {
        $schedules['15min'] = array(
            'interval' => 15 * MINUTE_IN_SECONDS,
            'display'  => __( 'Cada 15 minutos', 'tureserva' )
        );
        $schedules['30min'] = array(
            'interval' => 30 * MINUTE_IN_SECONDS,
            'display'  => __( 'Cada 30 minutos', 'tureserva' )
        );
        $schedules['6hours'] = array(
            'interval' => 6 * HOUR_IN_SECONDS,
            'display'  => __( 'Cada 6 horas', 'tureserva' )
        );
        
        // 'hourly' ya existe en WP por defecto
        
        return $schedules;
    }

    /**
     * Obtiene la frecuencia configurada actualmente.
     */
    public function get_frequency() {
        return get_option( self::OPTION_NAME, self::DEFAULT_FREQ );
    }

    /**
     * Establece una nueva frecuencia y reprograma el cron.
     * 
     * @param string $frequency Clave del intervalo (15min, 30min, hourly, 6hours) o 'manual' para desactivar.
     */
    public function set_frequency( $frequency ) {
        // Validar que el intervalo existe (o es 'hourly' que es nativo)
        $valid_intervals = array( '15min', '30min', 'hourly', '6hours', 'manual' );
        
        if ( ! in_array( $frequency, $valid_intervals ) ) {
            return new WP_Error( 'invalid_frequency', 'La frecuencia seleccionada no es válida.' );
        }

        update_option( self::OPTION_NAME, $frequency );

        // Reprogramar
        $this->schedule_event( $frequency );
        
        // Hook para integraciones
        do_action( 'tureserva_sync_frequency_changed', $frequency );

        return true;
    }

    /**
     * Lógica interna para programar el evento.
     */
    protected function schedule_event( $frequency ) {
        // 1. Limpiar cron existente
        $timestamp = wp_next_scheduled( self::CRON_HOOK );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, self::CRON_HOOK );
        }

        // Si es manual, no programamos nada más
        if ( $frequency === 'manual' ) {
            return;
        }

        // 2. Programar nuevo evento
        // Usamos time() + intervalo para que la primera ejecución no sea inmediata y sature
        wp_schedule_event( time(), $frequency, self::CRON_HOOK );
    }

    /**
     * Verifica en 'init' que el evento esté programado si debe estarlo.
     * Recuperación ante fallos o migraciones.
     */
    public function ensure_scheduled_event() {
        $freq = $this->get_frequency();

        if ( $freq === 'manual' ) {
            return;
        }

        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            $this->schedule_event( $freq );
        }
    }
    
    /**
     * Obtiene la fecha de la próxima ejecución programada.
     * 
     * @return int|false Timestamp o false si no está programado.
     */
    public function get_next_run() {
         return wp_next_scheduled( self::CRON_HOOK );
    }
}
