<?php
/**
 * Class TuReserva_API_Token_Manager
 *
 * Handles creation, validation, and management of API tokens.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TuReserva_API_Token_Manager {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'tureserva_api_tokens';
    }

    /**
     * Create a new API token.
     *
     * @param string $name Name of the token (e.g., "Mobile App").
     * @param array $scopes List of permissions (e.g., ['read:reservas']).
     * @param string|null $expires_at Date string (Y-m-d H:i:s) or null for no expiration.
     *
     * @return string|WP_Error Returns the plain token key on success (SHOWN ONCE), or WP_Error.
     */
    public function create_token( $name, $scopes = [], $expires_at = null ) {
        global $wpdb;

        // Generate a random 40-char token
        $token = wp_generate_password( 40, false );
        
        // Hash it for storage
        $token_hash = hash( 'sha256', $token );
        
        // Prefix for checking (first 6 chars)
        $token_prefix = substr( $token, 0, 6 );

        // Validate scopes
        if ( ! is_array( $scopes ) ) {
            $scopes = [];
        }

        $result = $wpdb->insert(
            $this->table_name,
            [
                'name'         => sanitize_text_field( $name ),
                'token_prefix' => $token_prefix,
                'token_hash'   => $token_hash,
                'scopes'       => json_encode( $scopes ),
                'status'       => 'active',
                'expires_at'   => $expires_at ? date( 'Y-m-d H:i:s', strtotime( $expires_at ) ) : null,
                'created_at'   => current_time( 'mysql' ),
            ],
            [ '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );

        if ( $result === false ) {
            return new WP_Error( 'db_insert_error', 'Could not save token to database.' );
        }

        return $token;
    }

    /**
     * Validate a token from a request.
     *
     * @param string $token The plain token string.
     * @return object|false Returns the token row object if valid, false otherwise.
     */
    public function validate_token( $token ) {
        global $wpdb;

        $token_hash = hash( 'sha256', $token );

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE token_hash = %s LIMIT 1",
            $token_hash
        ) );

        if ( ! $row ) {
            return false;
        }

        // Check if active
        if ( $row->status !== 'active' ) {
            return false;
        }

        // Check expiration
        if ( $row->expires_at && strtotime( $row->expires_at ) < time() ) {
            $this->update_status( $row->id, 'expired' );
            return false;
        }

        // Update last used
        $this->record_usage( $row->id );

        return $row;
    }

    /**
     * Check if a token has a specific scope.
     *
     * @param object $token_row The token database row.
     * @param string $required_scope The scope to check (e.g. 'write:reservas').
     * @return bool
     */
    public function has_scope( $token_row, $required_scope ) {
        if ( empty( $token_row->scopes ) ) {
            return false;
        }

        $scopes = json_decode( $token_row->scopes, true );
        
        if ( ! is_array( $scopes ) ) {
            return false;
        }

        // Admin wildcard
        if ( in_array( 'admin:*', $scopes ) ) {
            return true;
        }

        return in_array( $required_scope, $scopes );
    }

    /**
     * Revoke a token.
     *
     * @param int $id Token ID.
     * @return bool
     */
    public function revoke_token( $id ) {
        return $this->update_status( $id, 'revoked' );
    }

    /**
     * Update token status.
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    private function update_status( $id, $status ) {
        global $wpdb;
        $result = $wpdb->update(
            $this->table_name,
            [ 'status' => $status ],
            [ 'id' => $id ],
            [ '%s' ],
            [ '%d' ]
        );
        return $result !== false;
    }

    /**
     * Record usage timestamp.
     *
     * @param int $id
     */
    private function record_usage( $id ) {
        global $wpdb;
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$this->table_name} SET last_used_at = %s WHERE id = %d",
            current_time( 'mysql' ),
            $id
        ) );
    }

    /**
     * Get all tokens for admin list.
     *
     * @return array
     */
    public function get_tokens() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM {$this->table_name} ORDER BY created_at DESC" );
    }
}
