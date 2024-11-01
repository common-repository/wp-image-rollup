<?php
/*
Plugin Name: WP Image Rollup
Plugin URI: http://yasobe.ru/na/wprollup
Description: This is plugin will be rolls up pictures in all site pages. 
Version: 1.3.2
Author: Mikhail Chumachkin
Author URI: http://www.facebook.com/chumachkin.m
License: GPLv2 or later
*/

class WPImageRollUp{

    /**
     *
     **/
    private $plugin_NAME;

    /**
     *
     **/
    private $plugin_URL;

    /**
     *
     **/
    private $def_height;

    /**
     *
     **/
    private $def_width;

    /**
     *
     **/
    private $def_notinpage;

    /**
     *
     *
     **/
    public function __construct() {
        $this->plugin_NAME = 'WPImageRollUp';
        $this->plugin_URL = plugins_url('', __FILE__);

        $this->def_height =  400;
        $this->def_width  =  600;
        $this->def_notinpage  =  1;

        add_filter( 'the_content', array( &$this,'the_content'), 999 ); // Фильтр контента отдаваемый пользователю
        add_action('wp_enqueue_scripts', array( &$this,'wp_enqueue_script') ); // Загрузка javascript-ов
        add_action('wp_head', array( &$this,'wp_head') ); // Загрузка шапки
        add_action ( 'admin_menu', array (&$this, 'admin_menu') ); // Добавляем страницу с параметрами

    }

    /**
     *
     *
     **/
    public function admin_menu() {
        $page = add_options_page('WP Image Roll Up', 'WP Image Roll Up', 'manage_options', 'wp-image-roll-up', array(&$this,'admin_options'));
    }

    /**
     *
     *
     **/
    public function admin_options() {
        global $wp_image_roll_up;
        add_option('wp_image_roll_up');
        $data = array();    

        if( is_admin() && isset($_POST['form_submitted'])) {
            foreach ($_POST as $key => $value){
                if( $value != ''){
                    $data[$key] = $value;
                }
            }
            update_option('wp_image_roll_up', $data);
?>
            <div class="updated"><p><strong><?php _e('Changes saved.', $this->plugin_NAME); ?></strong></p></div>
<?php
        }else{
            $data = get_option('wp_image_roll_up'); 
        }
?>
        <div class="wrap">
            <h2>WP Image Roll Up</h2>
            <form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="image-height"><?php _e("Image height for rollup :", $this->plugin_NAME); ?></label>
                            </th>
                            <td>
                                <input type="text" name="image-height" value='<?php echo (!empty($data['image-height'])) ? $data['image-height'] : $this->def_height; ?>' class='wide' />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="image-width"><?php _e("Image width for rollup:", $this->plugin_NAME); ?></label>
                            </th>
                            <td>
                                <input type="text" name="image-width" value='<?php echo (!empty($data['image-width'])) ? $data['image-width'] : $this->def_width; ?>' class='wide' />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                Dont use rollup for pages
                            </th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span>Dont use rollup for pages</span></legend>
                                    <label for="image-notinpage">
                                        <input name="image-notinpage" type="checkbox" id="image-notinpage" value="<?php  $notinpage = (!empty($data['image-notinpage'])) ? $data['image-notinpage'] : $this->def_notinpage; echo $notinpage; ?>" <?php if ( $notinpage == 1) { ?>checked="checked" <?php } ?> >
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                            </th>
                            <td>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <input type="hidden" name="form_submitted" value="1" />
                <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes', $this->plugin_NAME) ?>" /></p>
            </form>
        </div>
<?php
    }

    /**
     *
     *
     **/
    public function the_content($content) {
        $pos_more = strpos($content,'<!--more-->');
        $notinpage = $this->get_option_notinpage();

        if ( !is_page() || (is_page() && $notinpage == 0) ) {

            if ( !is_single() && $pos_more === false) {
        
                preg_match_all('/\<img[^>]+\>/i', $content, $matches);
        
                foreach($matches[0] as $img) {
                    $new_img = str_replace('>'," onload='iru_img_loaded(this);'>",$img);
                    $content = str_replace($img,"<div class=\"iru-roll-up\">$new_img<div class=\"iru-roll-down\"></div><div class=\"iru-roll-down-triangle\"></div></div>", $content);
                }
            }
        }

        return $content;
    }

    public function wp_enqueue_script() {
        wp_enqueue_script( 'jquery');
    }

    private function get_option_height() {
        $data = get_option('wp_image_roll_up'); 
        $image_height = $this->def_height;

        if ($data !== false) {

            if ( isset($data['image-height']) ) {
                $image_height = (int)$data['image-height'];
            } 

            if ( $image_height == 0 || $image_height< 0 ) $image_height = $this->def_height;
        } 

        return $image_height;
     }

