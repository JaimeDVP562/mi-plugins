<?php
/**
 * Parameter Redirect
 *
 * Handles redirects based on URL parameters (GET/POST).
 * Example: site.com/link?pass=123 can redirect to a different URL than site.com/link?pass=
 *
 * @package SLP_Dynamic_Redirects
 * @subpackage Parameter
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class SLP_Dynamic_Redirects_Parameter {

    /**
     * The redirect type identifier.
     * Must match what is registered in the main dynamic redirects add-on.
     *
     * @var string
     */
    public $type = 'parameter';

    /**
     * Available comparison conditions.
     * Each condition is: [ label, operator ]
     *
     * @var array
     */
    public $conditions = array();

    /**
     * SLP_Dynamic_Redirects_Parameter constructor.
     */
    public function __construct() {

        $this->conditions = array(
            'equal'         => __( 'Is equal to', 'slp-dynamic-redirects' ),
            'not_equal'     => __( 'Is not equal to', 'slp-dynamic-redirects' ),
            'contains'      => __( 'Contains', 'slp-dynamic-redirects' ),
            'not_contains'  => __( 'Does not contain', 'slp-dynamic-redirects' ),
            'starts_with'   => __( 'Starts with', 'slp-dynamic-redirects' ),
            'ends_with'     => __( 'Ends with', 'slp-dynamic-redirects' ),
            'empty'         => __( 'Is empty', 'slp-dynamic-redirects' ),
            'not_empty'     => __( 'Is not empty', 'slp-dynamic-redirects' ),
        );

        $this->setup_hooks();
    }

    /**
     * Setup WordPress hooks.
     */
    public function setup_hooks() {

        // Register our type in the dynamic redirects types list
        add_filter( 'slp_dynamic_redirects_types', array( $this, 'register_type' ) );

        // Render the UI fields for this type in the link edit screen
        add_action( 'slp_dynamic_redirects_render_type_fields', array( $this, 'render_fields' ), 10, 2 );

        // Sanitize/save our type's data when a link is saved
        add_filter( 'slp_dynamic_redirects_sanitize_type_data', array( $this, 'sanitize_data' ), 10, 3 );

        // Evaluate whether this rule matches the current visitor
        add_filter( 'slp_dynamic_redirects_match_type', array( $this, 'match' ), 10, 3 );
    }

    // -------------------------------------------------------------------------
    // Registration
    // -------------------------------------------------------------------------

    /**
     * Register the "parameter" type in the dynamic redirects system.
     *
     * @param array $types Existing redirect types.
     * @return array
     */
    public function register_type( $types ) {

        $types[ $this->type ] = __( 'Parameter', 'slp-dynamic-redirects' );

        return $types;
    }

    // -------------------------------------------------------------------------
    // UI / Fields
    // -------------------------------------------------------------------------

    /**
     * Render the HTML fields for a single "parameter" redirect rule row.
     *
     * Called by the main add-on when rendering the metabox for a rule whose
     * type === 'parameter'.
     *
     * @param string $type    The redirect type being rendered.
     * @param array  $data    Saved rule data (may be empty for new rules).
     */
    public function render_fields( $type, $data ) {

        if ( $type !== $this->type ) {
            return;
        }

        $param_name = isset( $data['param_name'] ) ? esc_attr( $data['param_name'] ) : '';
        $condition  = isset( $data['condition'] )  ? esc_attr( $data['condition'] )  : 'equal';
        $value      = isset( $data['value'] )      ? esc_attr( $data['value'] )      : '';
        $url        = isset( $data['url'] )        ? esc_url( $data['url'] )         : '';

        ?>
        <div class="slp-dr-parameter-fields slp-dr-type-fields">

            <?php /* Parameter name */ ?>
            <div class="slp-dr-field-row">
                <label><?php _e( 'Parameter name', 'slp-dynamic-redirects' ); ?></label>
                <input
                    type="text"
                    class="slp-dr-param-name"
                    name="slp_dr_parameter[param_name]"
                    value="<?php echo $param_name; ?>"
                    placeholder="<?php esc_attr_e( 'e.g. pass', 'slp-dynamic-redirects' ); ?>"
                />
                <p class="description">
                    <?php _e( 'The name of the URL parameter to evaluate (GET or POST).', 'slp-dynamic-redirects' ); ?>
                </p>
            </div>

            <?php /* Condition selector */ ?>
            <div class="slp-dr-field-row">
                <label><?php _e( 'Condition', 'slp-dynamic-redirects' ); ?></label>
                <select class="slp-dr-condition" name="slp_dr_parameter[condition]">
                    <?php foreach ( $this->conditions as $key => $label ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $condition, $key ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php /* Value to compare against (hidden when condition is empty/not_empty) */ ?>
            <div class="slp-dr-field-row slp-dr-value-row">
                <label><?php _e( 'Value', 'slp-dynamic-redirects' ); ?></label>
                <input
                    type="text"
                    class="slp-dr-param-value"
                    name="slp_dr_parameter[value]"
                    value="<?php echo $value; ?>"
                    placeholder="<?php esc_attr_e( 'e.g. 123', 'slp-dynamic-redirects' ); ?>"
                />
                <p class="description">
                    <?php _e( 'The value the parameter will be compared against.', 'slp-dynamic-redirects' ); ?>
                </p>
            </div>

            <?php /* Target URL */ ?>
            <div class="slp-dr-field-row">
                <label><?php _e( 'Redirect URL', 'slp-dynamic-redirects' ); ?></label>
                <input
                    type="url"
                    class="slp-dr-url widefat"
                    name="slp_dr_parameter[url]"
                    value="<?php echo $url; ?>"
                    placeholder="https://example.com/destination"
                />
                <p class="description">
                    <?php _e( 'Where to redirect the visitor when the condition is met.', 'slp-dynamic-redirects' ); ?>
                </p>
            </div>

        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // Save / Sanitize
    // -------------------------------------------------------------------------

    /**
     * Sanitize the posted data for the "parameter" type before saving it.
     *
     * @param mixed  $sanitized  Already-sanitized data (pass-through for other types).
     * @param string $type       The redirect type being saved.
     * @param array  $raw        Raw $_POST data for the rule.
     * @return mixed
     */
    public function sanitize_data( $sanitized, $type, $raw ) {

        if ( $type !== $this->type ) {
            return $sanitized;
        }

        $data = isset( $raw['slp_dr_parameter'] ) ? $raw['slp_dr_parameter'] : array();

        $allowed_conditions = array_keys( $this->conditions );

        $condition = isset( $data['condition'] ) && in_array( $data['condition'], $allowed_conditions, true )
            ? $data['condition']
            : 'equal';

        return array(
            'param_name' => isset( $data['param_name'] ) ? sanitize_text_field( $data['param_name'] ) : '',
            'condition'  => $condition,
            'value'      => isset( $data['value'] )      ? sanitize_text_field( $data['value'] )      : '',
            'url'        => isset( $data['url'] )        ? esc_url_raw( $data['url'] )                : '',
        );
    }

    // -------------------------------------------------------------------------
    // Redirect matching
    // -------------------------------------------------------------------------

    /**
     * Check whether this rule matches the current request.
     *
     * The main add-on calls this filter for every saved rule. We check our type
     * and run the comparison. If it matches, we return the target URL so the
     * add-on knows it should redirect there.
     *
     * @param string|false $match  URL to redirect to, or false if not yet matched.
     * @param string       $type   The rule type.
     * @param array        $data   Saved rule data.
     * @return string|false        URL on match, false otherwise.
     */
    public function match( $match, $type, $data ) {

        // Already matched by a previous rule — keep it.
        if ( $match !== false ) {
            return $match;
        }

        if ( $type !== $this->type ) {
            return $match;
        }

        $param_name = isset( $data['param_name'] ) ? $data['param_name'] : '';
        $condition  = isset( $data['condition'] )  ? $data['condition']  : 'equal';
        $expected   = isset( $data['value'] )      ? $data['value']      : '';
        $url        = isset( $data['url'] )        ? $data['url']        : '';

        // Nothing useful saved — skip.
        if ( empty( $param_name ) || empty( $url ) ) {
            return false;
        }

        // Read the actual parameter value from the request (GET or POST).
        // Using $_REQUEST covers both, as instructed.
        $actual = $this->get_request_param( $param_name );

        // Evaluate the condition.
        if ( $this->parse_condition( $actual, $condition, $expected ) ) {
            return $url;
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Retrieve a parameter value from the current HTTP request.
     *
     * Uses $_REQUEST so it works for both GET and POST.
     * Returns null (not an empty string) when the parameter is completely absent,
     * so we can distinguish "param=&..." (present but empty) from "no param at all".
     *
     * @param string $name Parameter name.
     * @return string|null
     */
    protected function get_request_param( $name ) {

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! isset( $_REQUEST[ $name ] ) ) {
            return null;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return sanitize_text_field( wp_unslash( $_REQUEST[ $name ] ) );
    }

    /**
     * Compare two strings using the given condition operator.
     *
     * Mirrors the AutomatorWP parse_condition() logic mentioned in the brief.
     *
     * @param string|null $actual    The real value from the request (null = param absent).
     * @param string      $condition Condition key (see $this->conditions).
     * @param string      $expected  The value configured in the rule.
     * @return bool
     */
    protected function parse_condition( $actual, $condition, $expected ) {

        switch ( $condition ) {

            // Param must exist AND its value must equal the expected string.
            // This covers: site.com/link?pass=123  (good)
            // And rejects:  site.com/link?pass=    (bad — value is empty)
            case 'equal':
                return $actual !== null && $actual === $expected;

            // Param doesn't exist, or its value differs from expected.
            case 'not_equal':
                return $actual === null || $actual !== $expected;

            // Value contains the expected substring (case-insensitive).
            case 'contains':
                return $actual !== null && strpos( strtolower( $actual ), strtolower( $expected ) ) !== false;

            // Value does NOT contain the expected substring.
            case 'not_contains':
                return $actual === null || strpos( strtolower( $actual ), strtolower( $expected ) ) === false;

            // Value starts with expected string (case-insensitive).
            case 'starts_with':
                return $actual !== null && strpos( strtolower( $actual ), strtolower( $expected ) ) === 0;

            // Value ends with expected string (case-insensitive).
            case 'ends_with':
                if ( $actual === null ) {
                    return false;
                }
                $len = strlen( $expected );
                if ( $len === 0 ) {
                    return false;
                }
                return substr_compare( strtolower( $actual ), strtolower( $expected ), -$len ) === 0;

            // Param is absent OR present but with an empty value.
            // Covers: site.com/link?pass=   (empty)
            // And:    site.com/link          (absent)
            case 'empty':
                return $actual === null || $actual === '';

            // Param is present AND its value is non-empty.
            case 'not_empty':
                return $actual !== null && $actual !== '';

            default:
                return false;
        }
    }
}
