<?php
/**
* Plugin Name:         Fourth Estate Newswire Publisher
* Plugin URI:          https://www.FourthEstate.org
* Description:         Imports news from the Fourth Estate Newswire to WordPress
* Version:             1.0.0.5
* Author:              Fourth Estate
* Author URI:          https://www.FourthEstate.org
*
* This plugin is provided in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*
* Ongoing development by: the Fourth Estate Public Benefit Corporation
* Based on:  WordPress Superdesk Publisher originally developed by SourceFabric, AdminIT, Jeffrey Paul, and Douglas Arellanes.
*
*/
namespace NewswirePublisherWordpressPlugin;
if (!function_exists('add_action')) {
  echo 'Hi there!  I\'m just a simple plugin, not much I can do when called directly.';
  exit;
}


define('NWPWP_VERSION', '1.0');

define('NWPWP_REQUIRED_WP_VERSION', '4.7');

define('NWPWP_PLUGIN', __FILE__);

define('NWPWP_PLUGIN_DIR', untrailingslashit(dirname(NWPWP_PLUGIN)));

define('NWPWP_PLUGIN_URL', untrailingslashit(plugins_url('', NWPWP_PLUGIN)));

define('NWPWP_DATABASE_VERSION', '2');

define('NWPWP_DB_TABLE_SYNC_POST', 'sync_posts');
define('NWPWP_DB_TABLE_POSTMETA', 'postmeta');
define('NWPWP_DB_TABLE_USERS', 'users');


require_once NWPWP_PLUGIN_DIR . '/settings.php';


if (!function_exists('nwpwp_database_install')) {
function nwpwp_database_install() {
  global $wpdb;
  $table_name = $wpdb->prefix . NWPWP_DB_TABLE_SYNC_POST;

  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
    post_id bigint(20) NOT NULL,
		guid text NOT NULL,
    time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta($sql);

  add_option('NWPWP_DATABASE_VERSION', NWPWP_DATABASE_VERSION);
  //add_action('NWPWP_DATABASE_VERSION', __NAMESPACE__ . '\\NWPWP_DATABASE_VERSION');
}
}
/*remove_action( 'load-update-core.php', 'wp_update_plugins' );

add_filter( 'pre_site_transient_update_plugins', create_function( '$a', "return null;" ) );*/



if (!function_exists('nwpwp_database_update')) {
function nwpwp_database_update() {
  if (get_site_option('NWPWP_DATABASE_VERSION') != NWPWP_DATABASE_VERSION) {
    nwpwp_database_install();
  }
}
}


if (!function_exists('nwpwp_saveFile')) {
function nwpwp_saveFile($from, $to) {

    $response = file_get_contents($from);
    file_put_contents($to, $response);



  }
}


if (!function_exists('nwpwp_generatePassword')) {
function nwpwp_generatePassword() {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ%./\@';
  $charactersLength = strlen($characters);
  $password = '';
  for ($i = 0; $i < 8; $i++) {
    $password .= $characters[rand(0, $charactersLength - 1)];
  }
  $password = $password . strtotime("now");
  return $password;
}
}


if (!function_exists('nwpwp_saveAttachment')) {
function nwpwp_saveAttachment($picture, $post_ID, $caption, $alt) {
  $filenameQ = explode("/", $picture['renditions']['original']['media']);
  $filename = $filenameQ[count($filenameQ) - 1];

  $uploadDir = wp_upload_dir();
  nwpwp_saveFile($picture['renditions']['original']['href'], $uploadDir['path'] . "/" . $filename);

  $attachment = array(
      'guid' => $uploadDir['url'] . '/' . basename($filename),
      'post_mime_type' => $picture['mimetype'],
      'post_title' => $caption,
      'post_content' => '',
      'post_excerpt' => $caption,
      'post_status' => 'inherit'
  );

  $attach_id = wp_insert_attachment($attachment, date("Y") . "/" . date("m") . "/" . $filename, $post_ID);

  require_once( ABSPATH . 'wp-admin/includes/image.php' );

  $attach_data = wp_generate_attachment_metadata($attach_id, $uploadDir['path'] . "/" . $filename);

  wp_update_attachment_metadata($attach_id, $attach_data);
  set_post_thumbnail($post_ID, $attach_id);

  update_post_meta($attach_id, '_wp_attachment_image_alt', wp_slash($alt));
}
}


