/* ========================================
 * Header/Footer Builder Admin Scripts
 * ======================================== */
document.addEventListener('DOMContentLoaded', function () {
	'use strict';

	/* ========================================
	 * Add "View All Templates" button
	 * ======================================== */
	const addNewButton = document.querySelector('.page-title-action');
	if (addNewButton && typeof hfb_admin_data !== 'undefined') {
		// Create the new button.
		const customButton = document.createElement('a');
		customButton.href = hfb_admin_data.hfb_edit_url;
		customButton.textContent = hfb_admin_data.hfb_view_all_text;
		customButton.className = 'page-title-action';
		customButton.style.marginLeft = '10px';
		addNewButton.insertAdjacentElement('afterend', customButton);
	}
});
