<?php
/**
 * Unit tests for SLP_Dynamic_Redirects_Parameter
 *
 * Run with: php tests/test-parameter-redirect.php
 *
 * No external dependencies required — pure PHP, no WordPress needed.
 */

// ---------------------------------------------------------------------------
// Minimal stubs so the class file can be loaded outside WordPress
// ---------------------------------------------------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

function __( $text, $domain = '' ) { return $text; }
function _e( $text, $domain = '' ) { echo $text; }
function esc_attr( $text ) { return htmlspecialchars( $text, ENT_QUOTES ); }
function esc_html( $text ) { return htmlspecialchars( $text, ENT_QUOTES ); }
function esc_url( $url )   { return $url; }
function esc_url_raw( $url ) { return $url; }
function esc_attr_e( $text, $domain = '' ) { echo esc_attr( $text ); }
function sanitize_text_field( $text ) { return trim( strip_tags( $text ) ); }
function wp_unslash( $value ) { return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( $value ); }
function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {}
function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {}
function has_filter( $tag ) { return false; }
function selected( $selected, $current, $echo = true ) {
    $result = $selected == $current ? ' selected="selected"' : '';
    if ( $echo ) { echo $result; }
    return $result;
}

// ---------------------------------------------------------------------------
// Make the private helpers testable via a subclass
// ---------------------------------------------------------------------------

require_once __DIR__ . '/../includes/class-parameter-redirect.php';

class Testable_SLP_Dynamic_Redirects_Parameter extends SLP_Dynamic_Redirects_Parameter {

    /**
     * Expose get_request_param() for testing.
     */
    public function test_get_request_param( $name ) {
        return $this->get_request_param( $name );
    }

    /**
     * Expose parse_condition() for testing.
     */
    public function test_parse_condition( $actual, $condition, $expected ) {
        return $this->parse_condition( $actual, $condition, $expected );
    }
}

// ---------------------------------------------------------------------------
// Tiny test runner
// ---------------------------------------------------------------------------

$pass  = 0;
$fail  = 0;
$tests = array();

function assert_true( $label, $value ) {
    global $pass, $fail, $tests;
    if ( $value === true ) {
        $pass++;
        $tests[] = array( 'PASS', $label );
    } else {
        $fail++;
        $tests[] = array( 'FAIL', $label . ' (got: ' . var_export( $value, true ) . ')' );
    }
}

