(function ($) {
	'use strict';

	var frame;

	$('#bse-select-logo').on('click', function (e) {
		e.preventDefault();

		if (frame) {
			frame.open();
			return;
		}

		frame = wp.media({
			title: 'Logo auswählen',
			button: { text: 'Verwenden' },
			multiple: false,
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			$('#pdf_logo_id').val(attachment.id);
			$('#bse-logo-preview').html('<img src="' + attachment.url + '" style="max-height:80px;" alt="" />');
		});

		frame.open();
	});

	$('#bse-remove-logo').on('click', function (e) {
		e.preventDefault();
		$('#pdf_logo_id').val('');
		$('#bse-logo-preview').empty();
	});
})(jQuery);
