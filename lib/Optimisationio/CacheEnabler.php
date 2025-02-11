<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Optimisationio_CacheEnabler
*
* @since 1.0.0
*/

 class Optimisationio_CacheEnabler {


    /**
    * plugin options
    *
    * @since  1.0.0
    * @var    array
    */

    public static $options;


    /**
    * disk cache object
    *
    * @since  1.0.0
    * @var    object
    */

    public static $disk;


    /**
    * minify default settings
    *
    * @since  1.0.0
    * @var    integer
    */

    const MINIFY_DISABLED = 0;
    const MINIFY_HTML_ONLY = 1;
    const MINIFY_HTML_JS = 2;


    /**
    * constructor wrapper
    *
    * @since   1.0.0
    * @change  1.0.0
    */

    public static function instance()
    {
        new self();
    }


    /**
    * constructor
    *
    * @since   1.0.0
    * @change  1.2.0
    *
    * @param   void
    * @return  void
    */

    public function __construct()
    {
        // set default vars
        self::_set_default_vars();

        // register publish hook
        add_action(
            'init',
            array(
                __CLASS__,
                'register_publish_hooks'
            ),
            99
        );

        // clear cache hooks
        add_action(
            'ce_clear_post_cache',
            array(
                __CLASS__,
                'clear_page_cache_by_post_id'
            )
        );
        add_action(
            'ce_clear_cache',
            array(
                __CLASS__,
                'clear_total_cache'
            )
        );
        add_action(
            '_core_updated_successfully',
            array(
                __CLASS__,
                'clear_total_cache'
            )
        );
        add_action(
            'switch_theme',
            array(
                __CLASS__,
                'clear_total_cache'
            )
        );
        add_action(
            'wp_trash_post',
            array(
                __CLASS__,
                'clear_total_cache'
            )
        );
        add_action(
            'autoptimize_action_cachepurged',
            array(
                __CLASS__,
                'clear_total_cache'
            )
        );

        // add admin clear link
        add_action(
            'admin_bar_menu',
            array(
                __CLASS__,
                'add_admin_links'
            ),
            90
        );
        add_action(
            'init',
            array(
                __CLASS__,
                'process_clear_request'
            )
        );

        // admin
        if ( is_admin() ) {
            add_action(
                'wpmu_new_blog',
                array(
                    __CLASS__,
                    'install_later'
                )
            );
            add_action(
                'delete_blog',
                array(
                    __CLASS__,
                    'uninstall_later'
                )
            );

            add_action(
                'admin_init',
                array(
                    __CLASS__,
                    'register_textdomain'
                )
            );
            add_action(
                'admin_init',
                array(
                    __CLASS__,
                    'register_settings'
                )
            );

            add_action(
                'admin_enqueue_scripts',
                array(
                    __CLASS__,
                    'add_admin_resources'
                )
            );

            add_action(
                'transition_comment_status',
                array(
                    __CLASS__,
                    'change_comment'
                ),
                10,
                3
            );
            add_action(
                'comment_post',
                array(
                    __CLASS__,
                    'comment_post'
                ),
                99,
                2
            );
            add_action(
                'edit_comment',
                array(
                    __CLASS__,
                    'edit_comment'
                )
            );

            add_filter(
                'dashboard_glance_items',
                array(
                    __CLASS__,
                    'add_dashboard_count'
                )
            );
            add_action(
                'post_submitbox_misc_actions',
                array(
                    __CLASS__,
                    'add_clear_dropdown'
                )
            );
            add_filter(
                'plugin_row_meta',
                array(
                    __CLASS__,
                    'row_meta'
                ),
                10,
                2
            );
            add_filter(
                'plugin_action_links_' .Optimisationio_CdnEnabler_BASE,
                array(
                    __CLASS__,
                    'action_links'
                )
            );

            // warnings and notices
            add_action(
                'admin_notices',
                array(
                    __CLASS__,
                    'warning_is_permalink'
                )
            );
            add_action(
                'admin_notices',
                array(
                    __CLASS__,
                    'requirements_check'
                )
            );

        // caching
        } else {
            add_action(
                'pre_comment_approved',
                array(
                    __CLASS__,
                    'new_comment'
                ),
                99,
                2
            );

            add_action(
                'template_redirect',
                array(
                    __CLASS__,
                    'handle_cache'
                ),
                0
            );
        }
    }


    /**
    * deactivation hook
    *
    * @since   1.0.0
    * @change  1.1.1
    */

    public static function on_deactivation() {
        self::clear_total_cache(true);

        if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
            // unset WP_CACHE
            self::_set_wp_cache(false);
        }

        // delete advanced cache file
        unlink(WP_CONTENT_DIR . '/advanced-cache.php');
    }


    /**
    * activation hook
    *
    * @since   1.0.0
    * @change  1.1.1
    */

    public static function on_activation() {
        // multisite and network
        if ( is_multisite() && ! empty($_GET['networkwide']) ) {
            // blog ids
            $ids = self::_get_blog_ids();

            // switch to blog
            foreach ($ids as $id) {
                switch_to_blog($id);
                self::_install_backend();
            }

            // restore blog
            restore_current_blog();

        } else {
            self::_install_backend();
        }
        self::_set_wp_cache(false);
        if ( !defined( 'WP_CACHE' ) || !WP_CACHE ) {
            // set WP_CACHE
            self::_set_wp_cache(true);
        }

        // copy advanced cache file
        copy(Optimisationio_CdnEnabler_DIR . '/advanced-cache.php', WP_CONTENT_DIR . '/advanced-cache.php');
    }


    /**
    * install on multisite setup
    *
    * @since   1.0.0
    * @change  1.0.0
    */

    public static function install_later($id) {

        // check if multisite setup
        if ( ! is_plugin_active_for_network(Optimisationio_CdnEnabler_BASE) ) {
            return;
        }

        // switch to blog
        switch_to_blog($id);

        // installation
        self::_install_backend();

        // restore
        restore_current_blog();
    }


    /**
    * installation options
    *
    * @since   1.0.0
    * @change  1.0.0
    */

    public  static function _install_backend() {

        add_option(
            'cache',
            array()
        );

        // clear
        self::clear_total_cache(true);
    }


    /**
    * installation WP_CACHE (advanced cache)
    *
    * @since   1.1.1
    * @change  1.1.1
    */

    public static function _set_wp_cache($wp_cache_value = true) {
        $wp_config_file = ABSPATH . 'wp-config.php';

        if ( file_exists( $wp_config_file ) && is_writable( $wp_config_file ) ) {
            // get wp config as array
            $wp_config = file( $wp_config_file );

            if ($wp_cache_value) {
                $wp_cache_ce_line = "define('WP_CACHE', true); // Added by Optimisation.io". "\r\n";
            } else {
                $wp_cache_ce_line = '';
            }

            $found_wp_cache = false;

            foreach ( $wp_config as &$line ) {
                if ( preg_match( '/^\s*define\s*\(\s*[\'\"]WP_CACHE[\'\"]\s*,\s*(.*)\s*\)/', $line ) ) {
                    $line = $wp_cache_ce_line;
                    $found_wp_cache = true;
                    break;
                }
            }

            // add wp cache ce line if not found yet
            if ( ! $found_wp_cache ) {
                array_shift( $wp_config );
                array_unshift( $wp_config, "<?php\r\n", $wp_cache_ce_line );
            }

            // write wp-config.php file
            $fh = @fopen( $wp_config_file, 'w' );
            foreach( $wp_config as $ln ) {
                @fwrite( $fh, $ln );
            }

            @fclose( $fh );
        }
    }


    /**
    * uninstall per multisite blog
    *
    * @since   1.0.0
    * @change  1.0.0
    */

    public static function on_uninstall() {
        global $wpdb;

        // multisite and network
        if ( is_multisite() && ! empty($_GET['networkwide']) ) {
            // legacy blog
            $old = $wpdb->blogid;

            // blog id
            $ids = self::_get_blog_ids();

            // uninstall per blog
            foreach ($ids as $id) {
                switch_to_blog($id);
                self::_uninstall_backend();
            }

            // restore
            switch_to_blog($old);
        } else {
            self::_uninstall_backend();
        }
    }


    /**
    * uninstall for multisite and network
    *
    * @since   1.0.0
    * @change  1.0.0
    */

    public static function uninstall_later($id) {

        // check if network plugin
        if ( ! is_plugin_active_for_network(Optimisationio_CdnEnabler_BASE) ) {
            return;
        }

        // switch
        switch_to_blog($id);

        // uninstall
        self::_uninstall_backend();

        // restore
        restore_current_blog();
    }


    /**
    * uninstall
    *
    * @since   1.0.0
    * @change  1.0.0
    */

    public static function _uninstall_backend() {

        // delete options
        delete_option('cache');

        // clear cache
        self::clear_total_cache(true);
    }


    /**
    * get blog ids
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @return  array  blog ids array
    */

    public static function _get_blog_ids() {
        global $wpdb;

        return $wpdb->get_col("SELECT blog_id FROM `$wpdb->blogs`");
    }


    /**
    * set default vars
    *
    * @since   1.0.0
    * @change  1.0.0
    */

    public static function _set_default_vars() {

        // get options
        self::$options = self::_get_options();

        // disk cache
        if ( Optimisationio_CacheEnablerDisk::is_permalink() ) {
            self::$disk = new Optimisationio_CacheEnablerDisk;
        }
    }


    /**
    * get options
    *
    * @since   1.0.0
    * @change  1.1.0
    *
    * @return  array  options array
    */

    public static function _get_options() {

        return wp_parse_args(
            get_option(Optimisationio::OPTION_KEY . '_settings'),
            array(
                'cache_expires'        => 0,
                'cache_new_post'        => 0,
                'cache_new_comment'     => 0,
                'cache_compress'         => 0,
                'cache_webp'            => 0,
                'excl_ids'         => '',
                'minify_html'     => self::MINIFY_DISABLED,
            )
        );
    }


    /**
    * warning if no custom permlinks
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @return  array  options array
    */

    public static function warning_is_permalink() {

        if ( !Optimisationio_CacheEnablerDisk::is_permalink() AND current_user_can('manage_options') ) { ?>

            <div class="error">
                <p><?php printf( __('The <b>%s</b> plugin requires a custom permalink structure to start caching properly. Please go to <a href="%s">Permalink</a> to enable it.', 'optimisationio'), 'Optimisationio', admin_url( 'options-permalink.php' ) ); ?></p>
            </div>

        <?php
        }
    }


    /**
    * add action links
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @param   array  $data  existing links
    * @return  array  $data  appended links
    */

    public static function action_links($data) {

        // check user role
        if ( ! current_user_can('manage_options') ) {
            return $data;
        }

        return array_merge(
            $data,
            array(
                sprintf(
                    '<a href="%s">%s</a>',
                    add_query_arg(
                        array(
                            'page' => 'optimisationio'
                        ),
                        admin_url('options-general.php')
                    ),
                    esc_html__('Settings')
                )
            )
        );
    }


    /**
    * cache enabler meta links
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @param   array   $input  existing links
    * @param   string  $page   page
    * @return  array   $data   appended links
    */

    public static function row_meta($input, $page) {

        // check permissions
        if ( $page != Optimisationio_CdnEnabler_BASE ) {
            return $input;
        }

        return array_merge(
            $input,
            array(
            )
        );
    }


    /**
    * add dashboard cache size count
    *
    * @since   1.0.0
    * @change  1.1.0
    *
    * @param   array  $items  initial array with dashboard items
    * @return  array  $items  merged array with dashboard items
    */

    public static function add_dashboard_count( $items = array() ) {

        // check user role
        if ( ! current_user_can('manage_options') ) {
            return $items;
        }

        // get cache size
        $size = self::get_cache_size();

        // display items
        $items[] = sprintf(
            '<a href="%s" title="%s">%s %s</a>',
            add_query_arg(
                array(
                    'page' => 'optimisationio'
                ),
                admin_url('options-general.php')
            ),
            esc_html__('Disk Cache', 'optimisationio'),
            ( empty($size) ? esc_html__('Empty', 'optimisationio') : size_format($size) ),
            esc_html__('Cache Size', 'optimisationio')
        );

        return $items;
    }


    /**
    * get cache size
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @param   integer  $size  cache size (bytes)
    */

    public static function get_cache_size() {

        if ( ! $size = get_transient('cache_size') ) {

            $size = (int) self::$disk->cache_size(Optimisationio_CACHE_DIR);

            // set transient
            set_transient(
              'cache_size',
              $size,
              60 * 15
            );
        }

        return $size;
    }


    /**
    * add admin links
    *
    * @since   1.0.0
    * @change  1.1.0
    *
    * @hook    mixed
    *
    * @param   object  menu properties
    */

    public static function add_admin_links($wp_admin_bar) {

        // check user role
        if ( ! is_admin_bar_showing() OR ! apply_filters('user_can_clear_cache', current_user_can('manage_options')) ) {
            return;
        }

        // add admin purge link
        $wp_admin_bar->add_menu(
            array(
                'id'      => 'clear-cache',
                'href'   => wp_nonce_url( add_query_arg('_cache', 'clear'), '_cache__clear_nonce'),
                'parent' => 'top-secondary',
                'title'     => '<span class="ab-item">'.esc_html__('Clear Cache', 'optimisationio').'</span>',
                'meta'   => array( 'title' => esc_html__('Clear Cache', 'optimisationio') )
            )
        );

        if ( ! is_admin() ) {
            // add admin purge link
            $wp_admin_bar->add_menu(
                array(
                    'id'      => 'clear-url-cache',
                    'href'   => wp_nonce_url( add_query_arg('_cache', 'clearurl'), '_cache__clear_nonce'),
                    'parent' => 'top-secondary',
                    'title'     => '<span class="ab-item">'.esc_html__('Clear URL Cache', 'optimisationio').'</span>',
                    'meta'   => array( 'title' => esc_html__('Clear URL Cache', 'optimisationio') )
                )
            );
        }
    }


    /**
    * process clear request
    *
    * @since   1.0.0
    * @change  1.1.0
    *
    * @param   array  $data  array of metadata
    */

    public static function process_clear_request($data) {

        // check if clear request
        if ( empty($_GET['_cache']) OR ( $_GET['_cache'] !== 'clear' && $_GET['_cache'] !== 'clearurl' ) ) {
            return;
        }

        // validate nonce
        if ( empty($_GET['_wpnonce']) OR ! wp_verify_nonce($_GET['_wpnonce'], '_cache__clear_nonce') ) {
            return;
        }

        // check user role
        if ( ! is_admin_bar_showing() OR ! apply_filters('user_can_clear_cache', current_user_can('manage_options')) ) {
            return;
        }

        // load if network
        if ( ! function_exists('is_plugin_active_for_network') ) {
            require_once( ABSPATH. 'wp-admin/includes/plugin.php' );
        }

        // set clear url w/o query string
        $clear_url = preg_replace('/\?.*/', '', home_url( add_query_arg( NULL, NULL ) ));

        // multisite and network setup
        if ( is_multisite() && is_plugin_active_for_network(Optimisationio_CdnEnabler_BASE) ) {

            if ( is_network_admin() ) {

                // legacy blog
                $legacy = $GLOBALS['wpdb']->blogid;

                // blog ids
                $ids = self::_get_blog_ids();

                // switch blogs
                foreach ($ids as $id) {
                    switch_to_blog($id);
                    self::clear_page_cache_by_url(home_url());
                }

                // restore
                switch_to_blog($legacy);

                // clear notice
                if ( is_admin() ) {
                    add_action(
                        'network_admin_notices',
                        array(
                            __CLASS__,
                            'clear_notice'
                        )
                    );
                }
            } else {
                if ($_GET['_cache'] == 'clearurl') {
                    // clear specific multisite url cache
                    self::clear_page_cache_by_url($clear_url);
                } else {
                    // clear specific multisite cache
                    self::clear_page_cache_by_url(home_url());

                    // clear notice
                    if ( is_admin() ) {
                        add_action(
                            'admin_notices',
                            array(
                                __CLASS__,
                                'clear_notice'
                            )
                        );
                    }
                }
            }
        } else {
            if ($_GET['_cache'] == 'clearurl') {
                // clear url cache
                self::clear_page_cache_by_url($clear_url);
            } else {
                // clear cache
                self::clear_total_cache();

                // clear notice
                if ( is_admin() ) {
                    add_action(
                        'admin_notices',
                        array(
                            __CLASS__,
                            'clear_notice'
                        )
                    );
                }
            }
        }

        if ( ! is_admin() ) {
            wp_safe_redirect(
                remove_query_arg(
                    '_cache',
                    wp_get_referer()
                )
            );

            exit();
        }
    }


    /**
    * notification after clear cache
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @hook    mixed  user_can_clear_cache
    */

    public static function clear_notice() {

        // check if admin
        if ( ! is_admin_bar_showing() OR ! apply_filters('user_can_clear_cache', current_user_can('manage_options')) ) {
            return false;
        }

        echo sprintf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            esc_html__('The cache has been cleared.', 'optimisationio')
        );
    }


    /**
    * clear cache if post comment
    *
    * @since   1.2.0
    * @change  1.2.0
    *
    * @param   integer  $id  id of the comment
    * @param   mixed  $approved  approval status
    */

    public static function comment_post($id, $approved) {
        // check if comment is approved
        if ( $approved === 1 ) {
            if ( self::$options['cache_new_comment'] ) {
                self::clear_total_cache();
            } else {
                self::clear_page_cache_by_post_id(
                    get_comment($id)->comment_post_ID
                );
            }
        }
    }


    /**
    * clear cache if edit comment
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @param   integer  $id  id of the comment
    */

    public static function edit_comment($id) {
        // clear complete cache if option enabled
        if ( self::$options['cache_new_comment'] ) {
            self::clear_total_cache();
        } else {
            self::clear_page_cache_by_post_id(
                get_comment($id)->comment_post_ID
            );
        }
    }


    /**
    * clear cache if new comment
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @param   mixed  $approved  approval status
    * @param   array  $comment
    * @return  mixed  $approved  approval status
    */

    public static function new_comment($approved, $comment) {

        // check if comment is approved
        if ( $approved === 1 ) {
            if ( self::$options['cache_new_comment']) {
                self::clear_total_cache();
            } else {
                self::clear_page_cache_by_post_id( $comment['comment_post_ID'] );
            }
        }

        return $approved;
    }


    /**
    * clear cache if comment changes
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @param   string  $after_status
    * @param   string  $before_status
    * @param   object  $comment
    */

    public static function change_comment($after_status, $before_status, $comment) {

        // check if changes occured
        if ( $after_status != $before_status ) {
            if ( self::$options['cache_new_comment'] ) {
                self::clear_total_cache();
            } else {
                self::clear_page_cache_by_post_id( $comment->comment_post_ID );
            }
        }
    }


    /**
    * register publish hooks for custom post types
    *
    * @since   1.0.0
    * @since   1.0.0
    *
    * @param   void
    * @return  void
    */

    public static function register_publish_hooks() {

        // get post types
        $post_types = get_post_types(
            array('public' => true)
        );

        // check if empty
        if ( empty($post_types) ) {
            return;
        }

        // post type actions
        foreach ( $post_types as $post_type ) {
            add_action(
                'publish_' .$post_type,
                array(
                    __CLASS__,
                    'publish_post_types'
                ),
                10,
                2
            );
            add_action(
                'publish_future_' .$post_type,
                array(
                    __CLASS__,
                    'clear_total_cache'
                )
            );
        }
    }


    /**
    * delete post type cache on post updates
    *
    * @since   1.0.0
    * @change  1.0.7
    *
    * @param   integer  $post_ID  Post ID
    */

    public static function publish_post_types($post_ID, $post) {

        // check if post id or post is empty
        if ( empty($post_ID) OR empty($post) ) {
            return;
        }

        // check post status
        if ( ! in_array( $post->post_status, array('publish', 'future') ) ) {
            return;
        }

        // purge cache if clean post on update
        if ( ! isset($_POST['_clear_post_cache_on_update']) ) {

            // clear complete cache if option enabled
            if ( self::$options['cache_new_post'] ) {
                return self::clear_total_cache();
            } else {
                return self::clear_home_page_cache();
            }

        }

        // validate nonce
        if ( ! isset($_POST['_cache__status_nonce_' .$post_ID]) OR ! wp_verify_nonce($_POST['_cache__status_nonce_' .$post_ID], Optimisationio_CdnEnabler_BASE) ) {
            return;
        }

        // validate user role
        if ( ! current_user_can('publish_posts') ) {
            return;
        }

        // save as integer
        $clear_post_cache = (int)$_POST['_clear_post_cache_on_update'];

        // save user metadata
        update_user_meta(
            get_current_user_id(),
            '_clear_post_cache_on_update',
            $clear_post_cache
        );

        // purge complete cache or specific post
        if ( $clear_post_cache ) {
            self::clear_page_cache_by_post_id( $post_ID );
        } else {
            self::clear_total_cache();
        }
    }


    /**
    * clear page cache by post id
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @param   integer  $post_ID  Post ID
    */

    public static function clear_page_cache_by_post_id($post_ID) {

        // is int
        if ( ! $post_ID = (int)$post_ID ) {
            return;
        }

        // clear cache by URL
        self::clear_page_cache_by_url(
            get_permalink( $post_ID )
        );
    }


    /**
    * clear page cache by url
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @param  string  $url  url of a page
    */

    public static function clear_page_cache_by_url($url) {

        // validate string
        if ( ! $url = (string)$url ) {
            return;
        }

        call_user_func(
            array(
                self::$disk,
                'delete_asset'
            ),
            $url
        );
    }


    /**
    * clear home page cache
    *
    * @since   1.0.7
    * @change  1.0.7
    *
    */

    public static function clear_home_page_cache() {

        call_user_func(
            array(
                self::$disk,
                'clear_home'
            )
        );

    }


    /**
    * explode on comma
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @param   string  $input  input string
    * @return  array           array of strings
    */

    public static function _preg_split($input) {
        return (array)preg_split('/,/', $input, -1, PREG_SPLIT_NO_EMPTY);
    }


    /**
    * check if index.php
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @return  boolean  true if index.php
    */

    public static function _is_index() {
        return basename($_SERVER['SCRIPT_NAME']) != 'index.php';
    }


    /**
    * check if mobile
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @return  boolean  true if mobile
    */

    public static function _is_mobile() {
        return ( strpos(TEMPLATEPATH, 'wptouch') OR strpos(TEMPLATEPATH, 'carrington') OR strpos(TEMPLATEPATH, 'jetpack') OR strpos(TEMPLATEPATH, 'handheld') );
    }


    /**
    * check if logged in
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @return  boolean  true if logged in or cookie set
    */

    public static function _is_logged_in() {

        // check if logged in
        if ( is_user_logged_in() ) {
            return true;
        }

        // check cookie
        if ( empty($_COOKIE) ) {
            return false;
        }

        // check cookie values
        foreach ( $_COOKIE as $k => $v) {
            if ( preg_match('/^(wp-postpass|wordpress_logged_in|comment_author)_/', $k) ) {
                return true;
            }
        }
    }


    /**
    * check to bypass the cache
    *
    * @since   1.0.0
    * @change  1.0.7
    *
    * @return  boolean  true if exception
    *
    * @hook    boolean  bypass cache
    */

    public static function _bypass_cache() {

        // bypass cache hook
        if ( apply_filters('bypass_cache', false) ) {
            return true;
        }

        // conditional tags
        if ( self::_is_index() OR is_search() OR is_404() OR is_feed() OR is_trackback() OR is_robots() OR is_preview() OR post_password_required() ) {
            return true;
        }

        // DONOTCACHEPAGE check e.g. woocommerce
        if ( defined('DONOTCACHEPAGE') && DONOTCACHEPAGE ) {
            return true;
        }


        $options = get_option(Optimisationio::OPTION_KEY . '_settings', array());

        // Request method GET
        if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || $_SERVER['REQUEST_METHOD'] != 'GET' ) {
            return true;
        }

        // Request with query strings
        if ( ! empty($_GET) && ! isset( $_GET['utm_source'], $_GET['utm_medium'], $_GET['utm_campaign'] ) && get_option('permalink_structure') ) {
            return true;
        }

        // if logged in
        if ( self::_is_logged_in() ) {
            return true;
        }

        // if mobile request
        if ( self::_is_mobile() ) {
            return true;
        }

        // if post id excluded
        if ( $options['excl_ids'] && is_singular() ) {
            if ( in_array( $GLOBALS['wp_query']->get_queried_object_id(), self::_preg_split($options['excl_ids']) ) ) {
                return true;
            }
        }

        return false;
    }


    /**
    * minify html
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @param   string  $data  minify request data
    * @return  string  $data  minify response data
    *
    * @hook    array   cache_minify_ignore_tags
    */

    public static function _minify_cache($data) {
        // check if disabled
        if ( ! self::$options['minify_html'] ) {
            return $data;
        }

        // strlen limit
        if ( strlen($data) > 700000) {
            return $data;
        }

        // ignore this tags
        $ignore_tags = (array)apply_filters(
            'cache_minify_ignore_tags',
            array(
                'textarea',
                'pre'
            )
        );

        // ignore JS if selected
        if ( self::$options['minify_html'] !== self::MINIFY_HTML_JS ) {
            $ignore_tags[] = 'script';
        }

        // return of no ignore tags
        if ( ! $ignore_tags ) {
            return $data;
        }

        // stringify
        $ignore_regex = implode('|', $ignore_tags);

        // regex minification
        $cleaned = preg_replace(
            array(
                '/<!--[^\[><](.*?)-->/s',
                '#(?ix)(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:' .$ignore_regex. ')\b))*+)(?:<(?>' .$ignore_regex. ')\b|\z))#'
            ),
            array(
                '',
                ' '
            ),
            $data
        );

        // something went wrong
        if ( strlen($cleaned) <= 1 ) {
            return $data;
        }

        return $cleaned;
    }


    /**
    * clear complete cache
    *
    * @since   1.0.0
    * @change  1.0.0
    */

    public static function clear_total_cache() {

        // clear disk cache
        Optimisationio_CacheEnablerDisk::clear_cache();

        // delete transient
        delete_transient('cache_size');
    }


    /**
    * set cache
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @param   string  $data  content of a page
    * @return  string  $data  content of a page
    */

    public static function set_cache($data) {

        // check if empty
        if ( empty($data) ) {
            return '';
        }

        // store as asset
        call_user_func(
            array(
                self::$disk,
                'store_asset'
            ),
            self::_minify_cache($data)
        );

        return $data;
    }


    /**
    * handle cache
    *
    * @since   1.0.0
    * @change  1.0.1
    */

    public static function handle_cache() {
        // bypass cache
        if ( self::_bypass_cache() ) {
            return;
        }

        // get asset cache status
        $cached = call_user_func(
            array(
                self::$disk,
                'check_asset'
            )
        );

        // check if cache empty
        if ( empty($cached) ) {
            ob_start('Optimisationio_CacheEnabler::set_cache');
            return;
        }

        // get expiry status
        $expired = call_user_func(
            array(
                self::$disk,
                'check_expiry'
            )
        );

        // check if expired
        if ( $expired ) {
            ob_start('Optimisationio_CacheEnabler::set_cache');
            return;
        }

        // return cached asset
        call_user_func(
            array(
                self::$disk,
                'get_asset'
            )
        );
    }


    /**
    * add clear option dropdown on post publish widget
    *
    * @since   1.0.0
    * @change  1.0.0
    */

    public static function add_clear_dropdown() {

        // on published post page only
        if ( empty($GLOBALS['pagenow']) OR $GLOBALS['pagenow'] !== 'post.php' OR empty($GLOBALS['post']) OR ! is_object($GLOBALS['post']) OR $GLOBALS['post']->post_status !== 'publish' ) {
            return;
        }

        // check user role
        if ( ! current_user_can('publish_posts') ) {
            return;
        }

        // validate nonce
        wp_nonce_field(Optimisationio_CdnEnabler_BASE, '_cache__status_nonce_' .$GLOBALS['post']->ID);

        // get current action
        $current_action = (int)get_user_meta(
            get_current_user_id(),
            '_clear_post_cache_on_update',
            true
        );

        // init variables
        $dropdown_options = '';
        $available_options = array(
            esc_html__('Completely', 'optimisationio'),
            esc_html__('Page specific', 'optimisationio')
        );

        // set dropdown options
        foreach( $available_options as $key => $value ) {
            $dropdown_options .= sprintf(
                '<option value="%1$d" %3$s>%2$s</option>',
                $key,
                $value,
                selected($key, $current_action, false)
            );
        }

        // output drowdown
        echo sprintf(
            '<div class="misc-pub-section" style="border-top:1px solid #eee">
                <label for="cache_action">
                    %1$s: <span id="output-cache-action">%2$s</span>
                </label>
                <a href="#" class="edit-cache-action hide-if-no-js">%3$s</a>

                <div class="hide-if-js">
                    <select name="_clear_post_cache_on_update" id="cache_action">
                        %4$s
                    </select>

                    <a href="#" class="save-cache-action hide-if-no-js button">%5$s</a>
                     <a href="#" class="cancel-cache-action hide-if-no-js button-cancel">%6$s</a>
                 </div>
            </div>',
            esc_html__('Clear cache', 'optimisationio'),
            $available_options[$current_action],
            esc_html__('Edit'),
            $dropdown_options,
            esc_html__('OK'),
            esc_html__('Cancel')
        );
    }


    /**
    * enqueue scripts
    *
    * @since   1.0.0
    * @change  1.0.0
    */

    public static function add_admin_resources($hook) {

        // hook check
        if ( $hook !== 'index.php' AND $hook !== 'post.php' ) {
            return;
        }

        // plugin data
        $plugin_data = get_plugin_data(CE_FILE);

        // enqueue scripts
        switch($hook) {

            case 'post.php':
                wp_enqueue_script(
                    'cache-post',
                    plugins_url('js/post.js', CE_FILE),
                    array('jquery'),
                    $plugin_data['Version'],
                    true
                );
            break;

            default:
            break;
        }
    }




    /**
    * minify caching dropdown
    *
    * @since   1.0.0
    * @change  1.0.0
    *
    * @return  array    Key => value array
    */

    public static function _minify_select() {
        return array(
            self::MINIFY_DISABLED  => esc_html__('Disabled', 'optimisationio'),
            self::MINIFY_HTML_ONLY => esc_html__('HTML', 'optimisationio'),
            self::MINIFY_HTML_JS   => esc_html__('HTML & Inline JS', 'optimisationio')
        );
    }


    /**
    * Check plugin requirements
    *
    * @since   1.1.0
    * @change  1.1.0
    */

    public static function requirements_check() {

        // cache enabler options
        $options = self::$options;

        // WordPress version check
        if ( version_compare($GLOBALS['wp_version'], Optimisationio_CdnEnabler_MIN_WP.'alpha', '<') ) {
            show_message(
                sprintf(
                    '<div class="error"><p>%s</p></div>',
                    sprintf(
                        __('The <b>%s</b> is optimized for WordPress %s. Please disable the plugin or upgrade your WordPress installation (recommended).', 'optimisationio'),
                        'Cache Enabler',
                        Optimisationio_CdnEnabler_MIN_WP
                    )
                )
            );
        }

        // permission check
        if ( file_exists( Optimisationio_CACHE_DIR ) && !is_writable( Optimisationio_CACHE_DIR ) ) {
            show_message(
                sprintf(
                    '<div class="error"><p>%s</p></div>',
                    sprintf(
                        __('The <b>%s</b> requires write permissions %s on %s. Please <a href="%s" target="_blank">change the permissions</a>.', 'optimisationio'),
                        'Cache Enabler',
                        '<code>755</code>',
                        '<code>wp-content/cache</code>',
                        'http://codex.wordpress.org/Changing_File_Permissions',
                        Optimisationio_CdnEnabler_MIN_WP
                    )
                )
            );
        }

        // autoptimize minification check
        if ( defined('AUTOPTIMIZE_PLUGIN_DIR') && $options['minify_html'] ) {
            show_message(
                sprintf(
                    '<div class="error"><p>%s</p></div>',
                    sprintf(
                        __('The <b>%s</b> plugin is already active. Please disable minification in the <b>%s</b> settings.', 'optimisationio'),
                        'Autoptimize',
                        'Optimisationio'
                    )
                )
            );
        }
    }


    /**
    * register textdomain
    *
    * @since   1.0.0
    * @change  1.0.0
    */

    public static function register_textdomain() {

        load_plugin_textdomain(
            'optimisationio',
            false,
            'optimisationio/lang'
        );
    }


    /**
    * register settings
    *
    * @since   1.0.0
    * @change  1.0.0
    */

    public static function register_settings() {

        register_setting(
            'optimisationio',
            'cache',
            array(
                __CLASS__,
                'validate_settings'
            )
        );
    }


    /**
    * validate settings
    *
    * @since   1.0.0
    * @change  1.0.9
    *
    * @param   array  $data  array form data
    * @return  array         array form data valid
    */

    public static function validate_settings($data) {

        // check if empty
        if ( empty($data) ) {
            return;
        }

        // clear complete cache
        self::clear_total_cache(true);

        return array(
            'cache_expires'        => (int)$data['cache_expires'],
            'cache_new_post'     => (int)(!empty($data['cache_new_post'])),
            'cache_new_comment'     => (int)(!empty($data['cache_new_comment'])),
            'cache_webp'            => (int)(!empty($data['cache_webp'])),
            'cache_compress'        => (int)(!empty($data['cache_compress'])),
            'excl_ids'         => (string)sanitize_text_field(@$data['excl_ids']),
            'minify_html'     => (int)$data['minify_html']
        );
    }



}
