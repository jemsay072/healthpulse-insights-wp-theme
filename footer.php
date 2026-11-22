<?php
/**
 * The footer: AJAX newsletter banner (#newsletter) + dark publication footer.
 *
 * Path: wp-content/themes/healthpulse-insights/footer.php
 *
 * The #newsletter section lives here so the masthead "Subscribe" button
 * smooth-scrolls to it on every page.
 *
 * @package HealthPulse_Insights
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hp_brand = get_bloginfo( 'name' );
if ( '' === trim( (string) $hp_brand ) ) {
	$hp_brand = 'HealthPulse Insights';
}

$hp_topics = get_categories(
	array(
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 5,
		'hide_empty' => true,
		'exclude'    => array( (int) get_option( 'default_category' ) ),
	)
);

$hp_resources_url = get_post_type_archive_link( 'clinical_resource' );
?>

	<footer id="colophon" class="site-footer">

		<!-- Newsletter banner -->
		<section id="newsletter" class="scroll-mt-24 border-t border-sky-100 bg-ice-tint" aria-labelledby="newsletter-heading">
			<div class="mx-auto max-w-3xl px-4 py-14 text-center sm:px-6 lg:py-16">
				<span class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-white px-3 py-1 text-xs font-semibold text-med-blue-dark">
					<i class="fa-regular fa-envelope" aria-hidden="true"></i>
					<?php esc_html_e( 'The Weekly Pulse', 'healthpulse-insights' ); ?>
				</span>

				<h2 id="newsletter-heading" class="mt-5 font-serif text-3xl font-semibold leading-tight text-slate-deep sm:text-4xl">
					<?php echo esc_html( healthpulse_mod( 'healthpulse_newsletter_heading' ) ); ?>
				</h2>

				<p class="mx-auto mt-3 max-w-xl text-base text-slate-600">
					<?php esc_html_e( 'Research summaries, policy shifts and digital health developments, curated for people who work in healthcare. Free to join.', 'healthpulse-insights' ); ?>
				</p>

				<form id="newsletter-form" class="relative mx-auto mt-8 flex max-w-xl flex-col gap-3 sm:flex-row" method="post" action="#newsletter">
					<label for="newsletter-email" class="sr-only"><?php esc_html_e( 'Email address', 'healthpulse-insights' ); ?></label>
					<input
						id="newsletter-email"
						type="email"
						name="email"
						required
						autocomplete="email"
						placeholder="<?php esc_attr_e( 'you@organization.com', 'healthpulse-insights' ); ?>"
						class="w-full flex-1 rounded-lg border border-slate-300 bg-white px-4 py-3 text-base text-slate-deep placeholder:text-slate-400 focus:border-med-blue focus:outline-none focus:ring-2 focus:ring-med-blue/30"
					>

					<!-- Honeypot: hidden from people, filled by bots. -->
					<div class="sr-only" aria-hidden="true">
						<label for="newsletter-website"><?php esc_html_e( 'Leave this field empty', 'healthpulse-insights' ); ?></label>
						<input id="newsletter-website" type="text" name="hp_website" tabindex="-1" autocomplete="off" value="">
					</div>

					<button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0284C7] px-6 py-3 text-base font-semibold text-white transition-colors hover:bg-[#0369A1] disabled:cursor-not-allowed disabled:opacity-70">
						<?php esc_html_e( 'Subscribe', 'healthpulse-insights' ); ?>
					</button>
				</form>

				<p id="newsletter-status" class="mt-4 min-h-[1.25rem] text-sm font-medium" role="status" aria-live="polite"></p>
			</div>
		</section>

		<!-- Dark publication footer -->
		<div class="bg-slate-deep text-slate-300">
			<div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-12 lg:px-8">

				<div class="lg:col-span-6">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="inline-flex items-center gap-3">
						<span class="rounded-xl bg-white/10 p-0.5">
							<?php healthpulse_the_emblem( 'h-10 w-10' ); ?>
						</span>
						<span class="font-serif text-2xl font-bold text-white"><?php echo esc_html( $hp_brand ); ?></span>
					</a>
					<p class="mt-5 max-w-md text-sm leading-relaxed text-slate-400">
						<?php echo nl2br( esc_html( healthpulse_mod( 'healthpulse_footer_description' ) ) ); ?>
					</p>
				</div>

				<div class="lg:col-span-3">
					<h2 class="font-sans text-sm font-bold text-white"><?php esc_html_e( 'Explore', 'healthpulse-insights' ); ?></h2>
					<?php if ( has_nav_menu( 'footer' ) ) : ?>
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer',
								'container'      => false,
								'depth'          => 1,
								'menu_class'     => 'mt-4 m-0 list-none space-y-2.5 p-0 text-sm',
								'fallback_cb'    => false,
							)
						);
						?>
					<?php else : ?>
						<ul class="m-0 mt-4 list-none space-y-2.5 p-0 text-sm">
							<li><a class="text-slate-300 transition-colors hover:text-white" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Latest Insights', 'healthpulse-insights' ); ?></a></li>
							<?php if ( $hp_resources_url ) : ?>
								<li><a class="text-slate-300 transition-colors hover:text-white" href="<?php echo esc_url( $hp_resources_url ); ?>"><?php esc_html_e( 'Resource Hub', 'healthpulse-insights' ); ?></a></li>
							<?php endif; ?>
							<li><a class="text-slate-300 transition-colors hover:text-white" href="#newsletter" data-scroll-newsletter><?php esc_html_e( 'Newsletter', 'healthpulse-insights' ); ?></a></li>
						</ul>
					<?php endif; ?>
				</div>

				<div class="lg:col-span-3">
					<h2 class="font-sans text-sm font-bold text-white"><?php esc_html_e( 'Topics', 'healthpulse-insights' ); ?></h2>
					<?php if ( ! empty( $hp_topics ) && ! is_wp_error( $hp_topics ) ) : ?>
						<ul class="m-0 mt-4 list-none space-y-2.5 p-0 text-sm">
							<?php foreach ( $hp_topics as $hp_topic ) : ?>
								<li>
									<a class="text-slate-300 transition-colors hover:text-white" href="<?php echo esc_url( get_category_link( $hp_topic ) ); ?>">
										<?php echo esc_html( $hp_topic->name ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="mt-4 text-sm text-slate-400"><?php esc_html_e( 'Topics appear here once articles are published.', 'healthpulse-insights' ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="border-t border-white/10">
				<div class="mx-auto max-w-7xl space-y-4 px-4 py-6 sm:px-6 lg:px-8">
					<p class="rounded-lg border border-white/10 bg-white/5 p-4 text-xs leading-relaxed text-slate-400">
						<strong class="font-semibold text-slate-200"><?php esc_html_e( 'Medical disclaimer.', 'healthpulse-insights' ); ?></strong>
						<?php echo esc_html( healthpulse_mod( 'healthpulse_medical_disclaimer' ) ); ?>
					</p>
					<p class="text-xs text-slate-500">
						&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $hp_brand ); ?>. <?php esc_html_e( 'All rights reserved.', 'healthpulse-insights' ); ?>
					</p>
				</div>
			</div>
		</div>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>