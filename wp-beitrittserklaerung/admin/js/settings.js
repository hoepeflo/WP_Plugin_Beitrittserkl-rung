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

	function syncRequiredState($row) {
		var $visible = $row.find('.bse-field-visible');
		var $required = $row.find('.bse-field-required');

		if (!$visible.is(':checked')) {
			$required.prop('checked', false).prop('disabled', true);
		} else {
			$required.prop('disabled', false);
		}
	}

	$('table.widefat.striped tbody tr').each(function () {
		syncRequiredState($(this));
	});

	$(document).on('change', '.bse-field-visible', function () {
		syncRequiredState($(this).closest('tr'));
	});
})(jQuery);
