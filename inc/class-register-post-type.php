<?php

namespace NewswirePublisherWordpressPlugin\Inc;

class RegisterPostType {
    private static $instance = null;

    protected $post_type = 'news';
    protected $singular_upcase = 'News';
    protected $plural_upcase = 'News';
    protected $singular_downcase = 'news';
    protected $plural_downcase = 'news';

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        add_action( 'init', array( $this, 'register_post_type' ), 0 );
    }

    protected function get_labels()
    {
        return array(
            'name'                => _x( $this->plural_upcase, 'Post Type General Name', 'newswirepublisher' ),
            'singular_name'       => _x( $this->singular_upcase, 'Post Type Singular Name', 'newswirepublisher' ),
            'menu_name'           => __( $this->plural_upcase, 'newswirepublisher' ),
            'parent_item_colon'   => __( 'Parent ' . $this->singular_upcase, 'newswirepublisher' ),
            'all_items'           => __( 'All ' . $this->plural_upcase, 'newswirepublisher' ),
            'view_item'           => __( 'View ' . $this->singular_upcase, 'newswirepublisher' ),
            'add_new_item'        => __( 'Add New ' . $this->singular_upcase, 'newswirepublisher' ),
            'add_new'             => __( 'Add New', 'newswirepublisher' ),
            'edit_item'           => __( 'Edit ' . $this->singular_upcase, 'newswirepublisher' ),
            'update_item'         => __( 'Update ' . $this->singular_upcase, 'newswirepublisher' ),
            'search_items'        => __( 'Search ' . $this->singular_upcase, 'newswirepublisher' ),
            'not_found'           => __( 'Not Found', 'newswirepublisher' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'newswirepublisher' ),
        );
    }

    public function register_post_type()
    {
        $args = array(
            'label'               => __( $this->plural_upcase, 'newswirepublisher' ),
            'description'         => __( 'FourthEstate Newswire Wordpress plugin imported news', 'newswirepublisher' ),
            'labels'              => $this->get_labels(),
            'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
            'taxonomies'          => array( 'category', 'post_tag', 'places' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'show_in_rest' => true,
        
        );

        register_post_type( $this->post_type, $args );  
    }

    public static function get_registered_post_type()
    {
        return self::get_instance()->post_type;
    }
}

RegisterPostType::get_instance();
