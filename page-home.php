<?php
/**
 * Template Name: Front Page
 * 
 * Front page: featured story, resource highlights, and the AJAX article grid.
 *
 * Path: wp-content/themes/healthpulse-insights/front-page.php
 *
 * @package HealthPulse_Insights
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hp_default_cat = (int) get_option( 'default_category' );

/* Featured story: newest post flagged featured_story, else the newest post. */
$hp_hero_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 1,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'meta_query'          => array(
			array(
				'key'     => 'featured_story',
				'value'   => '1',
				'compare' => '=',
			),
		),
	)
);

if ( ! $hp_hero_query->have_posts() ) {
	$hp_hero_query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 1,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
}

$hp_hero_id = $hp_hero_query->have_posts() ? (int) $hp_hero_query->posts[0]->ID : 0;

/* Resource highlights. */
$hp_resource_query = new WP_Query(
	array(
		'post_type'      => 'clinical_resource',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'no_found_rows'  => true,
	)
);

/* First page of the article grid (same args as the AJAX handler). */
$hp_grid_args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => healthpulse_articles_per_page(),
	'paged'               => 1,
	'ignore_sticky_posts' => true,
);
if ( $hp_hero_id ) {
	$hp_grid_args['post__not_in'] = array( $hp_hero_id );
}
$hp_grid_query = new WP_Query( $hp_grid_args );

$hp_topics = get_categories(
	array(
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 6,
		'hide_empty' => true,
		'exclude'    => array( $hp_default_cat ),
	)
);

$hp_filter_classes = 'hp-filter-btn shrink-0 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:border-med-blue';
?>

