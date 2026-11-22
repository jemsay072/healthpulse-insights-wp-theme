/**
 * HealthPulse Insights: front-end modules.
 * Path: wp-content/themes/healthpulse-insights/assets/js/main.js
 *
 * Modules: search drawer + live search, mobile category menu, newsletter
 * scroll + signup, article topic filter + "Load More".
 * Depends on the localized global `healthpulseAjax` ({ ajaxUrl, nonce, strings }).
 *
 * Markup contract for front-page.php (Batch 2):
 *   #hp-article-grid     data-category="all" data-page="1" data-exclude="12,34" (optional)
 *   [data-hp-filter]     buttons/links with data-hp-filter="<category-slug|all>", class "hp-filter-btn"
 *   #hp-load-more        "Load More Insights" button
 *   #hp-grid-status      optional aria-live region
 */
(function () {
	'use strict';

	var cfg = window.healthpulseAjax || {};
	var strings = cfg.strings || {};
	var reduceMotion = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

	/* ---------- Utilities ---------- */

	function $(selector, context) {
		return (context || document).querySelector(selector);
	}

	function $$(selector, context) {
		return Array.prototype.slice.call((context || document).querySelectorAll(selector));
	}

	function t(key, fallback) {
		return strings[key] || fallback;
	}

	function debounce(fn, wait) {
		var timer;
		return function () {
			var args = arguments;
			var ctx = this;
			window.clearTimeout(timer);
			timer = window.setTimeout(function () {
				fn.apply(ctx, args);
			}, wait);
		};
	}

	/**
	 * POST to admin-ajax.php. Always resolves with the parsed WP JSON envelope
	 * ({ success, data }); rejects only on network failure or abort.
	 */
	function post(action, data, signal) {
		var body = new URLSearchParams();
		body.append('action', action);
		body.append('nonce', cfg.nonce || '');
		Object.keys(data || {}).forEach(function (key) {
			body.append(key, data[key]);
		});

		return fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString(),
			signal: signal
		}).then(function (response) {
			return response.json().catch(function () {
				return { success: false, data: {} };
			});
		});
	}

	function setOpen(button, panel, open) {
		button.setAttribute('aria-expanded', open ? 'true' : 'false');
		panel.classList.toggle('is-open', open);
	}

	function isSafeUrl(url) {
		return /^https?:\/\//i.test(url || '');
	}

	/* ---------- Search drawer + instant live search ---------- */

	function initSearch() {
		var toggle = $('#search-toggle');
		var drawer = $('#live-search-drawer');
		var input = $('#live-search-input');
		var form = $('#live-search-form');
		var results = $('#live-search-results');
		var status = $('#live-search-status');
		var viewAll = $('#live-search-all');

		if (!toggle || !drawer || !input || !results) {
			return;
		}

		var controller = null;
		var lastQuery = '';

		function isOpen() {
			return drawer.classList.contains('is-open');
		}

		function open() {
			setOpen(toggle, drawer, true);
			input.focus();
		}

		function close(returnFocus) {
			setOpen(toggle, drawer, false);
			if (returnFocus) {
				toggle.focus();
			}
		}

		function viewAllUrl(query) {
			var base = form ? form.action : window.location.origin + '/';
			return base + (base.indexOf('?') === -1 ? '?' : '&') + 's=' + encodeURIComponent(query);
		}

		function reset() {
			lastQuery = '';
			results.textContent = '';
			status.textContent = '';
			if (viewAll) {
				viewAll.hidden = true;
			}
		}

		function showMessage(message) {
			results.textContent = '';
			status.textContent = message;
		}

		function renderResults(data, query) {
			var items = (data && data.results) || [];
			var total = (data && data.total) || 0;
			results.textContent = '';

			if (viewAll) {
				viewAll.href = isSafeUrl(data && data.view_all) ? data.view_all : viewAllUrl(query);
				viewAll.hidden = false;
			}

			if (!items.length) {
				status.textContent = t('noResults', 'No results found for “%s”.').replace('%s', query);
				return;
			}

			status.textContent = total + ' ' + (total === 1 ? t('result', 'result') : t('results', 'results'));

			items.forEach(function (item) {
				var li = document.createElement('li');
				var link = document.createElement('a');
				var meta = document.createElement('span');
				var badge = document.createElement('span');
				var title = document.createElement('span');

				link.href = isSafeUrl(item.url) ? item.url : '#';
				link.className = 'block rounded-lg px-3 py-3 transition-colors hover:bg-ice-tint focus-visible:bg-ice-tint';

				meta.className = 'mb-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500';

				badge.className = item.type === 'resource'
					? 'rounded-full border border-sky-200 bg-ice-tint px-2 py-0.5 font-bold text-med-blue-dark'
					: 'rounded-full bg-med-blue px-2 py-0.5 font-bold text-white';
				badge.textContent = item.type_label || (item.type === 'resource' ? 'Resource' : 'Article');
				meta.appendChild(badge);

				if (item.category) {
					var cat = document.createElement('span');
					cat.className = 'font-semibold text-slate-600';
					cat.textContent = item.category;
					meta.appendChild(cat);
				}

				if (item.read_time) {
					var time = document.createElement('span');
					var icon = document.createElement('i');
					icon.className = 'fa-regular fa-clock mr-1';
					icon.setAttribute('aria-hidden', 'true');
					time.appendChild(icon);
					time.appendChild(document.createTextNode(item.read_time));
					meta.appendChild(time);
				}

				title.className = 'block font-serif text-lg font-semibold leading-snug text-slate-deep';
				title.textContent = item.title;

				link.appendChild(meta);
				link.appendChild(title);
				li.appendChild(link);
				results.appendChild(li);
			});
		}

		var runSearch = debounce(function () {
			var query = input.value.trim();

			if (query.length < 2) {
				if (controller) {
					controller.abort();
				}
				reset();
				return;
			}
			if (query === lastQuery) {
				return;
			}
			lastQuery = query;

			if (controller) {
				controller.abort();
			}
			controller = 'AbortController' in window ? new AbortController() : null;

			if (viewAll) {
				viewAll.href = viewAllUrl(query);
				viewAll.hidden = false;
			}
			status.textContent = t('searching', 'Searching…');

			post('healthpulse_live_search', { query: query }, controller ? controller.signal : undefined)
				.then(function (response) {
					if (input.value.trim() !== query) {
						return; // A newer query is already in flight.
					}
					if (!response || !response.success) {
						lastQuery = '';
						showMessage((response && response.data && response.data.message) || t('error', 'Something went wrong. Please try again.'));
						return;
					}
					renderResults(response.data, query);
				})
				.catch(function (error) {
					if (error && error.name === 'AbortError') {
						return;
					}
					lastQuery = '';
					showMessage(t('error', 'Something went wrong. Please try again.'));
				});
		}, 280);

		toggle.addEventListener('click', function () {
			if (isOpen()) {
				close(false);
			} else {
				open();
			}
		});

		input.addEventListener('input', runSearch);

		// Keyboard: Esc closes; arrow keys move between the input and results.
		drawer.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') {
				close(true);
				return;
			}
			if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') {
				return;
			}
			var links = $$('a', results);
			if (!links.length) {
				return;
			}
			if (document.activeElement === input) {
				if (event.key === 'ArrowDown') {
					event.preventDefault();
					links[0].focus();
				}
				return;
			}
			var index = links.indexOf(document.activeElement);
			if (index === -1) {
				return;
			}
			event.preventDefault();
			if (event.key === 'ArrowDown') {
				(links[index + 1] || links[index]).focus();
			} else if (index === 0) {
				input.focus();
			} else {
				links[index - 1].focus();
			}
		});

		// Click outside closes the drawer.
		document.addEventListener('click', function (event) {
			if (isOpen() && !drawer.contains(event.target) && !toggle.contains(event.target)) {
				close(false);
			}
		});
	}

	/* ---------- Mobile category menu ---------- */

	function initMobileNav() {
		var toggle = $('#nav-toggle');
		var panel = $('#primary-nav-panel');
		if (!toggle || !panel) {
			return;
		}
		var icon = $('i', toggle);

		function set(open) {
			setOpen(toggle, panel, open);
			if (icon) {
				icon.classList.toggle('fa-bars', !open);
				icon.classList.toggle('fa-xmark', open);
			}
		}

		toggle.addEventListener('click', function () {
			set(toggle.getAttribute('aria-expanded') !== 'true');
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
				set(false);
				toggle.focus();
			}
		});

		panel.addEventListener('click', function (event) {
			if (event.target.closest && event.target.closest('a')) {
				set(false);
			}
		});

		window.addEventListener('resize', debounce(function () {
			if (window.innerWidth >= 768) {
				set(false);
			}
		}, 150));
	}

	/* ---------- Subscribe button: smooth-scroll to #newsletter ---------- */

	function initNewsletterScroll() {
		$$('[data-scroll-newsletter]').forEach(function (link) {
			link.addEventListener('click', function (event) {
				var target = $('#newsletter');
				if (!target) {
					return; // Falls back to the plain #newsletter anchor.
				}
				event.preventDefault();
				target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });

				var field = $('#newsletter-email');
				if (field) {
					window.setTimeout(function () {
						field.focus({ preventScroll: true });
					}, reduceMotion ? 0 : 700);
				}
			});
		});
	}

	/* ---------- Newsletter signup (zero reload) ---------- */

	function initNewsletterForm() {
		var form = $('#newsletter-form');
		var statusEl = $('#newsletter-status');
		if (!form || !statusEl) {
			return;
		}

		var email = $('input[type="email"]', form);
		var button = $('button[type="submit"]', form);
		var honeypot = $('[name="hp_website"]', form);
		var idleLabel = button ? button.innerHTML : '';
		var busy = false;

		function show(message, type) {
			statusEl.textContent = message;
			statusEl.classList.remove('text-green-700', 'text-red-700');
			statusEl.classList.add(type === 'success' ? 'text-green-700' : 'text-red-700');
		}

		form.addEventListener('submit', function (event) {
			event.preventDefault();
			if (busy || !email) {
				return;
			}

			var value = email.value.trim();
			if (!value || !email.checkValidity()) {
				show(t('invalidEmail', 'Please enter a valid email address.'), 'error');
				email.focus();
				return;
			}

			busy = true;
			if (button) {
				button.disabled = true;
				button.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2" aria-hidden="true"></i>' + t('subscribing', 'Subscribing…');
			}
			statusEl.textContent = '';

			post('healthpulse_subscribe_newsletter', {
				email: value,
				hp_website: honeypot ? honeypot.value : ''
			})
				.then(function (response) {
					var message = response && response.data && response.data.message;
					if (response && response.success) {
						show(message || 'Subscribed!', 'success');
						form.reset();
					} else {
						show(message || t('error', 'Something went wrong. Please try again.'), 'error');
					}
				})
				.catch(function () {
					show(t('error', 'Something went wrong. Please try again.'), 'error');
				})
				.then(function () {
					busy = false;
					if (button) {
						button.disabled = false;
						button.innerHTML = idleLabel;
					}
				});
		});
	}

	/* ---------- Article topic filter + Load More ---------- */

	function initArticleFilter() {
		var grid = $('#hp-article-grid');
		if (!grid) {
			return;
		}

		var buttons = $$('[data-hp-filter]');
		var more = $('#hp-load-more');
		var status = $('#hp-grid-status');
		var moreLabel = more ? more.innerHTML : '';
		var busy = false;

		var state = {
			category: grid.getAttribute('data-category') || 'all',
			paged: parseInt(grid.getAttribute('data-page'), 10) || 1,
			exclude: grid.getAttribute('data-exclude') || ''
		};

		function setActive(slug) {
			buttons.forEach(function (button) {
				var active = button.getAttribute('data-hp-filter') === slug;
				button.classList.toggle('is-active', active);
				button.setAttribute('aria-pressed', active ? 'true' : 'false');
			});
		}

		function updateMore(hasMore) {
			if (more) {
				more.hidden = !hasMore;
			}
		}

		function load(category, paged, append) {
			if (busy) {
				return;
			}
			busy = true;
			grid.setAttribute('aria-busy', 'true');

			if (more) {
				more.disabled = true;
				more.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2" aria-hidden="true"></i>' + t('loading', 'Loading…');
			}

			var previousCount = $$('article', grid).length;

			post('healthpulse_filter_articles', {
				category: category,
				paged: paged,
				exclude: state.exclude
			})
				.then(function (response) {
					if (!response || !response.success) {
						throw new Error('request failed');
					}
					var data = response.data;

					if (append) {
						grid.insertAdjacentHTML('beforeend', data.html);
					} else {
						grid.innerHTML = data.html;
					}

					state.category = category;
					state.paged = paged;
					grid.setAttribute('data-category', category);
					grid.setAttribute('data-page', String(paged));
					grid.setAttribute('data-max-pages', String(data.max_pages));
					updateMore(!!data.has_more);

					if (status) {
						status.textContent = data.found + ' ' + (data.found === 1 ? t('article', 'article') : t('articles', 'articles'));
					}

					if (append) {
						var firstNew = $$('article', grid)[previousCount];
						var link = firstNew && $('a', firstNew);
						if (link) {
							link.focus({ preventScroll: true });
						}
					}
				})
				.catch(function () {
					if (status) {
						status.textContent = t('error', 'Something went wrong. Please try again.');
					}
					setActive(state.category);
				})
				.then(function () {
					busy = false;
					grid.removeAttribute('aria-busy');
					if (more) {
						more.disabled = false;
						more.innerHTML = moreLabel;
					}
				});
		}

		buttons.forEach(function (button) {
			button.addEventListener('click', function (event) {
				event.preventDefault();
				var slug = button.getAttribute('data-hp-filter') || 'all';
				if (busy || slug === state.category) {
					return;
				}
				setActive(slug);
				load(slug, 1, false);
			});
		});

		if (more) {
			more.addEventListener('click', function () {
				load(state.category, state.paged + 1, true);
			});
		}

		setActive(state.category);
	}

	/* ---------- Init ---------- */

	function init() {
		initSearch();
		initMobileNav();
		initNewsletterScroll();
		initNewsletterForm();
		initArticleFilter();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();