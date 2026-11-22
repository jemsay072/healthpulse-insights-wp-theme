<?php
/**
 * Generic archive: categories, tags, authors, dates, and resource topics.
 *
 * Path: wp-content/themes/healthpulse-insights/archive.php
 *
 * @package HealthPulse_Insights
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( is_category() || is_tag() || is_tax() ) {
	$hp_title = single_term_title( '', false );
} else {
	$hp_title = wp_strip_all_tags( get_the_archive_title() );
}

if ( is_category() ) {
	$hp_kicker = __( 'Topic', 'healthpulse-insights' );
} elseif ( is_tax( 'resource_topic' ) ) {
	$hp_kicker = __( 'Resource topic', 'healthpulse-insights' );
} elseif ( is_tag() ) {
	$hp_kicker = __( 'Tag', 'healthpulse-insights' );
} elseif ( is_author() ) {
	$hp_kicker = __( 'Author', 'healthpulse-insights' );
} else {
	$hp_kicker = __( 'Archive', 'healthpulse-insights' );
}

$hp_description = get_the_archive_description();
?>

<main id="primary" class="site-main">

	<header class="border-b border-slate-200 bg-white">
		<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-14">
			<p class="text-sm font-semibold text-med-blue"><?php echo esc_html( $hp_kicker ); ?></p>
			<h1 class="mt-2 font-serif text-4xl font-bold tracking-tight text-slate-deep sm:text-5xl">
				<?php echo esc_html( $hp_title ); ?>
			</h1>
			<?php if ( $hp_description ) : ?>
				<div class="mt-4 max-w-2xl text-lg leading-relaxed text-slate-600">
					<?php echo wp_kses_post( $hp_description ); ?>
				</div>
			<?php endif; ?>
		</div>
	</header>

	<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
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
			<div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center">
				<i class="fa-regular fa-folder-open text-3xl text-slate-400" aria-hidden="true"></i>
				<p class="mt-3 text-slate-600"><?php esc_html_e( 'Nothing has been published here yet.', 'healthpulse-insights' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mt-4 inline-flex items-center rounded-full bg-[#0284C7] px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#0369A1]">
					<?php esc_html_e( 'Back to latest insights', 'healthpulse-insights' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();