<main id="primary" class="site-main">

	<?php if ( $hp_hero_query->have_posts() ) : ?>
		<!-- Featured story + Resource Hub highlights -->
		<section class="mx-auto max-w-7xl px-4 pb-4 pt-10 sm:px-6 lg:px-8 lg:pt-14" aria-label="<?php esc_attr_e( 'Featured story', 'healthpulse-insights' ); ?>">
			<div class="grid gap-8 lg:grid-cols-12">

				<?php
				while ( $hp_hero_query->have_posts() ) :
					$hp_hero_query->the_post();

					$hp_id       = get_the_ID();
					$hp_cat      = healthpulse_primary_category( $hp_id );
					$hp_subtitle = trim( (string) healthpulse_get_field( 'article_subtitle', $hp_id, '' ) );
					$hp_summary  = '' !== $hp_subtitle ? $hp_subtitle : wp_trim_words( get_the_excerpt(), 34, '&hellip;' );
					$hp_reviewer = trim( (string) healthpulse_get_field( 'medical_reviewer', $hp_id, '' ) );
					?>
					<article class="group <?php echo $hp_resource_query->have_posts() ? 'lg:col-span-8' : 'lg:col-span-12'; ?>">
						<a href="<?php the_permalink(); ?>" class="block aspect-[16/9] overflow-hidden rounded-2xl bg-ice-tint" tabindex="-1" aria-hidden="true">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php
								the_post_thumbnail(
									'healthpulse-hero',
									array(
										'class'         => 'h-full w-full object-cover transition-transform duration-700 group-hover:scale-105',
										'loading'       => 'eager',
										'fetchpriority' => 'high',
									)
								);
								?>
							<?php else : ?>
								<span class="flex h-full w-full items-center justify-center text-med-blue/30">
									<i class="fa-solid fa-heart-pulse text-7xl" aria-hidden="true"></i>
								</span>
							<?php endif; ?>
						</a>

						<div class="mt-6">
							<div class="flex flex-wrap items-center gap-2 text-xs">
								<?php if ( $hp_cat && ! empty( $hp_cat['url'] ) ) : ?>
									<a href="<?php echo esc_url( $hp_cat['url'] ); ?>" class="rounded-full bg-ice-tint px-2.5 py-1 font-bold text-med-blue transition-colors hover:bg-med-blue hover:text-white">
										<?php echo esc_html( $hp_cat['name'] ); ?>
									</a>
								<?php endif; ?>
								<span class="inline-flex items-center gap-1 rounded-full bg-slate-deep px-2.5 py-1 font-semibold text-white">
									<i class="fa-solid fa-star text-[10px]" aria-hidden="true"></i>
									<?php esc_html_e( 'Featured story', 'healthpulse-insights' ); ?>
								</span>
							</div>

							<h2 class="mt-4 font-serif text-3xl font-bold leading-tight tracking-tight text-slate-deep sm:text-4xl lg:text-5xl">
								<a href="<?php the_permalink(); ?>" class="transition-colors hover:text-med-blue">
									<?php echo esc_html( get_the_title() ); ?>
								</a>
							</h2>

							<?php if ( '' !== $hp_summary ) : ?>
								<p class="mt-4 max-w-2xl font-serif text-lg leading-relaxed text-slate-600 sm:text-xl">
									<?php echo esc_html( $hp_summary ); ?>
								</p>
							<?php endif; ?>

							<div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500">
								<span class="font-semibold text-slate-700"><?php echo esc_html( get_the_author() ); ?></span>
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
										echo esc_html( sprintf( __( 'Reviewed by %s', 'healthpulse-insights' ), $hp_reviewer ) );
										?>
									</span>
								<?php endif; ?>
							</div>
						</div>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
				?>

				<?php if ( $hp_resource_query->have_posts() ) : ?>
					<aside class="lg:col-span-4" aria-labelledby="hp-resource-heading">
						<div class="rounded-2xl border border-sky-100 bg-ice-tint p-6">
							<div class="flex items-center justify-between gap-3">
								<h2 id="hp-resource-heading" class="font-serif text-2xl font-semibold text-slate-deep">
									<?php esc_html_e( 'From the Resource Hub', 'healthpulse-insights' ); ?>
								</h2>
								<i class="fa-regular fa-folder-open text-xl text-med-blue" aria-hidden="true"></i>
							</div>

							<ul class="mt-5 divide-y divide-sky-100">
								<?php
								while ( $hp_resource_query->have_posts() ) :
									$hp_resource_query->the_post();

									$hp_r_badge = trim( (string) healthpulse_get_field( 'resource_type_badge', get_the_ID(), '' ) );
									$hp_r_pages = trim( (string) healthpulse_get_field( 'resource_pages_count', get_the_ID(), '' ) );
									if ( '' === $hp_r_badge ) {
										$hp_r_badge = __( 'Resource', 'healthpulse-insights' );
									}
									?>
									<li class="py-4 first:pt-0 last:pb-0">
										<p class="flex flex-wrap items-center gap-x-2 text-xs font-semibold text-med-blue-dark">
											<span><?php echo esc_html( $hp_r_badge ); ?></span>
											<?php if ( '' !== $hp_r_pages ) : ?>
												<span class="font-medium text-slate-500"><?php echo esc_html( $hp_r_pages ); ?></span>
											<?php endif; ?>
										</p>
										<a href="<?php the_permalink(); ?>" class="mt-1 block font-serif text-lg font-semibold leading-snug text-slate-deep transition-colors hover:text-med-blue">
											<?php echo esc_html( get_the_title() ); ?>
										</a>
									</li>
									<?php
								endwhile;
								wp_reset_postdata();
								?>
							</ul>

							<?php $hp_archive_url = get_post_type_archive_link( 'clinical_resource' ); ?>
							<?php if ( $hp_archive_url ) : ?>
								<a href="<?php echo esc_url( $hp_archive_url ); ?>" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-med-blue hover:text-med-blue-dark">
									<?php esc_html_e( 'Browse all resources', 'healthpulse-insights' ); ?>
									<i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
								</a>
							<?php endif; ?>
						</div>
					</aside>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- AJAX article grid -->
	<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8" aria-labelledby="hp-latest-heading">

		<div class="flex flex-col gap-5 border-b border-slate-200 pb-6 md:flex-row md:items-end md:justify-between">
			<h2 id="hp-latest-heading" class="font-serif text-3xl font-bold text-slate-deep sm:text-4xl">
				<?php esc_html_e( 'Latest Insights', 'healthpulse-insights' ); ?>
			</h2>

			<?php if ( ! empty( $hp_topics ) && ! is_wp_error( $hp_topics ) ) : ?>
				<div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 md:mx-0 md:px-0 md:pb-0" role="group" aria-label="<?php esc_attr_e( 'Filter articles by topic', 'healthpulse-insights' ); ?>">
					<button type="button" class="<?php echo esc_attr( $hp_filter_classes ); ?> is-active" data-hp-filter="all" aria-pressed="true">
						<?php esc_html_e( 'Latest', 'healthpulse-insights' ); ?>
					</button>
					<?php foreach ( $hp_topics as $hp_topic ) : ?>
						<button type="button" class="<?php echo esc_attr( $hp_filter_classes ); ?>" data-hp-filter="<?php echo esc_attr( $hp_topic->slug ); ?>" aria-pressed="false">
							<?php echo esc_html( $hp_topic->name ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<p id="hp-grid-status" class="sr-only" role="status" aria-live="polite"></p>

		<div
			id="hp-article-grid"
			class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
			data-category="all"
			data-page="1"
			data-max-pages="<?php echo esc_attr( (int) $hp_grid_query->max_num_pages ); ?>"
			data-exclude="<?php echo esc_attr( $hp_hero_id ? (string) $hp_hero_id : '' ); ?>"
		>
			<?php if ( $hp_grid_query->have_posts() ) : ?>
				<?php
				while ( $hp_grid_query->have_posts() ) :
					$hp_grid_query->the_post();
					get_template_part( 'template-parts/card', 'article' );
				endwhile;
				wp_reset_postdata();
				?>
			<?php else : ?>
				<div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center">
					<i class="fa-regular fa-newspaper text-3xl text-slate-400" aria-hidden="true"></i>
					<p class="mt-3 text-slate-600"><?php esc_html_e( 'No articles have been published yet.', 'healthpulse-insights' ); ?></p>
					<?php if ( current_user_can( 'publish_posts' ) ) : ?>
						<a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>" class="mt-4 inline-flex items-center rounded-full bg-[#0284C7] px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#0369A1]">
							<?php esc_html_e( 'Write the first article', 'healthpulse-insights' ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="mt-10 text-center">
			<button
				id="hp-load-more"
				type="button"
				class="inline-flex items-center justify-center rounded-full bg-[#0284C7] px-7 py-3 text-base font-semibold text-white transition-colors hover:bg-[#0369A1] disabled:cursor-not-allowed disabled:opacity-70"
				<?php echo ( (int) $hp_grid_query->max_num_pages > 1 ) ? '' : 'hidden'; ?>
			>
				<?php esc_html_e( 'Load More Insights', 'healthpulse-insights' ); ?>
			</button>
		</div>
	</section>
</main>

<?php
get_footer();