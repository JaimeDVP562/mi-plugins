<?php
/**
 * Logs
 *
 * @package     AutomatorWP\Schedule_Actions\Logs
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// =============================================================================
// CÓDIGO ORIGINAL (sin cambios)
// =============================================================================

/**
 * Register custom log types
 *
 * @since  1.0.0
 */
function automatorwp_schelude_actions_log_types( $log_types ) {
    $log_types['schedule_actions'] = __( 'Scheduled', 'automatorwp-schedule-actions' );
    return $log_types;
}
add_filter( 'automatorwp_log_types', 'automatorwp_schelude_actions_log_types' );

/**
 * Columns rendering for logs list view
 *
 * @since  1.0.0
 */
function automatorwp_schedule_actions_manage_logs_custom_column( $column_name, $object_id ) {

    if( $column_name !== 'object_id' ) return;

    $log = ct_get_object( $object_id );

    if( $log->type !== 'schedule_actions' ) return;

    $action     = automatorwp_get_action_object( $log->object_id );
    $automation = false;

    if( $action ) {
        $automation = automatorwp_get_automation_object( $action->automation_id );
    }

    if( $automation ) {
        $title = ! empty( $automation->title ) ? $automation->title : __( '(No title)', 'automatorwp' ); ?>
        <a href="<?php echo ct_get_edit_link( 'automatorwp_automations', $automation->id ); ?>"><?php echo $title; ?></a>
        <?php
    } else {
        echo '&nbsp;';
    }
}
add_action( 'manage_automatorwp_logs_custom_column', 'automatorwp_schedule_actions_manage_logs_custom_column', 5, 2 );

/**
 * Custom log default icon
 *
 * @since 1.0.0
 */
function automatorwp_schedule_actions_get_log_default_icon( $icon, $log ) {
    if( $log->type !== 'schedule_actions' ) return $icon;
    return AUTOMATORWP_SCHEDULE_ACTIONS_URL . 'assets/schedule-actions.svg';
}
add_filter( 'automatorwp_get_log_default_icon', 'automatorwp_schedule_actions_get_log_default_icon', 10, 2 );

/**
 * Custom log default icon title
 *
 * @since 1.0.0
 */
function automatorwp_schedule_actions_get_log_default_icon_title( $title, $log ) {
    if( $log->type !== 'schedule_actions' ) return $title;
    return 'AutomatorWP - Schedule Actions';
}
add_filter( 'automatorwp_get_log_default_icon_title', 'automatorwp_schedule_actions_get_log_default_icon_title', 10, 2 );


// =============================================================================
// HELPERS: Detectar si un log está pendiente y obtener su evento programado
// =============================================================================

/**
 * Nombre del hook que usa este plugin para ejecutar la acción programada.
 * Definido en filters.php: add_action( 'automatorwp_schedule_actions_execute_action', ... )
 *
 * @since 1.2.0
 * @return string
 */
function automatorwp_schedule_actions_cron_hook() {
    return 'automatorwp_schedule_actions_execute_action';
}

/**
 * Comprueba si el log tiene todavía un evento pendiente de ejecutarse.
 *
 * El plugin programa el evento con los args: array( $action_id, $user_id, $event, $datetime_from )
 * Para verificar "pendiente" buscamos en Action Scheduler (si existe) o en WP-Cron
 * usando $action_id (= $log->object_id) y $user_id (= $log->user_id).
 *
 * @since 1.2.0
 *
 * @param int|stdClass $log  ID o objeto del log.
 * @return bool
 */
