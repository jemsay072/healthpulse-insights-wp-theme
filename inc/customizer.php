<?php
/**
 * HealthPulse Insights Theme Customizer
 *
 * @package HealthPulse_Insights
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function healthpulse_insights_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'healthpulse_insights_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'healthpulse_insights_customize_partial_blogdescription',
			)
		);
	}
}
add_action( 'customize_register', 'healthpulse_insights_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function healthpulse_insights_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function healthpulse_insights_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Shorter, cleaner excerpts for cards.
 */
function healthpulse_excerpt_length() {
	return 26;
}
add_filter( 'excerpt_length', 'healthpulse_excerpt_length', 999 );

function healthpulse_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'healthpulse_excerpt_more' );

/**
 * Number of articles per AJAX "page".
 */
function healthpulse_articles_per_page() {
	return max( 1, (int) apply_filters( 'healthpulse_articles_per_page', 6 ) );
}

/**
 * Make the main search query cover both articles and resources.
 * (Powers the "View all results" link in the live-search drawer.)
 *
 * @param WP_Query $query Main query.
 */
function healthpulse_search_both_types( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		$query->set( 'post_type', array( 'post', 'clinical_resource' ) );
	}
}
add_action( 'pre_get_posts', 'healthpulse_search_both_types' );

/**
 * Preconnect to the font hosts.
 *
 * @param array  $urls          URLs.
 * @param string $relation_type Relation type.
 * @return array
 */
function healthpulse_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'healthpulse_resource_hints', 10, 2 );

/* ==========================================================================
   8. AJAX endpoints (all nonce-protected, inputs sanitized)
   ========================================================================== */

/**
 * Verify the shared AJAX nonce or stop with a JSON 403.
 */
function healthpulse_verify_ajax_nonce() {
	if ( ! check_ajax_referer( 'healthpulse_ajax', 'nonce', false ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Your session expired. Please refresh the page and try again.', 'healthpulse-insights' ) ),
			403
		);
	}
}


/**
 * 2. Filter articles by category + "Load More" pagination.
 *
 * POST: category (slug, or "all"), paged (int), exclude (comma-separated post IDs, optional).
 * Returns: { html, has_more, max_pages, found, paged }.
 */
function healthpulse_ajax_filter_articles() {
	healthpulse_verify_ajax_nonce();

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified above.
	$category = isset( $_POST['category'] ) ? sanitize_title( wp_unslash( $_POST['category'] ) ) : '';
	$paged    = isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1;
	$exclude  = isset( $_POST['exclude'] ) ? sanitize_text_field( wp_unslash( $_POST['exclude'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => healthpulse_articles_per_page(),
		'paged'               => $paged,
		'ignore_sticky_posts' => true,
	);

	if ( '' !== $category && ! in_array( $category, array( 'all', 'latest' ), true ) ) {
		$args['category_name'] = $category;
	}

	$exclude_ids = array_filter( array_map( 'absint', explode( ',', $exclude ) ) );
	if ( ! empty( $exclude_ids ) ) {
		$args['post__not_in'] = $exclude_ids;
	}

	$query = new WP_Query( $args );

	ob_start();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/card', 'article' );
		}
	} else {
		printf(
			'<p class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">%s</p>',
			esc_html__( 'No articles in this topic yet. Check back soon.', 'healthpulse-insights' )
		);
	}
	$html = ob_get_clean();
	wp_reset_postdata();

	wp_send_json_success(
		array(
			'html'      => $html,
			'has_more'  => $paged < (int) $query->max_num_pages,
			'max_pages' => (int) $query->max_num_pages,
			'found'     => (int) $query->found_posts,
			'paged'     => $paged,
		)
	);
}
add_action( 'wp_ajax_healthpulse_filter_articles', 'healthpulse_ajax_filter_articles' );
add_action( 'wp_ajax_nopriv_healthpulse_filter_articles', 'healthpulse_ajax_filter_articles' );

/**
 * 3. Newsletter signup: stores a private newsletter_sub post and emails a confirmation.
 */
function healthpulse_ajax_subscribe_newsletter() {
	healthpulse_verify_ajax_nonce();

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified above.
	$honeypot = isset( $_POST['hp_website'] ) ? sanitize_text_field( wp_unslash( $_POST['hp_website'] ) ) : '';
	$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	$thanks = __( 'Thanks for subscribing! Check your inbox for a confirmation email.', 'healthpulse-insights' );

	// Bots fill the hidden field: pretend success, store nothing.
	if ( '' !== $honeypot ) {
		wp_send_json_success( array( 'message' => $thanks ) );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Please enter a valid email address.', 'healthpulse-insights' ) ),
			422
		);
	}

	$email = strtolower( $email );

	// Light rate limit: 5 signups per hour per IP (hashed, never stored raw).
	$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$rate_key = 'hp_rl_' . md5( $ip );
	$attempts = (int) get_transient( $rate_key );
	if ( $attempts >= 5 ) {
		wp_send_json_error(
			array( 'message' => __( 'Too many attempts. Please try again later.', 'healthpulse-insights' ) ),
			429
		);
	}
	set_transient( $rate_key, $attempts + 1, HOUR_IN_SECONDS );

	// Duplicate check. Direct query because WP_Query hides private posts from visitors.
	global $wpdb;
	$existing = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
			WHERE p.post_type = %s
			AND p.post_status IN ('private','publish','draft','pending')
			AND m.meta_key = %s AND m.meta_value = %s
			LIMIT 1",
			'newsletter_sub',
			'_healthpulse_email',
			$email
		)
	);

	if ( $existing ) {
		wp_send_json_success( array( 'message' => __( 'You are already subscribed. Thank you!', 'healthpulse-insights' ) ) );
	}

	$subscriber_id = wp_insert_post(
		array(
			'post_type'   => 'newsletter_sub',
			'post_status' => 'private',
			'post_title'  => $email,
		),
		true
	);

	if ( is_wp_error( $subscriber_id ) || ! $subscriber_id ) {
		wp_send_json_error(
			array( 'message' => __( 'We could not save your subscription. Please try again.', 'healthpulse-insights' ) ),
			500
		);
	}

	update_post_meta( $subscriber_id, '_healthpulse_email', $email );

	// Confirmation email. A mail failure never blocks the signup (it is already stored).
	$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	/* translators: %s: site name */
	$subject = sprintf( __( 'Welcome to %s', 'healthpulse-insights' ), $site_name );
	$message = sprintf(
		/* translators: 1: site name, 2: site URL */
		__( "Thank you for subscribing to %1\$s.\n\nYou are on the list. We will send you our latest healthcare reporting, research analysis and resources.\n\nRead the latest: %2\$s", 'healthpulse-insights' ),
		$site_name,
		home_url( '/' )
	);
	wp_mail( $email, $subject, $message, array( 'Content-Type: text/plain; charset=UTF-8' ) );

	wp_send_json_success( array( 'message' => $thanks ) );
}
add_action( 'wp_ajax_healthpulse_subscribe_newsletter', 'healthpulse_ajax_subscribe_newsletter' );
add_action( 'wp_ajax_nopriv_healthpulse_subscribe_newsletter', 'healthpulse_ajax_subscribe_newsletter' );


/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function healthpulse_insights_customize_preview_js() {
	wp_enqueue_script( 'healthpulse-insights-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '1.0.0', true );
}
add_action( 'customize_preview_init', 'healthpulse_insights_customize_preview_js' );