    private function get_option_width() {
        $data = get_option('wp_image_roll_up'); 
        $image_width = $this->def_width;

        if ($data !== false) {

            if ( isset($data['image-width']) ) {
                $image_width = (int)$data['image-width'];
            } 

            if ( $image_width == 0 || $image_width< 0 ) $image_width = $this->def_width;
        } 

        return $image_width;
     }

    private function get_option_notinpage() {
        $data = get_option('wp_image_roll_up'); 
        $image_notinpage = $this->def_notinpage;

        if ($data !== false) {

            if ( isset($data['image-notinpage']) ) {
                $image_notinpage = (int)$data['image-notinpage'];
            } 

        }

        return $image_notinpage;
     }

    /**
     *
     *
     **/
    public function wp_head() {
        $image_height = $this->get_option_height();
        $image_width = $this->get_option_width();

?>
    <style>
        .iru-roll-up {
            position:relative;
            overflow:hidden;
            margin:0 auto;
        }
        
        .iru-roll-up img{
            display:inline-block;
        }

        .iru-roll-down {
            position:absolute;
            height:18px;
            background:#000;
            opacity:0.7;
            bottom:0;
            left:0;
            cursor:pointer;
        }

        .iru-roll-down-triangle {
            position:absolute;
            bottom:1px;
            left:0;
            cursor:pointer;
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-top: 16px solid white;
        }
    </style>
    <script>

        window.iru_img_loaded = function(img_el) {
            var img_w;
            var img_h;
            var img_src;
            var wtoh;
            var htow;

            var el = jQuery(img_el).parent();

            img_src = jQuery(img_el).attr('src');
            
            var tmp_img = new Image();
            tmp_img.src = jQuery(img_el).attr("src");
            img_h = jQuery(tmp_img)[0].height;
            img_w = jQuery(tmp_img)[0].width;
            
            if ( img_src.indexOf('youtube') >= 0 ) {
                jQuery(img_el).width(0).height(0).hide();
                jQuery(el).hide();
            }
            
            if ( img_src.indexOf('wp-plugin') >= 0 ) {
                jQuery(img_el).width(0).height(0).hide();
                jQuery(el).hide();
            }

            wtoh = parseInt(img_w)/parseInt(img_h);
            htow = parseInt(img_h)/parseInt(img_w);

            if ( parseInt(img_h) > parseInt(<?php echo $image_height; ?>) ) {

                var el_width = Math.ceil( parseInt(img_h) * parseFloat(wtoh) );
                var el_height = img_h;

                if ( parseInt(el_width) > parseInt(<?php echo $image_width; ?>) ) {
                    el_width = parseInt(<?php echo $image_width; ?>);
                    el_height = Math.ceil( parseInt(el_width) * parseFloat(htow) );

                    jQuery(img_el).width( ''.concat( el_width,'px') );
                    jQuery(img_el).height( ''.concat( el_height,'px') );
                }

                if ( parseInt(el_height) > parseInt(<?php echo $image_height; ?>) ) {
                
                    jQuery(el).height( ''.concat(<?php echo $image_height; ?>,'px'));
                    jQuery(el).width( ''.concat( el_width,'px') );
                
                    jQuery(img_el).width( ''.concat( el_width,'px') );
                
                    jQuery(el).find('.iru-roll-down').width(''.concat(el_width,'px')).show();
                    jQuery(el).find('.iru-roll-down-triangle').show();
                    jQuery(el).find('.iru-roll-down-triangle').css('left',el_width/2-4);
                } else {
                    jQuery(el).find('.iru-roll-down').hide();
                    jQuery(el).find('.iru-roll-down-triangle').hide();
                }
            } else {
                jQuery(el).find('.iru-roll-down').hide();
                jQuery(el).find('.iru-roll-down-triangle').hide();
            }

        }

        jQuery(document).ready(function($){

            $(document).on('click','.iru-roll-down',function(event){
                event.preventDefault();
                var el = this;
                $(this).parent().animate( { 'height': $(this).parent().find('img').height() }, 1000, 
                    function(){
                        $(el).fadeOut();
                        $(el).parent().find('.iru-roll-down-triangle').fadeOut();
                });
            });

            $(document).on('click','.iru-roll-down-triangle',function(event){
                event.preventDefault();
                var el = this;
                $(this).parent().animate( { 'height': $(this).parent().find('img').height() }, 1000, 
                    function(){
                        $(el).fadeOut();
                        $(el).parent().find('.iru-roll-down').fadeOut();
                });
            });

        });
    </script>
<?php

    }

}

$wp_image_roll_up = new WPImageRollUp();

?>