function automatorwp_schedule_actions_log_is_pending( $log ) {

    if( is_numeric( $log ) ) {
        $log = automatorwp_get_log_object( (int) $log );
    }

    if( ! $log || ! isset( $log->type ) || $log->type !== 'schedule_actions' ) {
        return false;
    }

    $action_id = absint( $log->object_id );
    $user_id   = absint( $log->user_id );
    $hook      = automatorwp_schedule_actions_cron_hook();

    // --- Action Scheduler (prioritario si está instalado) ---
    if( function_exists( 'as_get_scheduled_actions' ) && ! apply_filters( 'automatorwp_schedule_actions_force_wp_cron', false ) ) {

        $actions = as_get_scheduled_actions( array(
            'hook'   => $hook,
            'status' => \ActionScheduler_Store::STATUS_PENDING,
            'args'   => array( $action_id, $user_id ),
        ), 'ids' );

        return ! empty( $actions );
    }

    // --- WP-Cron fallback ---
    // wp_next_scheduled necesita los args exactos; como el 3er y 4º arg ($event, $datetime_from)
    // varían, tenemos que buscar manualmente en la tabla del cron.
    $crons = _get_cron_array();
    if( empty( $crons ) ) return false;

    foreach( $crons as $timestamp => $hooks ) {
        if( ! isset( $hooks[ $hook ] ) ) continue;

        foreach( $hooks[ $hook ] as $key => $cron_event ) {
            $args = $cron_event['args'] ?? array();
            // Los dos primeros args son $action_id y $user_id
            if( isset( $args[0], $args[1] )
                && absint( $args[0] ) === $action_id
                && absint( $args[1] ) === $user_id ) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Devuelve todos los datos del evento programado para un log.
 *
 * Retorna un array con:
 *   - timestamp  (int)   UTC Unix timestamp de ejecución
 *   - action_id  (int)
 *   - user_id    (int)
 *   - event      (array) El array $event original
 *   - datetime   (int)   El $datetime_from original
 *   - source     (string) 'action_scheduler' | 'wp_cron'
 *
 * @since 1.2.0
 *
 * @param int|stdClass $log
 * @return array|false
 */
function automatorwp_schedule_actions_get_scheduled_event( $log ) {

    if( is_numeric( $log ) ) {
        $log = automatorwp_get_log_object( (int) $log );
    }

    if( ! $log || $log->type !== 'schedule_actions' ) return false;

    $action_id = absint( $log->object_id );
    $user_id   = absint( $log->user_id );
    $hook      = automatorwp_schedule_actions_cron_hook();

    // --- Action Scheduler ---
    if( function_exists( 'as_get_scheduled_actions' ) && ! apply_filters( 'automatorwp_schedule_actions_force_wp_cron', false ) ) {

        $actions = as_get_scheduled_actions( array(
            'hook'     => $hook,
            'status'   => \ActionScheduler_Store::STATUS_PENDING,
            'args'     => array( $action_id, $user_id ),
            'per_page' => 1,
        ) );

        if( empty( $actions ) ) return false;

        /** @var ActionScheduler_Action $as_action */
        $as_action = reset( $actions );
        $as_args   = $as_action->get_args();

        return array(
            'timestamp' => $as_action->get_schedule()->get_date()->getTimestamp(),
            'action_id' => absint( $as_args[0] ?? 0 ),
            'user_id'   => absint( $as_args[1] ?? 0 ),
            'event'     => $as_args[2] ?? array(),
            'datetime'  => absint( $as_args[3] ?? 0 ),
            'as_id'     => $as_action->get_id(),
            'source'    => 'action_scheduler',
        );
    }

    // --- WP-Cron fallback ---
    $crons = _get_cron_array();
    if( empty( $crons ) ) return false;

    foreach( $crons as $timestamp => $hooks ) {
        if( ! isset( $hooks[ $hook ] ) ) continue;

        foreach( $hooks[ $hook ] as $key => $cron_event ) {
            $args = $cron_event['args'] ?? array();
            if( isset( $args[0], $args[1] )
                && absint( $args[0] ) === $action_id
                && absint( $args[1] ) === $user_id ) {
                return array(
                    'timestamp'  => $timestamp,
                    'action_id'  => absint( $args[0] ),
                    'user_id'    => absint( $args[1] ),
                    'event'      => $args[2] ?? array(),
                    'datetime'   => absint( $args[3] ?? 0 ),
                    'cron_key'   => $key,
                    'source'     => 'wp_cron',
                );
            }
        }
    }

    return false;
}


// =============================================================================
// FEATURE 1 & 2: Botones en el listado de logs
// =============================================================================

/**
 * Añade botones "Cambiar fecha" y "Ejecutar ahora" en la columna 'title'
 * del listado, únicamente para logs de tipo schedule_actions pendientes.
 *
 * @since 1.2.0
 */
function automatorwp_schedule_actions_log_list_buttons( $column_name, $object_id ) {

    if( $column_name !== 'title' ) return;

    $log = ct_get_object( $object_id );
    if( ! $log || $log->type !== 'schedule_actions' ) return;

    $event = automatorwp_schedule_actions_get_scheduled_event( $log );
    if( ! $event ) return; // Ya ejecutado → no mostramos botones

    $log_id     = absint( $log->id );
    $timestamp  = $event['timestamp'];
    $current_dt = gmdate( 'Y-m-d\TH:i', $timestamp ); // Para input datetime-local (UTC)

    ?>
    <div class="automatorwp-sa-row-actions" style="margin-top:6px;display:flex;gap:6px;flex-wrap:wrap;">

        <a href="#"
           class="button button-small automatorwp-sa-change-date"
           data-log-id="<?php echo $log_id; ?>"
           data-current-date="<?php echo esc_attr( $current_dt ); ?>"
           data-nonce="<?php echo esc_attr( wp_create_nonce( 'automatorwp_sa_change_date_' . $log_id ) ); ?>">
            📅 <?php esc_html_e( 'Cambiar fecha', 'automatorwp-schedule-actions' ); ?>
        </a>

        <a href="<?php echo esc_url( wp_nonce_url(
                admin_url( 'admin-post.php?action=automatorwp_sa_run_now&log_id=' . $log_id ),
                'automatorwp_sa_run_now_' . $log_id
            ) ); ?>"
           class="button button-small automatorwp-sa-run-now"
           onclick="return confirm('<?php echo esc_js( __( '¿Ejecutar esta acción ahora mismo? Se cancelará el evento programado.', 'automatorwp-schedule-actions' ) ); ?>')">
            ▶ <?php esc_html_e( 'Ejecutar ahora', 'automatorwp-schedule-actions' ); ?>
        </a>

    </div>
    <?php
}
add_action( 'manage_automatorwp_logs_custom_column', 'automatorwp_schedule_actions_log_list_buttons', 10, 2 );


// =============================================================================
// FEATURE 1 & 2: Panel en la pantalla de edición del log (CT edit screen)
// =============================================================================

/**
 * Renderiza el panel de gestión en la vista de edición del log.
 *
 * @since 1.2.0
 */
function automatorwp_schedule_actions_log_edit_panel() {

    $ct_table = ct_get_current_table();
    if( ! $ct_table || $ct_table->name !== 'automatorwp_logs' ) return;

    $log_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
    if( ! $log_id ) return;

    $log = automatorwp_get_log_object( $log_id );
    if( ! $log || $log->type !== 'schedule_actions' ) return;

    $event = automatorwp_schedule_actions_get_scheduled_event( $log );

    ?>
    <div class="cmb2-wrap" style="margin-top:20px;">
        <div class="cmb-row" style="background:#fff;border:1px solid #ccd0d4;padding:20px;border-radius:3px;">

            <h2 style="margin-top:0;padding-bottom:10px;border-bottom:1px solid #eee;">
                🕐 <?php esc_html_e( 'Gestión de acción programada', 'automatorwp-schedule-actions' ); ?>
            </h2>

            <?php if( $event ) :
                $datetime_format = automatorwp_schedule_actions_get_datetime_format();
                $local_date = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $event['timestamp'] ), $datetime_format );
                $utc_input  = gmdate( 'Y-m-d\TH:i', $event['timestamp'] );
                $source_label = $event['source'] === 'action_scheduler'
                    ? __( 'Action Scheduler', 'automatorwp-schedule-actions' )
                    : __( 'WP-Cron', 'automatorwp-schedule-actions' );
            ?>

                <p>
                    <strong><?php esc_html_e( 'Fecha programada:', 'automatorwp-schedule-actions' ); ?></strong>
                    <?php echo esc_html( $local_date ); ?>
                    <span style="color:#999;font-size:11px;">(<?php echo esc_html( $source_label ); ?>)</span>
                </p>

                <!-- Cambiar fecha -->
                <fieldset style="border:1px solid #ddd;padding:15px;border-radius:3px;margin-bottom:15px;">
                    <legend style="font-weight:600;padding:0 6px;">
                        <?php esc_html_e( 'Cambiar fecha programada', 'automatorwp-schedule-actions' ); ?>
                    </legend>

                    <p style="margin-top:8px;">
                        <label for="automatorwp-sa-new-date">
                            <?php esc_html_e( 'Nueva fecha y hora (UTC):', 'automatorwp-schedule-actions' ); ?>
                        </label><br>
                        <input type="datetime-local"
                               id="automatorwp-sa-new-date"
                               value="<?php echo esc_attr( $utc_input ); ?>"
                               style="margin-top:5px;width:280px;padding:4px 8px;">
                        <br>
                        <em style="color:#666;font-size:11px;">
                            <?php esc_html_e( 'Introduce la hora en UTC. El evento programado se reprogramará automáticamente.', 'automatorwp-schedule-actions' ); ?>
                        </em>
                    </p>

                    <p>
                        <button type="button"
                                class="button button-primary automatorwp-sa-save-date"
                                data-log-id="<?php echo $log_id; ?>"
                                data-nonce="<?php echo esc_attr( wp_create_nonce( 'automatorwp_sa_change_date_' . $log_id ) ); ?>">
                            <?php esc_html_e( 'Guardar nueva fecha', 'automatorwp-schedule-actions' ); ?>
                        </button>
                        <span class="automatorwp-sa-feedback" style="margin-left:10px;display:none;"></span>
                    </p>
                </fieldset>

                <!-- Ejecutar ahora -->
                <fieldset style="border:1px solid #ddd;padding:15px;border-radius:3px;">
                    <legend style="font-weight:600;padding:0 6px;">
                        <?php esc_html_e( 'Ejecución inmediata', 'automatorwp-schedule-actions' ); ?>
                    </legend>

                    <p style="margin-top:8px;">
                        <?php esc_html_e( 'Cancela el evento pendiente y ejecuta la acción ahora mismo.', 'automatorwp-schedule-actions' ); ?>
                    </p>

                    <a href="<?php echo esc_url( wp_nonce_url(
                            admin_url( 'admin-post.php?action=automatorwp_sa_run_now&log_id=' . $log_id ),
                            'automatorwp_sa_run_now_' . $log_id
                        ) ); ?>"
                       class="button button-secondary"
                       onclick="return confirm('<?php echo esc_js( __( '¿Ejecutar esta acción ahora mismo?', 'automatorwp-schedule-actions' ) ); ?>')">
                        ▶ <?php esc_html_e( 'Ejecutar ahora', 'automatorwp-schedule-actions' ); ?>
                    </a>
                </fieldset>

            <?php else : ?>

                <p style="color:#666;font-style:italic;">
                    <?php esc_html_e( 'Esta acción ya ha sido ejecutada o no tiene un evento pendiente. No es posible modificarla.', 'automatorwp-schedule-actions' ); ?>
                </p>

            <?php endif; ?>

        </div>
    </div>
    <?php
}
add_action( 'ct_after_object_fields', 'automatorwp_schedule_actions_log_edit_panel' );


