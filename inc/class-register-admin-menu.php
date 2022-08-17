<?php

namespace NewswirePublisherWordpressPlugin\Inc;

class RegisterAdminMenu {
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
        add_action('admin_bar_menu', array( $this, 'nwpwp_add_toolbar_items' ), 100);
    }

    public function nwpwp_add_toolbar_items($admin_bar) {
        $admin_bar->add_menu( array(
          'id'    => 'newswire-publisher',
          'title' => 'Newswire Publisher',
          'href'  => admin_url('options-general.php?page=Newswire-Publisher%2Fadmin%2Fadmin.php'),
          'meta'  => array(
              'title' => __('Newswire Publisher'),            
          ),
      ));
    }
}

RegisterAdminMenu::get_instance();