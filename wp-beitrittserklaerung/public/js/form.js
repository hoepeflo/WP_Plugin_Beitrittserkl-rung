(function () {
	'use strict';

	function closestForm(element) {
		while (element && element.nodeName !== 'FORM') {
			element = element.parentElement;
		}
		return element;
	}

	function showMessage(wrapper, message, isSuccess) {
		var box = wrapper.querySelector('.bse-form-messages');
		if (!box) {
			return;
		}
		box.textContent = message;
		box.hidden = false;
		box.classList.toggle('bse-form-messages--success', !!isSuccess);
		if (!isSuccess) {
			box.classList.remove('bse-form-messages--success');
		}
	}

	function clearFieldErrors(form) {
		form.querySelectorAll('.bse-field-error').forEach(function (el) {
			el.hidden = true;
			el.textContent = '';
		});
	}

	function showFieldErrors(form, errors) {
		if (!errors) {
			return;
		}
		Object.keys(errors).forEach(function (key) {
			var errorEl = form.querySelector('.bse-field-error[data-field="' + key + '"]');
			if (errorEl) {
				errorEl.textContent = errors[key];
				errorEl.hidden = false;
			}
		});
	}

	function handleSubmit(event) {
		var form = event.target;
		if (!form.matches('[data-bse-form="1"]')) {
			return;
		}

		event.preventDefault();

		var wrapper = form.closest('.bse-form-wrapper');
		var submitBtn = form.querySelector('.bse-submit');
		var formData = new FormData(form);

		clearFieldErrors(form);
		showMessage(wrapper, '', false);
		wrapper.querySelector('.bse-form-messages').hidden = true;

		if (submitBtn) {
			submitBtn.disabled = true;
		}

		fetch(bseForm.ajaxUrl, {
			method: 'POST',
			body: formData,
			credentials: 'same-origin',
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (payload) {
				if (payload.success) {
					form.hidden = true;
					var thankYou = wrapper.querySelector('.bse-thank-you');
					if (thankYou) {
						thankYou.hidden = false;
					}
					return;
				}

				var message =
					(payload.data && payload.data.message) ||
					bseForm.i18n.submitError;
				showMessage(wrapper, message, false);

				if (payload.data && payload.data.errors) {
					showFieldErrors(form, payload.data.errors);
				}

				if (typeof window.turnstile !== 'undefined' && window.turnstile.reset) {
					var widget = form.querySelector('.cf-turnstile');
					if (widget) {
						window.turnstile.reset(widget);
					}
				}
			})
			.catch(function () {
				showMessage(wrapper, bseForm.i18n.submitError, false);
			})
			.finally(function () {
				if (submitBtn) {
					submitBtn.disabled = false;
				}
			});
	}

	document.addEventListener('submit', handleSubmit);
})();
