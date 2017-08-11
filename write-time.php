<?php
/*
Plugin Name: article write time
Plugin URI: http://11neko.com/
Description: To display the time it took to write the article .
Version: 0.1
Author: shinji yonetsu
Author URI: http://11neko.com/
License: GPLv2
*/
add_action( 'plugins_loaded', 'write_time_load_textdomain' );
function write_time_load_textdomain() {
    load_plugin_textdomain( 'write-time', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
}

//$showtext = new write_time;
function get_article_created_time() {
    date_default_timezone_set( 'Asia/Tokyo' );
    $create_time = time();
    global $post;
    $create_time_id = $post->ID;
    add_post_meta($create_time_id, 'create_time', $create_time);
}
add_action( 'admin_head-post-new.php', 'get_article_created_time' );


function get_article_edit_start_time( $post_id ){
if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
    return $post_id;

    date_default_timezone_set( 'Asia/Tokyo' );
    $start_edit_time = time();
    global $post;
    $created_edit_time_id = $post->ID;
    $post_time = $post->post_date;
    $last_edit_time = $post->post_modified;
    $post_time = strtotime($post_time);
    $last_edit_time = strtotime($last_edit_time);

    if( $post_time === $last_edit_time ){
        $start_edit_time_flg = get_post_meta($created_edit_time_id, 'start_edit_time', false);
        if( empty($start_edit_time_flg) ){
            add_post_meta($created_edit_time_id, 'start_edit_time', $start_edit_time);
        } else{
            update_post_meta($created_edit_time_id, 'start_edit_time', $start_edit_time);
        }
        $first_created_endtime = get_post_meta( $created_edit_time_id , 'first_created_endtime' , false );
        $create_time = get_post_meta( $created_edit_time_id , 'create_time' , false );
        $first_created_time_anser = $first_created_endtime[0] - $create_time[0];
        $first_article_time_flg = get_post_meta($created_edit_time_id, 'total_article_time', false);
        if( empty($first_article_time_flg)){
            add_post_meta($created_edit_time_id, 'total_article_time', $first_created_time_anser);
        } else{
            update_post_meta($created_edit_time_id, 'total_article_time', $first_created_time_anser);
        }
    } else{
        $ctmf = get_post_meta($created_edit_time_id, 'start_edit_time', false);
        if( empty( $ctmf ) ){
            add_post_meta($created_edit_time_id, 'start_edit_time', $start_edit_time);
        } else{
            delete_post_meta($created_edit_time_id, 'start_edit_time');
            add_post_meta($created_edit_time_id, 'start_edit_time', $start_edit_time);
        }
    }
}
add_action( 'admin_head-post.php', 'get_article_edit_start_time' );

function get_article_last_edit_time($post_id){
if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
    return $post_id;
    global $post;
    $created_edit_time_id = $post->ID;
    $last_edit_time = time();
    $start_edit_time = get_post_meta($created_edit_time_id, 'start_edit_time', false);
    $first_created_endtime = get_post_meta($created_edit_time_id, 'first_created_endtime', false);
    if( !empty($first_created_endtime)){
        if( !empty($start_edit_time)){
            $start_edit_time = get_post_meta($created_edit_time_id, 'start_edit_time', false);
            $total_edit_time = get_post_meta($created_edit_time_id, 'total_edit_time', false);
            if( empty($total_edit_time)){
                $total_edit_time = $last_edit_time - $start_edit_time[0];
                add_post_meta($created_edit_time_id ,'total_edit_time', $total_edit_time);
            } else{
                $old_time = get_post_meta($created_edit_time_id, 'total_edit_time', true);
                $total_edit_time = $last_edit_time - $start_edit_time[0];
                $total_edit_time_plus = $total_edit_time + $old_time;
                update_post_meta($post_id, 'total_edit_time', $total_edit_time_plus);
            }
        }
    } else{
        $first_created_endtime = $last_edit_time;
        add_post_meta($post_id, 'first_created_endtime', $last_edit_time);
    }
}

add_action('pre_post_update', 'get_article_last_edit_time');

// Program for beginners function
// single or page 
function display_time($time_format){
    global $post;
    $created_time = get_post_meta( $post->ID , 'total_article_time' , false );
    $edit_time = get_post_meta( $post->ID , 'total_edit_time' , false );
    $show_time = $created_time[0] + $edit_time[0];
    $show_hor_time = floor($show_time / 3600);
    $show_min_time = floor($show_time / 60);
    $show_sec_time = $show_time % 60;
    // time_format_setting
    if( !empty($created_time)){
        if($time_format == 'default'){ // japanse
            echo _e('This article' , 'write-time');
            if( $show_time > 60 ){
                echo $show_min_time, _e('minute' , 'write-time'),$show_sec_time,_e('second' , 'write-time');
            } elseif( $show_time > 3600 ){
                echo $show_hor_time,_e('hour' , 'write-time'),$show_min_time,_e('minute' , 'write-time'),$show_sec_time,_e('second' , 'write-time');
            } else{
                echo $show_sec_time,_e('second' , 'write-time');
            }
            echo _e('I wrote in .' , 'write-time');
        } elseif($time_format == 'only'){
            echo _e('This article' , 'write-time');
            if( $show_time > 60 ){
                echo $show_min_time,_e('<span>minute</span>' , 'write-time'),$show_sec_time,_e('<span>second</span>' , 'write-time');
            } elseif( $show_time > 3600 ){
                echo $show_hor_time,_e('<span>hour</span>' , 'write-time'),$show_min_time,_e('<span>minute</span>' , 'write-time'),$show_sec_time,_e('<span>second</span>' , 'write-time');
            } else{
                echo $show_sec_time,_e('<span>second</span>' , 'write-time');
            }
            echo _e('I wrote in .' , 'write-time');
        }
    }
}

?>