if (!function_exists('nwpwp_savePicture')) {
function nwpwp_savePicture($localPath, $postId, $oldSrc, $associations, $alt) {
  $filenameQ = explode("/", $localPath);
  $filename = $filenameQ[count($filenameQ) - 1];
  $uploadDir = wp_upload_dir();

  $name = null;
  $mimeType = null;
  foreach ($associations as $key => $value) {
    if (isset($value['renditions'])) {
      foreach ($value['renditions'] as $value2) {
        if ($value2['href'] === $oldSrc) {
          $name = $key;
          $mimeType = $value2['mimetype'];
          break 2;
        }
      }
    }
  }
  if ($name == null) {
    $caption = wp_strip_all_tags($alt);
    $alt = wp_strip_all_tags($alt);
  } else {
    $caption = nwpwp_generate_caption_image($associations[$name]);
    $alt = (!empty($associations[$name]['body_text'])) ? wp_strip_all_tags($associations[$name]['body_text']) : '';
  }
  $attachment = array(
      'guid' => $localPath,
      'post_mime_type' => $mimeType == null ? mime_content_type($uploadDir['path'] . "/" . $filename) : $mimeType,
      'post_title' => $caption,
      'post_content' => '',
      'post_excerpt' => $caption,
      'post_status' => 'inherit'
  );


  $attach_id = wp_insert_attachment($attachment, date("Y") . "/" . date("m") . "/" . $filename, $postId);

  require_once( ABSPATH . 'wp-admin/includes/image.php' );

  $attach_data = wp_generate_attachment_metadata($attach_id, $uploadDir['path'] . "/" . $filename);

  wp_update_attachment_metadata($attach_id, $attach_data);
  set_post_thumbnail($postId, $attach_id);

  update_post_meta($attach_id, '_wp_attachment_image_alt', wp_slash($alt));
}
}


if (!function_exists('nwpwp_custom_wpkses_post_tags')) {
function nwpwp_custom_wpkses_post_tags($tags, $context) {
  if ('post' === $context) {
    $tags['iframe'] = array(
        'style' => true,
        'src' => true,
        'height' => true,
        'width' => true,
        'frameborder' => true,
        'allowfullscreen' => true,
    );
  }
  return $tags;
}
}


if (!function_exists('nwpwp_generate_caption_image')) {
function nwpwp_generate_caption_image($media) {
  $caption = '';
  $settings = get_option('newswire_settings');
  if (!empty($media['description_text'])) {
    $caption .= wp_strip_all_tags($media['description_text']);
  }

  if (!empty($media['byline'])) {
    if (!empty($caption)) {
      $caption .= ' ';
    }

    $caption .= (empty($settings['separator-caption-image']) ? ':' : $settings['separator-caption-image']) . ' ' . wp_strip_all_tags($media['byline']);
  }

  if (!empty($media['copyrightholder']) && $settings['copyrightholder-image'] == 'on') {
    $caption .= ' / ' . wp_strip_all_tags($media['copyrightholder']);
  }

  if (!empty($media['copyrightnotice']) && $settings['copyrightnotice-image'] == 'on') {
    $caption .= ' ' . wp_strip_all_tags($media['copyrightnotice']);
  }

  return $caption;
}
}


if (!function_exists('nwpwp_embed_src')) {
function nwpwp_embed_src($src) {
  $uploadDir = wp_upload_dir();
  $filename = sha1($src);

  nwpwp_saveFile($src, $uploadDir['path'] . "/" . $filename);
  return $uploadDir['url'] . "/" . $filename;
}
}

class Nwpwpimage {
  public $src, $alt, $oldSrc;
  public function __construct(array $attrs, $oldSrc) {
    $this->src = $attrs['src'];
    $this->alt = isset($attrs['alt']) ? $attrs['alt'] : '';
    $this->oldSrc = $oldSrc;
    nwpwp_stripQuotes($this->src);
    nwpwp_stripQuotes($this->alt);
  }

}


if (!function_exists('nwpwp_stripQuotes')) {
function nwpwp_stripQuotes(&$value) {
  $value = mb_substr($value, 1, mb_strlen($value) - 2);
}
}


