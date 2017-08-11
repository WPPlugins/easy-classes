<?php
/*
* Plugin Name: Easy Classes
* Plugin URI: http://wordpress.org/plugins/easy-classes/
* Description: This plugin has been made to easily handle classes and teachers informations on a Wordpress website.
* Version: 1.2
* Author: Melina Donati
* Author URI: http://donati.melina.perso.sfr.fr/
* Text Domain: easyclasses
* License: GPL2
*/

/*  Copyright 2013  Melina Donati  (email : serenafelis@yahoo.fr)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

## ADMIN init ##
add_action('init', 'eac_easy_classes_init');
function eac_easy_classes_init() {

	load_plugin_textdomain('easyclasses', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	
	// EASY CLASS POST TYPE //
	register_post_type('easyclass', array(
	  'label' => __('Classes','easyclasses'),
	  'singular_label' => __('Class','easyclasses'),
	  'public' => true,
	  'show_ui' => true,
	  'capability_type' => 'post',
	  'hierarchical' => false,
	  'supports' => array('title', 'editor', 'author', 'thumbnail', 'comments'),
	  'menu_icon' => plugins_url( 'images/classes.png', __FILE__ )
	));
	
	// SUBJECT CATEGORY //
	register_taxonomy( 
		'subject', 
		'easyclass', 
		array( 
			'hierarchical' => true, 
			'label' => __('Subject','easyclasses'),
			'query_var' => true, 
			'rewrite' => false
		) 
	);
	
	// LEVEL TAG //
	register_taxonomy( 
		'level', 
		'easyclass', 
		array( 
			'hierarchical' => true, 
			'label' => __('Level','easyclasses'),
			'query_var' => true, 
			'rewrite' => false
		) 
	);
	
	// TEACHER CATEGORY //
	register_taxonomy( 
		'teacher', 
		'easyclass', 
		array( 
			'hierarchical' => true, 
			'label' => __('Teacher','easyclasses'),
			'query_var' => true, 
			'rewrite' => false
		) 
	);
	
	// DAY CATEGORY //
	register_taxonomy( 
		'day', 
		'easyclass', 
		array( 
			'hierarchical' => true, 
			'label' => __('Day','easyclasses'),
			'query_var' => true, 
			'rewrite' => false
		) 
	);
	
	// BEGINNING HOUR CATEGORY //
	register_taxonomy( 
		'beginning', 
		'easyclass', 
		array( 
			'hierarchical' => true, 
			'label' => __('Starting time','easyclasses'),
			'query_var' => true, 
			'rewrite' => false
		) 
	);
	
	// ENDING HOUR CATEGORY //
	register_taxonomy( 
		'ending', 
		'easyclass', 
		array( 
			'hierarchical' => true, 
			'label' => __('Ending time','easyclasses'),
			'query_var' => true, 
			'rewrite' => false
		) 
	);
	
	// PLACE CATEGORY //
	register_taxonomy( 
		'place', 
		'easyclass', 
		array( 
			'hierarchical' => true, 
			'label' => __('Place','easyclasses'),
			'query_var' => true, 
			'rewrite' => false
		) 
	);
	
	// ROOM CATEGORY //
	register_taxonomy( 
		'room', 
		'easyclass', 
		array( 
			'hierarchical' => true, 
			'label' => __('Room','easyclasses'),
			'query_var' => true, 
			'rewrite' => false
		) 
	);
	
	// TEACHER POST TYPE //
	register_post_type('teachers', array(
	  'label' => __('Teachers','easyclasses'),
	  'singular_label' => __('Teacher','easyclasses'),
	  'public' => true,
	  'show_ui' => true,
	  'capability_type' => 'post',
	  'hierarchical' => false,
	  'supports' => array('title', 'editor', 'author', 'thumbnail', 'comments'),
	  'menu_icon' => plugins_url( 'images/teachers.png', __FILE__ )
	));
	
	// LINKING TEACHER TAG TO TEACHERS //
	register_taxonomy_for_object_type( 'teacher', 'teachers' );
	
}

## CLASS : CONTENT CUSTOM TEMPLATE ##
add_filter('the_content', 'eac_easy_class_the_content');
// Customize content //
function eac_easy_class_the_content($content) {

    if (is_singular('easyclass') && in_the_loop()) {
		$post_id = get_the_ID();
		$subject = get_the_term_list( $post_id, 'subject', '<tr><td>'. __('Subject','easyclasses').' :</td><td>', ', ', '</td></tr>' );
		$level = get_the_term_list( $post_id, 'level', '<tr><td>'. __('Level','easyclasses').' :</td><td>', ', ', '</td></tr>' );
		$teacher = get_the_term_list( $post_id, 'teacher', '<tr><td>'. __('Teacher','easyclasses').' :</td><td>', ', ', '</td></tr>' );
		$day = get_the_term_list( $post_id, 'day', '<tr><td>'. __('Day','easyclasses').' :</td><td>', ', ', '</tr>' );
		$beginning = get_the_term_list( $post_id, 'beginning', '<tr><td>'. __('Starting time','easyclasses').' :</td><td>', ', ', '</td></tr>' );
		$ending = get_the_term_list( $post_id, 'ending', '<tr><td>'. __('Ending time','easyclasses').' :</td><td>', ', ', '</td></tr>' );
		$room = get_the_term_list( $post_id, 'room', '<tr><td>'. __('Room','easyclasses').' :</td><td>', ', ', '</td></tr>' );
		$place = get_the_term_list( $post_id, 'place', '<tr><td>'. __('Place','easyclasses').' :</td><td>', ', ', '</td></tr>' );
		
		$custom_content = '<table class="eac-class"><tbody>%s%s%s%s%s%s%s%s</tbody></table>';
		
		// Implementing it in the content //
        $content .= sprintf($custom_content, $subject, $level, $teacher, $day, $beginning, $ending, $room, $place);
	}

    return $content;
}

## TEACHER : CONTENT CUSTOM TEMPLATE ##
add_filter('the_content', 'eac_teacher_the_content');
// Customize content //
function eac_teacher_the_content($content) {

    if (is_singular('teachers') && in_the_loop()) {
	
		$custom_content = "";
	
		//if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
		//  $custom_content .= the_post_thumbnail('medium');
		//} 
	
		$custom_content .= "<center><table class='eac-teacher'><tbody><tr><th>".__('Classes','easyclasses')." :</th></tr>";
		
		$post_id = get_the_ID();
		// Finding the custom category term linking the teacher to its classes //
		$the_terms = get_the_terms( $post_id, 'teacher' );
		foreach($the_terms as $the_term) {
					$args = array(
						 'posts_per_page' => 8,
						 'orderby' => 'rand',
						 'post_type' => 'easyclass',
						 'teacher' => $the_term->slug,
						 'post_status' => 'publish'
					);
					// Get the classes posts //
					$teacher_classes = get_posts( $args );
					// If there are classes //
					if(!empty($teacher_classes)) {
						// Do stuff for each post found //
						foreach($teacher_classes as $class) {
							// Try to get the permalink //
							$permalink = get_permalink( $class->ID );
							// Display classes title as a link //
							if($permalink) {
								$custom_content.= '<tr><td><a href="'.$permalink.'">'.$class->post_title.'</a></td><tr>';
							// Or else don't display a link
							} else {
								$custom_content.= '<tr><td>'.$class->post_title.'</td>';
							}
						}
					// If there is no class //
					} else {
						$custom_content.= '<tr><td>'.__('There is no class registered at the moment.','easyclasses').'</td></tr>';
					}
		}
		
		$custom_content.= '</tbody></table></center>';
		$content.=$custom_content;
	}

    return $content;
}

## SCHEDULE ADMIN MENU
add_action( 'admin_menu', 'eac_register_schedule_page' );
function eac_register_schedule_page(){
    add_menu_page( __('Schedule','easyclasses'), __('Schedule','easyclasses'), 'manage_options', 'easy-classes/schedule.php', '', plugins_url( 'easy-classes/images/schedule.png' ), 25 );
}