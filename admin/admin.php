<?php


add_action( 'admin_init', 'do_something_152677' );
function do_something_152677 ()
{
    // Global object containing current admin page
    global $pagenow;
    if ( ('options-general.php' == $pagenow ) )
    {
      ?>
          <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
      <?php
    }
}

function plugin_add_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=fourth-estate-newswire-publisher/admin/admin.php">' . __( 'Settings' ) . '</a>';

    //array_push( $links, $settings_link );
    array_unshift($links, $settings_link);
    array_unique($links);
  	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );



add_action('admin_menu',  __NAMESPACE__ . '\\nwpwp_newswire_admin_actions');


add_filter('plugin_action_links_newswire/newswirePublisher.php',  __NAMESPACE__ . '\\nwpwp_plugin_action_links');

//var_dump(get_post_format_strings());die();

if (!function_exists('nwpwp_newswire_admin_actions')) {
function nwpwp_newswire_admin_actions() {
  add_submenu_page(
    'edit.php?post_type='. \NewswirePublisherWordpressPlugin\Inc\RegisterPostType::get_registered_post_type(),
    'Settings',
    'Settings',
    'manage_options',
    __FILE__,
    'nwpwp_newswire_admin',
  );
}
}

if (!function_exists('nwpwp_plugin_action_links')) {
function nwpwp_plugin_action_links($links) {
  $links[] = '<a href="' . esc_url(nwpwp_get_page_url()) . '">Settings</a>';
  return $links;
}
}

if (!function_exists('nwpwp_get_page_url')) {
function nwpwp_get_page_url() {

  $args = array('page' => 'newswire/admin/admin');

  $url = add_query_arg($args, admin_url('options-general.php'));

  return $url;
}
}

if (!function_exists('nwpwp_cstm_sanitize_array')) {
    function nwpwp_cstm_sanitize_array( $array )
    {
        foreach ( $array as $key => &$value )
        {
            if ( is_array( $value ) ) {
                $value = cwebco_cstm_sanitize_array($value);
            }
            else {
                $value = sanitize_text_field( $value );
            }
        }

    return $array;
    }
}


