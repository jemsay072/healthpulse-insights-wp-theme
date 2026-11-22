<?php
/**
 * Single resource / whitepaper.
 *
 * Path: wp-content/themes/healthpulse-insights/single-clinical_resource.php
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

		$hp_id       = get_the_ID();
		$hp_badge    = trim( (string) healthpulse_get_field( 'resource_type_badge', $hp_id, '' ) );
		$hp_file_url = trim( (string) healthpulse_get_field( 'resource_file_url', $hp_id, '' ) );
		$hp_pages    = trim( (string) healthpulse_get_field( 'resource_pages_count', $hp_id, '' ) );
		$hp_terms    = get_the_terms( $hp_id, 'resource_topic' );
		$hp_terms    = ( is_array( $hp_terms ) && ! empty( $hp_terms ) ) ? $hp_terms : array();
		$hp_archive  = get_post_type_archive_link( 'clinical_resource' );

		if ( '' === $hp_badge ) {
			$hp_badge = __( 'Resource', 'healthpulse-insights' );
		}
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<header class="border-b border-sky-100 bg-ice-tint">
				<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-14">
					<?php if ( $hp_archive ) : ?>
						<a href="<?php echo esc_url( $hp_archive ); ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-med-blue hover:text-med-blue-dark">
							<i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
							<?php esc_html_e( 'Resource Hub', 'healthpulse-insights' ); ?>
						</a>
					<?php endif; ?>

					<div class="mt-5 flex flex-wrap items-center gap-2 text-xs">
						<span class="inline-flex items-center gap-1 rounded-full border border-sky-200 bg-white px-3 py-1 font-bold text-med-blue-dark">
							<i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
							<?php echo esc_html( $hp_badge ); ?>
						</span>
						<?php foreach ( $hp_terms as $hp_term ) : ?>
							<?php $hp_term_url = get_term_link( $hp_term ); ?>
							<?php if ( ! is_wp_error( $hp_term_url ) ) : ?>
								<a href="<?php echo esc_url( $hp_term_url ); ?>" class="rounded-full bg-white px-3 py-1 font-semibold text-slate-600 transition-colors hover:text-med-blue">
									<?php echo esc_html( $hp_term->name ); ?>
								</a>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>

					<h1 class="mt-4 max-w-4xl font-serif text-4xl font-bold leading-tight tracking-tight text-slate-deep sm:text-5xl">
						<?php echo esc_html( get_the_title() ); ?>
					</h1>

					<?php if ( has_excerpt() ) : ?>
						<p class="mt-4 max-w-3xl font-serif text-xl leading-relaxed text-slate-600">
							<?php echo esc_html( get_the_excerpt() ); ?>
						</p>
					<?php endif; ?>
				</div>
			</header>

			<div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-12 lg:px-8">

				<div class="lg:col-span-8">
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="mb-10">
							<?php
							the_post_thumbnail(
								'healthpulse-hero',
								array(
									'class'   => 'w-full rounded-2xl object-cover',
									'loading' => 'eager',
								)
							);
							?>
						</figure>
					<?php endif; ?>

					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</div>

				<aside class="lg:col-span-4" aria-label="<?php esc_attr_e( 'Resource details', 'healthpulse-insights' ); ?>">
					<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-24">
						<h2 class="font-serif text-xl font-semibold text-slate-deep"><?php esc_html_e( 'About this resource', 'healthpulse-insights' ); ?></h2>

						<dl class="mt-4 space-y-3 text-sm">
							<div class="flex justify-between gap-4">
								<dt class="text-slate-500"><?php esc_html_e( 'Format', 'healthpulse-insights' ); ?></dt>
								<dd class="font-semibold text-slate-deep"><?php echo esc_html( $hp_badge ); ?></dd>
							</div>
							<?php if ( '' !== $hp_pages ) : ?>
								<div class="flex justify-between gap-4">
									<dt class="text-slate-500"><?php esc_html_e( 'Length', 'healthpulse-insights' ); ?></dt>
									<dd class="font-semibold text-slate-deep"><?php echo esc_html( $hp_pages ); ?></dd>
								</div>
							<?php endif; ?>
							<div class="flex justify-between gap-4">
								<dt class="text-slate-500"><?php esc_html_e( 'Published', 'healthpulse-insights' ); ?></dt>
								<dd class="font-semibold text-slate-deep">
									<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								</dd>
							</div>
							<div class="flex justify-between gap-4">
								<dt class="text-slate-500"><?php esc_html_e( 'Author', 'healthpulse-insights' ); ?></dt>
								<dd class="font-semibold text-slate-deep"><?php echo esc_html( get_the_author() ); ?></dd>
							</div>
						</dl>

						<?php if ( '' !== $hp_file_url ) : ?>
							<a href="<?php echo esc_url( $hp_file_url ); ?>" target="_blank" rel="noopener noreferrer" class="mt-6 flex w-full items-center justify-center gap-2 rounded-lg bg-[#0284C7] px-5 py-3 text-base font-semibold text-white transition-colors hover:bg-[#0369A1]">
								<i class="fa-solid fa-download" aria-hidden="true"></i>
								<?php esc_html_e( 'Download resource', 'healthpulse-insights' ); ?>
							</a>
						<?php else : ?>
							<p class="mt-6 rounded-lg bg-off-white px-4 py-3 text-center text-sm font-medium text-slate-500">
								<?php esc_html_e( 'The download file is not available yet.', 'healthpulse-insights' ); ?>
							</p>
						<?php endif; ?>
					</div>
				</aside>
			</div>
		</article>
		<?php
	endwhile;

	/* Related resources: same topic, excluding this one. */
	$hp_current_id = get_queried_object_id();
	$hp_topic_ids  = wp_get_post_terms( $hp_current_id, 'resource_topic', array( 'fields' => 'ids' ) );

	if ( ! empty( $hp_topic_ids ) && ! is_wp_error( $hp_topic_ids ) ) :
		$hp_related = new WP_Query(
			array(
				'post_type'      => 'clinical_resource',
				'post_status'    => 'publish',
				'posts_per_page' => 3,
				'post__not_in'   => array( $hp_current_id ),
				'no_found_rows'  => true,
				'tax_query'      => array(
					array(
						'taxonomy' => 'resource_topic',
						'field'    => 'term_id',
						'terms'    => $hp_topic_ids,
					),
				),
			)
		);

		if ( $hp_related->have_posts() ) :
			?>
			<section class="border-t border-slate-200 bg-white" aria-labelledby="hp-related-resources-heading">
				<div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
					<h2 id="hp-related-resources-heading" class="font-serif text-3xl font-bold text-slate-deep">
						<?php esc_html_e( 'Related resources', 'healthpulse-insights' ); ?>
					</h2>
					<div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
						<?php
						while ( $hp_related->have_posts() ) :
							$hp_related->the_post();
							get_template_part( 'template-parts/card', 'resource' );
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