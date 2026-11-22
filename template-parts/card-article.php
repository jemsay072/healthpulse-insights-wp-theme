<?php
/**
 * Template part: article card.
 *
 * Path: wp-content/themes/healthpulse-insights/template-parts/card-article.php
 *
 * Used inside The Loop on archives/search and rendered by the
 * healthpulse_filter_articles AJAX handler on the front page.
 *
 * @package HealthPulse_Insights
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hp_post_id   = get_the_ID();
$hp_category  = healthpulse_primary_category( $hp_post_id );
$hp_subtitle  = trim( (string) healthpulse_get_field( 'article_subtitle', $hp_post_id, '' ) );
$hp_reviewer  = trim( (string) healthpulse_get_field( 'medical_reviewer', $hp_post_id, '' ) );
$hp_read_time = healthpulse_read_time( $hp_post_id );
$hp_featured  = healthpulse_is_featured( $hp_post_id );
$hp_summary   = '' !== $hp_subtitle ? $hp_subtitle : wp_trim_words( get_the_excerpt(), 26, '&hellip;' );
?>
<article id="post-<?php echo esc_attr( $hp_post_id ); ?>" <?php post_class( 'group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-shadow hover:shadow-md' ); ?>>

	<a href="<?php the_permalink(); ?>" class="relative block aspect-[16/10] overflow-hidden bg-ice-tint" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php
			the_post_thumbnail(
				'healthpulse-card',
				array(
					'class'   => 'h-full w-full object-cover transition-transform duration-500 group-hover:scale-105',
					'loading' => 'lazy',
				)
			);
			?>
		<?php else : ?>
			<span class="flex h-full w-full items-center justify-center text-med-blue/40">
				<i class="fa-solid fa-heart-pulse text-5xl" aria-hidden="true"></i>
			</span>
		<?php endif; ?>
	</a>

	<div class="flex flex-1 flex-col p-5">

		<?php if ( $hp_category || $hp_featured ) : ?>
			<div class="mb-3 flex flex-wrap items-center gap-2">
				<?php if ( $hp_category && ! empty( $hp_category['url'] ) ) : ?>
					<a href="<?php echo esc_url( $hp_category['url'] ); ?>" class="rounded-full bg-ice-tint px-2.5 py-1 text-xs font-bold text-med-blue transition-colors hover:bg-med-blue hover:text-white">
						<?php echo esc_html( $hp_category['name'] ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $hp_featured ) : ?>
					<span class="inline-flex items-center gap-1 rounded-full bg-slate-deep px-2.5 py-1 text-xs font-semibold text-white">
						<i class="fa-solid fa-star text-[10px]" aria-hidden="true"></i>
						<?php esc_html_e( 'Featured', 'healthpulse-insights' ); ?>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<h3 class="font-serif text-xl font-semibold leading-snug text-slate-deep">
			<a href="<?php the_permalink(); ?>" class="transition-colors hover:text-med-blue">
				<?php echo esc_html( get_the_title() ); ?>
			</a>
		</h3>

		<?php if ( '' !== $hp_summary ) : ?>
			<p class="mt-2 line-clamp-3 text-sm leading-relaxed text-slate-600">
				<?php echo esc_html( $hp_summary ); ?>
			</p>
		<?php endif; ?>

		<div class="mt-auto flex flex-wrap items-center gap-x-3 gap-y-1 border-t border-slate-100 pt-4 text-xs text-slate-500">
			<span class="font-semibold text-slate-700"><?php echo esc_html( get_the_author() ); ?></span>
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<?php if ( '' !== $hp_read_time ) : ?>
				<span class="inline-flex items-center gap-1">
					<i class="fa-regular fa-clock" aria-hidden="true"></i>
					<?php echo esc_html( $hp_read_time ); ?>
				</span>
			<?php endif; ?>
			<?php if ( '' !== $hp_reviewer ) : ?>
				<span class="ml-auto inline-flex items-center gap-1 font-medium text-med-blue" title="<?php echo esc_attr( sprintf( /* translators: %s: reviewer name */ __( 'Medically reviewed by %s', 'healthpulse-insights' ), $hp_reviewer ) ); ?>">
					<i class="fa-solid fa-circle-check" aria-hidden="true"></i>
					<span class="sr-only"><?php echo esc_html( sprintf( /* translators: %s: reviewer name */ __( 'Medically reviewed by %s', 'healthpulse-insights' ), $hp_reviewer ) ); ?></span>
				</span>
			<?php endif; ?>
		</div>
	</div>
</article>