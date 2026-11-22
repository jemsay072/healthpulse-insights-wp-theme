<?php
/**
 * Resource Hub archive (clinical_resource).
 *
 * Path: wp-content/themes/healthpulse-insights/archive-clinical_resource.php
 *
 * @package HealthPulse_Insights
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hp_topics = get_terms(
	array(
		'taxonomy'   => 'resource_topic',
		'hide_empty' => true,
	)
);

$hp_archive_url = get_post_type_archive_link( 'clinical_resource' );
$hp_pill        = 'shrink-0 rounded-full border px-4 py-2 text-sm font-semibold transition-colors';
$hp_pill_off    = 'border-slate-300 bg-white text-slate-700 hover:border-med-blue';
$hp_pill_on     = 'border-[#0284C7] bg-[#0284C7] text-white';
?>

<main id="primary" class="site-main">

	<header class="border-b border-sky-100 bg-ice-tint">
		<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
			<p class="inline-flex items-center gap-2 text-sm font-semibold text-med-blue-dark">
				<i class="fa-regular fa-folder-open" aria-hidden="true"></i>
				<?php esc_html_e( 'Resource Hub', 'healthpulse-insights' ); ?>
			</p>
			<h1 class="mt-3 font-serif text-4xl font-bold tracking-tight text-slate-deep sm:text-5xl">
				<?php esc_html_e( 'Resources & Whitepapers', 'healthpulse-insights' ); ?>
			</h1>
			<p class="mt-4 max-w-2xl text-lg leading-relaxed text-slate-600">
				<?php esc_html_e( 'In-depth reports, guides and whitepapers on clinical practice, health policy and digital health, ready to read or download.', 'healthpulse-insights' ); ?>
			</p>

			<?php if ( ! empty( $hp_topics ) && ! is_wp_error( $hp_topics ) && $hp_archive_url ) : ?>
				<nav class="-mx-4 mt-8 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:px-0" aria-label="<?php esc_attr_e( 'Resource topics', 'healthpulse-insights' ); ?>">
					<a href="<?php echo esc_url( $hp_archive_url ); ?>" class="<?php echo esc_attr( $hp_pill . ' ' . ( is_post_type_archive( 'clinical_resource' ) ? $hp_pill_on : $hp_pill_off ) ); ?>"<?php echo is_post_type_archive( 'clinical_resource' ) ? ' aria-current="page"' : ''; ?>>
						<?php esc_html_e( 'All resources', 'healthpulse-insights' ); ?>
					</a>
					<?php foreach ( $hp_topics as $hp_topic ) : ?>
						<?php
						$hp_term_url = get_term_link( $hp_topic );
						if ( is_wp_error( $hp_term_url ) ) {
							continue;
						}
						$hp_is_current = is_tax( 'resource_topic', $hp_topic->slug );
						?>
						<a href="<?php echo esc_url( $hp_term_url ); ?>" class="<?php echo esc_attr( $hp_pill . ' ' . ( $hp_is_current ? $hp_pill_on : $hp_pill_off ) ); ?>"<?php echo $hp_is_current ? ' aria-current="page"' : ''; ?>>
							<?php echo esc_html( $hp_topic->name ); ?>
						</a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</div>
	</header>

	<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
		<?php if ( have_posts() ) : ?>
			<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card', 'resource' );
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
				<i class="fa-regular fa-file-lines text-3xl text-slate-400" aria-hidden="true"></i>
				<p class="mt-3 text-slate-600"><?php esc_html_e( 'No resources have been published yet.', 'healthpulse-insights' ); ?></p>
				<?php if ( current_user_can( 'publish_posts' ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=clinical_resource' ) ); ?>" class="mt-4 inline-flex items-center rounded-full bg-[#0284C7] px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#0369A1]">
						<?php esc_html_e( 'Add the first resource', 'healthpulse-insights' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();