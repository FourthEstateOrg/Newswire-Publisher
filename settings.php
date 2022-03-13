<?php

if (is_admin()) {
  require_once NWPWP_PLUGIN_DIR . '/admin/admin.php';
} else {
  require_once NWPWP_PLUGIN_DIR . '/front/front.php';
}