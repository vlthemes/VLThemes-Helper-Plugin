(function () {
	'use strict';

	const initCustomCss = () => {
		if (typeof elementor === 'undefined') return;

		// Add page-level custom CSS
		const addPageCustomCss = () => {
			const customCSS = elementor.settings.page.model.get('vlt_custom_css');

			if (!customCSS) return;

			const pageSelector = `.elementor-page-${elementor.config.document.id}`;
			const processedCSS = customCSS.replace(/selector/g, pageSelector);

			// Remove existing style
			const existingStyle = elementor.$previewContents.find('#vlt-custom-css-page');
			if (existingStyle.length) {
				existingStyle.remove();
			}

			// Add new style to preview
			const styleElement = jQuery('<style>', {
				id: 'vlt-custom-css-page',
				text: processedCSS
			});

			elementor.$previewContents.find('head').append(styleElement);
		};

		// Add element-level custom CSS
		const addCustomCss = (css, context) => {
			if (!context?.model) return css;

			const { model } = context;
			const customCSS = model.get('settings')?.get('vlt_custom_css');

			if (!customCSS) return css;

			const isDocument = model.get('elType') === 'document';
			const selector = isDocument
				? elementor.config.document.settings.cssWrapperSelector
				: `.elementor-element.elementor-element-${model.get('id')}`;

			return css + '\n' + customCSS.replace(/selector/g, selector);
		};

		// Register hooks and events
		elementor.hooks.addFilter('editor/style/styleText', addCustomCss);
		elementor.settings.page.model.on('change:vlt_custom_css', addPageCustomCss);
		elementor.on('preview:loaded', addPageCustomCss);
	};

	jQuery(window).on('elementor:init', initCustomCss);

})();
