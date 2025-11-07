/**
 * Jarallax Module
 * Handles parallax scrolling effects using Jarallax library
 *
 * @global jarallax - External library loaded via wp_enqueue_script
 */

/* global jarallax */

export default class JarallaxModule {
	constructor({ isMobileDevice, debounceResize }) {
		this.isMobileDevice = isMobileDevice;
		this.debounceResize = debounceResize;
		this.initialized = false;
		this.defaultSpeed = 0.8;
	}

	/**
	 * Initialize Jarallax on elements
	 */
	init() {
		// Check if Jarallax library is loaded
		if (typeof jarallax === 'undefined') {
			console.warn('Jarallax library not loaded');
			return;
		}

		// Skip Jarallax on mobile devices (performance optimization)
		if (this.isMobileDevice()) {
			console.info('Jarallax disabled on mobile device');
			return;
		}

		// Find all jarallax elements
		const elements = this.getJarallaxElements();

		if (elements.length === 0) {
			return;
		}

		// Initialize jarallax on all elements
		jarallax(elements, {
			speed: this.defaultSpeed
		});

		this.initialized = true;
		console.info(`Jarallax initialized on ${elements.length} elements`);
	}

	/**
	 * Get all elements that should have jarallax
	 * @returns {NodeList}
	 */
	getJarallaxElements() {
		const selectors = [
			'.jarallax',
			'.elementor-section.jarallax',
			'.elementor-column.jarallax > .elementor-column-wrap',
			'.elementor-container.jarallax'
		];

		return document.querySelectorAll(selectors.join(', '));
	}

	/**
	 * Destroy jarallax instances
	 */
	destroy() {
		if (!this.initialized || typeof jarallax === 'undefined') {
			return;
		}

		const elements = this.getJarallaxElements();

		if (elements.length > 0) {
			jarallax(elements, 'destroy');
			this.initialized = false;
			console.info('Jarallax destroyed');
		}
	}

	/**
	 * Refresh jarallax calculations
	 */
	refresh() {
		if (!this.initialized || typeof jarallax === 'undefined') {
			return;
		}

		const elements = this.getJarallaxElements();

		if (elements.length > 0) {
			// Destroy and reinit for proper recalculation
			jarallax(elements, 'destroy');
			jarallax(elements, {
				speed: this.defaultSpeed
			});
		}
	}

	/**
	 * Setup refresh handlers for dynamic content
	 */
	setupRefreshHandlers() {
		// Debounced resize refresh
		this.debounceResize(() => {
			this.refresh();
		});

		// Refresh when Elementor widgets are rendered
		if (window.elementorFrontend && window.elementorFrontend.hooks && window.elementorFrontend.hooks.addAction) {
			window.elementorFrontend.hooks.addAction('frontend/element_ready/global', () => {
				// Small delay to ensure DOM is ready
				setTimeout(() => {
					this.refresh();
				}, 100);
			});
		}
	}
}
