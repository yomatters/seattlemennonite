<?php
/**
 * @package WordPress
 * @subpackage Seattle Mennonite
 */

function seattlemennonite_setup() {
	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );
	
	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'seattlemennonite' ),
	) );
}
add_action( 'after_setup_theme', 'seattlemennonite_setup' );

function seattlemennonite_widgets_init() {
    register_sidebar(array(
	    'name' => __('Header'),
	    'id' => 'header',
		'description' => __( 'The top right side of the header' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h2 class="widget-title">',
		'after_title' => '</h2>',
	));
    
	register_sidebar(array(
	    'name' => __('Sidebar Top'),
	    'id' => 'sidebar-top',
		'description' => __( 'The top of the sidebar' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h2 class="widget-title">',
		'after_title' => '</h2>',
	));
	
	register_sidebar(array(
	    'name' => __('Sidebar Bottom'),
	    'id' => 'sidebar-bottom',
		'description' => __( 'The bottom of the sidebar' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h2 class="widget-title">',
		'after_title' => '</h2>',
	));
}

add_action( 'widgets_init', 'seattlemennonite_widgets_init' );

function seattlemennonite_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'seattlemennonite_page_menu_args' );

function seattlemennonite_get_section($map, $child) {
    // Given the menu map and the child element, recurse
    // upward until the top-level ancestor is found. Then
    // return an array containing its title and URL.
    
    $parent = $child->menu_item_parent;
    if ( $parent == 0 ) {
        return array(
            'title' => $child->title,
            'url' => $child->url,
        );
    } elseif ( in_array( $parent, array_keys( $map ) ) ) {
        return seattlemennonite_get_section( $map, $map[$parent] );
    }
}

function seattlemennonite_section() {
    // Figure out what section (i.e. top level menu item) we are
    // in. We do this by first looking for categories that match
    // the top level menu items and then looking at the menu structure
    // itself to figure out where we are.

    $locations = get_nav_menu_locations();
    if ( isset( $locations['primary'] ) ) {
        $menu = wp_get_nav_menu_object( $locations[ 'primary' ] );
        
        // Get an array of menu items.
        $menu_items = wp_get_nav_menu_items( $menu->term_id );
        _wp_menu_item_classes_by_context( $menu_items );
        
        // These classes indicate that this is the current menu item.
        $current_classes = array(
            'current-menu-ancestor', 
            'current-menu-item', 
            'current-post-ancestor'
        );
        
        // Create an array that maps the id of the menu item to
        // the item object.
        $map = array();
        foreach ( (array) $menu_items as $key => $menu_item ) {
            $map[$menu_item->db_id] = $menu_item;
        }
        unset($menu_items);        
        
        foreach ( $map as $key => $menu_item ) {
            // If this is a category, we check to see if it matches the
            // name of one of the top level menu items. If it is a post
            // or page, we check to see if it is in such a category.
            // Otherwise, we look at the classes of the the menu item
            // to try to determine if it is the active one.
            
            $parent = $menu_item->menu_item_parent;
            $title = $menu_item->title;
            $classes = $menu_item->classes;
            if ( ( $parent == 0 && ( is_category($title) || in_category($title) ) ) || 
                ( array_intersect($current_classes, $classes) ) ) {
                return seattlemennonite_get_section($map, $menu_item);
            }
        }        
    }
}

add_filter('body_class','seattlemennonite_body_classes');
function seattlemennonite_body_classes($classes) {
    // Based on the current category or menu section, assign the 
    // appropriate body class.
    $section = seattlemennonite_section();
    if ( $section ) {
        $classes[] = 'section-' . sanitize_title($section['title']);
    } else {
        $classes[] = 'section-general';
    }
	return $classes;
}

function seattlemennonite_post_categories($excl=''){
    $categories = get_the_category($post->ID);
    if(!empty($categories)){
      	$exclude=$excl;
      	$exclude = explode(",", $exclude);
        $printCount = 0;
      	foreach ($categories as $cat) {
      		$html = '';
      		if(!in_array($cat->cat_ID, $exclude)) {
                if($printCount > 0){
                    $html = ', ';
                }
                $html .= '<a href="' . get_category_link($cat->cat_ID) . '" ';
                $html .= 'title="' . $cat->cat_name . '">' . $cat->cat_name . '</a>';
                echo $html;
                $printCount++;
      		}
	    }
    }
}

# TRUE if:
#   a custom excerpt exists which is shorter than the_content, or..
#   the_content's length exceeds $threshold_length.
# FALSE otherwise
function seattlemennonite_should_use_the_excerpt($the_excerpt, $the_content, $threshold_length){
    if(strlen($the_excerpt) > strlen($the_content)){
        return false;
    }else if(strlen($the_content) >= $threshold_length){
        return true;
    }else if(strcmp($the_excerpt, substr($the_content,0,strlen($the_excerpt))) == 0){
        return false;
    }
    return true;
}

?>