if (!function_exists('nwpwp_embed_images')) {
function nwpwp_embed_images($html, &$image) {
  $result = array();
  preg_match_all('/<img[^>]+>/i', $html, $result);
  if (count($result) > 0) {
    $img = array();
    foreach ($result as $row) {
      if (count($row) > 0) {
        foreach ($row as $img_tag) {
          preg_match_all('/(src|title|alt)=("[^"]*")/i', $img_tag, $img[$img_tag]);
        }
      }
    }

    if (count($img) > 0) {
      foreach ($img as $htmlTag => $src) {
        $attrs = array();
        if (isset($src[1], $src[2])) {
          $oldSrc = '';
          foreach ($src[1] as $key => $attr) {
            $value = $src[2][$key];
            if ($attr === "src") {
              nwpwp_stripQuotes($value);
              $oldSrc = $value;
              $value = '"' . nwpwp_embed_src($value) . '"';
            }

            $attrs[$attr] = $value;
          }

          if ($image == null) {
            $image = new Nwpwpimage($attrs, $oldSrc);
          }

          $newHtmlTag = "<img";
          foreach ($attrs as $attrName => $attrValue) {
            $newHtmlTag .= " " . $attrName . "=" . $attrValue;
          }
          $newHtmlTag .= ">";
          $html = str_replace($htmlTag, $newHtmlTag, $html);
        }
      }
    }
  }

  return $html;
}
}

//add_filter('wp_kses_allowed_html', 'nwpwp_custom_wpkses_post_tags', 10, 2);
add_action('wp_kses_allowed_html', __NAMESPACE__ . '\\nwpwp_custom_wpkses_post_tags', 10, 2);

wp_oembed_add_provider('#http://(www\.)?youtube\.com/watch.*#i', 'http://www.youtube.com/oembed', true);

register_activation_hook(__FILE__, __NAMESPACE__ . '\\nwpwp_database_install');

add_action('plugins_loaded', __NAMESPACE__ . '\\nwpwp_database_update');

add_action('rest_api_init', function ()
{
  register_rest_route( 'autoloadapi', 'data',array(
  'methods' => 'POST',
  'callback' => __NAMESPACE__ . '\\nwpwp_autoload_data'
  ));  // mandatory check done

});


/*** adding settings link to plugins page **/
// add menu links to the plugin entry in the plugins menu
function plugin_add_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=fourth-estate-newswire-publisher/admin/admin.php">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );

/**********************************************************************/



