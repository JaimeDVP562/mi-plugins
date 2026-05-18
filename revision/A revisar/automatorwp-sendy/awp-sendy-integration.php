<?php
/**
 * Plugin Name: [AWP] Integracion Sendy
 * Description: Integración Sendy con AutomateWP (acciones + triggers)
 * Version: 1.1
 * Author: Tu Nombre
 */

if (!defined('ABSPATH')) {
    exit;
}

class AWP_Sendy_Integration {

    private $option_name = 'awp_sendy_settings';

    public function __construct() {
        // ADMIN
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);

        // FRONTEND
        add_shortcode('awp_sendy_form', [$this, 'render_form']);
        add_action('init', [$this, 'handle_form']);

        // AWP INTEGRATION
        add_filter('automatewp_actions', [$this, 'register_awp_action']);
        add_filter('automatewp_triggers', [$this, 'register_awp_trigger']);
    }

    /**
     * ADMIN MENU
     */
    public function add_admin_menu() {
        add_options_page(
            'Sendy Settings',
            'AWP Sendy',
            'manage_options',
            'awp-sendy',
            [$this, 'settings_page']
        );
    }

    /**
     * REGISTER SETTINGS
     */
    public function register_settings() {
        register_setting($this->option_name, $this->option_name);

        add_settings_section(
            'awp_sendy_section',
            'Configuración Sendy',
            null,
            'awp-sendy'
        );

        add_settings_field(
            'api_url',
            'API URL',
            [$this, 'api_url_field'],
            'awp-sendy',
            'awp_sendy_section'
        );

        add_settings_field(
            'list_id',
            'List ID',
            [$this, 'list_id_field'],
            'awp-sendy',
            'awp_sendy_section'
        );
    }

    public function api_url_field() {
        $options = get_option($this->option_name);
        ?>
        <input type="text" name="<?php echo $this->option_name; ?>[api_url]" value="<?php echo esc_attr($options['api_url'] ?? ''); ?>" size="50">
        <?php
    }

    public function list_id_field() {
        $options = get_option($this->option_name);
        ?>
        <input type="text" name="<?php echo $this->option_name; ?>[list_id]" value="<?php echo esc_attr($options['list_id'] ?? ''); ?>">
        <?php
    }

    /**
     * SETTINGS PAGE
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Configuración Sendy</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields($this->option_name);
                do_settings_sections('awp-sendy');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * FRONTEND FORM
     */
    public function render_form() {
        ob_start();
        ?>
        <form method="post">
            <input type="email" name="awp_email" placeholder="Tu email" required>
            <input type="text" name="awp_name" placeholder="Tu nombre">
            <?php wp_nonce_field('awp_sendy_nonce', 'awp_nonce'); ?>
            <button type="submit">Suscribirse</button>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * HANDLE FORM
     */
    public function handle_form() {

        if (!isset($_POST['awp_email'])) return;

        if (!isset($_POST['awp_nonce']) || !wp_verify_nonce($_POST['awp_nonce'], 'awp_sendy_nonce')) {
            return;
        }

        $email = sanitize_email($_POST['awp_email']);
        $name  = sanitize_text_field($_POST['awp_name']);

        // Enviar a Sendy
        $this->subscribe($email, $name);

        // 🔥 Disparar trigger AWP
        do_action('awp_sendy_form_submitted', $email, $name);
    }

    /**
     * SEND TO SENDY
     */
    private function subscribe($email, $name) {

        $options = get_option($this->option_name);

        if (empty($options['api_url']) || empty($options['list_id'])) {
            return false;
        }

        $response = wp_remote_post($options['api_url'] . '/subscribe', [
            'body' => [
                'email' => $email,
                'name'  => $name,
                'list'  => $options['list_id'],
                'boolean' => 'true'
            ]
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        return true;
    }

    /**
     * =========================
     * AWP ACTION
     * =========================
     */
    public function register_awp_action($actions) {

        $actions['awp_sendy_subscribe'] = [
            'label' => 'Suscribir usuario a Sendy',
            'group' => 'Sendy',
            'callback' => [$this, 'awp_sendy_action_callback'],
            'fields' => [
                'email' => [
                    'type' => 'text',
                    'label' => 'Email'
                ],
                'name' => [
                    'type' => 'text',
                    'label' => 'Nombre'
                ]
            ]
        ];

        return $actions;
    }

    public function awp_sendy_action_callback($user_id, $action_data, $workflow, $step) {

        $email = !empty($action_data['email']) ? $action_data['email'] : '';
        $name  = !empty($action_data['name']) ? $action_data['name'] : '';

        if (empty($email)) return;

        $this->subscribe($email, $name);
    }

    /**
     * =========================
     * AWP TRIGGER
     * =========================
     */
    public function register_awp_trigger($triggers) {

        $triggers['awp_sendy_form_submitted'] = [
            'label' => 'Formulario Sendy enviado',
            'group' => 'Sendy',
            'action' => 'awp_sendy_form_submitted'
        ];

        return $triggers;
    }
}

new AWP_Sendy_Integration();