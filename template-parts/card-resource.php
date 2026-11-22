<?php
/**
 * Template part: resource / whitepaper card.
 *
 * Path: wp-content/themes/healthpulse-insights/template-parts/card-resource.php
 *
 * @package HealthPulse_Insights
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hp_post_id = get_the_ID();
$hp_badge   = trim( (string) healthpulse_get_field( 'resource_type_badge', $hp_post_id, '' ) );
$hp_pages   = trim( (string) healthpulse_get_field( 'resource_pages_count', $hp_post_id, '' ) );
$hp_terms   = get_the_terms( $hp_post_id, 'resource_topic' );
$hp_topic   = ( is_array( $hp_terms ) && ! empty( $hp_terms ) ) ? $hp_terms[0] : null;

if ( '' === $hp_badge ) {
	$hp_badge = __( 'Resource', 'healthpulse-insights' );
}
?>
<article id="post-<?php echo esc_attr( $hp_post_id ); ?>" <?php post_class( 'group flex flex-col overflow-hidden rounded-xl border border-sky-100 bg-white shadow-sm transition-shadow hover:shadow-md' ); ?>>

	<a href="<?php the_permalink(); ?>" class="relative block aspect-[16/9] overflow-hidden bg-ice-tint" tabindex="-1" aria-hidden="true">
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
				<i class="fa-regular fa-file-lines text-5xl" aria-hidden="true"></i>
			</span>
		<?php endif; ?>
	</a>

	<div class="flex flex-1 flex-col p-5">
		<div class="mb-3 flex flex-wrap items-center gap-2 text-xs">
			<span class="inline-flex items-center gap-1 rounded-full border border-sky-200 bg-ice-tint px-2.5 py-1 font-bold text-med-blue-dark">
				<i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
				<?php echo esc_html( $hp_badge ); ?>
			</span>
			<?php if ( '' !== $hp_pages ) : ?>
				<span class="font-medium text-slate-500"><?php echo esc_html( $hp_pages ); ?></span>
			<?php endif; ?>
		</div>

		<h3 class="font-serif text-xl font-semibold leading-snug text-slate-deep">
			<a href="<?php the_permalink(); ?>" class="transition-colors hover:text-med-blue">
				<?php echo esc_html( get_the_title() ); ?>
			</a>
		</h3>

		<p class="mt-2 line-clamp-3 text-sm leading-relaxed text-slate-600">
			<?php echo esc_html( wp_trim_words( get_the_excerpt(), 26, '&hellip;' ) ); ?>
		</p>

		<div class="mt-auto flex items-center justify-between gap-3 border-t border-slate-100 pt-4 text-xs text-slate-500">
			<span>
				<?php if ( $hp_topic ) : ?>
					<span class="font-semibold text-slate-700"><?php echo esc_html( $hp_topic->name ); ?></span>
				<?php else : ?>
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				<?php endif; ?>
			</span>
			<a href="<?php the_permalink(); ?>" class="inline-flex items-center gap-1.5 font-semibold text-med-blue hover:text-med-blue-dark">
				<?php esc_html_e( 'View resource', 'healthpulse-insights' ); ?>
				<i class="fa-solid fa-arrow-right text-[10px]" aria-hidden="true"></i>
			</a>
		</div>
	</div>
</article>