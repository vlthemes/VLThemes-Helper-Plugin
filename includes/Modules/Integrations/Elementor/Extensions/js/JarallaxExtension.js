(function ($) {
	'use strict';

	class JarallaxExtension {
		constructor() {
			this.initialized = false;
			this.speed = 0.8;
			this.resizeCallbacks = [];
			this.resizeTimer = null;
			this.init();
		}

		init() {
			$(window).on('resize orientationchange load', () => this.triggerResize());
			$(() => {
				this.initJarallax();
				this.setupHandlers();
			});
		}

		triggerResize() {
			clearTimeout(this.resizeTimer);
			this.resizeTimer = setTimeout(() => {
				this.resizeCallbacks.forEach(cb => cb());
			}, 250);
		}

		debounceResize(cb) {
			if (typeof cb === 'function' && !this.resizeCallbacks.includes(cb)) {
				this.resizeCallbacks.push(cb);
			}
		}

		isMobile() {
			return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
		}

		initJarallax() {
			if (typeof jarallax === 'undefined') {
				console.warn('Jarallax not loaded');
				return;
			}

			if (this.isMobile()) {
				console.info('Jarallax disabled on mobile');
				return;
			}

			const els = document.querySelectorAll('.jarallax, .elementor-section.jarallax, .elementor-column.jarallax > .elementor-column-wrap, .elementor-container.jarallax');
			if (!els.length) return;

			jarallax(els, { speed: this.speed });
			this.initialized = true;
			console.info('Jarallax Extension initialized');
		}

		refresh() {
			if (!this.initialized || typeof jarallax === 'undefined') return;

			const els = document.querySelectorAll('.jarallax, .elementor-section.jarallax, .elementor-column.jarallax > .elementor-column-wrap, .elementor-container.jarallax');
			if (els.length) {
				jarallax(els, 'destroy');
				jarallax(els, { speed: this.speed });
			}
		}

		setupHandlers() {
			this.debounceResize(() => this.refresh());

			$(window).on('elementor/frontend/init', () => {
				if (window.elementorFrontend?.hooks) {
					elementorFrontend.hooks.addAction('frontend/element_ready/global', () => {
						setTimeout(() => this.refresh(), 100);
					});
				}
			});
		}
	}

	new JarallaxExtension();

})(jQuery);