function nwpwp_autoload_data($request)
{

  $json =  file_get_contents( 'php://input' );

  //$json = file_get_contents('php://input');
  //$json = file_get_contents('./log/newswire.txt');

  file_put_contents(NWPWP_PLUGIN_DIR.'/log/newswire.txt', $json . "\n\n", FILE_APPEND);

  $obj = json_decode($json, true);
  global $wpdb;
  $content = "";
  if ($obj['type'] == 'text') {

    $settings = get_option('newswire_settings');

    if ($obj['pubstatus'] == 'usable')
    {
      if (isset($obj['evolvedfrom'])) {
        $guid = wp_strip_all_tags($obj['evolvedfrom']);
      } else {
        $guid = wp_strip_all_tags($obj['guid']);
      }

      $sync = $wpdb->get_row("SELECT post_id FROM " . $wpdb->prefix . NWPWP_DB_TABLE_SYNC_POST . " WHERE guid = '" . $guid . "'");

      $append = false;
      $prepend = false;
      $updateText = '';
      if($sync && $settings['update-log-option'] == 'on'){
          $updateText = $settings['update-log-text'] . ' '. date( $settings['update-log-date-format']). '.';
          if($settings['update-log-position'] == 'on'){
              $prepend = true;
          }else{
              $append = true;
          }
      }

      if (!empty($obj['located'])) {
        if($settings['location-modifier'] == 'all-caps'){
          $obj['located'] = mb_strtoupper($obj['located']);
        }
        $content .= '<p>';
        if($prepend){
          $content .= $updateText .'<br>';
        }
        $content .= wp_strip_all_tags($obj['located']) . $settings['separator-located'];
        $content .= mb_substr($obj['body_html'], mb_strpos($obj['body_html'], '>') + 1, mb_strlen($obj['body_html']));
      } else {
        $content .= '<p>';
        if($prepend){
          $content .= $updateText .'<br>';
        }
        $content .= mb_substr($obj['body_html'], mb_strpos($obj['body_html'], '>') + 1, mb_strlen($obj['body_html']));
      }

      /* if ($settings['display-copyright'] == "on" && isset($obj['associations']['featuremedia']['copyrightnotice'])) {
        $content.= "<p>" . wp_strip_all_tags($obj['associations']['featuremedia']['copyrightnotice']) . "</p>";
        } */

      if (!empty($obj['ednote'])) {
        $content .= "<p>Editors Note: " . wp_strip_all_tags($obj['ednote']) . "</p>";
      }

      if($append){
         $content .= "<p>".$updateText."</p>";
      }

      if ($settings['import-keywords'] && $settings['import-keywords'] == 'on') {
        if (isset($obj['keywords']) && count($obj['keywords']) > 0) {
          foreach ($obj['keywords'] as $keyword) {
            $taxonomyTag[] = wp_strip_all_tags($keyword);
          }
        }
      }

      if ($settings['convert-slugline'] && $settings['convert-slugline'] == 'on') {
        if (isset($obj['slugline']) && !empty($obj['slugline'])) {
          $ignoreKeywords = explode(',', $settings['slugline-ignored']);
          $tmpKeywords = explode($settings['slugline-separator'], $obj['slugline']);

          foreach ($tmpKeywords as $word) {
            if (!in_array($word, $ignoreKeywords)) {
              $taxonomyTag[] = $word;
            }
          }
        }
      }

      foreach ($obj['subject'] as $subject) {
        if ($settings['subject-type'] == 'tags') {
          $taxonomyTag[] = wp_strip_all_tags($subject['name']);
        } elseif ($settings['subject-type'] == 'categories') {
          $categoryExist = $wpdb->get_row("SELECT terms.term_id, term_taxonomy.term_taxonomy_id FROM " . $wpdb->prefix . "terms terms JOIN " . $wpdb->prefix . "term_taxonomy term_taxonomy ON term_taxonomy.term_id = terms.term_id WHERE term_taxonomy.taxonomy = 'category' AND terms.name = '" . wp_strip_all_tags($subject['name']) . "'");

          if ($categoryExist) {
            $taxonomyCategory[] = $categoryExist->term_taxonomy_id;
          } else {
            $category_id = wp_insert_term(wp_strip_all_tags($subject['name']), 'category');
            $taxonomyCategory[] = $category_id['term_taxonomy_id'];
          }
        }
      }

      if ($settings['convert-services'] == 'on') {
        foreach ($obj['service'] as $service) {
          $categoryExist = $wpdb->get_row("SELECT terms.term_id, term_taxonomy.term_taxonomy_id FROM " . $wpdb->prefix . "terms terms JOIN " . $wpdb->prefix . "term_taxonomy term_taxonomy ON term_taxonomy.term_id = terms.term_id WHERE term_taxonomy.taxonomy = 'category' AND terms.name = '" . wp_strip_all_tags($service['name']) . "'");

          if ($categoryExist) {
            $taxonomyCategory[] = $categoryExist->term_taxonomy_id;
          } else {
            $category_id = wp_insert_term(wp_strip_all_tags($service['name']), 'category');
            if ( !is_wp_error($category_id) ) {
              $taxonomyCategory[] = $category_id['term_taxonomy_id'];
            }

          }
        }
      }

      if ($taxonomyCategory && !empty($taxonomyCategory)) {
        $category = $taxonomyCategory;
      } else {
        $category = $settings['category'];
      }

      if ($settings['author-byline'] && $settings['author-byline'] == 'on') {
        $author_name = $obj['byline'];
        if (!empty($settings['byline-words'])) {
          $replaceWords = explode(',', $settings['byline-words']);
          foreach ($replaceWords as $value) {
            $author_name = str_replace(trim($value) . " ", "", $author_name);
          }
        }

        $authorExist = $wpdb->get_row("SELECT ID user_id FROM " . $wpdb->prefix . NWPWP_DB_TABLE_USERS . " WHERE display_name = '" . wp_strip_all_tags($author_name) . "'");

        if (!$authorExist) {
          $table_name = $wpdb->prefix . NWPWP_DB_TABLE_USERS;

          $userArray = array(
              'user_login' => strtolower(str_replace(" ", "-", $author_name)),
              'user_pass' => nwpwp_generatePassword(),
              'display_name' => wp_strip_all_tags($author_name)
          );

          $author_id = wp_insert_user($userArray);
        } else {
          $author_id = $authorExist->user_id;
        }
      } else if ($settings['author-byline'] == 'off') {
        $author_id = $settings['author'];
      } else {
        $author_id = 0;
      }

      $image = null;
      if (isset($settings['download-images']) && $settings['download-images'] === 'on') {
        $content = nwpwp_embed_images($content, $image);
      }



      if ($sync) {
        $post_ID = $sync->post_id;
        $edit_post = array(
            'ID' => $sync->post_id,
            'post_title' => wp_strip_all_tags($obj['headline']),
            'post_name' => wp_strip_all_tags($obj['headline']),
            'post_content' => $content,
            'post_author' => (int) $author_id,
            'post_content_filtered' => $content,
            'post_category' => $category
        );

        if (isset($settings['post-formats'], $settings['post-formats-table']) and ! empty($obj['profile']) and $settings['post-formats'] == 'on') {
          if (isset($settings['post-formats-table'][$obj['profile']])) {
            set_post_format($post_ID, $settings['post-formats-table'][$obj['profile']]);
          }
        }

        wp_update_post($edit_post);

        $attachmentExist = get_post_thumbnail_id($post_ID);

        if ($attachmentExist) {
          wp_delete_attachment($attachmentExist);
        }

        if ($taxonomyTag && !empty($taxonomyTag)) {
          wp_set_post_tags($post_ID, $taxonomyTag);
        }

        $wpdb->insert(
                $wpdb->prefix . NWPWP_DB_TABLE_SYNC_POST, array(
            'post_id' => $post_ID,
            'guid' => wp_strip_all_tags($obj['guid']),
            'time' => current_time('mysql')
                )
        );

        if ($settings['priority_threshhold'] && $settings['priority_threshhold'] >= $obj['priority']) {
          stick_post($post_ID);
        } else {
          unstick_post($post_ID);
        }
      } else {
        $postarr = array(
            'post_title' => wp_strip_all_tags($obj['headline']),
            'post_name' => wp_strip_all_tags($obj['headline']),
            'post_content' => $content,
            'post_content_filtered' => $content,
            'post_author' => (int) $author_id,
            'post_status' => $settings['status'],
            'post_category' => $category,
        );

        $post_ID = wp_insert_post($postarr, true);


        $cstmupdate_post = array( 'ID'=> $id, 'post_status'   =>  $settings['status'] );

        wp_update_post($cstmupdate_post);







        if (isset($settings['post-formats'], $settings['post-formats-table']) and ! empty($obj['profile']) and $settings['post-formats'] == 'on') {
          if (isset($settings['post-formats-table'][$obj['profile']])) {
            set_post_format($post_ID, $settings['post-formats-table'][$obj['profile']]);
          }
        }

        if ($taxonomyTag && !empty($taxonomyTag)) {
          wp_set_post_tags($post_ID, $taxonomyTag);
        }

        $table_name = $wpdb->prefix . NWPWP_DB_TABLE_SYNC_POST;

        $wpdb->insert(
                $table_name, array(
            'post_id' => $post_ID,
            'guid' => wp_strip_all_tags($obj['guid']),
            'time' => current_time('mysql')
                )
        );

        if ($settings['priority_threshhold'] && $settings['priority_threshhold'] >= $obj['priority']) {
          stick_post($post_ID);
        }
      }

      /* save featured media */
      if ($obj['associations']['featuremedia'] && $obj['associations']['featuremedia']['type'] == 'picture') {
        $filenameQ = explode("/", $obj['associations']['featuremedia']['renditions']['original']['media']);
        $filename = $filenameQ[count($filenameQ) - 1];

        $fileExist = $wpdb->get_row("SELECT meta_id, post_id FROM " . $wpdb->prefix . "postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE '%" . wp_strip_all_tags($filename) . "'");

        if ($fileExist) {
          set_post_thumbnail($post_ID, $fileExist->post_id);
        } else {
          $caption = nwpwp_generate_caption_image($obj['associations']['featuremedia']);
          $alt = (!empty($obj['associations']['featuremedia']['body_text'])) ? wp_strip_all_tags($obj['associations']['featuremedia']['body_text']) : '';
          nwpwp_saveAttachment($obj['associations']['featuremedia'], $post_ID, $caption, $alt);
        }
      } else if ($image !== null) {
        $filenameQ = explode("/", $image->src);
        $filename = $filenameQ[count($filenameQ) - 1];

        $fileExist = $wpdb->get_row("SELECT meta_id, post_id FROM " . $wpdb->prefix . "postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE '%" . wp_strip_all_tags($filename) . "'");

        if ($fileExist) {
          set_post_thumbnail($post_ID, $fileExist->post_id);
        } else {
          nwpwp_savePicture($image->src, $post_ID, $image->oldSrc, isset($obj['associations']) ? $obj['associations'] : array(), $image->alt);
        }
      }
    }
    elseif ($obj['pubstatus'] == 'canceled')
    {
      /* remove article */
      $guid = wp_strip_all_tags($obj['guid']);

      $sync = $wpdb->get_row("SELECT post_id FROM " . $wpdb->prefix . NWPWP_DB_TABLE_SYNC_POST . " WHERE guid = '" . $guid . "'");

      if ($sync) {

        $edit_post = array(
            'ID' => $sync->post_id,
            'post_status' => 'draft'
        );

        wp_update_post($edit_post);
      }
    }
  }








}

?>
