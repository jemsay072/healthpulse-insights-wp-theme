<?php
/**
 * HealthPulse Insights functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package HealthPulse_Insights
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'HEALTHPULSE_VERSION' ) ) {
	define( 'HEALTHPULSE_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function healthpulse_insights_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on HealthPulse Insights, use a find and replace
		* to change 'healthpulse-insights' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'healthpulse-insights', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	add_theme_support( 'responsive-embeds' );

	// This theme uses wp_nav_menu() in one location.

	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Editorial Menu', 'healthpulse-insights' ),
			'footer'  => esc_html__( 'Footer Menu', 'healthpulse-insights' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'healthpulse_insights_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	add_image_size( 'healthpulse-card', 800, 500, true );
	add_image_size( 'healthpulse-hero', 1400, 788, true );
}
add_action( 'after_setup_theme', 'healthpulse_insights_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function healthpulse_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'healthpulse_content_width', 768 );
}
add_action( 'after_setup_theme', 'healthpulse_content_width', 0 );

/**
 * Register widget area.
 *
 */
function healthpulse_insights_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'healthpulse-insights' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'healthpulse-insights' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'healthpulse_insights_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function healthpulse_insights_scripts() {
	$version = HEALTHPULSE_VERSION;

	// Google Fonts: Newsreader (editorial headlines) + Plus Jakarta Sans (UI + body).
	wp_enqueue_style(
		'healthpulse-fonts',
		'https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400;1,6..72,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);

	// Font Awesome 6 Free.
	wp_enqueue_style(
		'healthpulse-fontawesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
		array(),
		'6.5.1'
	);

	// Tailwind Play CDN, configured inline AFTER the CDN script so `tailwind` exists.
	wp_enqueue_script( 'healthpulse-tailwind', 'https://cdn.tailwindcss.com', array(), null, false );

	$tailwind_config = <<<'JS'
	tailwind.config = {
		theme: {
			extend: {
				colors: {
					'slate-deep': '#0F172A',
					'med-blue': '#0284C7',
					'med-blue-dark': '#0369A1',
					'ice-tint': '#F0F9FF',
					'off-white': '#F8FAFC'
				},
				fontFamily: {
					serif: ['Newsreader', 'Georgia', 'serif'],
					sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif']
				}
			}
		}
	};
	JS;
	wp_add_inline_script( 'healthpulse-tailwind', $tailwind_config, 'after' );

	// Theme stylesheet (header + prose + drawer rules).
	wp_enqueue_style(
		'healthpulse-style',
		get_stylesheet_uri(),
		array( 'healthpulse-fonts', 'healthpulse-fontawesome' ),
		$version
	);

	// Modular front-end JS.
	wp_enqueue_script(
		'healthpulse-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		$version,
		true
	);

	wp_localize_script(
		'healthpulse-main',
		'healthpulseAjax',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'healthpulse_ajax' ),
			'strings' => array(
				'searching'    => __( 'Searching…', 'healthpulse-insights' ),
				'noResults'    => __( 'No results found for “%s”. Try a different keyword.', 'healthpulse-insights' ),
				'result'       => __( 'result', 'healthpulse-insights' ),
				'results'      => __( 'results', 'healthpulse-insights' ),
				'article'      => __( 'article', 'healthpulse-insights' ),
				'articles'     => __( 'articles', 'healthpulse-insights' ),
				'error'        => __( 'Something went wrong. Please try again.', 'healthpulse-insights' ),
				'loadMore'     => __( 'Load More Insights', 'healthpulse-insights' ),
				'loading'      => __( 'Loading…', 'healthpulse-insights' ),
				'subscribing'  => __( 'Subscribing…', 'healthpulse-insights' ),
				'invalidEmail' => __( 'Please enter a valid email address.', 'healthpulse-insights' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'healthpulse_insights_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

