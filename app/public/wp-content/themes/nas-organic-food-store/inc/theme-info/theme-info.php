<?php
/**
 * Theme Info Admin Menu
 */
if ( ! class_exists( 'Organic_Theme_Info' ) ) {
    class Organic_Theme_Info{

        private $config;
        private $theme_name;
        private $theme_slug;
        private $theme_version;
        private $page_title;
        private $menu_title;
        private $tabs;

        /**
         * Constructor.
         */
        public function __construct( $config ) {
            $this->config = $config;
            $this->prepare_class();

            /* Admin menu */
            add_action( 'admin_menu', array( $this, 'bk_admin_menu' ) );

            /* Enqueue script and style for about page */
            add_action( 'admin_enqueue_scripts', array( $this, 'style_and_scripts' ) );

            /* Ajax callback for dismissable required actions */
            add_action( 'wp_ajax_organic_theme_info_update_recommended_action', array( $this, 'update_recommended_action_callback' ) );
        }

        /**
         * Prepare and setup class properties.
         */
        public function prepare_class() {
            $theme = wp_get_theme();
            $this->theme_name    = esc_attr( $theme->get( 'Name' ) );
            $this->theme_slug    = $theme->get_template();
            $this->theme_version = $theme->get( 'Version' );
            $this->page_title    = $this->theme_name . esc_html__( ' Info', 'nas-organic-food-store' );
            $this->menu_title    = $this->theme_name . esc_html__( ' Theme', 'nas-organic-food-store' );
            $this->tabs          = isset( $this->config['tabs'] ) ? $this->config['tabs'] : array();
        }

        /**
         * Return the valid array of recommended actions.
         * @return array The valid array of recommended actions.
         */
        /**
         * Dismiss required actions
         */
        public function update_recommended_action_callback() {

            /*getting for provided array*/
            $recommended_actions = isset( $this->config[ 'recommended_actions' ] ) ? $this->config[ 'recommended_actions' ] : array();

            /*from js action*/
            $action_id = esc_attr( ( isset( $_GET[ 'id' ] ) ) ? $_GET[ 'id' ] : 0 );
            $todo = esc_attr( ( isset( $_GET[ 'todo' ] ) ) ? $_GET[ 'todo'] : '' );

            /*getting saved actions*/
            $saved_actions = get_option( $this->theme_slug . '_recommended_actions' );

            echo esc_html( wp_unslash( $action_id ) ); /* this is needed and it's the id of the dismissable required action */

            if ( ! empty( $action_id ) ) {

                if( 'reset' == $todo ){
                    $saved_actions_new = array();
                    if ( ! empty( $recommended_actions ) ) {

                        foreach ( $recommended_actions as $recommended_action ) {
                            $saved_actions[ $recommended_action['id'] ] = true;
                        }
                        update_option( $this->theme_slug . '_recommended_actions', $saved_actions_new );
                    }
                }
                /* if the option exists, update the record for the specified id */
                elseif ( !empty( $saved_actions) and is_array( $saved_actions ) ) {

                    switch ( esc_html( $todo ) ) {
                        case 'add';
                            $saved_actions[ $action_id ] = true;
                            break;
                        case 'dismiss';
                            $saved_actions[ $action_id ] = false;
                            break;
                    }
                    update_option( $this->theme_slug . '_recommended_actions', $saved_actions );

                    /* create the new option,with false for the specified id */
                }
                else {
                    $saved_actions_new = array();
                    if ( ! empty( $recommended_actions ) ) {

                        foreach ( $recommended_actions as $recommended_action ) {
                            echo esc_html($recommended_action['id']);
                            echo " ". esc_html($todo);
                            if ( $recommended_action['id'] == $action_id ) {
                                switch ( esc_html( $todo ) ) {
                                    case 'add';
                                        $saved_actions_new[ $action_id ] = true;
                                        break;
                                    case 'dismiss';
                                        $saved_actions_new[ $action_id ] = false;
                                        break;
                                }
                            }
                        }
                    }
                    update_option( $this->theme_slug . '_recommended_actions', $saved_actions_new );
                }
            }
            exit;
        }

        private function get_recommended_actions() {
            $saved_actions = get_option( $this->theme_slug . '_recommended_actions' );
            if ( ! is_array( $saved_actions ) ) {
                $saved_actions = array();
            }
            $recommended_actions = isset( $this->config['recommended_actions'] ) ? $this->config['recommended_actions'] : array();
            $valid       = array();
            if( isset( $recommended_actions ) && is_array( $recommended_actions ) ){
                foreach ( $recommended_actions as $recommended_action ) {
                    if (
                        (
                            ! isset( $recommended_action['check'] ) ||
                            ( isset( $recommended_action['check'] ) && ( $recommended_action['check'] == false ) )
                        )
                        &&
                        ( ! isset( $saved_actions[ $recommended_action['id'] ] ) ||
                            ( isset( $saved_actions[ $recommended_action['id']] ) && ($saved_actions[ $recommended_action['id']] == true ) )
                        )
                    ) {
                        $valid[] = $recommended_action;
                    }
                }
            }
            return $valid;
        }

        private function count_recommended_actions() {
            $count = 0;
            $actions_count = $this->get_recommended_actions();
            if ( ! empty( $actions_count ) ) {
                $count = count( $actions_count );
            }
            return $count;
        }
        
        /**
         * Adding Theme Info Menu under Appearance.
         */
        function bk_admin_menu() {

            if ( ! empty( $this->page_title ) && ! empty( $this->menu_title ) ) {
                $count = $this->count_recommended_actions();
                $menu_title = $count > 0 ? $this->menu_title . '<span class="badge-action-count">' . esc_html( $count ) . '</span>' : $this->menu_title;
                add_theme_page( $this->page_title, $menu_title, 'edit_theme_options', $this->theme_slug . '-info', array(
                    $this,
                    'organic_theme_info_screen',
                ) );
            }
        }

        /**
         * Render the info content screen.
         */
        public function Organic_theme_info_screen() {

            $theme_name_config = esc_attr ( wp_get_theme()->get('Name') );
            $theme_name_config_url = strtolower( str_replace( ' ', '-', $theme_name_config ) );

            if ( ! empty( $this->config[ 'info_title' ] ) ) {
                $welcome_title = $this->config[ 'info_title' ];
            }
            if ( ! empty( $this->config[ 'info_content' ] ) ) {
                $welcome_content = $this->config[ 'info_content' ];
            }
            if ( ! empty( $this->config[ 'quick_links' ] ) ) {
                $quick_links = $this->config[ 'quick_links' ];
            }

            if (
                ! empty( $welcome_title ) ||
                ! empty( $welcome_content ) ||
                ! empty( $quick_links ) ||
                ! empty( $this->tabs )
            ) {
                echo '<div class="wrap about-wrap info-wrap epsilon-wrap">';
                echo '<div class="header-wrap display-grid col-grid-2 align-center">';
                echo '<div class="theme-detail col">';

                if ( ! empty( $welcome_title ) ) {
                    echo '<h1>';
                    echo esc_html( $welcome_title );
                    if ( ! empty( $this->theme_version ) ) {
                        echo esc_html( $this->theme_version );
                    }
                    echo '</h1>';
                }
                if ( ! empty( $welcome_content ) ) {
                    echo '<div class="about-text">' . wp_kses_post( $welcome_content ) . '</div>';
                }

                /*quick links*/
                if( !empty( $quick_links ) && is_array( $quick_links ) ){
                    echo '<p class="quick-links">';
                    foreach ( $quick_links as $quick_key => $quick_link ) {
                        $button = 'button-secondary';
                        if( 'pro_url' == $quick_key ){
                            $button = 'button button-primary button-hero';
                        }
                        echo '<a href="'.esc_url( $quick_link['url'] ).'" class="button button-hero '.esc_attr( $button ).'" target="_blank">'.$quick_link['text'].'</a>';
                    }
                    echo "</p>";
                }
                echo '</div>';
                echo '<div class="theme-img col">';
                echo '<a href="' . esc_url( 'https://www.templatehouse.net/themes/grocery-store-and-organic-food-woocommerce-ecommerce-wordpress-theme/' ).'" target="_blank">';
                echo '<img src="' . get_template_directory_uri() . '/screenshot.png" alt="screenshot" />';
                echo '</a>';
                echo '</div>';
                echo '</div>';
                /* Display tabs */
                if ( ! empty( $this->tabs ) ) {
                    $current_tab = isset( $_GET['tab'] ) ? wp_unslash( $_GET['tab'] ) : 'getting_started';

                    echo '<h2 class="nav-tab-wrapper wp-clearfix">';
                    $count = $this->count_recommended_actions();
                    foreach ( $this->tabs as $tab_key => $tab_name ) {

                        echo '<a href="' . esc_url( admin_url( 'themes.php?page=' . $this->theme_slug . '-info' ) ) . '&tab=' . $tab_key . '" class="nav-tab ' . ( $current_tab == $tab_key ? 'nav-tab-active' : '' ) . '" role="tab" data-toggle="tab">';
                        echo esc_html( $tab_name );
                        if ( $tab_key == 'recommended_actions' ) {
                            if ( $count > 0 ) {
                                echo '<span class="badge-action-count">' . esc_html( $count ) . '</span>';
                            }
                        }
                        echo '</a>';
                    }

                    echo '</h2>';

                    /* Display content for current tab, dynamic method according to key provided*/
                    if ( method_exists( $this, $current_tab ) ) {

                        echo "<div class='changelog point-releases'>";
                        $this->$current_tab();
                        echo "</div>";
                    }
                }
                echo '</div><!--/.wrap.about-wrap-->';
            }
        }

        /**
         * Getting started tab
         */
        public function getting_started() {
            echo '<div class="feature-section display-grid col-grid-3">';
            if ( ! empty( $this->config['gs_steps'] ) ) {
                $gs_steps = $this->config['gs_steps'];
                if ( ! empty( $gs_steps ) ) {

                    /*defaults values for gs_steps array */
                    $defaults = array(
                        'title'     => '',
                        'desc'       => '',
                        'button_label'   => '',
                        'button_link'   => '',
                        'is_button' => true,
                        'is_new_tab' => false,
                        'is_gs' => false,
                    );
                    foreach ( $gs_steps as $gs_step ) {
                        $instance = wp_parse_args( (array) $gs_step, $defaults );

                        /*allowed 7 value in array */
                        $title = $instance[ 'title' ];
                        $desc = $instance[ 'desc'];
                        $button_label = $instance[ 'button_label'];
                        $button_link = $instance[ 'button_link'];
                        $is_button = $instance[ 'is_button'];
                        $is_new_tab = $instance[ 'is_new_tab'];
                        $is_gs = $instance[ 'is_gs' ];
                        
                        echo '<div class="col-items">';
                        
                        if( $is_gs ) {
                            
                        }else{
                            if ( ! empty( $title ) ) {
                                echo '<h3>';
                                echo esc_html($title);
                                echo '</h3>';
                            }

                            if ( ! empty( $desc ) ) {
                                echo '<p>' . $desc . '</p>';
                            }

                            if ( ! empty( $button_link ) && ! empty( $button_label ) ) {

                                echo '<p>';
                                $button_class = '';
                                if ( $is_button ) {
                                    $button_class = 'button button-primary button-hero';
                                }

                                $button_new_tab = '_self';
                                if ( isset( $is_new_tab ) ) {
                                    if ( $is_new_tab ) {
                                        $button_new_tab = '_blank';
                                    }
                                }
                                echo '<a target="' . $button_new_tab . '" href="' . $button_link . '" class="' . $button_class . '">' . $button_label . '</a>';
                                echo '</p>';
                            }
                            echo '</div>';
                        }
                    }
                }
            }
            echo '</div><!-- .feature-section col-wrap -->';
        }

        /**
         * Recommended Plugins tab
         */
        public function check_plugin_status( $slug ) {

            $path = WPMU_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php';
            if ( ! file_exists( $path ) ) {
                $path = WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php';
                if ( ! file_exists( $path ) ) {
                    $path = false;
                }
            }

            if ( file_exists( $path ) ) {
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

                $needs = is_plugin_active( $slug . '/' . $slug . '.php' ) ? 'deactivate' : 'activate';

                return array( 'status' => is_plugin_active( $slug . '/' . $slug . '.php' ), 'needs' => $needs );
            }

            return array( 'status' => false, 'needs' => 'install' );
        }

        public function create_action_link( $state, $slug ) {

            switch ( $state ) {
                case 'install':
                    return wp_nonce_url(
                        add_query_arg(
                            array(
                                'action' => 'install-plugin',
                                'plugin' => $slug
                            ),
                            network_admin_url( 'update.php' )
                        ),
                        'install-plugin_' . $slug
                    );
                    break;

                case 'deactivate':
                    return add_query_arg(
                            array(
                                'action'        => 'deactivate',
                                'plugin'        => rawurlencode( $slug . '/' . $slug . '.php' ),
                                'plugin_status' => 'all',
                                'paged'         => '1',
                                '_wpnonce'      => wp_create_nonce( 'deactivate-plugin_' . $slug . '/' . $slug . '.php' )
                                ),
                            network_admin_url( 'plugins.php' )
                    );
                    break;

                case 'activate':
                    return add_query_arg(
                            array(
                                'action'        => 'activate',
                                'plugin'        => rawurlencode( $slug . '/' . $slug . '.php' ),
                                'plugin_status' => 'all',
                                'paged'         => '1',
                                '_wpnonce'      => wp_create_nonce( 'activate-plugin_' . $slug . '/' . $slug . '.php' )
                            ),
                            network_admin_url( 'plugins.php' )
                    );
                    break;
            }
        }

        public function recommended_actions() {

            $recommended_actions = isset( $this->config['recommended_actions'] ) ? $this->config['recommended_actions'] : array();
            $hooray = true;
            if ( ! empty( $recommended_actions ) ) {

                echo '<div class="feature-section action-recommended demo-import-boxed" id="plugin-filter">';

                if ( ! empty( $recommended_actions ) && is_array( $recommended_actions ) ) {

                    /*get saved recommend actions*/
                    $saved_recommended_actions = get_option( $this->theme_slug . '_recommended_actions' );

                    /*defaults values for getting_started array */
                    $defaults = array(
                        'title'         => '',
                        'desc'          => '',
                        'check'         => false,
                        'plugin_slug'   => '',
                        'id'            => ''
                    );
                    foreach ( $recommended_actions as $action_key => $action_value ) {
                        $instance = wp_parse_args( (array) $action_value, $defaults );

                        /*allowed 5 value in array */
                        $title = $instance[ 'title' ];
                        $desc = $instance[ 'desc' ];
                        $check = $instance[ 'check' ];
                        $plugin_slug = $instance[ 'plugin_slug' ];
                        $id = $instance[ 'id' ];

                        $hidden = false;

                        /*magic check for recommended actions*/
                        if (
                            isset( $saved_recommended_actions[ $id ] ) &&
                            $saved_recommended_actions[ $id ] == false ) {
                            $hidden = true;
                        }
                        if ( $hidden ) {
                            echo '';
                        }
                        $done = '';
                        if ( $check ) {
                           $done = 'done';
                        }

                        echo "<div class='bk-theme-info-action-recommended-box {$done}'>";

                        if ( ! $hidden ) {
                            echo '<span data-action="dismiss" class="dashicons dashicons-visibility bk-theme-info-recommended-action-button" id="' . esc_attr( $action_value['id'] ) . '"></span>';
                        } else {
                            echo '<span data-action="add" class="dashicons dashicons-hidden bk-theme-info-recommended-action-button" id="' . esc_attr( $action_value['id'] ) .'"></span>';
                        }

                        if ( ! empty( $title) ) {
                            echo '<h3>' . wp_kses_post( $title ) . '</h3>';
                        }

                        if ( ! empty( $desc ) ) {
                            echo '<p>' . wp_kses_post( $desc ) . '</p>';
                        }

                        if ( ! empty( $plugin_slug ) ) {

                            $active = $this->check_plugin_status( $action_value['plugin_slug'] );
                            $url    = $this->create_action_link( $active['needs'], $action_value['plugin_slug'] );
                            $label  = '';
                            $class  = '';
                            switch ( $active['needs'] ) {

                                case 'install':
                                    $class = 'install-now button';
                                    $label = esc_html__( 'Install', 'nas-organic-food-store' );
                                    break;

                                case 'activate':
                                    $class = 'activate-now button button-primary';
                                    $label = esc_html__( 'Activate', 'nas-organic-food-store' );

                                    break;

                                case 'deactivate':
                                    $class = 'deactivate-now button';
                                    $label = esc_html__( 'Deactivate', 'nas-organic-food-store' );

                                    break;
                            }

                            ?>
                            <p class="plugin-card-<?php echo esc_attr( $action_value['plugin_slug'] ) ?> action_button <?php echo ( $active['needs'] !== 'install' && $active['status'] ) ? 'active' : '' ?>">
                                <a data-slug="<?php echo esc_attr( $action_value['plugin_slug'] ) ?>"
                                   class="<?php echo esc_attr( $class ); ?>"
                                   href="<?php echo esc_url( $url ) ?>"> <?php echo esc_html( $label ) ?> </a>
                            </p>

                            <?php

                        }
                        echo '</div>';
                        $hooray = false;
                    }
                }
                if ( $hooray ){
                    echo '<span class="hooray">' . esc_html__( 'Hooray! There are no recommended actions for you right now.', 'nas-organic-food-store' ) . '</span>';
                    echo '<a data-action="reset" id="reset" class="reset-all" href="#">'.esc_html__('Show All Recommended Actions', 'nas-organic-food-store').'</a>';
                }
                echo '</div>';
            }
        }

        /**
         * Recommended plugins tab
         */
        /*
         * Call plugin api
         */
        public function call_plugin_api( $slug ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

            if ( false === ( $call_api = get_transient( 'Organic_theme_info_plugin_information_transient_' . $slug ) ) ) {
                $call_api = plugins_api( 'plugin_information', array(
                    'slug'   => $slug,
                    'fields' => array(
                        'downloaded'        => false,
                        'rating'            => false,
                        'description'       => false,
                        'short_description' => true,
                        'donate_link'       => false,
                        'tags'              => false,
                        'sections'          => true,
                        'homepage'          => true,
                        'added'             => false,
                        'last_updated'      => false,
                        'compatibility'     => false,
                        'tested'            => false,
                        'requires'          => false,
                        'downloadlink'      => false,
                        'icons'             => true
                    )
                ) );
                set_transient( 'Organic_theme_info_plugin_information_transient_' . $slug, $call_api, 30 * MINUTE_IN_SECONDS );
            }

            return $call_api;
        }
        public function get_plugin_icon( $arr ) {

            if ( ! empty( $arr['svg'] ) ) {
                $plugin_icon_url = $arr['svg'];
            } elseif ( ! empty( $arr['2x'] ) ) {
                $plugin_icon_url = $arr['2x'];
            } elseif ( ! empty( $arr['1x'] ) ) {
                $plugin_icon_url = $arr['1x'];
            } else {
                $plugin_icon_url = get_template_directory_uri() . '/assets/placeholder_plugin.png';
            }

            return $plugin_icon_url;
        }
        public function recommended_plugins() {
            $recommended_plugins = $this->config['recommended_plugins'];

            if ( ! empty( $recommended_plugins ) ) {
                if ( ! empty( $recommended_plugins ) && is_array( $recommended_plugins ) ) {

                    echo '<div class="feature-section recommended-plugins col-wrap demo-import-boxed" id="plugin-filter">';

                    foreach ( $recommended_plugins as $recommended_plugins_item ) {

                        if ( ! empty( $recommended_plugins_item['slug'] ) ) {
                            $info   = $this->call_plugin_api( $recommended_plugins_item['slug'] );
                            if ( ! empty( $info->icons ) ) {
                                $icon = $this->get_plugin_icon( $info->icons );
                            }

                            $active = $this->check_plugin_status( $recommended_plugins_item['slug'] );

                            if ( ! empty( $active['needs'] ) ) {
                                $url = $this->create_action_link( $active['needs'], $recommended_plugins_item['slug'] );
                            }

                            echo '<div class="col"><div class="col-items plugin_box">';
                            if ( ! empty( $icon ) ) {
                                echo '<img src="'.esc_url( $icon ).'" alt="plugin box image">';
                            }
                            if ( ! empty(  $info->version ) ) {
                                echo '<span class="version">'. ( ! empty( $this->config['recommended_plugins']['version_label'] ) ? esc_html( $this->config['recommended_plugins']['version_label'] ) : '' ) . esc_html( $info->version ).'</span>';
                            }
                            if ( ! empty( $info->author ) ) {
                                echo '<span class="separator"> | </span>' . wp_kses_post( $info->author );
                            }

                            if ( ! empty( $info->name ) && ! empty( $active ) ) {
                                echo '<div class="action_bar ' . ( ( $active['needs'] !== 'install' && $active['status'] ) ? 'active' : '' ) . '">';
                                echo '<span class="plugin_name">' . ( ( $active['needs'] !== 'install' && $active['status'] ) ? 'Active: ' : '' ) . esc_html( $info->name ) . '</span>';
                                echo '</div>';

                                $label = '';

                                switch ( $active['needs'] ) {

                                    case 'install':
                                        $class = 'install-now button';
                                        $label = esc_html__( 'Install', 'nas-organic-food-store' );
                                        break;

                                    case 'activate':
                                        $class = 'activate-now button button-primary';
                                        $label = esc_html__( 'Activate', 'nas-organic-food-store' );

                                        break;

                                    case 'deactivate':
                                        $class = 'deactivate-now button';
                                        $label = esc_html__( 'Deactivate', 'nas-organic-food-store' );

                                        break;
                                }

                                echo '<span class="plugin-card-' . esc_attr( $recommended_plugins_item['slug'] ) . ' action_button ' . ( ( $active['needs'] !== 'install' && $active['status'] ) ? 'active' : '' ) . '">';
                                echo '<a data-slug="' . esc_attr( $recommended_plugins_item['slug'] ) . '" class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
                                echo '</span>';
                            }
                            echo '</div></div><!-- .col.plugin_box -->';
                        }
                    }
                    echo '</div><!-- .recommended-plugins -->';
                }
            }
        }

        /**
         * Child themes
         */
        public function child_themes() {
            echo '<div id="child-themes" class="bk-theme-info-tab-pane">';
            $child_themes = isset( $this->config['child_themes'] ) ? $this->config['child_themes'] : array();
            if ( ! empty( $child_themes ) ) {

                /*defaults values for child theme array */
                $defaults = array(
                    'title'        => '',
                    'screenshot'   => '',
                    'download_link'=> '',
                    'preview_link' => ''
                );
                if ( ! empty( $child_themes ) && is_array( $child_themes ) ) {
                    echo '<div class="bk-about-row">';
                    $i = 0;
                    foreach ( $child_themes as $child_theme ){
                        $instance = wp_parse_args( (array) $child_theme, $defaults );

                        /*allowed 5 value in array */
                        $title = $instance[ 'title' ];
                        $screenshot = $instance[ 'screenshot'];
                        $download_link = $instance[ 'download_link'];
                        $preview_link = $instance[ 'preview_link'];

                        if( !empty( $screenshot) ){
                            echo '<div class="bk-about-child-theme">';
                            echo '<div class="bk-theme-info-child-theme-image">';

                            echo '<img src="' . esc_url( $screenshot ) . '" alt="' . ( ! empty( $title ) ? esc_attr( $title ) : '' ) . '" />';

                            if ( ! empty( $title ) ) {
                                echo '<div class="bk-theme-info-child-theme-details">';
                                echo '<div class="theme-details">';
                                echo '<span class="theme-name">' . esc_html( $title  ). '</span>';
                                if ( ! empty( $download_link ) ) {
                                    echo '<a href="' . esc_url( $download_link ) . '" class="button button-primary install right">' . esc_html__( 'Download', 'nas-organic-food-store' ) . '</a>';
                                }
                                if ( ! empty( $preview_link ) ) {
                                    echo '<a class="button button-secondary preview right" target="_blank" href="' . $preview_link . '">' . esc_html__( 'Live Preview', 'nas-organic-food-store' ). '</a>';
                                }
                                echo '</div>';
                                echo '</div>';
                            }

                            echo "</div>";
                            echo "</div>";
                            $i++;
                        }
                        if( 0 == $i % 3 ){
                            echo "</div><div class='bk-about-row'>";/*.bk-about-row end-start*/
                        }
                    }

                    echo '</div>';/*.bk-about-row end*/
                }// End if().
            }// End if().
            echo '</div>';
        }

        /**
         * Changelog tab
         */
        private function parse_changelog() {
            WP_Filesystem();
            global $wp_filesystem;
            if ( is_child_theme() ){
                $changelog = $wp_filesystem->get_contents( get_stylesheet_directory() . '/inc/theme-info/changelog.txt' );
            }else{
                $changelog = $wp_filesystem->get_contents( get_template_directory() . '/inc/theme-info/changelog.txt' );
            }
            if ( is_wp_error( $changelog ) ) {
                $changelog = '';
            }
            return $changelog;
        }

       

        

        /**
         * Load css and scripts for the about page
         */
        public function style_and_scripts( $hook_suffix ) {

            // this is needed on all admin pages, not just the about page, for the badge action count in the WordPress main sidebar
            wp_enqueue_style( 'bk-theme-info-css', get_template_directory_uri() . '/inc/theme-info/assets/css/theme-info.css' );

            if ( 'appearance_page_' . $this->theme_slug . '-info' == $hook_suffix ) {

                wp_enqueue_style( 'plugin-install' );
                wp_enqueue_script( 'plugin-install' );
                wp_enqueue_script( 'updates' );

                $count = $this->count_recommended_actions();
                wp_localize_script( 'bk-theme-info-js', 'Organic_theme_info_object', array(
                    'nr_actions_recommended'   => $count,
                    'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
                    'template_directory'       => get_template_directory_uri()
                ) );

            }

        }
    }
}

