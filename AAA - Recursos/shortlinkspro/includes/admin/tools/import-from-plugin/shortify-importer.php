<?php
/**
 * BetterLinks Importer
 *
 * @package     ShortLinksPro\Classes\BetterLinks_Importer
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if (defined('ABSPATH') && !defined('WP_INSTALLING')) {
    add_action('plugins_loaded', function() {
        try {
            if (class_exists('ShortLinksPro_Plugin_Importer')) {
                $importer = new ShortLinksPro_Shortify_Importer();
                $importer->init();
                error_log('ShortLinksPro_Shortify_Importer inicializado correctamente.');
            } else {
                error_log('No se pudo inicializar ShortLinksPro_Shortify_Importer: Clase padre no disponible.');
            }
        } catch (Exception $e) {
            error_log('Error al inicializar ShortLinksPro_Shortify_Importer: ' . $e->getMessage());
        }
    });
}

class ShortLinksPro_Shortify_Importer extends ShortLinksPro_Plugin_Importer {

    public function init() {
        $this->plugin = 'shortify';
        $this->args = array(
            'label' => 'Shortify',
            'supports' => array( 'links', 'link_categories', 'link_tags', 'clicks' ),
            // ----- Links -----
            'links' => array(
                'table' => 'kc_us_links',
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
                        'from' => 'params_forwarding',
                    ),
                    'tracking' => array(
                        'from' => 'track_me',
                    ),
                    'author_id' => array(
                        'from' => 'created_by_id',
                    ),
                    'created_at' => array(
                        'from' => 'created_at',
                    ),
                    'updated_at' => array(
                        'from' => 'updated_at',
                    ),
                    // Metas no tenemos estan sin cambiar parametros
                    'notes' => array(
                        'from' => 'link_note',
                    ),
                    'status' => array(
                        'from' => 'link_status',
                    ),
                    'param_struct' => array(
                        'from' => 'param_struct',
                    ),
                    'link_date_gmt' => array(
                        'from' => 'link_date_gmt',
                    ),
                    'link_modified_gmt' => array(
                        'from' => 'link_modified_gmt',
                    ),
                    'wildcards' => array(
                        'from' => 'wildcards', // Corregido: eliminado el tabulador extra
                    ),
                    'expire' => array(
                        'from' => 'expire', // Corregido: eliminado el tabulador extra
                    ),
                    'dynamic_redirect' => array(
                        'from' => 'dynamic_redirect', // Corregido: eliminado el tabulador extra
                    ),
                    'favorite' => array(
                        'from' => 'favorite', // Corregido: eliminado el tabulador extra
                    ),
                    'uncloaked' => array(
                        'from' => 'uncloaked', // Corregido: eliminado el tabulador extra
                    ),
                ),
                'meta_table' => 'slp_link_metas', // Añadida tabla de metadatos
                'meta_relationship_id_field' => 'link_id',
                'metas' => array(
                    // Añadir metas si es necesario
                ),
            ),
            // ----- Categories -----
            'link_categories' => array(
                'table' => 'kc_us_groups',
                'id_field' => 'id',
                'where' => "",
                'fields' => array(
                    'name' => array(
                        'from' => 'name',
                    ),
                    'slug' => array(
                        'from' => 'name', // Generar slug a partir del nombre
                        'sanitize_cb' => array($this, 'generate_slug_from_name'),
                        'unique' => true,
                    ),
                    'description' => array(
                        'from' => 'description',
                    ),
                ),
                // Metas
                'meta_table' => 'slp_term_metas', // Añadida tabla de metadatos
                'meta_relationship_id_field' => 'term_id',
                'metas' => array(
                    // Añadir metas si es necesario
                ),
                // Relationships
                'relationship' => array(
                    'table' => 'kc_us_links_groups',
                    'term_field' => 'group_id', // Corregido: cambiado 'group_id' a 'term_field'
                    'object_field' => 'link_id',
                    'object_table' => 'links',
                ),
            ),
            // ----- Tags -----
            'link_tags' => array(
                'table' => '', // No hay tabla específica para tags en Shortify
                'id_field' => '',
                'where' => "",
                'fields' => array(
                    // Los tags se manejarán en un proceso personalizado 
                ),
                'meta_table' => '',
                'metas' => array(),
                'relationship' => array(),
            ),
            // ----- Clicks -----
            'clicks' => array(
                'table' => 'kc_us_clicks',
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
                        'from' => 'user_agent', // Usar user_agent para extraer el navegador
                        'sanitize_cb' => array( $this, 'browser_sanitize_cb' ),
                    ),
                    'browser_version' => array(
                        'from' => 'browser_version',
                        'sanitize_cb' => array( $this, 'browser_version_sanitize_cb' ),
                    ),
                    'browser_type' => array(
                        'from' => 'browser_type',
                        'sanitize_cb' => array( $this, 'browser_type_sanitize_cb' ),
                    ),
                    'os' => array(
                        'from' => 'os',
                        'sanitize_cb' => array( $this, 'os_sanitize_cb' ),
                    ),
                    'os_version' => array(
                        'from' => 'os', // Basado en el campo OS, extraer la versión
                        'sanitize_cb' => array( $this, 'os_version_sanitize_cb' ),
                    ),
                    'device' => array(
                        'from' => 'device',
                        'sanitize_cb' => array( $this, 'device_sanitize_cb' ),
                    ),
                    'user_agent' => array(
                        'from' => 'user_agent',
                    ),
                    'referrer' => array(
                        'from' => 'referer',
                    ),
                    'uri' => array(
                        'from' => 'uri',
                    ),
                    'parameters' => array(
                        'from' => 'uri', // Extraer parámetros de URI
                        'sanitize_cb' => array( $this, 'parameters_sanitize_cb' ),
                    ),
                    'visitor_id' => array(
                        'from' => 'visitor_id',
                    ),
                    'first_click' => array(
                        'from' => 'is_first_click',
                    ),
                    'created_at' => array(
                        'from' => 'created_at',
                    ),
                    // Table fields to metas
                    'host' => array(
                        'from' => 'host',
                    ),
                    'brand_name' => array(
                        'from' => 'device', // Extraer marca del dispositivo
                        'sanitize_cb' => array( $this, 'brand_name_sanitize_cb' ),
                    ),
                    'model' => array(
                        'from' => 'device', // Extraer modelo del dispositivo
                        'sanitize_cb' => array( $this, 'model_sanitize_cb' ),
                    ),
                    'bot_name' => array(
                        'from' => 'is_robot',
                        'sanitize_cb' => array( $this, 'bot_name_sanitize_cb' ),
                    ),
                    'language' => array(
                        'from' => 'user_agent', // Extraer idioma del user agent
                        'sanitize_cb' => array( $this, 'language_sanitize_cb' ),
                    ),
                    'click_count' => array(
                        'default' => 1, // Valor por defecto
                    ),
                    'click_order' => array(
                        'default' => 0, // Valor por defecto
                    ),
                    'created_at_gmt' => array(
                        'from' => 'created_at', // Convertir a GMT
                        'sanitize_cb' => array( $this, 'created_at_gmt_sanitize_cb' ),
                    ),
                    'rotation_target_url' => array(
                        'default' => '', // Valor por defecto vacío
                    ),
                ),
                'meta_table' => 'slp_click_metas', // Añadida tabla de metadatos
                'meta_relationship_id_field' => 'click_id',
                'metas' => array(
                    // Añadir metas si es necesario
                ),
            ),
        );
    }

    public $browser;
    public $browser_data = array();

    // Método para generar slug desde el nombre
    public function generate_slug_from_name($value, $entry, $field, $field_args) {
        if (!empty($value)) {
            return sanitize_title($value);
        }
        return 'category-' . time();
    }

    // Métodos de sanitización para datos de navegador y SO
    public function browser_sanitize_cb($value, $entry, $field, $field_args) {
        if (!empty($value)) {
            $this->browser = $value;
            $this->parse_user_agent($value);
            
            if (isset($entry['browser']) && !empty($entry['browser'])) {
                // Si ya existe 'browser' en la entrada, usarlo
                return $entry['browser'];
            } elseif (isset($this->browser_data['browser'])) {
                // Si no, usar lo que detectamos
                return $this->browser_data['browser'];
            }
        }
        return 'Unknown';
    }

    public function browser_version_sanitize_cb($value, $entry, $field, $field_args) {
        if (!empty($value)) {
            return $value; // Usar el valor existente si está presente
        } elseif ($this->browser !== false && isset($this->browser_data['browser_version'])) {
            return $this->browser_data['browser_version'];
        }
        return '';
    }

    public function browser_type_sanitize_cb($value, $entry, $field, $field_args) {
        if (!empty($value)) {
            return $value; // Usar el valor existente si está presente
        } elseif ($this->browser !== false && isset($this->browser_data['browser_type'])) {
            return $this->browser_data['browser_type'];
        }
        return 'desktop';
    }

    public function os_sanitize_cb($value, $entry, $field, $field_args) {
        if (!empty($value)) {
            return $value; // Usar el valor existente si está presente
        } elseif ($this->browser !== false && isset($this->browser_data['os'])) {
            return $this->browser_data['os'];
        }
        return 'Unknown';
    }

    public function os_version_sanitize_cb($value, $entry, $field, $field_args) {
        // Extraer versión del sistema operativo
        if ($this->browser !== false && isset($this->browser_data['os_version'])) {
            return $this->browser_data['os_version'];
        } elseif (!empty($value)) {
            // Intentar extraer la versión del string del SO
            $matches = array();
            if (preg_match('/(Windows|Mac OS X|Android|iOS) ?([\d\.]+)?/i', $value, $matches)) {
                if (isset($matches[2])) {
                    return $matches[2];
                }
            }
        }
        return '';
    }

    public function device_sanitize_cb($value, $entry, $field, $field_args) {
        if (!empty($value)) {
            return $value; // Usar el valor existente si está presente
        } elseif ($this->browser !== false && isset($this->browser_data['device'])) {
            return $this->browser_data['device'];
        }
        return 'desktop';
    }
    
    public function parameters_sanitize_cb($value, $entry, $field, $field_args) {
        // Extraer parámetros del URI
        if (!empty($value) && strpos($value, '?') !== false) {
            $parts = explode('?', $value);
            if (isset($parts[1])) {
                return $parts[1];
            }
        }
        return '';
    }
    
    public function brand_name_sanitize_cb($value, $entry, $field, $field_args) {
        if (!empty($value)) {
            // Extraer marca del dispositivo
            $parts = explode(' ', $value);
            if (count($parts) > 0) {
                return $parts[0];
            }
        }
        return '';
    }
    
    public function model_sanitize_cb($value, $entry, $field, $field_args) {
        if (!empty($value)) {
            // Extraer modelo del dispositivo
            $parts = explode(' ', $value);
            if (count($parts) > 1) {
                return implode(' ', array_slice($parts, 1));
            }
        }
        return '';
    }
    
    public function bot_name_sanitize_cb($value, $entry, $field, $field_args) {
        if (!empty($value) && $value == 1) {
            // Si es robot, intentar identificar el tipo
            if (isset($entry['user_agent'])) {
                $ua = strtolower($entry['user_agent']);
                if (strpos($ua, 'googlebot') !== false) return 'Googlebot';
                if (strpos($ua, 'bingbot') !== false) return 'Bingbot';
                if (strpos($ua, 'yandex') !== false) return 'Yandexbot';
                if (strpos($ua, 'baidu') !== false) return 'Baidubot';
                if (strpos($ua, 'bot') !== false) return 'Unknown Bot';
            }
            return 'Unknown Bot';
        }
        return '';
    }
    
    public function language_sanitize_cb($value, $entry, $field, $field_args) {
        if (!empty($value)) {
            // Intentar extraer el idioma del user agent
            $matches = array();
            if (preg_match('/\b([a-z]{2}(?:-[A-Z]{2})?)\b/', $value, $matches)) {
                return $matches[1];
            }
        }
        return '';
    }
    
    public function created_at_gmt_sanitize_cb($value, $entry, $field, $field_args) {
        if (!empty($value)) {
            // Convertir la fecha a GMT
            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return gmdate('Y-m-d H:i:s', $timestamp);
            }
        }
        return current_time('mysql', true);
    }

    // Método auxiliar para analizar el user-agent
    private function parse_user_agent($user_agent) {
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
    new ShortLinksPro_Shortify_Importer();
}