<?php

/**
 * Plugin Importer
 *
 * @package     ShortLinksPro\Classes\Plugin_Importer
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if (defined('ABSPATH') && !defined('WP_INSTALLING')) {
    add_action('plugins_loaded', function () {
        try {
            if (class_exists('ShortLinksPro_Plugin_Importer')) {
                $importer = new ShortLinksPro_PrettyLinks_Importer(); // Corregido el nombre de la clase
                $importer->init();
                error_log('ShortLinksPro_PrettyLinks_Importer inicializado correctamente.');
            } else {
                error_log('No se pudo inicializar ShortLinksPro_PrettyLinks_Importer: Clase padre no disponible.');
            }
        } catch (Exception $e) {
            error_log('Error al inicializar ShortLinksPro_PrettyLinks_Importer: ' . $e->getMessage());
        }
    });
}

class ShortLinksPro_PrettyLinks_Importer extends ShortLinksPro_Plugin_Importer
{

    public function init()
    {
        $this->plugin = 'prettylinks';
        $this->args = array(
            'label' => 'Pretty Links',
            'supports' => array('links', 'link_categories', 'link_tags', 'clicks'),
            // ----- Links -----
            'links' => array(
                'table' => 'prli_links',
                'id_field' => 'id',
                'where' => '',
                'fields' => array(
                    'title' => array(
                        'from' => 'name'
                    ),
                    'url' => array(
                        'from' => 'url',
                    ),
                    'slug' => array(
                        'from' => 'slug',
                    ),
                    'redirect_type' => array(
                        'from' => 'redirect_type',
                    ),
                    'nofollow' => array(
                        'from' => 'nofollow',
                    ),
                    'sponsored' => array(
                        'from' => 'sponsored',
                    ),
                    'parameter_forwarding' => array(
                        'from' => 'param_forwarding',
                    ),
                    'tracking' => array(
                        'from' => 'track_me',
                    ),
                    'author_id' => array(
                        'default_cb' => array($this, 'author_id_default_cb'), // Añadida función callback
                    ),
                    'created_at' => array(
                        'from' => 'created_at',
                    ),
                    'updated_at' => array(
                        'from' => 'updated_at',
                    ),
                    // Table fields to metas
                    'notes' => array(
                        'from' => 'description',
                    ),
                    'link_status' => array(
                        'from' => 'link_status',
                    ),
                    'param_struct' => array(
                        'from' => 'param_struct',
                    ),
                    'link_id' => array(
                        'from' => 'id', // link ID
                    ),
                    'link_cpt_id' => array(
                        'from' => 'link_cpt_id', // post ID
                    ),
                    'group_id' => array(
                        'from' => 'group_id',
                    ),
                    'prettypay_link' => array(
                        'from' => 'prettypay_link',
                    ),
                ),
                'meta_table' => 'prli_link_metas',
                'meta_relationship_id_field' => 'link_id',
                'metas' => array(
                    'delay' => array(
                        'from' => 'delay',
                    ),
                ),
            ),
            // ----- Categories -----
            'link_categories' => array(
                'table' => 'term_taxonomy',
                'join_table' => 'terms',
                'join_on' => 'term_taxonomy.term_id = terms.term_id',
                'id_field' => 'term_taxonomy_id',
                'where' => "term_taxonomy.taxonomy = 'pretty-link-category'",
                'fields' => array(
                    'name' => array(
                        'from' => 'name',
                    ),
                    'slug' => array(
                        'from' => 'slug',
                        'unique' => true,
                    ),
                    'description' => array(
                        'from' => 'description',
                    ),
                ),
                'meta_table' => 'termmeta',
                'metas' => array(),
                // Relationships
                'relationship' => array(
                    'table' => 'term_relationships',
                    'term_field' => 'term_taxonomy_id',
                    'object_field' => 'object_id',
                    'object_table' => 'links',
                ),
            ),
            // ----- Tags -----
            'link_tags' => array(
                'table' => 'term_taxonomy',
                'join_table' => 'terms',
                'join_on' => 'term_taxonomy.term_id = terms.term_id',
                'id_field' => 'term_taxonomy_id',
                'where' => "term_taxonomy.taxonomy = 'pretty-link-tag'",
                'fields' => array(
                    'name' => array(
                        'from' => 'name',
                    ),
                    'slug' => array(
                        'from' => 'slug',
                        'unique' => true,
                    ),
                    'description' => array(
                        'from' => 'description',
                    ),
                ),
                'meta_table' => 'termmeta',
                'metas' => array(),
                // Relationships
                'relationship' => array(
                    'table' => 'term_relationships',
                    'term_field' => 'term_taxonomy_id',
                    'object_field' => 'object_id',
                    'object_table' => 'links',
                ),

            ),
            // ----- Clicks -----
            'clicks' => array(
                'table' => 'prli_clicks',
                'id_field' => 'id',
                'where' => '',
                'fields' => array(
                    'link_id' => array(
                        'from' => 'link_id',
                        'relationship' => 'links',
                    ),
                    'ip' => array(
                        'from' => 'ip',
                    ),
                    'browser' => array(
                        'from' => 'browser',
                        'sanitize_cb' => array($this, 'browser_sanitize_cb'),
                    ),
                    'browser_version' => array(
                        'from' => 'bversion',
                        'sanitize_cb' => array($this, 'browser_version_sanitize_cb'),
                    ),
                    'browser_type' => array(
                        'from' => 'btype',
                        'sanitize_cb' => array($this, 'browser_type_sanitize_cb'),
                    ),
                    'os' => array(
                        'from' => 'os',
                        'sanitize_cb' => array($this, 'os_sanitize_cb'),
                    ),
                    'os_version' => array(
                        'from' => 'os', // Asignamos el campo base de OS y luego lo procesamos
                        'sanitize_cb' => array($this, 'os_version_sanitize_cb'),
                    ),
                    'device' => array(
                        'from' => 'btype', // Usamos btype como base para determinar el dispositivo
                        'sanitize_cb' => array($this, 'device_sanitize_cb'),
                    ),
                    'user_agent' => array(
                        'from' => 'host', // Usamos host como fuente alternativa
                    ),
                    'referrer' => array(
                        'from' => 'referer',
                    ),
                    'uri' => array(
                        'from' => 'uri',
                    ),
                    'parameters' => array(
                        'from' => 'uri', // Extraemos los parámetros del URI
                        'sanitize_cb' => array($this, 'parameters_sanitize_cb'),
                    ),
                    'visitor_id' => array(
                        'from' => 'vuid',
                    ),
                    'first_click' => array(
                        'from' => 'first_click',
                    ),
                    'created_at' => array(
                        'from' => 'created_at',
                    ),
                    // Table fields to metas
                    'host' => array(
                        'from' => 'host',
                    ),
                    'robot' => array(
                        'from' => 'robot',
                    ),
                ),
                'meta_table' => 'slp_click_metas', // Añadida tabla de metadatos
                'meta_relationship_id_field' => 'click_id',
                'metas' => array(
                    // Podemos añadir metas específicos si es necesario
                ),
            ),
        );  
    }

    public function author_id_default_cb($entry, $field, $field_args)
    {
        global $wpdb;
        $default = '';

        if (isset($entry['link_cpt_id']) && absint($entry['link_cpt_id']) > 0) {
            // Corregida la sintaxis de la consulta SQL
            $default = absint($wpdb->get_var($wpdb->prepare("SELECT post_author FROM {$wpdb->posts} WHERE ID = %d", absint($entry['link_cpt_id']))));
        }

        return $default;
    }

    public $browser;
    public $browser_data = array();

    public function browser_sanitize_cb($value, $entry, $field, $field_args)
    {
        if (strpos($value, '/') !== false) {
            // $value es el user-agent completo, lo guardamos para usarlo en otros métodos
            $this->browser = $value;

            // Aquí analizaríamos el user-agent con alguna librería de detección
            // Por simplicidad, extraemos información básica
            $this->parse_user_agent($value);

            // Retornamos el nombre del navegador
            return isset($this->browser_data['browser']) ? $this->browser_data['browser'] : $value;
        } else {
            $this->browser = false;
            return $value;
        }
    }

    public function browser_version_sanitize_cb($value, $entry, $field, $field_args)
    {
        if ($this->browser !== false && isset($this->browser_data['browser_version'])) {
            return $this->browser_data['browser_version'];
        }
        return $value;
    }

    public function browser_type_sanitize_cb($value, $entry, $field, $field_args)
    {
        if ($this->browser !== false && isset($this->browser_data['browser_type'])) {
            return $this->browser_data['browser_type'];
        }
        return $value;
    }

    public function os_sanitize_cb($value, $entry, $field, $field_args)
    {
        if ($this->browser !== false && isset($this->browser_data['os'])) {
            return $this->browser_data['os'];
        }
        return $value;
    }

    public function os_version_sanitize_cb($value, $entry, $field, $field_args)
    {
        if ($this->browser !== false && isset($this->browser_data['os_version'])) {
            return $this->browser_data['os_version'];
        }
        return '';
    }

    public function device_sanitize_cb($value, $entry, $field, $field_args)
    {
        if ($this->browser !== false && isset($this->browser_data['device'])) {
            return $this->browser_data['device'];
        }

        // Si no tenemos un dispositivo explícito, lo inferimos del tipo de navegador
        if (!empty($value)) {
            if (stripos($value, 'mobile') !== false) {
                return 'smartphone';
            } elseif (stripos($value, 'tablet') !== false) {
                return 'tablet';
            }
        }

        return 'desktop';
    }

    public function parameters_sanitize_cb($value, $entry, $field, $field_args)
    {
        // Extraer parámetros del URI
        $params = '';
        if (!empty($value) && strpos($value, '?') !== false) {
            $parts = explode('?', $value);
            if (isset($parts[1])) {
                $params = $parts[1];
            }
        }
        return $params;
    }

    // Método auxiliar para analizar el user-agent
    private function parse_user_agent($user_agent)
    {
        // Esta es una implementación simple - en un caso real usaríamos una librería como DeviceDetector
        $this->browser_data = array(
            'browser' => 'Unknown',
            'browser_version' => '',
            'browser_type' => 'desktop',
            'os' => 'Unknown',
            'os_version' => '',
            'device' => 'desktop'
        );

        // Detectar navegador
        if (strpos($user_agent, 'Chrome') !== false) {
            $this->browser_data['browser'] = 'Chrome';
            preg_match('/Chrome\/([\d\.]+)/', $user_agent, $matches);
            if (isset($matches[1])) {
                $this->browser_data['browser_version'] = $matches[1];
            }
        } elseif (strpos($user_agent, 'Firefox') !== false) {
            $this->browser_data['browser'] = 'Firefox';
            preg_match('/Firefox\/([\d\.]+)/', $user_agent, $matches);
            if (isset($matches[1])) {
                $this->browser_data['browser_version'] = $matches[1];
            }
        } elseif (strpos($user_agent, 'Safari') !== false && strpos($user_agent, 'Chrome') === false) {
            $this->browser_data['browser'] = 'Safari';
            preg_match('/Version\/([\d\.]+)/', $user_agent, $matches);
            if (isset($matches[1])) {
                $this->browser_data['browser_version'] = $matches[1];
            }
        } elseif (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false) {
            $this->browser_data['browser'] = 'Internet Explorer';
            preg_match('/(MSIE|rv:)([\d\.]+)/', $user_agent, $matches);
            if (isset($matches[2])) {
                $this->browser_data['browser_version'] = $matches[2];
            }
        } elseif (strpos($user_agent, 'Edge') !== false) {
            $this->browser_data['browser'] = 'Edge';
            preg_match('/Edge\/([\d\.]+)/', $user_agent, $matches);
            if (isset($matches[1])) {
                $this->browser_data['browser_version'] = $matches[1];
            }
        }

        // Detectar SO
        if (strpos($user_agent, 'Windows') !== false) {
            $this->browser_data['os'] = 'Windows';
            if (strpos($user_agent, 'Windows NT 10.0') !== false) {
                $this->browser_data['os_version'] = '10';
            } elseif (strpos($user_agent, 'Windows NT 6.3') !== false) {
                $this->browser_data['os_version'] = '8.1';
            } elseif (strpos($user_agent, 'Windows NT 6.2') !== false) {
                $this->browser_data['os_version'] = '8';
            } elseif (strpos($user_agent, 'Windows NT 6.1') !== false) {
                $this->browser_data['os_version'] = '7';
            }
        } elseif (strpos($user_agent, 'Macintosh') !== false || strpos($user_agent, 'Mac OS X') !== false) {
            $this->browser_data['os'] = 'Mac OS';
            preg_match('/Mac OS X (\d+[._]\d+[._]\d+)/', $user_agent, $matches);
            if (isset($matches[1])) {
                $this->browser_data['os_version'] = str_replace('_', '.', $matches[1]);
            }
        } elseif (strpos($user_agent, 'Linux') !== false) {
            $this->browser_data['os'] = 'Linux';
        } elseif (strpos($user_agent, 'Android') !== false) {
            $this->browser_data['os'] = 'Android';
            preg_match('/Android ([\d\.]+)/', $user_agent, $matches);
            if (isset($matches[1])) {
                $this->browser_data['os_version'] = $matches[1];
            }
            $this->browser_data['device'] = 'smartphone';
            $this->browser_data['browser_type'] = 'mobile';
        } elseif (strpos($user_agent, 'iPhone') !== false || strpos($user_agent, 'iPad') !== false || strpos($user_agent, 'iPod') !== false) {
            $this->browser_data['os'] = 'iOS';
            preg_match('/OS ([\d_]+)/', $user_agent, $matches);
            if (isset($matches[1])) {
                $this->browser_data['os_version'] = str_replace('_', '.', $matches[1]);
            }
            if (strpos($user_agent, 'iPad') !== false) {
                $this->browser_data['device'] = 'tablet';
            } else {
                $this->browser_data['device'] = 'smartphone';
            }
            $this->browser_data['browser_type'] = 'mobile';
        }

        // Detectar tipo de dispositivo si aún no se ha determinado
        if (strpos($user_agent, 'Mobile') !== false && $this->browser_data['device'] == 'desktop') {
            $this->browser_data['device'] = 'smartphone';
            $this->browser_data['browser_type'] = 'mobile';
        } elseif (strpos($user_agent, 'Tablet') !== false && $this->browser_data['device'] == 'desktop') {
            $this->browser_data['device'] = 'tablet';
            $this->browser_data['browser_type'] = 'mobile';
        }
    }
}

// Instanciamos la clase correctamente
if (class_exists('ShortLinksPro_Plugin_Importer')) {
    new ShortLinksPro_PrettyLinks_Importer();
}
