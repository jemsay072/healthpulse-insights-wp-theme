



<?php
/**
 * The header: two-tier editorial masthead, live search drawer, sticky category nav.
 *
 * Path: wp-content/themes/healthpulse-insights/header.php
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
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'bg-off-white font-sans text-slate-700 antialiased' ); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'healthpulse-insights' ); ?></a>

	<header id="masthead" class="site-header relative z-50 border-b border-slate-200 bg-white">

		<!-- Tier 1: utility + masthead bar -->
		<div class="mx-auto grid max-w-7xl grid-cols-[1fr_auto_1fr] items-center gap-3 px-4 py-4 sm:px-6 lg:px-8">

			<!-- Left: date + kicker (hidden on small mobile) -->
			<div class="min-w-0">
				<div class="hidden sm:block">
					<p class="truncate text-xs font-semibold text-slate-deep">
						<time datetime="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>"><?php echo esc_html( date_i18n( 'l, F j, Y' ) ); ?></time>
					</p>
					<p class="mt-0.5 hidden truncate text-[10px] font-bold uppercase tracking-[0.18em] text-med-blue md:block">
						<?php esc_html_e( 'Medical & Healthcare Intelligence', 'healthpulse-insights' ); ?>
					</p>
				</div>
			</div>

			<!-- Center: emblem + brand -->
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="flex items-center gap-2.5 sm:gap-3">
				<?php healthpulse_the_emblem( 'h-9 w-9 shrink-0 sm:h-10 sm:w-10' ); ?>
				<span class="font-serif text-xl font-bold leading-none tracking-tight text-slate-deep sm:text-2xl lg:text-3xl">
					<?php echo esc_html( $hp_brand ); ?>
				</span>
			</a>

			<!-- Right: search toggle + subscribe -->
			<div class="flex items-center justify-end gap-2">
				<button
					id="search-toggle"
					type="button"
					class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-deep transition-colors hover:border-med-blue hover:text-med-blue"
					aria-expanded="false"
					aria-controls="live-search-drawer"
					aria-label="<?php esc_attr_e( 'Toggle search', 'healthpulse-insights' ); ?>"
				>
					<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
					<span class="hidden sm:inline"><?php esc_html_e( 'Search', 'healthpulse-insights' ); ?></span>
				</button>

				<a
					href="#newsletter"
					data-scroll-newsletter
					class="inline-flex h-10 items-center rounded-full bg-[#0284C7] px-4 text-sm font-semibold text-white transition-colors hover:bg-[#0369A1]"
				>
					<?php esc_html_e( 'Subscribe', 'healthpulse-insights' ); ?>
				</a>
			</div>
		</div>

		<!-- Slide-down instant live search drawer -->
		<div id="live-search-drawer" class="hp-drawer border-t border-slate-200 bg-white">
			<div class="hp-drawer-inner">
				<div class="mx-auto max-w-3xl px-4 py-6 sm:px-6">

					<form id="live-search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="relative">
						<label for="live-search-input" class="sr-only"><?php esc_html_e( 'Search articles and resources', 'healthpulse-insights' ); ?></label>
						<i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"></i>
						<input
							id="live-search-input"
							type="search"
							name="s"
							autocomplete="off"
							placeholder="<?php esc_attr_e( 'Search articles, research and resources', 'healthpulse-insights' ); ?>"
							aria-controls="live-search-results"
							class="w-full rounded-xl border border-slate-300 bg-off-white py-3.5 pl-11 pr-4 text-base text-slate-deep placeholder:text-slate-400 focus:border-med-blue focus:outline-none focus:ring-2 focus:ring-med-blue/30"
						>
					</form>

					<p id="live-search-status" class="mt-3 min-h-[1rem] text-xs font-medium text-slate-500" role="status" aria-live="polite"></p>

					<ul id="live-search-results" class="mt-1 divide-y divide-slate-100"></ul>

					<a id="live-search-all" href="<?php echo esc_url( home_url( '/' ) ); ?>" hidden class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-med-blue hover:text-med-blue-dark">
						<?php esc_html_e( 'View all results', 'healthpulse-insights' ); ?>
						<i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
					</a>
				</div>
			</div>
		</div>
	</header><!-- #masthead -->

	<!-- Tier 2: sticky category navigation (sibling of the header so sticky spans the whole page) -->
	<nav id="site-navigation" class="hp-sticky-nav sticky top-0 z-40 border-b border-slate-200 bg-[#F8FAFC]" aria-label="<?php esc_attr_e( 'Primary', 'healthpulse-insights' ); ?>">
		<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

			<button
				id="nav-toggle"
				type="button"
				class="flex w-full items-center justify-between py-3 text-sm font-semibold text-slate-deep md:hidden"
				aria-expanded="false"
				aria-controls="primary-nav-panel"
			>
				<span><?php esc_html_e( 'Browse topics', 'healthpulse-insights' ); ?></span>
				<i class="fa-solid fa-bars" aria-hidden="true"></i>
			</button>

			<div id="primary-nav-panel" class="hp-drawer hp-nav-panel">
				<div class="hp-drawer-inner">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'menu_id'        => 'primary-menu',
							'container'      => false,
							'depth'          => 1,
							'menu_class'     => 'm-0 flex list-none flex-col p-0 pb-3 md:flex-row md:justify-center md:gap-8 md:pb-0',
							'fallback_cb'    => 'healthpulse_fallback_menu',
						)
					);
					?>
				</div>
			</div>
		</div>
	</nav><!-- #site-navigation -->