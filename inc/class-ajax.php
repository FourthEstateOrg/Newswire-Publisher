<?php

namespace NewswirePublisherWordpressPlugin\Inc;

class Ajax {
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        add_action( 'wp_ajax_migrate_posts_to_news', array( $this, 'migrate_posts_to_news' ) );
        add_action( 'wp_ajax_migrate_all_posts_to_news', array( $this, 'migrate_all_posts_to_news' ) );
    }

    public function migrate_posts_to_news() {
        global $wpdb;
        $args = array(
            'post_type'      => 'post',
            // 'post_type'      => 'news',
            'meta_key'       => 'newswire_migrated',
            'meta_compare'   => 'NOT EXISTS',
            'posts_per_page' => 50,
        );
        $found_posts = new \WP_Query( $args );

        $result = array(
            'success'        => 1,
            'total_posts'    => count( $found_posts->posts ),
            'total_migrated' => 0,
        );
        
        if ( ! $found_posts->have_posts() ) {
            $result = array(
                'success' => 0,
                'message' => 'No more posts to migrate.'
            );
            wp_die( json_encode( $result ) );
        };

        foreach ($found_posts->posts as $post) {
            $matched = $wpdb->get_row("SELECT post_id FROM " . $wpdb->prefix . NWPWP_DB_TABLE_SYNC_POST . " WHERE post_id = '" . $post->ID . "'");
            if ( $matched ) {
                wp_update_post(array(
                    'ID'        => $post->ID,
                    'post_type' => RegisterPostType::get_registered_post_type(),
                ));
                $result['total_migrated'] += 1;
            }
            update_post_meta($post->ID, 'newswire_migrated', 1);
        }

        // echo $whatever;

        wp_die( json_encode( $result ) ); // this is required to terminate immediately and return a proper response
    }

    public function migrate_all_posts_to_news() {
        global $wpdb;
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => 50,
        );
        $found_posts = new \WP_Query( $args );

        $result = array(
            'success'        => 1,
            'total_posts'    => count( $found_posts->posts ),
            'total_migrated' => 0,
        );
        
        if ( ! $found_posts->have_posts() ) {
            $result = array(
                'success' => 0,
                'message' => 'No more posts to migrate.'
            );
            wp_die( json_encode( $result ) );
        };

        foreach ($found_posts->posts as $post) {
            wp_update_post(array(
                'ID'        => $post->ID,
                'post_type' => RegisterPostType::get_registered_post_type(),
            ));
            $result['total_migrated'] += 1;
            update_post_meta($post->ID, 'newswire_migrated', 1);
        }

        // echo $whatever;

        wp_die( json_encode( $result ) ); // this is required to terminate immediately and return a proper response
    }
}

Ajax::get_instance();