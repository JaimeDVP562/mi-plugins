<?php
/**
 * User Product
 *
 * @package     AutomatorWP\Integrations\Thrive_Apprentice\Actions\User_Product
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Thrive_Apprentice_User_Product extends AutomatorWP_Integration_Action {

    public $integration = 'thrive_apprentice';
    public $action = 'thrive_apprentice_user_product';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Enroll user to a product', 'automatorwp' ),
            'select_option'     => __( 'Enroll user to <strong>a product</strong>', 'automatorwp' ),
            /* translators: %1$s: Product title. */
            'edit_label'        => sprintf( __( 'Enroll user to %1$s', 'automatorwp' ), '{term}' ),
            /* translators: %1$s: Product title. */
            'log_label'         => sprintf( __( 'Enroll user to %1$s', 'automatorwp' ), '{term}' ),
            'options'           => array(
                'term' => automatorwp_utilities_term_option( array(
                    'name'                  => __( 'Product:', 'automatorwp-thrive-apprentice' ),
                    'option_none_label'     => __( 'all products', 'automatorwp-thrive-apprentice' ),
                    'option_custom'         => true,
                    'option_custom_desc'    => __( 'Product ID', 'automatorwp-thrive-apprentice' ),
                    'taxonomy'              => 'tva_product',
                ) ),
            ),
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        global $wpdb;

        // Shorthand
        $product_id = $action_options['term'];

        // Check specific product
        if( $product_id !== 'any' ) {

            $product = get_term( $product_id, 'tva_product' );

            // Bail if course doesn't exists
            if( ! $product ) {
                return;
            }

            $products = array( $product_id );

        } else {

            $terms = get_terms( array( 
                'taxonomy' => 'tva_product',
                'hide_empty' => false,
            ) );

            foreach ( $terms as $term ) {
                $products[] = $term->term_id;
            }

        }

        // Enroll user in products
        foreach( $products as $product_id ) {
            TVA_Customer::enrol_user_to_product( $user_id, $product_id );
        }

    }

}

new AutomatorWP_Thrive_Apprentice_User_Product();