if (!function_exists('nwpwp_newswire_admin')) {
function nwpwp_newswire_admin()
{
  if(current_user_can('administrator'))
  {
    $post_formats = get_post_format_strings();

    if (isset($_POST['url'])) {
      $resultArray = array();
      if (isset($_POST['post-formats-value'], $_POST['post-formats-input']))
      {
        $post_formats_value = $_POST['post-formats-value'];
        $post_formats_value = nwpwp_cstm_sanitize_array( $post_formats_value ); // array sanitize
        foreach ($post_formats_value as $key => $value) {
          if (!isset($_POST['post-formats-input'][$key])) {
            continue;
          }
          if (isset($post_formats[$value])) {
            $inputValue = sanitize_text_field($_POST['post-formats-input'][$key]);
            if (!isset($resultArray[$inputValue]) && !empty($inputValue))
              $resultArray[$inputValue] = $value;
          }
        }
      }

      $settings = array(
          'url'                     => sanitize_text_field($_POST['url']),
          'username'                => sanitize_text_field($_POST['username']),
          'password'                => sanitize_text_field($_POST['password']),
          'client_id'               => sanitize_text_field($_POST['client_id']),
          'status'                  => sanitize_text_field($_POST['status']),
          'author'                  => sanitize_text_field($_POST['author']),
          'author-byline'           => sanitize_text_field($_POST['author-byline']),
          'byline-words'            => sanitize_text_field($_POST['byline-words']),
          'display-copyright'       => sanitize_text_field($_POST['display-copyright']),
          'import-keywords'         => sanitize_text_field($_POST['import-keywords']),
          'convert-services'        => sanitize_text_field($_POST['convert-services']),
          'subject-type'            => sanitize_text_field($_POST['subject-type']),
          'category'                => nwpwp_cstm_sanitize_array( $_POST['category'] ),  // array sanitize
          'separator-caption-image' => sanitize_text_field($_POST['separator-caption-image']),
          'copyrightholder-image'   => sanitize_text_field($_POST['copyrightholder-image']),
          'copyrightnotice-image'   => sanitize_text_field($_POST['copyrightnotice-image']),
          'separator-located'       => sanitize_text_field($_POST['separator-located']),
          'convert-slugline'        => sanitize_text_field($_POST['convert-slugline']),
          'slugline-separator'      => sanitize_text_field($_POST['slugline-separator']),
          'slugline-ignored'        => sanitize_text_field($_POST['slugline-ignored']),
          'priority_threshhold'     => sanitize_text_field($_POST['priority_threshhold']),
          'download-images'         => sanitize_text_field($_POST['download-images']),
          'post-formats'            => sanitize_text_field($_POST['download-images']),
          'post-formats-table'      => $resultArray,
          'location-modifier'       => sanitize_text_field($_POST['location-modifier']),
          'update-log-option'       => sanitize_text_field($_POST['update-log-option']),
          'update-log-date-format'  => sanitize_text_field($_POST['update-log-date-format']),
          'update-log-text'         => sanitize_text_field($_POST['update-log-text']),
          'update-log-position'     => sanitize_text_field($_POST['update-log-position']),
          'places-meta-tag-link'    => sanitize_text_field($_POST['places-meta-tag-link']),
      );
      update_option('newswire_settings', $settings);
    } else if (get_option('newswire_settings')) {
      $settings = get_option('newswire_settings');
    } else {
      $settings = array(
          'url' => '',
          'client_id' => '',
          'username' => '',
          'password' => '',
          'status' => 'publish',
          'author' => '',
          'author-byline' => '',
          'byline-words' => '',
          'display-copyright' => '',
          'import-keywords' => '',
          'convert-services' => '',
          'subject-type' => '',
          'category' => '',
          'separator-caption-image' => '',
          'copyrightholder-image' => '',
          'copyrightnotice-image' => '',
          'separator-located' => '',
          'convert-slugline' => '',
          'slugline-separator' => '',
          'slugline-ignored' => '',
          'priority_threshhold' => '',
          'download-images' => '',
          'post-formats' => '',
          'post-formats-table' => array(),
          'location-modifier' => 'standard',
          'update-log-option' => 'off',
          'update-log-date-format' => 'Y-m-d H:i',
          'update-log-text' => 'This article was updated at',
          'update-log-position' => 'off'
      );
    }
    $statuses = array(
        'publish' => 'Published',
        'pending' => 'Pending Review',
        'draft' => 'Draft'
    );

    $authors = array();

    $categories = array();

    $args = array(
        'hide_empty' => 0,
    );
    $all_categories = get_categories($args);

    foreach ($all_categories as $category) {
      $categories[$category->cat_ID] = $category->cat_name;
    }

    $all_users = get_users();
    foreach ($all_users as $user) {
      $authors[$user->ID] = $user->data->display_name;
    }


  if (!function_exists('nwpwp_make_table_row')) {
    function nwpwp_make_table_row($format, $text_value, $formats) {
      $new_element = '<tr><td><input type="text" name="post-formats-input[]" onkeyup="debounce(validateInput, 250);" class="regular-text" value="' . esc_html($text_value) . '" /></td><td><select name="post-formats-value[]">';
      foreach ($formats as $key => $value) {
        $new_element .= '<option ' . ($key === $format ? 'selected="selected" ' : '') . 'value="' . esc_html($key) . '">' . esc_html($value) . '</option>';
      }
      $new_element .= '</select></td><td><a href="#" onclick="removeThisRow(this); return false;">Delete</a></td></tr>';
      return $new_element;
    }
  }

    ?>
    <style>
      .row {
          width: 100%;
          display: flex;
      }
      .col-md-8 {
          width: 75%;
      }
      .col-md-4 {
          width: 25%;
      }
      .col-md-12 {
          width: 100%;
      }
    </style>
    <div class="wrap">
        <h2>Fourth Estate Newswire Publisher</h2>




        <div class="container">
          <div class="row">
            <!-- right section start -->
            <div class="col-md-8">
              <form action="" method="POST">
                <input type="hidden" name="url" value="<?php echo get_site_url(); ?>/wp-json/autoloadapi/data">
                  <table class="form-table">
                      <tbody>
                          <tr>
                              <th>HTTP PUSH Endpoint (autoload) </th>
                              <td><?php echo get_site_url(); ?>/wp-json/autoloadapi/data</td>
                          </tr>
                          <tr>
                              <th scope="row">
              Default status of ingested articles
                              </th>
                              <td>
                                  <fieldset>
                                      <?php
                                      foreach ($statuses as $key => $value) {
                                        ?>
                                        <label for="status-<?php echo($key); ?>">
                                            <input type="radio" name="status" id="status-<?php echo($key); ?>" value="<?php echo($key); ?>"<?php
                                            if ($key == $settings['status']) {
                                              echo(' checked');
                                            }
                                            ?>> <?php echo($value); ?>
                                        </label>
                                        <br>
                                        <?php
                                      }
                                      ?>
                                  </fieldset>
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  <label for="author">Default author</label>
                              </th>
                              <td>
                                  <select name="author" id="author">
                                      <?php
                                      foreach ($authors as $key => $value) {
                                        ?>
                                        <option value="<?php echo($key); ?>"<?php
                                        if ($key == $settings['author']) {
                                          echo(' selected');
                                        }
                                        ?>><?php echo($value); ?></option>
                                                <?php
                                              }
                                              ?>
                                  </select>
                                  <br /><br />
                                  <label>
                                      <input type="checkbox" name="author-byline" <?php echo ($settings['author-byline']) ? "checked" : ""; ?>> Show byline in posts
                                  </label>
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  <label for="byline-words">Choose words to replace in the byline</label>
                              </th>
                              <td>
                                  <input type="text" name="byline-words" id="byline-words" class="regular-text" value="<?php echo($settings['byline-words']); ?>">
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  Copyright Display
                              </th>
                              <td>
                                  <fieldset>
                                      <label for="status-off">
                                          <input type="radio" name="display-copyright" id="status-off" value="off"<?php
                                          if ($settings['display-copyright'] == 'off') {
                                            echo(' checked');
                                          }
                                          ?>> off
                                      </label>
                                      <br>
                                      <label for="status-on">
                                          <input type="radio" name="display-copyright" id="status-on" value="on"<?php
                                          if ($settings['display-copyright'] == 'on') {
                                            echo(' checked');
                                          }
                                          ?>> on
                                      </label>
                                  </fieldset>
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  Import keywords as WP tags
                              </th>
                              <td>
                                  <fieldset>
                                      <label for="off">
                                          <input type="radio" name="import-keywords" id="off" value="off"<?php
                                          if ($settings['import-keywords'] == 'off') {
                                            echo(' checked');
                                          }
                                          ?>> off
                                      </label>
                                      <br>
                                      <label for="on">
                                          <input type="radio" name="import-keywords" id="on" value="on"<?php
                                          if ($settings['import-keywords'] == 'on') {
                                            echo(' checked');
                                          }
                                          ?>> on
                                      </label>
                                  </fieldset>
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  Import Newswire categories as WordPress categories
                              </th>
                              <td>
                                  <fieldset>
                                      <label for="off">
                                          <input type="radio" name="convert-services" id="off" value="off"<?php
                                          if ($settings['convert-services'] == 'off') {
                                            echo(' checked');
                                          }
                                          ?>> off
                                      </label>
                                      <br>
                                      <label for="on">
                                          <input type="radio" name="convert-services" id="on" value="on"<?php
                                          if ($settings['convert-services'] == 'on') {
                                            echo(' checked');
                                          }
                                          ?>> on
                                      </label>
                                  </fieldset>
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  Import IPTC MediatTopics as
                              </th>
                              <td>
                                  <fieldset>
                                      <label for="tags">
                                          <input type="radio" name="subject-type" id="tags" value="tags"<?php
                                          if ($settings['subject-type'] == 'tags' || !$settings['subject-type'] || $settings['subject-type'] == null) {
                                            echo(' checked');
                                          }
                                          ?>> WordPress tags
                                      </label>
                                      <br>
                                      <label for="categories">
                                          <input type="radio" name="subject-type" id="categories" value="categories"<?php
                                          if ($settings['subject-type'] == 'categories') {
                                            echo(' checked');
                                          }
                                          ?>> WordPress categories
                                      </label>
                                  </fieldset>
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  <label for="category">Default category</label>
                              </th>
                              <td>
                                  <select name="category[]" id="category" multiple>
                                      <?php
                                      foreach ($categories as $key => $value) {
                                        ?>
                                        <option value="<?php echo($key); ?>"<?php
                                        if (isset($settings['category']) && is_array($settings['category'])) {
                                          if (in_array($key, $settings['category'])) {
                                            echo(' selected');
                                          }
                                        }
                                        ?>><?php echo($value); ?></option>
                                                <?php
                                              }
                                              ?>
                                  </select>
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  <label for="separator-caption-image">Separator for caption image</label>
                              </th>
                              <td>
                                  <input type="text" name="separator-caption-image" id="separator-caption-image" class="regular-text" value="<?php echo($settings['separator-caption-image']); ?>">
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
              Display image copyright holder in the caption
                              </th>
                              <td>
                                  <fieldset>
                                      <label for="copyrightholder-image-off">
                                          <input type="radio" name="copyrightholder-image" id="copyrightholder-image-off" value="off"<?php
                                          if ($settings['copyrightholder-image'] == 'off') {
                                            echo(' checked');
                                          }
                                          ?>> off
                                      </label>
                                      <br>
                                      <label for="copyrightholder-image-on">
                                          <input type="radio" name="copyrightholder-image" id="copyrightholder-image-on" value="on"<?php
                                          if ($settings['copyrightholder-image'] == 'on') {
                                            echo(' checked');
                                          }
                                          ?>> on
                                      </label>
                                  </fieldset>
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
              Display the image copyright notice in the caption
                              </th>
                              <td>
                                  <fieldset>
                                      <label for="copyrightnotice-image-off">
                                          <input type="radio" name="copyrightnotice-image" id="copyrightnotice-image-off" value="off"<?php
                                          if ($settings['copyrightnotice-image'] == 'off') {
                                            echo(' checked');
                                          }
                                          ?>> off
                                      </label>
                                      <br>
                                      <label for="copyrightnotice-image-on">
                                          <input type="radio" name="copyrightnotice-image" id="copyrightnotice-image-on" value="on"<?php
                                          if ($settings['copyrightnotice-image'] == 'on') {
                                            echo(' checked');
                                          }
                                          ?>> on
                                      </label>
                                  </fieldset>
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  <label for="separator-located">Separator between the location and name</label>
                              </th>
                              <td>
                                  <input type="text" name="separator-located" id="separator-located" class="regular-text" value="<?php echo($settings['separator-located']); ?>">
                              </td>
                          </tr>
                           <tr>
                                <th scope="row">
                                               Display location as
                             </th>
                             <td>
                             <fieldset>
                                      <label for="location-modifier-all-caps">
                                          <input type="radio" name="location-modifier" id="location-modifier-all-caps" value="all-caps"<?php
                                          if ($settings['location-modifier'] == 'all-caps') {
                                            echo(' checked');
                                          }
                                          ?>> ALL CAPS
                                      </label>
                                      <br>
                                      <label for="location-modifier-standard">
                                          <input type="radio" name="location-modifier" id="location-modifier-standard" value="standard"<?php
                                          if (!isset($settings['location-modifier']) || $settings['location-modifier'] != 'all-caps') {
                                            echo(' checked');
                                          }
                                          ?>> Standard case
                                      </label>
                                  </fieldset>
                               </td>
                            </tr>
                          <tr>
                              <th scope="row">
                                  Convert slugline keywords into WP tags
                              </th>
                              <td>
                                  <fieldset>
                                      <label for="convert-slugline-off">
                                          <input type="radio" name="convert-slugline" id="convert-slugline-off" value="off"<?php
                                          if ($settings['convert-slugline'] == 'off') {
                                            echo(' checked');
                                          }
                                          ?>> off
                                      </label>
                                      <br>
                                      <label for="convert-slugline-on">
                                          <input type="radio" name="convert-slugline" id="convert-slugline-on" value="on"<?php
                                          if ($settings['convert-slugline'] == 'on') {
                                            echo(' checked');
                                          }
                                          ?>> on
                                      </label>
                                  </fieldset>
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  <label for="slugline-separator">Slugline value separator</label>
                              </th>
                              <td>
                                  <input type="text" name="slugline-separator" id="slugline-separator" class="regular-text" value="<?php echo($settings['slugline-separator']); ?>">
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  <label for="slugline-ignored">Keywords to be ignored</label>
                              </th>
                              <td>
                                  <input type="text" name="slugline-ignored" id="slugline-ignored" class="regular-text" value="<?php echo($settings['slugline-ignored']); ?>">
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  <label for="priority_threshhold">Priority threshhold of </label>
                              </th>
                              <td>
                                  <select name="priority_threshhold" id="priority_threshhold">
                                      <option value="0"> </option>
                                      <?php
                                      for ($i = 1; $i <= 6; $i++) {
                                        ?>
                                        <option value="<?php echo($i); ?>"<?php
                                        if (isset($settings['priority_threshhold']) && $settings['priority_threshhold'] == $i) {
                                          echo(' selected');
                                        }
                                        ?>><?php echo($i); ?></option>
                                                <?php
                                              }
                                              ?>
                                  </select>
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  Download images from the wire
                              </th>
                              <td>
                                  <fieldset>
                                      <label for="download-images-off">
                                          <input type="radio" name="download-images" id="download-images-off" value="off"<?php
                                          if ($settings['download-images'] == 'off') {
                                            echo(' checked');
                                          }
                                          ?>> off
                                      </label>
                                      <br>
                                      <label for="download-images-on">
                                          <input type="radio" name="download-images" id="download-images-on" value="on"<?php
                                          if ($settings['download-images'] == 'on') {
                                            echo(' checked');
                                          }
                                          ?>> on
                                      </label>
                                  </fieldset>
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  Add update logs to articles
                              </th>
                              <td>
                                  <fieldset>
                                      <label for="update-log-option-off">
                                          <input type="radio" name="update-log-option" id="update-log-option-off" value="off"<?php
                                          if (!isset($settings['update-log-option']) || $settings['update-log-option'] == 'off') {
                                            echo(' checked');
                                          }
                                          ?>> off
                                      </label>
                                      <br>
                                      <label for="update-log-option-on">
                                          <input type="radio" name="update-log-option" id="update-log-option-on" value="on"<?php
                                          if ($settings['update-log-option'] == 'on') {
                                            echo(' checked');
                                          }
                                          ?>> on
                                      </label>
                                  </fieldset>
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  Update log position in article
                              </th>
                              <td>
                                  <fieldset>
                                      <label for="update-log-position-off">
                                          <input type="radio" name="update-log-position" id="update-log-position-off" value="off"<?php
                                          if (!isset($settings['update-log-position']) || $settings['update-log-position'] == 'off') {
                                            echo(' checked');
                                          }
                                          ?>> append
                                      </label>
                                      <br>
                                      <label for="update-log-position-on">
                                          <input type="radio" name="update-log-position" id="update-log-position-on" value="on"<?php
                                          if ($settings['update-log-option'] == 'on') {
                                            echo(' checked');
                                          }
                                          ?>> prepend
                                      </label>
                                  </fieldset>
                              </td>
                          </tr>

                          <tr>
                              <th scope="row">
                                  <label for="update-log-date-format">Update log date format</label>
                              </th>
                              <td>
                                  <input type="text" name="update-log-date-format" id="update-log-date-format" class="regular-text" value="<?php echo($settings['update-log-date-format']); ?>">
                              </td>
                          </tr>
                           <tr>
                              <th scope="row">
                                  <label for="update-log-text">Update log date text</label>
                              </th>
                              <td>
                                  <input type="text" name="update-log-text" id="update-log-text" class="regular-text" value="<?php echo(!isset($settings['update-log-text']) ? 'This article was updated at' : $settings['update-log-text']); ?>">

                                  <?php
                                  echo date(!empty($settings['update-log-date-format']) ? $settings['update-log-date-format'] : 'Y-m-d H:i'); ?>.
                              </td>
                          </tr>
                          <tr>
                              <th scope="row">
                                  Match Newswire Content Profiles with WordPress Post Formats
                              </th>
                              <td>
                                  <fieldset>
                                      <label for="post-formats-off">
                                          <input type="radio" name="post-formats" id="post-formats-off" value="off"<?php
                                          if ($settings['post-formats'] == 'off') {
                                            echo(' checked');
                                          }
                                          ?>> off
                                      </label>
                                      <br>
                                      <label for="post-formats-on">
                                          <input type="radio" name="post-formats" id="post-formats-on" value="on"<?php
                                          if ($settings['post-formats'] == 'on') {
                                            echo(' checked');
                                          }
                                          ?>> on
                                      </label>
                                  </fieldset>
                              </td>
                          </tr>
                          <tr>
                              <td colspan="2">
                                  <table>
                                      <thead>
                                          <tr>
                                              <th>Newswire Content Profile Name</th>
                                              <th>WordPress Post Format</th>
                                              <th><a href="javascript:rowAddFunction();">Add row</a></th>
                                          </tr>
                                      </thead>
                                      <tbody id="post-format-tbody">
                                          <?php
                                          if (isset($settings['post-formats-table']) and is_array($settings['post-formats-table'])) {
                                            foreach ($settings['post-formats-table'] as $key => $value) {
                                              echo nwpwp_make_table_row($value, $key, $post_formats);
                                            }
                                          }
                                          ?>
                                      </tbody>
                                  </table>
                              </td>
                          </tr>
                      </tbody>
                  </table>

                  <h2>Meta Options</h2>
                  <table class="form-table">
                      <tbody>
                          <tr>
                              <th scope="row">
                                  <label for="places-meta-tag-link">Places Meta URL</label>
                              </th>
                              <td>
                                  <input type="text" name="places-meta-tag-link" id="places-meta-tag-link" class="regular-text" value="<?php echo(isset($settings['places-meta-tag-link']) ? $settings['places-meta-tag-link']: ''); ?>">
                              </td>
                          </tr>
                      </tbody>
                    </table>
                  <p class="submit">
                      <input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
                  </p>
              </form>

              <button class="button" id="migrate-all-post-to-news">Migrate All Posts to News</button>
              <button class="button" id="migrate-post-to-news">Migrate Posts to News</button>

            </div>
            <!-- right section end -->



            <!-- left section start -->
            <div class="col-md-4">


              <div class="container">

                <div class="row">
                  <div class="col-md-12">
                    <div class="cstm_textboxesinner">
                      <p><a title="Fourth Estate &reg; " href="https://www.fourthestate.org" target="_blank"><img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'assets/FourthEstate-logo-square.png'; ?> " alt="Fourth Estate &reg;" width="100%"></a></p>

                    </div>
                  </div>
                </div>

                <br>

                <div class="row">
                  <div class="col-md-12">
                    <div class="cstm_textboxesinner">
                      <p><b>Don't have a Newswire Account?</b></p>
                      <a href="https://www.fourthestate.org/services/newswire/">Request newswire access</a>
                    </div>
                  </div>
                </div>

                <br>

                <div class="row">
                  <div class="col-md-12">
                    <div class="cstm_textboxesinner">
                      <p><b>Are you a local news publisher?</b></p>
                      <a href="https://www.fourthestate.org/membership/">Become a Fourth Estate member</a>
                    </div>
                  </div>
                </div>



              </div>


            </div>
            <!-- left section end -->



          </div>
        </div>
















    </div>




  <style type="text/css">
    .cstm_textboxesinner
    {
      background-color: white;
      padding: 16px;
    }
  </style>








    <script type="text/javascript">
      var select_options = <?php echo json_encode($post_formats); ?>;
      var $ = jQuery;

      var rowAddFunction = function () {
          var element = $("#post-format-tbody");
          var new_element = '<tr>\n\
    <td><input type="text" name="post-formats-input[]" class="regular-text" />\n\
    </td><td>\n\
    <select name="post-formats-value[]">';
          $.each(select_options, function (key, value) {
              new_element += '<option value="' + key + '">' + value + '</option>';
          });
          new_element += '</select></td><td><a href="#" onclick="removeThisRow(this); return false;">Delete</a></td></tr>';
          element.append(new_element);
      };

      function debounce(func, wait, immediate) {
          console.log(func);
          var timeout;
          return function () {
              var context = this, args = arguments;
              var later = function () {
                  timeout = null;
                  if (!immediate)
                      func.apply(context, args);
              };
              var callNow = immediate && !timeout;
              clearTimeout(timeout);
              timeout = setTimeout(later, wait);
              if (callNow)
                  func.apply(context, args);
          };
      }

      var removeThisRow = function (el) {
          var element = $(el).parent()
                  .parent();
          element.remove();
      };

      var validateInput = function () {
          var el = this;
          var element = $(el);
          var text_value = element.val();
          var found = false;
          var input_elements = $("#post-format-tbody input");
          $.each(input_elements, function (_, input_element) {
              if (el !== input_element) {
                  if (text_value === $(input_element).val()) {
                      found = true;
                      return false;
                  }
              }
          });

          if (found) {
              if (!element.hasClass('spwp-input-error')) {
                  element.addClass('spwp-input-error');
              }
          } else {
              if (element.hasClass('spwp-input-error')) {
                  element.removeClass('spwp-input-error');
              }
          }

          $("#submit").prop('disabled', found);
      };

      $(document).ready(function () {
          rowAddFunction();
          var validateDebounce = debounce(validateInput, 250);
          $("#post-format-tbody input").live('keyup', validateDebounce);
      });
      window.migration_data = {
        total_posts: 0,
        total_migrated: 0,
      }
      jQuery(document.body).on('click', '#migrate-post-to-news', function() {
        window.migration_data = {
          total_posts: 0,
          total_migrated: 0,
        }
        add_loader();
        jQuery('#migration-result').remove();
        run_post_migration();
      });
      jQuery(document.body).on('click', '#migrate-all-post-to-news', function() {
        window.migration_data = {
          total_posts: 0,
          total_migrated: 0,
        }
        add_loader();
        jQuery('#migration-result').remove();
        run_post_migration('migrate_all_posts_to_news');
      });
      function add_loader() {
        jQuery('<p id="migration-loader"><img id="migration-loader" src="/wp-admin/images/loading.gif" /></p>').insertAfter('#migrate-post-to-news');
      }
      function remove_loader() {
        jQuery('#migration-loader').remove();
      }
      function run_post_migration(action = 'migrate_posts_to_news') {
        var data = {
          'action': action,
        };
        jQuery.post(ajaxurl, data, function(response) {
          var json_response = JSON.parse(response);
          console.log(json_response)
          if (json_response.success == 1) {
            window.migration_data.total_posts += json_response.total_posts;
            window.migration_data.total_migrated += json_response.total_migrated;
            run_post_migration(action);
          } else {
            remove_loader();
            jQuery([
              '<div id="migration-result">',
              '<p style="font-weight: bold; color: green;">Success!</p>',
              '<p>Total Posts: ' + window.migration_data.total_posts +'</p>',
              '<p>Total Migrated: ' + window.migration_data.total_migrated +'</p>',
              '</div>',
            ].join('')).insertAfter('#migrate-post-to-news');
            jQuery('<p>Total Migrated: ' + window.migration_data.total_migrated +'</p>');
          }
        });
      }

    </script>
    <style type="text/css">
        .spwp-input-error{
            border-color: red !important;
        }
    </style>
    <?php
    }
  }
}


?>
