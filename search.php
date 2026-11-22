<?php
/**
 * Search results (articles + resources).
 *
 * Path: wp-content/themes/healthpulse-insights/search.php
 *
 * @package HealthPulse_Insights
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wp_query;
$hp_term  = get_search_query();
$hp_found = (int) $wp_query->found_posts;
?>

<main id="primary" class="site-main">

	<header class="border-b border-slate-200 bg-white">
		<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-14">
			<p class="text-sm font-semibold text-med-blue"><?php esc_html_e( 'Search results', 'healthpulse-insights' ); ?></p>
			<h1 class="mt-2 font-serif text-4xl font-bold tracking-tight text-slate-deep sm:text-5xl">
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: search term */
						__( 'Results for “%s”', 'healthpulse-insights' ),
						esc_html( $hp_term )
					)
				);
				?>
			</h1>
			<?php if ( '' !== $hp_term ) : ?>
				<p class="mt-3 text-slate-600">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: number of results */
							_n( '%d result across articles and resources', '%d results across articles and resources', $hp_found, 'healthpulse-insights' ),
							$hp_found
						)
					);
					?>
				</p>
			<?php endif; ?>

			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="mt-6 flex max-w-xl gap-2">
				<label for="hp-search-page-input" class="sr-only"><?php esc_html_e( 'Search again', 'healthpulse-insights' ); ?></label>
				<input
					id="hp-search-page-input"
					type="search"
					name="s"
					value="<?php echo esc_attr( $hp_term ); ?>"
					class="w-full flex-1 rounded-lg border border-slate-300 bg-off-white px-4 py-3 text-base text-slate-deep focus:border-med-blue focus:outline-none focus:ring-2 focus:ring-med-blue/30"
				>
				<button type="submit" class="rounded-lg bg-[#0284C7] px-5 py-3 text-base font-semibold text-white transition-colors hover:bg-[#0369A1]">
					<?php esc_html_e( 'Search', 'healthpulse-insights' ); ?>
				</button>
			</form>
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
			<div class="mx-auto max-w-xl rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center">
				<i class="fa-solid fa-magnifying-glass text-3xl text-slate-400" aria-hidden="true"></i>
				<h2 class="mt-4 font-serif text-2xl font-semibold text-slate-deep"><?php esc_html_e( 'No matches found', 'healthpulse-insights' ); ?></h2>
				<p class="mt-2 text-slate-600"><?php esc_html_e( 'Try a broader keyword, check the spelling, or browse the latest insights.', 'healthpulse-insights' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mt-5 inline-flex items-center rounded-full bg-[#0284C7] px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#0369A1]">
					<?php esc_html_e( 'Browse latest insights', 'healthpulse-insights' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();