function assert_equals( $label, $actual, $expected ) {
    global $pass, $fail, $tests;
    if ( $actual === $expected ) {
        $pass++;
        $tests[] = array( 'PASS', $label );
    } else {
        $fail++;
        $tests[] = array( 'FAIL', $label . ' (expected: ' . var_export( $expected, true ) . ', got: ' . var_export( $actual, true ) . ')' );
    }
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

$obj = new Testable_SLP_Dynamic_Redirects_Parameter();

// ── equal ──────────────────────────────────────────────────────────────────

// site.com/link?pass=123  →  SHOULD match  (equal, "123")
$_REQUEST = array( 'pass' => '123' );
assert_true( '[equal] pass=123 matches "123"',
    $obj->test_parse_condition( $obj->test_get_request_param( 'pass' ), 'equal', '123' ) );

// site.com/link?pass=     →  SHOULD NOT match  (equal, "123")
$_REQUEST = array( 'pass' => '' );
assert_true( '[equal] pass= does NOT match "123"',
    ! $obj->test_parse_condition( $obj->test_get_request_param( 'pass' ), 'equal', '123' ) );

// site.com/link           →  SHOULD NOT match  (equal, "123")
$_REQUEST = array();
assert_true( '[equal] absent param does NOT match "123"',
    ! $obj->test_parse_condition( $obj->test_get_request_param( 'pass' ), 'equal', '123' ) );

// ── not_equal ──────────────────────────────────────────────────────────────

$_REQUEST = array( 'pass' => 'abc' );
assert_true( '[not_equal] pass=abc does NOT equal "123"',
    $obj->test_parse_condition( $obj->test_get_request_param( 'pass' ), 'not_equal', '123' ) );

$_REQUEST = array( 'pass' => '123' );
assert_true( '[not_equal] pass=123 IS equal to "123" → false',
    ! $obj->test_parse_condition( $obj->test_get_request_param( 'pass' ), 'not_equal', '123' ) );

// ── contains ───────────────────────────────────────────────────────────────

$_REQUEST = array( 'q' => 'hello world' );
assert_true( '[contains] "hello world" contains "world"',
    $obj->test_parse_condition( $obj->test_get_request_param( 'q' ), 'contains', 'world' ) );

$_REQUEST = array( 'q' => 'hello' );
assert_true( '[contains] "hello" does NOT contain "world"',
    ! $obj->test_parse_condition( $obj->test_get_request_param( 'q' ), 'contains', 'world' ) );

// case-insensitive
$_REQUEST = array( 'q' => 'Hello World' );
assert_true( '[contains] case-insensitive: "Hello World" contains "world"',
    $obj->test_parse_condition( $obj->test_get_request_param( 'q' ), 'contains', 'world' ) );

// ── not_contains ───────────────────────────────────────────────────────────

$_REQUEST = array( 'q' => 'hello' );
assert_true( '[not_contains] "hello" does not contain "world"',
    $obj->test_parse_condition( $obj->test_get_request_param( 'q' ), 'not_contains', 'world' ) );

// ── starts_with ────────────────────────────────────────────────────────────

$_REQUEST = array( 'token' => 'abc123' );
assert_true( '[starts_with] "abc123" starts with "abc"',
    $obj->test_parse_condition( $obj->test_get_request_param( 'token' ), 'starts_with', 'abc' ) );

$_REQUEST = array( 'token' => '123abc' );
assert_true( '[starts_with] "123abc" does NOT start with "abc"',
    ! $obj->test_parse_condition( $obj->test_get_request_param( 'token' ), 'starts_with', 'abc' ) );

// ── ends_with ──────────────────────────────────────────────────────────────

$_REQUEST = array( 'token' => 'hello_world' );
assert_true( '[ends_with] "hello_world" ends with "world"',
    $obj->test_parse_condition( $obj->test_get_request_param( 'token' ), 'ends_with', 'world' ) );

$_REQUEST = array( 'token' => 'world_hello' );
assert_true( '[ends_with] "world_hello" does NOT end with "world"',
    ! $obj->test_parse_condition( $obj->test_get_request_param( 'token' ), 'ends_with', 'world' ) );

// ── empty ──────────────────────────────────────────────────────────────────

// site.com/link?pass=     →  IS empty
$_REQUEST = array( 'pass' => '' );
assert_true( '[empty] pass= is empty',
    $obj->test_parse_condition( $obj->test_get_request_param( 'pass' ), 'empty', '' ) );

// site.com/link           →  param absent also counts as empty
$_REQUEST = array();
assert_true( '[empty] absent param counts as empty',
    $obj->test_parse_condition( $obj->test_get_request_param( 'pass' ), 'empty', '' ) );

// site.com/link?pass=123  →  NOT empty
$_REQUEST = array( 'pass' => '123' );
assert_true( '[empty] pass=123 is NOT empty',
    ! $obj->test_parse_condition( $obj->test_get_request_param( 'pass' ), 'empty', '' ) );

// ── not_empty ──────────────────────────────────────────────────────────────

$_REQUEST = array( 'pass' => '123' );
assert_true( '[not_empty] pass=123 is NOT empty → true',
    $obj->test_parse_condition( $obj->test_get_request_param( 'pass' ), 'not_empty', '' ) );

$_REQUEST = array( 'pass' => '' );
assert_true( '[not_empty] pass= IS empty → false',
    ! $obj->test_parse_condition( $obj->test_get_request_param( 'pass' ), 'not_empty', '' ) );

$_REQUEST = array();
assert_true( '[not_empty] absent param IS empty → false',
    ! $obj->test_parse_condition( $obj->test_get_request_param( 'pass' ), 'not_empty', '' ) );

// ── match() (full integration) ─────────────────────────────────────────────

$_REQUEST = array( 'pass' => '123' );
$result = $obj->match( false, 'parameter', array(
    'param_name' => 'pass',
    'condition'  => 'equal',
    'value'      => '123',
    'url'        => 'https://example.com/premium',
) );
assert_equals( '[match] pass=123, equal "123" → redirect URL', $result, 'https://example.com/premium' );

$_REQUEST = array( 'pass' => '' );
$result = $obj->match( false, 'parameter', array(
    'param_name' => 'pass',
    'condition'  => 'equal',
    'value'      => '123',
    'url'        => 'https://example.com/premium',
) );
assert_equals( '[match] pass= (empty), equal "123" → no match', $result, false );

// Already matched → should be returned unchanged regardless.
$_REQUEST = array( 'pass' => '123' );
$result = $obj->match( 'https://already-matched.com', 'parameter', array(
    'param_name' => 'pass',
    'condition'  => 'equal',
    'value'      => '123',
    'url'        => 'https://example.com/premium',
) );
assert_equals( '[match] already matched → pass through', $result, 'https://already-matched.com' );

// Wrong type → no match.
$_REQUEST = array( 'pass' => '123' );
$result = $obj->match( false, 'technology', array(
    'param_name' => 'pass',
    'condition'  => 'equal',
    'value'      => '123',
    'url'        => 'https://example.com/premium',
) );
assert_equals( '[match] wrong type → false', $result, false );

// ends_with with an empty expected value should never match.
$_REQUEST = array( 'token' => 'anything' );
$result = $obj->test_parse_condition( $obj->test_get_request_param( 'token' ), 'ends_with', '' );
assert_equals( '[ends_with] empty expected value should NOT match', $result, false );

// ---------------------------------------------------------------------------
// Output results
// ---------------------------------------------------------------------------

echo "\n";
echo "SLP Dynamic Redirects – Parameter Redirect — Test Results\n";
echo str_repeat( '─', 60 ) . "\n";

foreach ( $tests as $test ) {
    $icon = $test[0] === 'PASS' ? '✓' : '✗';
    printf( "  %s [%s] %s\n", $icon, $test[0], $test[1] );
}

echo str_repeat( '─', 60 ) . "\n";
printf( "  Total: %d  |  Passed: %d  |  Failed: %d\n\n", $pass + $fail, $pass, $fail );

exit( $fail > 0 ? 1 : 0 );
