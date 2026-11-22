<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package HealthPulse_Insights
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function healthpulse_insights_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'healthpulse_insights_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function healthpulse_insights_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'healthpulse_insights_pingback_header' );


/* ==========================================================================
   3. Template helpers (ACF-safe, no warnings before posts/ACF exist)
   ========================================================================== */

/**
 * Get a custom field with ACF if active, else post meta, else a default.
 *
 * @param string   $key     Field name.
 * @param int|null $post_id Post ID (defaults to the current post).
 * @param mixed    $default Fallback value.
 * @return mixed
 */
function healthpulse_get_field( $key, $post_id = null, $default = '' ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return $default;
	}

	$value = function_exists( 'get_field' ) ? get_field( $key, $post_id ) : get_post_meta( $post_id, $key, true );

	if ( null === $value || false === $value || '' === $value || array() === $value ) {
		return $default;
	}
	return $value;
}

/**
 * Whether the post is flagged as a featured story.
 *
 * @param int|null $post_id Post ID.
 * @return bool
 */
function healthpulse_is_featured( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return false;
	}
	$value = function_exists( 'get_field' ) ? get_field( 'featured_story', $post_id ) : get_post_meta( $post_id, 'featured_story', true );
	return ! empty( $value ) && '0' !== $value;
}

/**
 * Estimated read time. Uses the ACF value when set, otherwise ~200 wpm.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function healthpulse_read_time( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return '';
	}

	$manual = trim( (string) healthpulse_get_field( 'estimated_read_time', $post_id, '' ) );
	if ( '' !== $manual ) {
		if ( is_numeric( $manual ) ) {
			/* translators: %s: minutes */
			return sprintf( __( '%s min read', 'healthpulse-insights' ), $manual );
		}
		return $manual;
	}

	$text  = wp_strip_all_tags( strip_shortcodes( (string) get_post_field( 'post_content', $post_id ) ) );
	$parts = preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
	$words = is_array( $parts ) ? count( $parts ) : str_word_count( $text );

	$minutes = max( 1, (int) ceil( $words / 200 ) );
	/* translators: %d: minutes */
	return sprintf( __( '%d min read', 'healthpulse-insights' ), $minutes );
}

/**
 * Key takeaways as an array of lines (newline-separated textarea field).
 *
 * @param int|null $post_id Post ID.
 * @return string[]
 */
function healthpulse_get_takeaways( $post_id = null ) {
	$raw = (string) healthpulse_get_field( 'key_takeaways', $post_id, '' );
	if ( '' === trim( $raw ) ) {
		return array();
	}
	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	$lines = is_array( $lines ) ? array_map( 'trim', $lines ) : array();
	return array_values( array_filter( $lines, 'strlen' ) );
}

/**
 * First meaningful category (skips "Uncategorized").
 *
 * @param int $post_id Post ID.
 * @return array|null { name, url }
 */
function healthpulse_primary_category( $post_id ) {
	$cats = get_the_category( $post_id );
	if ( empty( $cats ) || is_wp_error( $cats ) ) {
		return null;
	}
	foreach ( $cats as $cat ) {
		if ( 'uncategorized' === $cat->slug ) {
			continue;
		}
		$url = get_category_link( $cat );
		return array(
			'name' => $cat->name,
			'url'  => is_wp_error( $url ) ? '' : $url,
		);
	}
	return null;
}

/**
 * Output the pulse emblem SVG (static markup; only the class is dynamic).
 *
 * @param string $class CSS classes for the <svg>.
 */
function healthpulse_the_emblem( $class = 'h-10 w-10' ) {
	?>
	<svg class="<?php echo esc_attr( $class ); ?>" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
		<rect width="48" height="48" rx="12" fill="#0F172A"/>
		<path d="M7 25h8.5l4-10 6 20 5-14 2.5 4H41" stroke="#FFFFFF" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
		<circle cx="38.5" cy="11.5" r="3.4" fill="#0284C7" stroke="#FFFFFF" stroke-width="1.5"/>
	</svg>
	<?php
}

/* ==========================================================================
   4. Custom post types & taxonomy
   ========================================================================== */