// =============================================================================
// FEATURE 1: AJAX – Cambiar fecha
// =============================================================================

/**
 * Handler AJAX para reprogramar el evento de un log pendiente.
 *
 * @since 1.2.0
 */
function automatorwp_schedule_actions_ajax_change_date() {

    if( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Sin permisos suficientes.', 'automatorwp-schedule-actions' ) ) );
    }

    $log_id   = isset( $_POST['log_id'] )   ? absint( $_POST['log_id'] )                : 0;
    $new_date = isset( $_POST['new_date'] ) ? sanitize_text_field( $_POST['new_date'] ) : '';

    if( ! $log_id || ! $new_date ) {
        wp_send_json_error( array( 'message' => __( 'Datos inválidos.', 'automatorwp-schedule-actions' ) ) );
    }

    if( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'automatorwp_sa_change_date_' . $log_id ) ) {
        wp_send_json_error( array( 'message' => __( 'Token de seguridad inválido.', 'automatorwp-schedule-actions' ) ) );
    }

    $log   = automatorwp_get_log_object( $log_id );
    $event = automatorwp_schedule_actions_get_scheduled_event( $log );

    if( ! $event ) {
        wp_send_json_error( array( 'message' => __( 'Esta acción ya ha sido ejecutada.', 'automatorwp-schedule-actions' ) ) );
    }

    // "Y-m-d\TH:i" del input datetime-local → tratamos como UTC
    $new_timestamp = strtotime( str_replace( 'T', ' ', $new_date ) . ':00 UTC' );

    if( ! $new_timestamp || $new_timestamp <= time() ) {
        wp_send_json_error( array( 'message' => __( 'La nueva fecha debe ser en el futuro.', 'automatorwp-schedule-actions' ) ) );
    }

    if( ! automatorwp_schedule_actions_reschedule( $log, $event, $new_timestamp ) ) {
        wp_send_json_error( array( 'message' => __( 'No se pudo reprogramar el evento. Inténtalo de nuevo.', 'automatorwp-schedule-actions' ) ) );
    }

    $datetime_format = automatorwp_schedule_actions_get_datetime_format();
    $formatted_date  = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $new_timestamp ), $datetime_format );

    wp_send_json_success( array(
        'message'        => __( 'Fecha actualizada correctamente.', 'automatorwp-schedule-actions' ),
        'formatted_date' => $formatted_date,
    ) );
}
add_action( 'wp_ajax_automatorwp_sa_change_date', 'automatorwp_schedule_actions_ajax_change_date' );

