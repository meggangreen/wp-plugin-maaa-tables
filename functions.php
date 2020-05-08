<?php

/****************************
  *** DASHBOARD COLUMNS ***
****************************/
function wpse126301_dashboard_columns() {
    add_screen_option(
        'layout_columns',
        array(
            'max'     => 2,
            'default' => 2
        )
    );
}
add_action( 'admin_head-index.php', 'wpse126301_dashboard_columns' );

/****************************
  *** ENQUEUE CUSTOM JS ***
****************************/
function childmg_js() {
    wp_enqueue_script( 'childmg_script', get_stylesheet_directory_uri() . '/javascript.js', array( 'jquery' ) );
}
add_action( 'wp_enqueue_scripts', 'childmg_js' );

/******************************
  *** ENQUEUE STYLESHEETS ***
******************************/
function childmg_stylesheet() {
    wp_enqueue_style( 'style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'superhero-child', get_stylesheet_directory_uri() . '/style.css', array('style') );
}

add_action( 'wp_enqueue_scripts', 'childmg_stylesheet' );

/********************************************
  *** STRIP MOST NON-NUMERIC CHARACTERS ***
********************************************/
function maaa_filterinput($maaa_input) {
    $maaa_pattern = "/[^A-Za-z0-9 &!(),-.:=\'?$;/";
    $maaa_replacement = "";
    return preg_replace($maaa_pattern, $maaa_replacement, $maaa_input, -1);
} //end function

/**************************************
  *** REMOVE MORE-LINK PAGE SCROLL ***
***************************************/
function remove_more_link_jump( $link ) {
    $link = preg_replace( '|#more-[0-9]+|', '', $link );
    return $link;
} //end function
add_filter( 'the_content_more_link', 'remove_more_link_jump' );

/***************************************
  *** CHANGE 'SELECT CATEGORY' TEXT ***
****************************************/
function _category_dropdown_filter( $cat_args ) {
        $cat_args['show_option_none'] = __('Select Country');
        return $cat_args;
} //end function
add_filter( 'widget_categories_dropdown_args', '_category_dropdown_filter' );

/**************************
  *** CUSTOM DATE_DIFF ***
***************************/
function maaa_date_diff($dateinput1, $dateinput2, $daysum) {
    $maaa_pluralize = function($nb,$str){return $nb>1?$str.'s':$str;}; // adds plurals
    $ts1 = strtotime($dateinput1);
    $ts2 = strtotime($dateinput2);
    $diff = ($ts2 - $ts1);
    $diff = $daysum * 86400 + $diff;
    if ( $diff > 31536000 ) {
        $y_diff = floor($diff / (365 * 86400));
        $d_diff = floor(($diff - $y_diff * 365 * 86400) / 86400);
    } else {
        $y_diff = 0;
        $d_diff = floor($diff / 86400);
    } //endif
    if ( $y_diff > 0 ) {
        $y_diff_str = ' ' . $y_diff . $maaa_pluralize($y_diff, ' year');
    } else {
        $y_diff_str = '';
    } //endif
    if ( $d_diff > 0 ) {
        $d_diff_str = ' ' . $d_diff . $maaa_pluralize($d_diff, ' day');
    } else {
        $d_diff_str = '';
    } //endif
    $diff = $diff / 86400;
    $diff_str = $y_diff_str . $d_diff_str;
    return array($diff_str, $diff);
} //end function

/**********************************
  *** CONVERT NUMBERS TO WORDS ***
***********************************/
function childmg_n2w($mg_number) {
    //simplified from http://www.karlrixon.co.uk/writing/convert-numbers-to-words-with-php/

    $mg_dictionary  = array(
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        5 => 'five',
        6 => 'six',
        7 => 'seven',
        8 => 'eight',
        9 => 'nine'
    );

    $mg_string = $mg_dictionary[$mg_number];
    return $mg_string;
}

/***************************************
  *** PRESENT DAYS & COUNTRY HEADER ***
****************************************/
function maaa_stats_header(){
    global $wpdb;
    $wpdb->show_errors();

    //Retrieve number of days and present country
    $maaa_table = $wpdb->prefix . "maaa_days";

    $maaa_day1 = $wpdb->get_var( "SELECT entry_ts FROM $maaa_table ORDER BY entry_ts ASC LIMIT 0 , 1" );
    $maaa_day2 = $wpdb->get_var( "SELECT exit_ts FROM $maaa_table ORDER BY entry_ts DESC LIMIT 0 , 1" );
    if ($maaa_day2 == "0000-00-00 00:00:00") { $maaa_day2 = date('Y-m-d H:i:s', time()-21600); }
    $maaa_daytotal = maaa_date_diff($maaa_day1,$maaa_day2,0)[0];

    $maaa_prescountry = $wpdb->get_var( "SELECT country FROM $maaa_table ORDER BY entry_ts DESC LIMIT 0 , 1" );
    $maaa_prescountry_id = get_cat_ID($maaa_prescountry);
    $maaa_prescountry_info = get_category($maaa_prescountry_id);
    $maaa_prescountry_count = $maaa_prescountry_info->category_count;
    if ($maaa_prescountry == "USA") { $maaa_prescountry = "the USA"; }
    if ($maaa_prescountry_count == 0) {
        $maaa_prescountry_url = $maaa_prescountry;
    } else {
        $maaa_prescountry_url = esc_url(get_category_link($maaa_prescountry_id));
        $maaa_prescountry_url = '<a href="' . $maaa_prescountry_url . '" title="' . $maaa_prescountry . '">' . $maaa_prescountry . '</a>';
    } //end if
    return array($maaa_daytotal, $maaa_prescountry_url);
} //end function

/************************
  *** COMMENTS LOOP ***
************************/
/**
 * Template for comments and pingbacks.
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since superhero 1.0
 * @child Meggan Green
 */
function superhero_comment( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    switch ( $comment->comment_type ) :
        case 'pingback' :
        case 'trackback' :
    ?>
    <li class="post pingback">
    <p><?php _e( 'Pingback:', 'superhero' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'superhero' ), ' ' ); ?></p>
    <?php
    break;
    default :
    ?>
    <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
    <article id="comment-<?php comment_ID(); ?>" class="comment">

    <footer>

    <div class="comment-author vcard">
    <?php //echo maaa_megatar( get_comment_author_email() ); ?>
    <?php echo get_avatar( $comment, 48 ); ?>
    <?php printf( __( 'At %1$s %2$s, %3$s said:', 'superhero' ), get_comment_time(), get_comment_date(), sprintf( '<span class="fn">%s</span>', ucwords(get_comment_author_link()) ) ); ?>
    </div><!-- .comment-author .vcard -->

    <?php if ( $comment->comment_approved == '0' ) : ?>
        <em><?php _e( 'Your comment is awaiting moderation.', 'superhero' ); ?></em>
        <br />
    <?php endif; ?>

    <div class="comment-meta commentmetadata">
    <?php edit_comment_link( __( '(Edit)', 'superhero' ), ' ' ); ?>
    </div><!-- .comment-meta .commentmetadata -->

    </footer>

    <div class="comment-content"><?php comment_text(); ?></div>
    <div class="reply">
    <?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
    </div><!-- .reply -->
    </article><!-- #comment-## -->
    <?php
    break;
    endswitch;
} //end function

