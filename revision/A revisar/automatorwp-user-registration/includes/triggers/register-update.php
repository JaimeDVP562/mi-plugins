<?php
/**
 * User Register
 *
 * @package     AutomatorWP\Integrations\User_Registration\Triggers\User_Register
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_User_Registration_Update extends AutomatorWP_Integration_Trigger {

    public $integration = 'user_registration';
    public $trigger = 'user_registration_user_update';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {
  automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User update profile', 'automatorwp-user-registration' ),
            'select_option'     => __( 'User <strong>update </strong> profile', 'automatorwp-user-registration' ),
            /* translators  %1$s: Number of times.*/
            'edit_label'        => sprintf( __( ' User update profile %1$s time(s)', 'automatorwp-user-registration' ), '{times}' ),
            
            'log_label'         => sprintf( __( 'User registers through', 'automatorwp-user-registration' ),  ),
            'action'            => 'user_registration_validate_profile_update',
            'function'          => array( $this, 'listenerupdate' ),
            'priority'          => 10,
            'accepted_args'     => 4,
            'options'           => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_user_registration_get_webhook_tags()
            )
        ) );

    }

    
    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param array $profile 
     * @param array $form_data Data from the registration form
     * @param int $form_id ID of the form used
     * @param int $user_id ID of the newly registered user
     */


    //do_action( 'user_registration_validate_profile_update', $profile, $form_data, $form_id, $user_id );
    public function listenerupdate( $profile ,$form_data, $form_id, $user_id ) {

        error_log('MENSAJE DE PRUEBA');
        $usss_id = get_current_user_id();
        if ($usss_id === 0 ) {
        return;
        }

       
        //ANOTACION ->(Tengo que desarrollar esta parte para sacar toda la info del form_data, datos como nickname, email, etc.
        //Para este miercoles espero tener todo el desarrollo hecho para que se puedan usar como tags todos los datos gratuitos de esta aplicacion) 
        //el form_data actualmente tiene los siguientes datos: first y last, display_name, user_url, user_email, user_pass etc.....
        $user_login = $form_data['user_login']->value;
        $user_email = $form_data['user_email']->value;
        $user_profile =  $profile;
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'form_id'       => $form_id,
            'form_data'     => $form_data,
            'user_login'    => $user_login,
            'user_email'    => $user_email,
            'first_name'    => $form_data['first_name']->value,
            'last_name'     => $form_data['last_name']->value,
            'nickname'      => $form_data['nickname']->value,
            'display_name'  => $form_data['display_name']->value,
            'user_url'      => $form_data['user_url']->value,
            'description'   => $form_data['description']->value,
            'user_profile'  => $form_data["display_name"]->value
        ) );

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if user deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                          True if user deserves trigger, false otherwise
     */
    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Don't deserve if form is not received
        if( ! isset( $event['form_id'] ) ) {


            $fallo = ('fallo: form_id existe');
            
            error_log($fallo);
            return false;
        }

        // Bail if form doesn't match with the trigger option
        //if( $trigger_options['form'] !== 'any' && absint( $event['form_id'] ) !== absint( $trigger_options['form'] ) ) {
        //    return $deserves_trigger;
        //}

        return $deserves_trigger;

    }


    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }
        //Anotacion ->(Tengo que desarrollar esta parte para sacar toda la info del form_data, datos como nickname, email, etc.
        $log_meta['user_login'] = ( isset( $event['user_login'] ) ? $event['user_login'] : '' );
        $log_meta['user_email'] = ( isset( $event['user_email'] ) ? $event['user_email'] : '' );
        $log_meta['first_name'] = ( isset( $event['first_name'] ) ? $event['first_name'] : '' );
        $log_meta['last_name'] =  ( isset( $event['last_name']  ) ? $event['last_name'] : '');
        $log_meta['nickname'] =  ( isset( $event['nickname']  ) ? $event['nickname'] : '');
        $log_meta['display_name'] =  ( isset( $event['display_name']  ) ? $event['display_name'] : '');
        $log_meta['user_url'] =  ( isset( $event['user_url']  ) ? $event['user_url'] : '');
        $log_meta['description'] =  ( isset( $event['description']  ) ? $event['description'] : '');
        $log_meta['user_profile'] = ( isset( $event['user_profile'] ) ? $event['user_profile'] : '');

        return $log_meta;

    }

}

new AutomatorWP_User_Registration_Update();