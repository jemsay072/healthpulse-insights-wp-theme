<?php
/**
 * Template Name: About
 *
 * About page: editorial mission, standards, and a newsletter call to action.
 * Applies automatically to a page with the slug "about", or select it under
 * Page Attributes > Template.
 *
 * Path: wp-content/themes/healthpulse-insights/page-about.php
 *
 * @package HealthPulse_Insights
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hp_standards = array(
	array(
		'icon'  => 'fa-solid fa-scale-balanced',
		'title' => __( 'Independent reporting', 'healthpulse-insights' ),
		'text'  => __( 'Our coverage is editorially independent. Sponsors and partners never influence what we publish or how we frame it.', 'healthpulse-insights' ),
	),
	array(
		'icon'  => 'fa-solid fa-user-doctor',
		'title' => __( 'Clinical review', 'healthpulse-insights' ),
		'text'  => __( 'Articles on clinical topics are checked by a qualified medical reviewer, credited on the story when review has taken place.', 'healthpulse-insights' ),
	),
	array(
		'icon'  => 'fa-solid fa-book-open',
		'title' => __( 'Transparent sourcing', 'healthpulse-insights' ),
		'text'  => __( 'We link to primary research, regulatory documents and datasets so readers can verify claims for themselves.', 'healthpulse-insights' ),
	),
);
?>

<main id="primary" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<header class="border-b border-slate-200 bg-white">
				<div class="mx-auto max-w-4xl px-4 py-14 text-center sm:px-6 lg:py-20">
					<p class="text-sm font-semibold text-med-blue"><?php esc_html_e( 'About us', 'healthpulse-insights' ); ?></p>
					<h1 class="mt-3 font-serif text-4xl font-bold leading-tight tracking-tight text-slate-deep sm:text-5xl lg:text-6xl">
						<?php echo esc_html( get_the_title() ); ?>
					</h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="mx-auto mt-5 max-w-2xl font-serif text-xl leading-relaxed text-slate-600">
							<?php echo esc_html( get_the_excerpt() ); ?>
						</p>
					<?php else : ?>
						<p class="mx-auto mt-5 max-w-2xl font-serif text-xl leading-relaxed text-slate-600">
							<?php esc_html_e( 'Clear, careful reporting on the research, policy and technology shaping modern healthcare.', 'healthpulse-insights' ); ?>
						</p>
					<?php endif; ?>
				</div>
			</header>

			<div class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
				<?php if ( '' !== trim( get_the_content() ) ) : ?>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				<?php else : ?>
					<div class="entry-content">
						<h2><?php esc_html_e( 'Our mission', 'healthpulse-insights' ); ?></h2>
						<p><?php esc_html_e( 'Healthcare moves quickly, and the evidence behind it is often buried in journals, regulations and press releases. We exist to make that information clear, accurate and useful for the people who work in healthcare and the readers who depend on it.', 'healthpulse-insights' ); ?></p>
						<p><?php esc_html_e( 'Edit this page in WordPress to replace this default text with your own story.', 'healthpulse-insights' ); ?></p>
					</div>
				<?php endif; ?>
			</div>

			<section class="border-y border-sky-100 bg-ice-tint" aria-labelledby="hp-standards-heading">
				<div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
					<h2 id="hp-standards-heading" class="text-center font-serif text-3xl font-bold text-slate-deep sm:text-4xl">
						<?php esc_html_e( 'Our editorial standards', 'healthpulse-insights' ); ?>
					</h2>

					<ul class="mt-10 grid gap-6 md:grid-cols-3">
						<?php foreach ( $hp_standards as $hp_standard ) : ?>
							<li class="rounded-xl border border-sky-100 bg-white p-6">
								<span class="flex h-11 w-11 items-center justify-center rounded-full bg-ice-tint text-lg text-med-blue">
									<i class="<?php echo esc_attr( $hp_standard['icon'] ); ?>" aria-hidden="true"></i>
								</span>
								<h3 class="mt-4 font-serif text-xl font-semibold text-slate-deep"><?php echo esc_html( $hp_standard['title'] ); ?></h3>
								<p class="mt-2 text-sm leading-relaxed text-slate-600"><?php echo esc_html( $hp_standard['text'] ); ?></p>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>

			<section class="mx-auto max-w-3xl px-4 py-14 text-center sm:px-6" aria-labelledby="hp-about-cta-heading">
				<h2 id="hp-about-cta-heading" class="font-serif text-3xl font-bold text-slate-deep">
					<?php esc_html_e( 'Stay in the loop', 'healthpulse-insights' ); ?>
				</h2>
				<p class="mt-3 text-slate-600"><?php esc_html_e( 'Join our newsletter for the week’s most important healthcare reporting.', 'healthpulse-insights' ); ?></p>
				<a href="#newsletter" data-scroll-newsletter class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#0284C7] px-7 py-3 text-base font-semibold text-white transition-colors hover:bg-[#0369A1]">
					<i class="fa-regular fa-envelope" aria-hidden="true"></i>
					<?php esc_html_e( 'Subscribe to the newsletter', 'healthpulse-insights' ); ?>
				</a>
			</section>
		</article>
		<?php
	endwhile;
	?>
</main>

<?php
get_footer();