/*************************
  *** ENTRY META DATA ***
**************************/
/**
 * Prints entry meta data in header (date, time, author, type, category)
 *
 * @since superhero 1.0
 * @child Meggan Green
 */
if ( ! function_exists( 'superhero_posted_on' ) ) :
function superhero_posted_on() {
    printf( __( '<a href="http://www.meggangreen.com/maaa/blog/date/%1$s/%2$s" title="View all posts in %3$s %1$s" rel="bookmark"><time class="entry-date" datetime="%4$s">%5$s</time></a>', 'superhero' ),
        esc_attr( get_the_date( 'Y' ) ),
        esc_attr( get_the_date( 'm' ) ),
        esc_attr( get_the_date( 'F' ) ),
        esc_attr( get_the_date( 'c' ) ),
        esc_html( get_the_date() )
    );
    if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search
        /* translators: used between list items, there is a space after the comma */
        $categories_list = get_the_category_list( __( ', ', 'superhero' ) );
        if ( $categories_list && superhero_categorized_blog() ) :
            echo '<span class="cat-links">';
            printf( __( ' | %1$s', 'superhero' ), $categories_list );
            echo '</span>';
        endif; // End if categories

        /* translators: used between list items, there is a space after the comma */
        $tags_list = get_the_tag_list( '', __( ', ', 'superhero' ) );
        if ( $tags_list ) :
            echo '<span class="tags-links">';
            printf( __( ' | %1$s', 'superhero' ), $tags_list );
            echo '</span>';
        endif; // End if tags

    endif; // End if post type
} //end function
endif;

/*************************
  *** ENTRY META DATA ***
**************************/
/**
 * Prints the permalink and edit links in footer
 *
 * @child Meggan Green
 */
function maaa_content_footer() {
    /* translators: used between list items, there is a space after the comma */
    $meta_text = __( 'Bookmark the <a href="%1$s" title="permalink to %2$s" rel="bookmark">permalink</a>.', 'superhero' );
    printf(
        $meta_text,
        get_permalink(),
        the_title_attribute( 'echo=0' )
    );
    edit_post_link( __( 'Edit', 'superhero' ), '<span class="sep"> | </span><span class="edit-link">', '</span>' );
} //end function

/******************************
  *** COUNTRY GALLERY URL ***
******************************/
function maaa_country_gallery( $maaa_singlecattitle ){
    global $wpdb;
    $wpdb->show_errors();

    //Get gallery url
    $maaa_pquery = $maaa_singlecattitle . ' Gallery';
    $maaa_pages = get_all_page_ids();
    foreach ($maaa_pages as $maaa_pageid) if (get_the_title($maaa_pageid) == $maaa_pquery) return sprintf( __( '%s', 'superhero' ), ' &nbsp; | &nbsp; <a href="' . get_page_link($maaa_pageid) . '">Gallery</a>' );
    return false;
} //end function

/***********************************
  *** COUNTRY GALLERY FEAT-IMG ***
***********************************/
function maaa_country_featimg( $maaa_singlecattitle ){
    global $wpdb;
    $wpdb->show_errors();

    //Get gallery url
    $maaa_pquery = $maaa_singlecattitle . ' Gallery';
    $maaa_pages = get_all_page_ids();
    foreach ($maaa_pages as $maaa_pageid) if (get_the_title($maaa_pageid) == $maaa_pquery) if ( '' != get_the_post_thumbnail($maaa_pageid) ) return $maaa_pageid;
    return '0';
} //end function

?>