/**
 * Reprograma el evento (Action Scheduler o WP-Cron) conservando todos los args originales
 * y actualiza el título del log en la BD.
 *
 * @since 1.2.0
 *
 * @param stdClass $log           Objeto del log.
 * @param array    $event         Datos del evento actual (de automatorwp_schedule_actions_get_scheduled_event).
 * @param int      $new_timestamp Nuevo timestamp UTC.
 * @return bool
 */
function automatorwp_schedule_actions_reschedule( $log, $event, $new_timestamp ) {

    global $wpdb;

    $hook = automatorwp_schedule_actions_cron_hook();

    // Los args originales del evento (se reutilizan íntegramente)
    $original_args = array(
        $event['action_id'],
        $event['user_id'],
        $event['event'],
        $event['datetime'],
    );

    // --- Cancelar el evento actual ---
    if( $event['source'] === 'action_scheduler' && function_exists( 'as_unschedule_action' ) ) {
        as_unschedule_action( $hook, $original_args );
    } else {
        // WP-Cron: necesitamos el timestamp exacto
        wp_unschedule_event( $event['timestamp'], $hook, $original_args );
    }

    // --- Programar en el nuevo timestamp ---
    if( function_exists( 'as_schedule_single_action' ) && ! apply_filters( 'automatorwp_schedule_actions_force_wp_cron', false ) ) {
        $result = as_schedule_single_action( $new_timestamp, $hook, $original_args );
        $ok = ! empty( $result ); // as_ devuelve el action ID o 0/false en error
    } else {
        $result = wp_schedule_single_event( $new_timestamp, $hook, $original_args );
        $ok = $result !== false;
    }

    if( ! $ok ) return false;

    // --- Actualizar el título del log para reflejar la nueva fecha ---
    $ct_table = ct_setup_table( 'automatorwp_logs' );

    $datetime_format = automatorwp_schedule_actions_get_datetime_format();
    $new_date_local  = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $new_timestamp ), $datetime_format );

    // Reconstruir el título siguiendo el mismo patrón del filters.php original:
    // 'Action "%s" scheduled for %s'
    // Para simplificar, actualizamos solo la parte de la fecha en el título actual del log
    $log_obj = automatorwp_get_log_object( absint( $log->id ) );
    $old_title = $log_obj ? $log_obj->title : '';

    // Reemplazamos la fecha al final del título (el formato original termina en la fecha)
    $new_title = preg_replace(
        '/\d{4}[-\/]\d{2}[-\/]\d{2}.+$/',
        $new_date_local,
        $old_title
    );

    // Si el regex no encontró nada, simplemente añadimos la nueva fecha
    if( $new_title === $old_title || empty( $new_title ) ) {
        $new_title = $old_title . ' → ' . $new_date_local;
    }

    $wpdb->update(
        $ct_table->db->table_name,
        array( 'title' => $new_title ),
        array( 'id' => absint( $log->id ) ),
        array( '%s' ),
        array( '%d' )
    );

    ct_reset_setup_table();

    return true;
}


