<?php
/**
 * 404 template.
 *
 * Path: wp-content/themes/healthpulse-insights/404.php
 *
 * @package HealthPulse_Insights
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hp_resources_url = get_post_type_archive_link( 'clinical_resource' );
?>

<main id="primary" class="site-main">
	<section class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6">
		<span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-ice-tint text-2xl text-med-blue">
			<i class="fa-solid fa-heart-crack" aria-hidden="true"></i>
		</span>
		<h1 class="mt-6 font-serif text-4xl font-bold tracking-tight text-slate-deep sm:text-5xl">
			<?php esc_html_e( 'We could not find that page', 'healthpulse-insights' ); ?>
		</h1>
		<p class="mt-4 text-lg text-slate-600">
			<?php esc_html_e( 'The link may be outdated or the page may have moved. Try searching, or head back to the latest insights.', 'healthpulse-insights' ); ?>
		</p>

		<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="mx-auto mt-8 flex max-w-md gap-2">
			<label for="hp-404-search" class="sr-only"><?php esc_html_e( 'Search', 'healthpulse-insights' ); ?></label>
			<input id="hp-404-search" type="search" name="s" class="w-full flex-1 rounded-lg border border-slate-300 bg-white px-4 py-3 text-base text-slate-deep focus:border-med-blue focus:outline-none focus:ring-2 focus:ring-med-blue/30">
			<button type="submit" class="rounded-lg bg-[#0284C7] px-5 py-3 text-base font-semibold text-white transition-colors hover:bg-[#0369A1]">
				<?php esc_html_e( 'Search', 'healthpulse-insights' ); ?>
			</button>
		</form>

		<div class="mt-8 flex flex-wrap justify-center gap-4 text-sm font-semibold">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-med-blue hover:text-med-blue-dark"><?php esc_html_e( 'Latest insights', 'healthpulse-insights' ); ?></a>
			<?php if ( $hp_resources_url ) : ?>
				<a href="<?php echo esc_url( $hp_resources_url ); ?>" class="text-med-blue hover:text-med-blue-dark"><?php esc_html_e( 'Resource Hub', 'healthpulse-insights' ); ?></a>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();