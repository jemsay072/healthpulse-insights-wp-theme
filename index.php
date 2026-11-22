<?php
/**
 * Fallback template (required by WordPress).
 *
 * Path: wp-content/themes/healthpulse-insights/index.php
 *
 * @package HealthPulse_Insights
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main">
	<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
		<h1 class="mb-8 font-serif text-4xl font-bold tracking-tight text-slate-deep">
			<?php esc_html_e( 'Latest Insights', 'healthpulse-insights' ); ?>
		</h1>

		<?php if ( have_posts() ) : ?>
			<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
				<?php
				while ( have_posts() ) :
					the_post();
					if ( 'clinical_resource' === get_post_type() ) {
						get_template_part( 'template-parts/card', 'resource' );
					} else {
						get_template_part( 'template-parts/card', 'article' );
					}
				endwhile;
				?>
			</div>

			<div class="mt-12">
				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => '<i class="fa-solid fa-arrow-left" aria-hidden="true"></i><span class="sr-only">' . esc_html__( 'Previous page', 'healthpulse-insights' ) . '</span>',
						'next_text' => '<span class="sr-only">' . esc_html__( 'Next page', 'healthpulse-insights' ) . '</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i>',
					)
				);
				?>
			</div>
		<?php else : ?>
			<p class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-600">
				<?php esc_html_e( 'Nothing to show yet.', 'healthpulse-insights' ); ?>
			</p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();