// =============================================================================
// FEATURE 2: admin-post – Ejecutar ahora
// =============================================================================

/**
 * Cancela el evento pendiente y ejecuta la acción de forma síncrona.
 *
 * @since 1.2.0
 */
function automatorwp_schedule_actions_handle_run_now() {

    $log_id = isset( $_GET['log_id'] ) ? absint( $_GET['log_id'] ) : 0;

    if( ! $log_id ) {
        wp_die( esc_html__( 'ID de log inválido.', 'automatorwp-schedule-actions' ) );
    }

    check_admin_referer( 'automatorwp_sa_run_now_' . $log_id );

    if( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Sin permisos suficientes.', 'automatorwp-schedule-actions' ) );
    }

    $log   = automatorwp_get_log_object( $log_id );
    $event = automatorwp_schedule_actions_get_scheduled_event( $log );

    if( ! $event ) {
        wp_die( esc_html__( 'Esta acción no está pendiente o ya fue ejecutada.', 'automatorwp-schedule-actions' ) );
    }

    $hook          = automatorwp_schedule_actions_cron_hook();
    $original_args = array(
        $event['action_id'],
        $event['user_id'],
        $event['event'],
        $event['datetime'],
    );

    // Cancelar el evento programado
    if( $event['source'] === 'action_scheduler' && function_exists( 'as_unschedule_action' ) ) {
        as_unschedule_action( $hook, $original_args );
    } else {
        wp_unschedule_event( $event['timestamp'], $hook, $original_args );
    }

    // Ejecutar la acción de forma síncrona con los mismos args que usaría el cron
    call_user_func_array(
        'automatorwp_schedule_actions_execute_action',
        $original_args
    );

    // Borrar el log ahora que la acción ya se ha ejecutado (Feature 4)
    automatorwp_schedule_actions_delete_log( $log_id );

    wp_safe_redirect( add_query_arg(
        array( 'automatorwp_sa_notice' => 'ran_now' ),
        admin_url( 'admin.php?page=automatorwp_logs' )
    ) );
    exit;
}
add_action( 'admin_post_automatorwp_sa_run_now', 'automatorwp_schedule_actions_handle_run_now' );