function healthpulse_register_content_types() {
	// Resources & Whitepapers.
	register_post_type(
		'clinical_resource',
		array(
			'labels'              => array(
				'name'               => __( 'Resources & Whitepapers', 'healthpulse-insights' ),
				'singular_name'      => __( 'Resource', 'healthpulse-insights' ),
				'menu_name'          => __( 'Resources', 'healthpulse-insights' ),
				'add_new_item'       => __( 'Add New Resource', 'healthpulse-insights' ),
				'edit_item'          => __( 'Edit Resource', 'healthpulse-insights' ),
				'new_item'           => __( 'New Resource', 'healthpulse-insights' ),
				'view_item'          => __( 'View Resource', 'healthpulse-insights' ),
				'search_items'       => __( 'Search Resources', 'healthpulse-insights' ),
				'not_found'          => __( 'No resources found.', 'healthpulse-insights' ),
				'not_found_in_trash' => __( 'No resources found in Trash.', 'healthpulse-insights' ),
				'all_items'          => __( 'All Resources', 'healthpulse-insights' ),
			),
			'public'              => true,
			'has_archive'         => 'resources',
			'rewrite'             => array(
				'slug'       => 'resources',
				'with_front' => false,
			),
			'menu_icon'           => 'dashicons-media-document',
			'menu_position'       => 6,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
			'exclude_from_search' => false,
		)
	);

	// Resource topics.
	register_taxonomy(
		'resource_topic',
		'clinical_resource',
		array(
			'labels'            => array(
				'name'          => __( 'Resource Topics', 'healthpulse-insights' ),
				'singular_name' => __( 'Resource Topic', 'healthpulse-insights' ),
				'search_items'  => __( 'Search Topics', 'healthpulse-insights' ),
				'all_items'     => __( 'All Topics', 'healthpulse-insights' ),
				'edit_item'     => __( 'Edit Topic', 'healthpulse-insights' ),
				'add_new_item'  => __( 'Add New Topic', 'healthpulse-insights' ),
				'menu_name'     => __( 'Topics', 'healthpulse-insights' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'resource-topic',
				'with_front' => false,
			),
		)
	);

	// Private newsletter subscribers: visible in WP Admin only, never on the front end.
	register_post_type(
		'newsletter_sub',
		array(
			'labels'              => array(
				'name'          => __( 'Newsletter Subscribers', 'healthpulse-insights' ),
				'singular_name' => __( 'Subscriber', 'healthpulse-insights' ),
				'menu_name'     => __( 'Subscribers', 'healthpulse-insights' ),
				'all_items'     => __( 'All Subscribers', 'healthpulse-insights' ),
				'search_items'  => __( 'Search Subscribers', 'healthpulse-insights' ),
				'not_found'     => __( 'No subscribers yet.', 'healthpulse-insights' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'show_in_nav_menus'   => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'menu_icon'           => 'dashicons-email-alt',
			'menu_position'       => 26,
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
		)
	);
}
add_action( 'init', 'healthpulse_register_content_types' );

/**
 * Flush rewrite rules once on theme activation.
 */
function healthpulse_activate() {
	healthpulse_register_content_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'healthpulse_activate' );

function healthpulse_deactivate() {
	flush_rewrite_rules();
}
add_action( 'switch_theme', 'healthpulse_deactivate' );

/* ==========================================================================
   5. ACF Free field groups (local, safe if ACF is not installed)
   ========================================================================== */

function healthpulse_register_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_healthpulse_article',
			'title'    => 'Article Details',
			'fields'   => array(
				array(
					'key'          => 'field_hp_article_subtitle',
					'label'        => 'Article Subtitle',
					'name'         => 'article_subtitle',
					'type'         => 'text',
					'instructions' => 'One-line standfirst shown under the headline and on cards.',
				),
				array(
					'key'          => 'field_hp_estimated_read_time',
					'label'        => 'Estimated Read Time',
					'name'         => 'estimated_read_time',
					'type'         => 'text',
					'instructions' => 'Leave blank to calculate automatically from the word count.',
					'placeholder'  => '6 min read',
				),
				array(
					'key'         => 'field_hp_medical_reviewer',
					'label'       => 'Medical Reviewer',
					'name'        => 'medical_reviewer',
					'type'        => 'text',
					'placeholder' => 'Dr. Jane Smith, MD',
				),
				array(
					'key'          => 'field_hp_key_takeaways',
					'label'        => 'Key Takeaways',
					'name'         => 'key_takeaways',
					'type'         => 'textarea',
					'instructions' => 'One takeaway per line. Shown in a summary box on the article.',
					'rows'         => 5,
					'new_lines'    => '',
				),
				array(
					'key'     => 'field_hp_featured_story',
					'label'   => 'Featured Story',
					'name'    => 'featured_story',
					'type'    => 'true_false',
					'message' => 'Feature this story on the homepage',
					'ui'      => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'post',
					),
				),
			),
			'position' => 'normal',
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_healthpulse_resource',
			'title'    => 'Resource Details',
			'fields'   => array(
				array(
					'key'         => 'field_hp_resource_type_badge',
					'label'       => 'Resource Type Badge',
					'name'        => 'resource_type_badge',
					'type'        => 'text',
					'placeholder' => 'PDF Whitepaper',
				),
				array(
					'key'          => 'field_hp_resource_file_url',
					'label'        => 'Resource File URL',
					'name'         => 'resource_file_url',
					'type'         => 'url',
					'instructions' => 'Link to the downloadable file (Media Library URL or external).',
				),
				array(
					'key'         => 'field_hp_resource_pages_count',
					'label'       => 'Pages Count',
					'name'        => 'resource_pages_count',
					'type'        => 'text',
					'placeholder' => '18 Pages',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'clinical_resource',
					),
				),
			),
			'position' => 'normal',
		)
	);
}
add_action( 'acf/init', 'healthpulse_register_acf_fields' );