$theme_name_config = esc_attr ( wp_get_theme()->get('Name') );
$theme_name_config_url = strtolower( str_replace( ' ', '-', $theme_name_config ) );

$config = array(

    // Main welcome title
    'info_title' => sprintf( esc_html__( 'Welcome to %s - ', 'nas-organic-food-store' ), $theme_name_config ),

    // Main welcome content
    'info_content' => sprintf( esc_html__( '%s is now installed and ready to use. We hope the following information will help and you enjoy using it!', 'nas-organic-food-store' ), '<b>'.$theme_name_config.'</b>' ),

    /**
     * Quick links
     */
    'quick_links' => array(
        'theme_url'  => array(
            'text' => __( 'Theme Info', 'nas-organic-food-store' ),
            'url' => esc_url( 'https://www.templatehouse.net/themes/grocery-store-and-organic-food-woocommerce-ecommerce-wordpress-theme/' )
        ),

    ),

    'tabs' => array(
        'getting_started'      => esc_html__( 'Getting Started', 'nas-organic-food-store' ),
        
        'recommended_plugins'  => esc_html__( 'Recommended Plugins', 'nas-organic-food-store' ),
        
    ),

    /*Getting started tab*/
    'gs_steps' => array(
        
        'two' => array(
            'title' => esc_html__( 'Pre-built Demos', 'nas-organic-food-store' ),
            'desc' => esc_html__( 'We recommend utilizing our collection of pre-designed starter site templates as an efficient and convenient way to begin your project.', 'nas-organic-food-store' ),
            'button_label' => esc_html__( 'Browse Demos', 'nas-organic-food-store' ),
            'button_link' => esc_url( 'https://www.templatehouse.net/themes/grocery-store-and-organic-food-woocommerce-ecommerce-wordpress-theme/' ),
            'is_button' => true,
            'is_new_tab' => true
        ),


        'seven' => array(
            'title' => esc_html__( 'Need WordPress Theme Developemnt or Customization?', 'nas-organic-food-store' ),
            'desc' => esc_html__( 'We Create Beautiful & SEO Friendly WordPress Websites. Theme Customization to Custom WordPress Plugin Development.', 'nas-organic-food-store' ),
            'button_label' => esc_html__( 'Contact Now', 'nas-organic-food-store' ),
            'button_link' => esc_url( 'https://templatehouse.net/custom-wordpress-solution/' ),
            'is_button' => true,
            'is_new_tab' => true
        ),


       
        'five' => array(
            'title' => esc_html__( 'Love Our Product?', 'nas-organic-food-store' ),
            'desc' => esc_html__( 'We would greatly appreciate your motivation through a 5-star rating on our profile.', 'nas-organic-food-store' ),
            'button_label' => esc_html__( 'Rate Us', 'nas-organic-food-store' ),
            'button_link' => esc_url( 'https://wordpress.org/support/theme/' .$theme_name_config_url. '/reviews' ),
            'is_button' => true,
            'is_new_tab' => true
        ),
        'six' => array(
            'title' => esc_html__( 'Have Question? Ask Us, Now!', 'nas-organic-food-store' ),
            'desc' => esc_html__( 'We are committed to resolving any issues that may arise to ensure a seamless experience while using our theme.', 'nas-organic-food-store' ),
            'button_label' => esc_html__( 'Get Support', 'nas-organic-food-store' ),
            'button_link' => esc_url( 'https://templatehouse.net/contact-us/' ),
            'is_button' => true,
            'is_new_tab' => true
        ),


      





    ),

    // recommended actions array.
    'recommended_actions' => array(

    ),

    // Free vs pro array.
    'free_pro' => array(
        array(
            'desc'=> __( 'Access to All Starter Sites', 'nas-organic-food-store' ),
            'free' => __( 'no','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ), 
        array(
            'desc'=> __( 'Access to All Templates & Patterns', 'nas-organic-food-store' ),
            'free' => __( 'no','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'Work with the Page Builders', 'nas-organic-food-store' ),
            'free' => __( 'no','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'Creative & Unique Blocks', 'nas-organic-food-store' ),
            'free' => __( 'no','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'Elementor Ready', 'nas-organic-food-store' ),
            'free' => __( 'no','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'Priority Updates', 'nas-organic-food-store' ),
            'free' => __( 'no','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'Priority Support', 'nas-organic-food-store' ),
            'free' => __( 'no','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'Full Site Editing', 'nas-organic-food-store' ),
            'free' => __( 'yes','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'Customize Everything', 'nas-organic-food-store' ),
            'free' => __( 'yes','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'Lighting Fast', 'nas-organic-food-store' ),
            'free' => __( 'yes','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'One-click Demo Installer', 'nas-organic-food-store' ),
            'free' => __( 'yes','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'Fully Responsive', 'nas-organic-food-store' ),
            'free' => __( 'yes','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'Clean & Optimized Code', 'nas-organic-food-store' ),
            'free' => __( 'yes','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'Multiple Site Styles', 'nas-organic-food-store' ),
            'free' => __( 'yes','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'Global Settings', 'nas-organic-food-store' ),
            'free' => __( 'yes','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'CSS Animation', 'nas-organic-food-store' ),
            'free' => __( 'yes','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'Gutenberg Ready', 'nas-organic-food-store' ),
            'free' => __( 'yes','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'eCommerce Ready', 'nas-organic-food-store' ),
            'free' => __( 'yes','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'SEO Optimized', 'nas-organic-food-store' ),
            'free' => __( 'yes','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'Multilingual Compatibility', 'nas-organic-food-store' ),
            'free' => __( 'yes','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
        array(
            'desc'=> __( 'RTL/LTR Language Support', 'nas-organic-food-store' ),
            'free' => __( 'yes','nas-organic-food-store' ),
            'pro'  => __( 'yes','nas-organic-food-store' ),
        ),
    ),

    // Generic Plugins array.
    'recommended_plugins' => array(
        'WooCommerce - WordPress Plugin' => array(
            'slug' => 'woocommerce'
        ),


        'Contact form 7' => array(
            'slug' => 'contact-form-7'
        ),







    ),
);

return new Organic_Theme_Info( $config );