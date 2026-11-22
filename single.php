<?php
/**
 * Single article template.
 *
 * Path: wp-content/themes/healthpulse-insights/single.php
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

		$hp_id        = get_the_ID();
		$hp_cat       = healthpulse_primary_category( $hp_id );
		$hp_subtitle  = trim( (string) healthpulse_get_field( 'article_subtitle', $hp_id, '' ) );
		$hp_reviewer  = trim( (string) healthpulse_get_field( 'medical_reviewer', $hp_id, '' ) );
		$hp_takeaways = healthpulse_get_takeaways( $hp_id );
		$hp_caption   = get_the_post_thumbnail_caption();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<header class="mx-auto max-w-3xl px-4 pt-12 sm:px-6 lg:pt-16">
				<?php if ( $hp_cat && ! empty( $hp_cat['url'] ) ) : ?>
					<a href="<?php echo esc_url( $hp_cat['url'] ); ?>" class="inline-block rounded-full bg-ice-tint px-3 py-1 text-xs font-bold text-med-blue transition-colors hover:bg-med-blue hover:text-white">
						<?php echo esc_html( $hp_cat['name'] ); ?>
					</a>
				<?php endif; ?>

				<h1 class="mt-4 font-serif text-4xl font-bold leading-tight tracking-tight text-slate-deep sm:text-5xl">
					<?php echo esc_html( get_the_title() ); ?>
				</h1>

				<?php if ( '' !== $hp_subtitle ) : ?>
					<p class="mt-4 font-serif text-xl leading-relaxed text-slate-600 sm:text-2xl">
						<?php echo esc_html( $hp_subtitle ); ?>
					</p>
				<?php endif; ?>

				<div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-3 border-y border-slate-200 py-4 text-sm text-slate-500">
					<span class="inline-flex items-center gap-2.5">
						<?php
						echo get_avatar( get_the_author_meta( 'ID' ), 36, '', '', array( 'class' => 'rounded-full' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped markup.
						?>
						<span>
							<?php esc_html_e( 'By', 'healthpulse-insights' ); ?>
							<span class="font-semibold text-slate-deep"><?php echo esc_html( get_the_author() ); ?></span>
						</span>
					</span>
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					<span class="inline-flex items-center gap-1.5">
						<i class="fa-regular fa-clock" aria-hidden="true"></i>
						<?php echo esc_html( healthpulse_read_time( $hp_id ) ); ?>
					</span>
					<?php if ( '' !== $hp_reviewer ) : ?>
						<span class="inline-flex items-center gap-1.5 font-medium text-med-blue">
							<i class="fa-solid fa-circle-check" aria-hidden="true"></i>
							<?php
							/* translators: %s: reviewer name */
							echo esc_html( sprintf( __( 'Medically reviewed by %s', 'healthpulse-insights' ), $hp_reviewer ) );
							?>
						</span>
					<?php endif; ?>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="mx-auto mt-8 max-w-5xl px-4 sm:px-6">
					<?php
					the_post_thumbnail(
						'healthpulse-hero',
						array(
							'class'   => 'w-full rounded-2xl object-cover',
							'loading' => 'eager',
						)
					);
					?>
					<?php if ( $hp_caption ) : ?>
						<figcaption class="mt-3 text-sm text-slate-500"><?php echo wp_kses_post( $hp_caption ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>

			<div class="mx-auto max-w-3xl px-4 sm:px-6">

				<?php if ( ! empty( $hp_takeaways ) ) : ?>
					<aside class="mt-10 rounded-xl border border-sky-100 bg-ice-tint p-6" aria-labelledby="hp-takeaways-heading">
						<h2 id="hp-takeaways-heading" class="flex items-center gap-2.5 font-serif text-xl font-semibold text-slate-deep">
							<i class="fa-solid fa-list-check text-med-blue" aria-hidden="true"></i>
							<?php esc_html_e( 'Key takeaways', 'healthpulse-insights' ); ?>
						</h2>
						<ul class="mt-4 space-y-3">
							<?php foreach ( $hp_takeaways as $hp_point ) : ?>
								<li class="flex gap-3 text-base leading-relaxed text-slate-700">
									<i class="fa-solid fa-check mt-1.5 text-xs text-med-blue" aria-hidden="true"></i>
									<span><?php echo esc_html( $hp_point ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</aside>
				<?php endif; ?>

				<div class="entry-content mt-10">
					<?php
					the_content();

					wp_link_pages(
						array(
							'before' => '<nav class="mt-8 flex flex-wrap items-center gap-2 text-sm font-semibold" aria-label="' . esc_attr__( 'Article pages', 'healthpulse-insights' ) . '"><span>' . esc_html__( 'Pages:', 'healthpulse-insights' ) . '</span>',
							'after'  => '</nav>',
						)
					);
					?>
				</div>

				<?php
				$hp_tags = get_the_tags();
				if ( $hp_tags && ! is_wp_error( $hp_tags ) ) :
					?>
					<ul class="mt-10 flex flex-wrap gap-2" aria-label="<?php esc_attr_e( 'Tags', 'healthpulse-insights' ); ?>">
						<?php foreach ( $hp_tags as $hp_tag ) : ?>
							<li>
								<a href="<?php echo esc_url( get_tag_link( $hp_tag ) ); ?>" class="inline-block rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 transition-colors hover:border-med-blue hover:text-med-blue">
									<?php echo esc_html( $hp_tag->name ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php $hp_bio = get_the_author_meta( 'description' ); ?>
				<?php if ( $hp_bio ) : ?>
					<div class="mt-10 flex gap-4 rounded-xl border border-slate-200 bg-white p-5">
						<?php
						echo get_avatar( get_the_author_meta( 'ID' ), 56, '', '', array( 'class' => 'h-14 w-14 shrink-0 rounded-full' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped markup.
						?>
						<div>
							<p class="font-serif text-lg font-semibold text-slate-deep"><?php echo esc_html( get_the_author() ); ?></p>
							<p class="mt-1 text-sm leading-relaxed text-slate-600"><?php echo esc_html( $hp_bio ); ?></p>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</article>
		<?php
	endwhile;

	/* Related reads: same category, excluding this article. */
	$hp_current_id = get_queried_object_id();
	$hp_cat_ids    = wp_get_post_categories( $hp_current_id, array( 'exclude' => array( (int) get_option( 'default_category' ) ) ) );

	if ( ! empty( $hp_cat_ids ) && ! is_wp_error( $hp_cat_ids ) ) :
		$hp_related = new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => 3,
				'category__in'        => $hp_cat_ids,
				'post__not_in'        => array( $hp_current_id ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);

		if ( $hp_related->have_posts() ) :
			?>
			<section class="mt-16 border-t border-slate-200 bg-white" aria-labelledby="hp-related-heading">
				<div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
					<h2 id="hp-related-heading" class="font-serif text-3xl font-bold text-slate-deep">
						<?php esc_html_e( 'Keep reading', 'healthpulse-insights' ); ?>
					</h2>
					<div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
						<?php
						while ( $hp_related->have_posts() ) :
							$hp_related->the_post();
							get_template_part( 'template-parts/card', 'article' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</div>
			</section>
			<?php
		endif;
	endif;
	?>
</main>

<?php
get_footer();