// =============================================================================
// FEATURE 3: Eliminar acciones programadas al borrar un usuario
// =============================================================================

/**
 * Al eliminar un usuario, cancela y borra todos sus logs de tipo schedule_actions
 * que tengan un evento todavía pendiente.
 *
 * @since 1.2.0
 *
 * @param int $user_id ID del usuario eliminado.
 */
function automatorwp_schedule_actions_on_user_deleted( $user_id ) {

    global $wpdb;

    $ct_table = ct_setup_table( 'automatorwp_logs' );
    $table    = $ct_table->db->table_name;

    $logs = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$table} WHERE type = %s AND user_id = %d",
        'schedule_actions',
        absint( $user_id )
    ) );

    ct_reset_setup_table();

    if( empty( $logs ) ) return;

    $hook = automatorwp_schedule_actions_cron_hook();

    foreach( $logs as $log ) {

        $event = automatorwp_schedule_actions_get_scheduled_event( $log );

        if( ! $event ) continue; // Ya ejecutado

        $original_args = array(
            $event['action_id'],
            $event['user_id'],
            $event['event'],
            $event['datetime'],
        );

        // Cancelar el evento
        if( $event['source'] === 'action_scheduler' && function_exists( 'as_unschedule_action' ) ) {
            as_unschedule_action( $hook, $original_args );
        } else {
            wp_unschedule_event( $event['timestamp'], $hook, $original_args );
        }

        // Borrar el log
        $ct_table = ct_setup_table( 'automatorwp_logs' );
        $ct_table->db->delete( absint( $log->id ) );
        ct_reset_setup_table();
    }
}
add_action( 'delete_user', 'automatorwp_schedule_actions_on_user_deleted', 10, 1 );


