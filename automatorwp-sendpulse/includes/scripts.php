<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Sendpulse\Scripts
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_sendpulse_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets - assets registration for SendPulse
    wp_register_style( 'automatorwp-sendpulse-css', AUTOMATORWP_SENDPULSE_URL . 'assets/css/automatorwp-sendpulse' . $suffix . '.css', array(), AUTOMATORWP_SENDPULSE_VER, 'all' );

    // Scripts - assets registration for SendPulse
    wp_register_script( 'automatorwp-sendpulse-js', AUTOMATORWP_SENDPULSE_URL . 'assets/js/automatorwp-sendpulse' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_SENDPULSE_VER, true );

}
add_action( 'admin_init', 'automatorwp_sendpulse_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_sendpulse_admin_enqueue_scripts( $hook ) {

    // Only enqueue on AutomatorWP pages where admin UI is shown (settings and automations)
    $allowed_pages = array( 'automatorwp_settings', 'automatorwp_automations', 'edit_automatorwp_automations', 'automatorwp' );

    $should_enqueue = false;
    if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $allowed_pages, true ) ) {
        $should_enqueue = true;
    } else {
        // Try to detect AutomatorWP screens via current screen ID
        if ( function_exists( 'get_current_screen' ) ) {
            $screen = get_current_screen();
            if ( $screen && strpos( $screen->id, 'automatorwp' ) !== false ) {
                $should_enqueue = true;
            }
        }

        // Also allow when editing AutomatorWP custom post types (some builders render on edit.php)
        if ( isset( $_GET['post_type'] ) && strpos( $_GET['post_type'], 'automatorwp' ) !== false ) {
            $should_enqueue = true;
        }
    }

    if ( ! $should_enqueue ) {
        return;
    }

    wp_enqueue_style( 'automatorwp-sendpulse-css' );

    wp_localize_script( 'automatorwp-sendpulse-js', 'automatorwp_sendpulse', array(
        'nonce'    => automatorwp_get_admin_nonce(),
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        // Exact settings page URL used as OAuth redirect URI (must match provider registration)
        'settings_page_url' => admin_url( 'admin.php?page=automatorwp_settings&tab=sendpulse' ),
    ) );

    wp_enqueue_script( 'automatorwp-sendpulse-js' );

    // Inline: apply a small UI robustness fix in the AutomatorWP admin builder so
    // the SendPulse action option box reliably expands and the input fields are
    // focusable. This mirrors the temporary workaround used during debugging but
    // runs only in admin screens where this plugin enqueues its scripts.
    $inline = <<<'JS'
(function(){
    try {
        // Remove draggable attributes that cause the cursor to show as "move"
        // and can intercept clicks on the option toggle.
        var els = document.querySelectorAll('[draggable="true"]');
        Array.prototype.forEach.call(els, function(e){ try{ e.removeAttribute('draggable'); e.style.cursor = ''; }catch(ignore){} });

        // Ensure clicking the action label toggles the option form. Some
        // admin themes or builders may intercept pointer events; make the
        // label explicitly clickable.
        document.addEventListener('click', function(ev){
            var t = ev.target;
            // find nearest action label element
            var label = t.closest ? t.closest('.automatorwp-automation-item-label > .automatorwp-option') : null;
            if (label) {
                try { label.click(); } catch(ignore){}
            }
        }, true);

        // Helper: ensure a visible Subscriber Email input exists inside a form element
        function ensureFallbackInDOM(of){
            try {
                if (!of || !of.querySelector) return;
                // skip if an email input already present
                if ( of.querySelector('input[type="email"], input[name="email"], input[data-automatorwp-fallback="1"]') ) return;
                // find label that mentions Subscriber Email
                var labels = of.querySelectorAll('label');
                var found = null;
                for (var i=0;i<labels.length;i++){
                    try{ if (labels[i].textContent && labels[i].textContent.indexOf('Subscriber Email') !== -1) { found = labels[i]; break; } }catch(e){}
                }
                var input = document.createElement('input'); input.type='email'; input.name='email'; input.placeholder='example@domain.test'; input.className='regular-text'; input.setAttribute('data-automatorwp-fallback','1');
                if (found && found.parentNode) {
                    try {
                        // Insert only the input near the existing label to avoid duplicating titles
                        found.parentNode.appendChild(input);
                    } catch(e) {
                        of.insertBefore(input, of.firstChild);
                    }
                } else {
                    var row = document.createElement('div'); row.className='cmb-row automatorwp-sendpulse-email-row'; row.style.marginBottom='8px';
                    var lab = document.createElement('label'); lab.className='cmb2-id-sub'; lab.style.display='block'; lab.style.fontWeight='600'; lab.style.marginBottom='4px'; lab.innerHTML='Subscriber Email:<span style="color:#d00">*</span>';
                    row.appendChild(lab); row.appendChild(input);
                    of.insertBefore(row, of.firstChild);
                }
            } catch(e){}
        }

        // Initial pass: ensure existing forms have the fallback input
        try {
            document.querySelectorAll('.automatorwp-option-form-container').forEach(function(of){
                try { ensureFallbackInDOM(of); } catch(e){}
            });
        } catch(e){}

        // Observe DOM mutations so we re-inject or ensure the input exists when
        // AutomatorWP/CMB2 renders or re-renders option forms dynamically.
        try {
            var mo = new MutationObserver(function(mutations){
                mutations.forEach(function(m){
                    try {
                        if (m.addedNodes && m.addedNodes.length) {
                            Array.prototype.forEach.call(m.addedNodes, function(node){
                                try {
                                    if (!node || node.nodeType !== 1) return;
                                    if (node.matches && node.matches('.automatorwp-option-form-container')) {
                                        ensureFallbackInDOM(node);
                                    }
                                    if (node.querySelectorAll) {
                                        var forms = node.querySelectorAll('.automatorwp-option-form-container');
                                        Array.prototype.forEach.call(forms, function(f){ try { ensureFallbackInDOM(f); } catch(e){} });
                                    }
                                } catch(e){}
                            });
                        }
                        // If attributes (like class) changed on an element inside the form,
                        // try to ensure the fallback still exists for its closest form.
                        if (m.type === 'attributes' && m.target) {
                            try {
                                var form = (m.target.closest) ? m.target.closest('.automatorwp-option-form-container') : null;
                                if (form) ensureFallbackInDOM(form);
                            } catch(e){}
                        }
                    } catch(e){}
                });
            });
            mo.observe(document.body || document.documentElement, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });
        } catch(e){}

    } catch(e) { /* silent */ }
})();
JS;

    wp_add_inline_script( 'automatorwp-sendpulse-js', $inline );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_sendpulse_admin_enqueue_scripts', 100 );