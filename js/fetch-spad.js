jQuery(document).ready(function($) {
	const spadLanguageSelect = $('#fetch_spad_language');
	const timezoneContainer = $('#timezone-container');

	function updateTimezoneVisibility() {
		let showTimezone = false;

		if (spadLanguageSelect.val() === 'english') {
			showTimezone = true;
		}

		if (showTimezone) {
			timezoneContainer.show();
		} else {
			timezoneContainer.hide();
		}
	}

	// Initial update
	updateTimezoneVisibility();

	// Listen for changes
	spadLanguageSelect.on('change', updateTimezoneVisibility);
});