// =============================================================================
// FEATURE 4: Borrar el log tras la ejecución
// =============================================================================

/**
 * Borra un log concreto por su ID de la tabla automatorwp_logs.
 * Función helper usada tanto por "Ejecutar ahora" como por el hook del cron.
 *
 * @since 1.2.0
 *
 * @param int $log_id
 */
function automatorwp_schedule_actions_delete_log( $log_id ) {

    $log_id = absint( $log_id );
    if( ! $log_id ) return;

    $ct_table = ct_setup_table( 'automatorwp_logs' );
    $ct_table->db->delete( $log_id );
    ct_reset_setup_table();
}

/**
 * Cuando el cron (o Action Scheduler) ejecuta la acción programada,
 * borramos el log de tipo schedule_actions correspondiente.
 *
 * Buscamos por object_id=$action_id y user_id para localizar el log correcto.
 * Prioridad 9999 para ejecutarse DESPUÉS de automatorwp_schedule_actions_execute_action (prioridad 10).
 *
 * @since 1.2.0
 *
 * @param int   $action_id
 * @param int   $user_id
 * @param array $event
 * @param int   $datetime
 */
function automatorwp_schedule_actions_delete_log_after_run( $action_id, $user_id, $event = array(), $datetime = 0 ) {

    global $wpdb;

    $action_id = absint( $action_id );
    $user_id   = absint( $user_id );

    if( ! $action_id || ! $user_id ) return;

    $ct_table = ct_setup_table( 'automatorwp_logs' );
    $table    = $ct_table->db->table_name;

    // Buscar todos los logs pendientes de este action+user (puede haber más de uno)
    $logs = $wpdb->get_results( $wpdb->prepare(
        "SELECT id FROM {$table} WHERE type = %s AND object_id = %d AND user_id = %d",
        'schedule_actions',
        $action_id,
        $user_id
    ) );

    ct_reset_setup_table();

    if( empty( $logs ) ) return;

    foreach( $logs as $log ) {
        automatorwp_schedule_actions_delete_log( absint( $log->id ) );
    }
}
add_action( 'automatorwp_schedule_actions_execute_action', 'automatorwp_schedule_actions_delete_log_after_run', 9999, 4 );


// =============================================================================
// Admin notice
// =============================================================================

/**
 * Muestra aviso de éxito tras usar "Ejecutar ahora".
 *
 * @since 1.2.0
 */
function automatorwp_schedule_actions_admin_notices_custom() {
    if( isset( $_GET['automatorwp_sa_notice'] ) && $_GET['automatorwp_sa_notice'] === 'ran_now' ) { ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e( '✓ La acción programada se ha ejecutado correctamente.', 'automatorwp-schedule-actions' ); ?></p>
        </div>
    <?php }
}
add_action( 'admin_notices', 'automatorwp_schedule_actions_admin_notices_custom' );