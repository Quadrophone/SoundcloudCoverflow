<?php 
/*
Plugin Name: Soundcloud Coverflow
Plugin URI: http://jamespodles.com/
Description: Show off your Soundcloud tracks and sets in a coverflow-style display
Version: 1.0
Author: James Podles
Author URI: http://jamespodles.com
License: GPLv2
 	Copyright 2014 James Podles (email: james at jamespodles.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  SUA
*/
    register_activation_hook( __FILE__, 'coverflow-install');
    function coverflow_install(){
      if (version_compare('$wp_version', '3.5', '<')) {
       wp_die('This plugin requires WordPress version 3.5 or higher');
     }
   }
   function wpb_adding_scripts() {
    wp_register_script('my_amazing_script', plugins_url('js/touchwipe.min.js', __FILE__), array('jquery'), true);
    wp_enqueue_script('my_amazing_script');
  }
  add_action( 'wp_enqueue_scripts', 'wpb_adding_scripts' ); 
  add_action( 'wp_enqueue_scripts', 'prefix_add_my_stylesheet' );
  function prefix_add_my_stylesheet() {
    wp_register_style( 'prefix-style', plugins_url('css/style.css', __FILE__) );
    wp_enqueue_style( 'prefix-style' );
  }
  add_action('admin_menu','coverflow_create_menu');
  function coverflow_create_menu() {
   add_menu_page('Coverflow Plugin Settings', 'Soundcloud Coverflow', 'administrator', __FILE__, 'coverflow_settings_page',plugins_url('/img/soundcloud-coverflow.png', __FILE__));
   add_action( 'admin_init', 'register_mysettings' );
 }
 function register_mysettings() {
   register_setting( 'coverflow-settings-group', 'soundcloud_url');
   register_setting( 'coverflow-settings-group', 'coverflow_color');
 }
 add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );
 function mw_enqueue_color_picker( $hook_suffix ) {
  wp_enqueue_style( 'wp-color-picker' );
  wp_enqueue_script( 'my-script-handle', plugins_url('my-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}
function coverflow_settings_page() {
  ?>
  <div class="wrap">
    <h2>Your Plugin Name</h2>
    <form method="post" action="options.php">
      <?php settings_fields( 'coverflow-settings-group' ); ?>
      <?php do_settings_sections( 'coverflow-settings-group' ); ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row">Soundcloud Playlist URL</th>
          <td><input type="text" name="soundcloud_url" value="<?php echo get_option('soundcloud_url'); ?>" />
          </td>
        </tr>
        <tr>
          <td>Play Button Color: <input type="text" name="coverflow_color" value="<?php echo get_option('coverflow_color'); ?>" class="coverflow-color" data-default-color="#444" /></td>
        </tr>
      </table>
      <script type="text/javascript">
        jQuery(document).ready(function($){
          $('.coverflow-color').wpColorPicker();
        });
      </script>
      <?php submit_button(); ?>
    </form>
  </div>
  <?php }
  function coverflow_sanitize_options($input) {
    $input['soundcloud_url'] = esc_url( $input['soundcloud_url'] );
  }
  function coverflow_func(){ ?>
  <div class="coverflow-wrap">
    <div class="coverflow">
      <?php 
      $client_id = "e084be80c2b787523d82a4786cb7fa06";
      $playlist = get_option( 'soundcloud_url');
      $playlist_json = wp_remote_get("http://api.soundcloud.com/resolve.json?url=" . $playlist . '&client_id=' . $client_id);
      $playlistData = json_decode($playlist_json['body'], true);
      $playlistID = $playlistData['id'];
      $color = get_option('coverflow_color');
      $color = str_replace('#','',$color);
      ?>
      <div>
      </div>
      <?php
      $json = wp_remote_get("http://api.soundcloud.com/playlists/" . $playlistID . "/tracks.json?client_id=" . $client_id);
      $soundcloudData = json_decode($json['body'], true);
      foreach ($soundcloudData as $track) {
       $title = $track['title'];
       $artwork = $track['artwork_url'];
       $trackID = $track['id'];
       echo "<div class='flow-item'><div class='music-player'><iframe width='100%' height='63' scrolling='no' frameborder='no' src='https://w.soundcloud.com/player/?url=http%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F" . $trackID . "&amp;color=" . $color . "&amp;auto_play=false&amp;show_artwork=false'></iframe></div><img src='"; echo str_replace ('large.jpg', 't300x300.jpg',$artwork); echo "'><div class='reflection'></div><img class='reflection' src='"; echo str_replace ('large.jpg', 't300x300.jpg',$artwork); echo "'></div>";
     }
     ?>
   </div>
 </div>
 <script src="http://connect.soundcloud.com/sdk.js"></script>
 <script>
  jQuery(document).ready(function($) {
   $(function(){
    transformCovers();
    $('.coverflow').on('click','.flow-item:not(.selected)',function(){
      transformCovers($(this));
    });
  });
   function transformCovers(centerItem,callback) {
    if(typeof(centerItem)=="undefined"){
      var items = $('.coverflow .flow-item');
      centerItem = items.eq(parseInt(items.length/2));
    }
    if(!centerItem.hasClass('selected')){
      var leftItems = centerItem.prevAll('.flow-item');
      var rightItems = centerItem.nextAll('.flow-item');
      var transform_vals = "translateX(0px) rotateY(0deg) translateZ(0)";
      centerItem.css({"transform": transform_vals});
      centerItem.css({"-webkit-transform": transform_vals});
      leftItems.each(function(i){
        var itemdelta = i+1;
        var transform_vals = "translateX("+((itemdelta)*-30)+"px) rotateY(40deg) translateZ(-200px)";
        $(this).css({
         "transform": transform_vals, 
         "-webkit-transform": transform_vals,
         "-ms-transform": transform_vals
       });
      });
      rightItems.each(function(i){
        var itemdelta = i+1;
        var transform_vals = "translateX("+((itemdelta)*30)+"px) rotateY(-40deg) translateZ(-200px)";
        $(this).css({
         "transform": transform_vals, 
         "-webkit-transform": transform_vals,
         "-ms-transform": transform_vals
       });
      });
      centerItem.addClass('selected').siblings('.selected').removeClass('selected');
    }
  }
  $('.coverflow-wrap').touchwipe ({
    wipeRight: function(){ transformCovers($('.flow-item.selected').prev());},
    wipeLeft: function(){ transformCovers($('.flow-item.selected').next());},
    preventDefaultEvents: true
  });
  $(window).keydown(function(e) {
    if (e.keyCode == 37) {
      transformCovers($('.flow-item.selected').prev());
    }
    if (e.keyCode == 39) {
      transformCovers($('.flow-item.selected').next());
    }
  });
});
</script>
<?php
}
add_shortcode('coverflow','coverflow_func');
