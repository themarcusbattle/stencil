<?php

	function init_stencil() {

		// Define theme supoort for Menus & Featured Images
		add_theme_support( 'menus' );
		add_theme_support( 'post-thumbnails' );

		// Define menu locations for Theme
		register_nav_menu( 'header-menu', __('Header Menu') );
		register_nav_menu( 'contact-menu', __('Contact Menu') );

		add_option( 'homepage_layout', 'one-pager', '', 'yes' );
		add_option( 'google_tag_manager', '', '', 'yes' );


		if ( isset($_REQUEST['google_tag_manager']) ) {
			update_option( 'google_tag_manager', $_POST['google_tag_manager'] );
		}

	}

	function stencil_scripts() {

		wp_enqueue_style( 'bootstrap-css', get_template_directory_uri() . '/assets/css/bootstrap.min.css' );
		wp_enqueue_style( 'stencil-css', get_template_directory_uri() . '/assets/css/stencil.css' );

		wp_enqueue_script( 'jquery-1-11', 'https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js', false, false, true );
		wp_enqueue_script( 'bootstrap-js', get_template_directory_uri() . '/assets/js/bootstrap.min.js', array('jquery-1-11'), false, true );

		wp_enqueue_script( 'stencil-js', get_template_directory_uri() . '/assets/js/stencil.js', array('jquery-1-11'), false, true );

	}

	function stencil_admin_menu() {
		add_menu_page( 'Stencil', 'Stencil', 'administrator', 'stencil-theme', 'stencil_page_settings', '', 58 );
		add_submenu_page( 'stencil-theme', 'Analytics', 'Analytics', 'administrator', 'stencil-theme-analytics', 'stencil_page_analytics' );
	}

	function stencil_page_settings() {
		include( plugin_dir_path(__FILE__) . 'admin-pages/settings.php' );
	}

	function stencil_page_analytics() {
		include( plugin_dir_path(__FILE__) . 'admin-pages/analytics.php' );
	}

	function stencil_customize_register( $wp_customize ) {

		$colors = array();
		$colors[] = array(
			'slug'=>'content_text_color', 
			'default' => '#333',
			'label' => __('Content Text Color', 'Ari')
		);
		$colors[] = array(
			'slug'=>'content_link_color', 
			'default' => '#88C34B',
			'label' => __('Content Link Color', 'Ari')
		);
		foreach( $colors as $color ) {
			// SETTINGS
			$wp_customize->add_setting(
				$color['slug'], array(
					'default' => $color['default'],
					'type' => 'option', 
					'capability' => 
					'edit_theme_options'
				)
			);
			// CONTROLS
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					$color['slug'], 
					array('label' => $color['label'], 
					'section' => 'colors',
					'settings' => $color['slug'])
				)
			);
		}

	}

	function stencil_widgets_init() {

		$the_sidebars = wp_get_sidebars_widgets();

		if ( isset($the_sidebars['stencil_footer']) && ($the_sidebars['stencil_footer']) ) {
			
			$stencil_footer = $the_sidebars['stencil_footer'];
			$stencil_footer_count = floor( 12 / count( $stencil_footer ) );

		} else {

			$stencil_footer_count = 12;

		}

		register_sidebar( array(
			'name' => 'Left Sidebar',
			'id' => 'stencil_left_sidebar',
			'before_widget' => '<div class="stencil-widget%1 stencil-widget-sidebar">',
			'after_widget' => '</div>',
			'before_title' => '<h3>',
			'after_title' => '</h3>',
		) );

		register_sidebar( array(
			'name' => 'Right Sidebar',
			'id' => 'stencil_right_sidebar',
			'before_widget' => '<div class="stencil-widget%1 stencil-widget-sidebar">',
			'after_widget' => '</div>',
			'before_title' => '<h3>',
			'after_title' => '</h3>',
		) );

		register_sidebar( array(
			'name' => 'Footer',
			'id' => 'stencil_footer',
			'before_widget' => '<div class="col-md-' . $stencil_footer_count . ' stencil-widget stencil-widget-footer">',
			'after_widget' => '</div>',
			'before_title' => '<h4>',
			'after_title' => '</h4>',
		) );	

		register_widget( 'Sub_Menu_Widget' );

	}

	function stencil_excerpt_length( $length ) {
		return 33;
	}

	function stencil_excerpt_more( ) {
		global $post;
		return '<p><a class="read_more" href="'. get_permalink($post->ID) . '">' . 'Read More</a></p>';
	}

	function stencil_meta_boxes() {
		$post_types = get_post_types( array( 'public' => true ), 'names' );

		// Set meta box for all public post types 
		foreach( $post_types as $post_type ) {
			add_meta_box( 'page_layout', 'Page Layout', 'stencil_meta_box_page_layout', $post_type, 'side', 'high' );
		}
	}

	function stencil_meta_box_page_layout( $post ) {

		$page_layout = get_post_meta( $post->ID, '_page_layout', true );

		echo '<p><strong>Layout</strong></p>';
		echo '<select name="_page_layout">';
		echo '<option value="">--</option>';
		echo '<option value="full-width">Full Width</option>';
		echo '<option value="left-sidebar">Left Sidebar</option>';
		echo '<option value="right-sidebar">Right Sidebar</option>';
		echo '</select>';

		echo "<script>jQuery('select[name=\"_page_layout\"]').val('" . $page_layout . "');</script>";
	}

	function stencil_save_post( $post_id ) {

		if ( isset($_REQUEST['_page_layout']) ) {
			update_post_meta( $post_id, '_page_layout', $_REQUEST['_page_layout'] );
		}

	}

	class Sub_Menu_Widget extends WP_Widget {

		/**
		 * Sets up the widgets name etc
		 */
		public function __construct() {
			parent::__construct(
				'sub_menu_widget', // Base ID
				__('Sub Menu', 'sub_menu_widget'), // Name
				array( 'description' => __( 'Sub Menu for Pages', 'sub_menu_widget' ), ) // Args
			);
		}

		/**
		 * Outputs the content of the widget
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {
			
			global $post;
			
			$post_id = $post->ID;
			$post_title = $post->post_title;
			$child_pages = get_pages( );

			$title = apply_filters( 'widget_title', $instance['title'] );

			echo $args['before_widget'];
			if ( ! empty( $title ) )
				echo $args['before_title'] . $title . $args['after_title'];
			
			echo "<h3>More $post_title</h3>";
			echo "<ul>";
			wp_list_pages("title_li=&child_of=$post_id");
			echo "</ul>";

			echo $args['after_widget'];

		}

		/**
		 * Ouputs the options form on admin
		 *
		 * @param array $instance The widget options
		 */
		public function form( $instance ) {
			// outputs the options form on admin
			echo "<p>This widget will display a sub page menu. There are no additional options at this time.</p>";
		}

		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 */
		public function update( $new_instance, $old_instance ) {
			// processes widget options to be saved
		}

	}

	class HeaderMenuWalker extends Walker_Nav_Menu {
		
		// add classes to ul sub-menus
		function start_lvl( &$output, $depth ) {
			
			$indent = str_repeat( "\t", $depth );
			$output .= "\n$indent<ul class=\"dropdown-menu\">\n";

		}

		// add main/sub classes to li's and links
		function start_el(  &$output, $item, $depth = 0, $args = array(), $id = 0 ) {

			global $wp_query;
		    $indent = ( $depth > 0 ? str_repeat( "\t", $depth ) : '' ); // code indent
		  
		    // depth dependent classes
		    $depth_classes = array(
		        ( $depth == 0 ? 'main-menu-item' : 'sub-menu-item' ),
		        ( $depth >=2 ? 'sub-sub-menu-item' : '' ),
		        ( $depth % 2 ? 'menu-item-odd' : 'menu-item-even' ),
		        'menu-item-depth-' . $depth
		    );
		    $depth_class_names = esc_attr( implode( ' ', $depth_classes ) );
		  
		    // passed classes
		    $classes = empty( $item->classes ) ? array() : (array) $item->classes;
		    $class_names = esc_attr( implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) ) );

		    // Detect if li has children
		    if ( strpos( $class_names, 'menu-item-has-children' ) !== false ) {
				$class_names .= ' dropdown';
				$has_children = true;
			} else {
				$has_children = false;
			}

		    // Build html
		    $output .= $indent . '<li id="nav-menu-item-'. $item->ID . '" class="' . $depth_class_names . ' ' . $class_names . '">';
		  
		    // link attributes
		    $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		    $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		    $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		    $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
		    $attributes .= ' class="menu-link ' . ( $depth > 0 ? 'sub-menu-link' : 'main-menu-link' ) . '"';

		    // Output toggle if the link has children
		    if ( $has_children ) {

		    	$item_output = '<a class="dropdown-toggle" data-toggle="dropdown" href="#">' . apply_filters( 'the_title', $item->title, $item->ID ) . ' <span class="caret"></span></a>';

		    } else {

		    	$item_output = sprintf( '%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
			        $args->before,
			        $attributes,
			        $args->link_before,
			        apply_filters( 'the_title', $item->title, $item->ID ),
			        $args->link_after,
			        $args->after
			    );

		    }
		  
		    // build html
		    $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );

		}

	}

	add_action( 'init', 'init_stencil' );
	add_action( 'admin_menu', 'stencil_admin_menu' );
	add_action( 'wp_enqueue_scripts', 'stencil_scripts' );

	add_action( 'customize_register', 'stencil_customize_register' );
	add_action( 'widgets_init', 'stencil_widgets_init' );

	add_filter( 'excerpt_length', 'stencil_excerpt_length', 999 );
	add_filter( 'excerpt_more', 'stencil_excerpt_more' );

	add_action( 'add_meta_boxes', 'stencil_meta_boxes' );
	add_action( 'save_post','stencil_save_post' );

?>