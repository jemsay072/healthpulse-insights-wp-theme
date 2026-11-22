<?php
/**
 * Standard page template.
 *
 * Path: wp-content/themes/healthpulse-insights/page.php
 *
 * @package HealthPulse_Insights
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'mx-auto max-w-3xl px-4 py-14 sm:px-6' ); ?>>
			<h1 class="font-serif text-4xl font-bold leading-tight tracking-tight text-slate-deep sm:text-5xl">
				<?php echo esc_html( get_the_title() ); ?>
			</h1>
			<div class="entry-content mt-8">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>

<?php
get_footer();