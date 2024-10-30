<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Optimisationio_Admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'menu'));
        add_action( 'admin_enqueue_scripts', array($this,'load_custom_wp_admin_style' ));
    }

    public function menu()
    {
        add_menu_page(__('Optimisation.io', 'optimise'), __('Optimisation.io', 'optimise'), 'manage_options', 'optimisationio', array($this, 'caheEnabler'));

        add_submenu_page('', __('Update Cache Enabler', 'optimise'), __('Update Cache Enabler', 'optimise'), 'manage_options', 'optimisationio-cache-settings', array($this, 'updateCacheEnabler'));

        add_submenu_page('', __('CDN Enabler', 'optimise'), __('CDN Enabler', 'optimise'), 'manage_options', 'optimisationio-cdn-enabler', array($this, 'cdnEnabler'));

        add_submenu_page('', __('Update CDN Enabler', 'optimise'), __('Update CDN Enabler', 'optimise'), 'manage_options', 'optimisationio-update-cdn-enabler', array($this, 'updateCdnEnabler'));


    }

    public function load_custom_wp_admin_style($hook)
    {
        if(preg_match('/optimisationio/i', $hook)) {
            wp_enqueue_style( 'custom_wp_admin_css', plugins_url('css/optimisationio.css', __FILE__.'/../../../../') );
        }

    }

    public function cdnEnabler()
    {
        $settings = get_option(Optimisationio::OPTION_KEY . '_cdnsettings', array());
        $data     = array('settings' => $settings);
        echo Optimisationio_View::render('cdn_enabler', $data);
    }
    public function updateCdnEnabler()
    {

        $array = array(
            'cdn_root_url'            => sanitize_text_field($_POST['cdn_root_url']),
            'cdn_file_extensions'     => sanitize_text_field($_POST['cdn_file_extensions']),
            'cdn_css_root_url'        => sanitize_text_field($_POST['cdn_css_root_url']),
            'cdn_css_file_extensions' => sanitize_text_field($_POST['cdn_css_file_extensions']),
            'cdn_js_root_url'         => sanitize_text_field($_POST['cdn_js_root_url']),
            'cdn_js_file_extensions'  => sanitize_text_field($_POST['cdn_js_file_extensions']),
        );
        

        $options  = $array;
        $settings = update_option(Optimisationio::OPTION_KEY . '_cdnsettings', $options);
        $this->addMessage('CDN Enabler settings updated successfully');

        $this->redirectUrl(admin_url('admin.php?page=optimisationio-cdn-enabler'));

    }

    public function caheEnabler()
    {
        // wp cache check
        if (!defined('WP_CACHE') || !WP_CACHE) {
            echo sprintf(
                '<div class="notice notice-warning"><p>%s</p></div>',
                sprintf(
                    __("%s is not set in %s.", 'optimisation.io'),
                    "<code>define('WP_CACHE', true);</code>",
                    "wp-config.php"
                )
            );
        }
        $selectoptions = Optimisationio_CacheEnabler::_minify_select();
        $settings      = get_option(Optimisationio::OPTION_KEY . '_settings', array());
        $data          = array('settings' => $settings, 'selectoptions' => $selectoptions, 'cacheSize' => (Optimisationio_CacheEnabler::get_cache_size() / 1000) . ' Kb');
        echo Optimisationio_View::render('cache_enabler', $data);
    }

    public function updateCacheEnabler()
    {
        $array = array(
            'cache_expires'     => sanitize_text_field($_POST['cache_expires']),
            'cache_new_post'    => ($_POST['cache_new_post']) ? 1 : 0,
            'cache_new_comment' => ($_POST['cache_new_comment']) ? 1 : 0,
            'cache_webp'        => ($_POST['cache_webp']) ? 1 : 0,
            'cache_compress'    => ($_POST['cache_compress']) ? 1 : 0,
            'excl_ids'          => sanitize_text_field($_POST['excl_ids']),
            'minify_html'       => sanitize_text_field($_POST['minify_html']),
        );

        $options  = $array;
        $settings = update_option(Optimisationio::OPTION_KEY . '_settings', $options);
        $this->addMessage('Cache Settings updated successfully');

        $this->redirectUrl(admin_url('admin.php?page=optimisationio'));
    }

    private function sendemail($to, $subject, $message, $from)
    {
        $headers = "From: " . strip_tags($from) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        @mail($to, $subject, $message, $headers);
    }

    private function addMessage($msg, $type = 'success')
    {
        if ($type == 'success') {
            printf(
                "<div class='updated'><p><strong>%s</strong></p></div>",
                $msg
            );
        } else {
            printf(
                "<div class='error'><p><strong>%s</strong></p></div>",
                $msg
            );
        }
    }
    private function redirectUrl($url)
    {
        //header('Location:'.$url);
        echo '<script>';
        echo 'window.location.href="' . $url . '"';
        echo '</script>';
    }

}