/* ==========================================================================
   6. Customizer: Appearance > Customize > Publication Settings
   ========================================================================== */

function healthpulse_default_mods() {
	return array(
		'healthpulse_footer_description'  => __( 'HealthPulse Insights delivers independent reporting, clinical research analysis, and policy intelligence for healthcare professionals, administrators, and informed readers.', 'healthpulse-insights' ),
		'healthpulse_medical_disclaimer'  => __( 'The information published on HealthPulse Insights is for educational and informational purposes only and does not constitute medical advice, diagnosis, or treatment. Always seek the advice of a qualified health professional with any questions about a medical condition.', 'healthpulse-insights' ),
		'healthpulse_newsletter_heading'  => __( 'Get the Pulse on Healthcare, Delivered Weekly', 'healthpulse-insights' ),
	);
}

/**
 * Get a Customizer value, falling back to the default when empty.
 *
 * @param string $key Setting ID.
 * @return string
 */
function healthpulse_mod( $key ) {
	$defaults = healthpulse_default_mods();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	$value    = get_theme_mod( $key, $default );
	return ( '' === $value || null === $value ) ? $default : (string) $value;
}

function healthpulse_customize_register( $wp_customize ) {
	$defaults = healthpulse_default_mods();

	$wp_customize->add_section(
		'healthpulse_publication',
		array(
			'title'    => __( 'Publication Settings', 'healthpulse-insights' ),
			'priority' => 30,
		)
	);

	$wp_customize->add_setting(
		'healthpulse_footer_description',
		array(
			'default'           => $defaults['healthpulse_footer_description'],
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	$wp_customize->add_control(
		'healthpulse_footer_description',
		array(
			'label'   => __( 'Footer publication description', 'healthpulse-insights' ),
			'section' => 'healthpulse_publication',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'healthpulse_medical_disclaimer',
		array(
			'default'           => $defaults['healthpulse_medical_disclaimer'],
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	$wp_customize->add_control(
		'healthpulse_medical_disclaimer',
		array(
			'label'   => __( 'Medical disclaimer text', 'healthpulse-insights' ),
			'section' => 'healthpulse_publication',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'healthpulse_newsletter_heading',
		array(
			'default'           => $defaults['healthpulse_newsletter_heading'],
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'healthpulse_newsletter_heading',
		array(
			'label'   => __( 'Newsletter heading', 'healthpulse-insights' ),
			'section' => 'healthpulse_publication',
			'type'    => 'text',
		)
	);
}
add_action( 'customize_register', 'healthpulse_customize_register' );

/* ==========================================================================
   7. Navigation helpers
   ========================================================================== */

/**
 * Add link classes to menu anchors so style.css can mark the active item.
 *
 * @param array    $atts Link attributes.
 * @param WP_Post  $item Menu item.
 * @param stdClass $args Menu args.
 * @return array
 */
function healthpulse_nav_link_attributes( $atts, $item, $args ) {
	$location = isset( $args->theme_location ) ? $args->theme_location : '';
	$existing = isset( $atts['class'] ) ? $atts['class'] : '';

	if ( 'primary' === $location ) {
		$atts['class'] = trim( $existing . ' hp-nav-link' );
	} elseif ( 'footer' === $location ) {
		$atts['class'] = trim( $existing . ' text-slate-300 transition-colors hover:text-white' );
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'healthpulse_nav_link_attributes', 10, 3 );

/**
 * 1. Live search across articles + resources.
 */
function healthpulse_ajax_live_search() {
	healthpulse_verify_ajax_nonce();

	$term = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above.

	$view_all = add_query_arg( 's', rawurlencode( $term ), home_url( '/' ) );

	if ( mb_strlen( $term ) < 2 ) {
		wp_send_json_success(
			array(
				'results'  => array(),
				'total'    => 0,
				'view_all' => esc_url_raw( $view_all ),
			)
		);
	}

	$query = new WP_Query(
		array(
			'post_type'           => array( 'post', 'clinical_resource' ),
			'post_status'         => 'publish',
			's'                   => $term,
			'posts_per_page'      => 6,
			'ignore_sticky_posts' => true,
		)
	);

	$results = array();

	foreach ( $query->posts as $found ) {
		$is_resource = ( 'clinical_resource' === $found->post_type );

		if ( $is_resource ) {
			$terms    = get_the_terms( $found->ID, 'resource_topic' );
			$category = ( is_array( $terms ) && ! empty( $terms ) )
				? $terms[0]->name
				: (string) healthpulse_get_field( 'resource_type_badge', $found->ID, '' );
		} else {
			$primary  = healthpulse_primary_category( $found->ID );
			$category = $primary ? $primary['name'] : '';
		}

		$results[] = array(
			'id'        => (int) $found->ID,
			'type'      => $is_resource ? 'resource' : 'post',
			'type_label'=> $is_resource ? __( 'Resource', 'healthpulse-insights' ) : __( 'Article', 'healthpulse-insights' ),
			'category'  => wp_specialchars_decode( wp_strip_all_tags( $category ), ENT_QUOTES ),
			'title'     => wp_specialchars_decode( wp_strip_all_tags( get_the_title( $found ) ), ENT_QUOTES ),
			'url'       => esc_url_raw( get_permalink( $found ) ),
			'read_time' => healthpulse_read_time( $found->ID ),
		);
	}

	wp_send_json_success(
		array(
			'results'  => $results,
			'total'    => (int) $query->found_posts,
			'view_all' => esc_url_raw( $view_all ),
		)
	);
}
add_action( 'wp_ajax_healthpulse_live_search', 'healthpulse_ajax_live_search' );
add_action( 'wp_ajax_nopriv_healthpulse_live_search', 'healthpulse_ajax_live_search' );

/**
 * Default editorial menu, shown until a menu is assigned to "primary".
 */
function healthpulse_fallback_menu() {
	$category_url = static function ( $slug ) {
		$term = get_category_by_slug( $slug );
		return $term ? get_category_link( $term ) : home_url( '/category/' . $slug . '/' );
	};

	$resources_url = get_post_type_archive_link( 'clinical_resource' );
	$about_page    = get_page_by_path( 'about' );

	$items = array(
		array(
			'label'   => __( 'Latest', 'healthpulse-insights' ),
			'url'     => home_url( '/' ),
			'current' => is_front_page() || is_home(),
		),
		array(
			'label'   => __( 'Clinical Research', 'healthpulse-insights' ),
			'url'     => $category_url( 'clinical-research' ),
			'current' => is_category( 'clinical-research' ),
		),
		array(
			'label'   => __( 'Health Policy', 'healthpulse-insights' ),
			'url'     => $category_url( 'health-policy' ),
			'current' => is_category( 'health-policy' ),
		),
		array(
			'label'   => __( 'Digital Health', 'healthpulse-insights' ),
			'url'     => $category_url( 'digital-health' ),
			'current' => is_category( 'digital-health' ),
		),
		array(
			'label'   => __( 'Resource Hub', 'healthpulse-insights' ),
			'url'     => $resources_url ? $resources_url : home_url( '/resources/' ),
			'current' => is_post_type_archive( 'clinical_resource' ) || is_singular( 'clinical_resource' ) || is_tax( 'resource_topic' ),
		),
		array(
			'label'   => __( 'About', 'healthpulse-insights' ),
			'url'     => $about_page ? get_permalink( $about_page ) : home_url( '/about/' ),
			'current' => is_page( 'about' ),
		),
	);

	echo '<ul id="primary-menu" class="m-0 flex list-none flex-col p-0 pb-3 md:flex-row md:justify-center md:gap-8 md:pb-0">';
	foreach ( $items as $item ) {
		printf(
			'<li class="menu-item%1$s"><a class="hp-nav-link" href="%2$s"%3$s>%4$s</a></li>',
			$item['current'] ? ' current-menu-item' : '',
			esc_url( $item['url'] ),
			$item['current'] ? ' aria-current="page"' : '',
			esc_html( $item['label'] )
		);
	}
	echo '</ul>';
}


