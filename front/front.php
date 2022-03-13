<?php

add_shortcode('nwpwp_mvp', 'nwpwp_content');
function nwpwp_content() {

  $settings = get_option('newswire_settings');

  $this_url         = $settings['url'] . "/oauth/token";
  $this_client_id   = $settings['client_id'];
  $this_grant_type  = 'password';
  $this_username    = $settings['username'];
  $this_password    = $settings['password'];

  $fields = array('client_id'=>$this_client_id, 'grant_type'=>$this_grant_type, 'username'=>$this_username, 'password'=>$this_password);
  $response = wp_remote_post($this_url, array(
        'method' => 'POST',
        'headers' => array(),
        'httpversion' => '1.0',
        'sslverify' => false,
        'body' => $fields
    ));


  $config = json_decode($response);


  $newurl = $settings['url'] . "/packages?start_date=" . date("Y-m-d", strtotime('-1 days'));
  $headers = array('Authorization: ' . $config->token_type . ' ' . $config->access_token);

  $responsenew = wp_remote_post($newurl, array(
        'method' => 'POST',
        'headers' => $headers,
        'httpversion' => '1.0',
        'sslverify' => false,
        'body' => array()
    ));



  if ($responsenew) {
    require_once NWPWP_PLUGIN_DIR . '/front/wrapper.php';
    $content = nwpwp_view($responsenew);
  }

  return $content;
}
