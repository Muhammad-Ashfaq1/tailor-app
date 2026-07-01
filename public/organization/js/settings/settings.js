/*
 | Settings forms — AJAX submit for every `form.settings-form` on the page.
 | Uses the shared notyf toasts + the CSRF-aware axios defaults from app.js.
 | Submits FormData (so file uploads + nested notification names both work),
 | maps 422 errors inline, and disables the submit button while saving.
 */
(function () {
    'use strict';

    function clearErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback[data-field]').forEach(function (el) {
            el.textContent = '';
        });
    }

    function setLoading(button, loading) {
        if (!button) {
            return;
        }
        if (loading) {
            button.dataset.originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving…';
        } else {
            button.disabled = false;
            if (button.dataset.originalHtml) {
                button.innerHTML = button.dataset.originalHtml;
            }
        }
    }

    // Laravel error keys are dotted (events.order_placed.email); inputs use
    // bracket names (events[order_placed][email]) — convert to find the input.
    function dottedToBracket(key) {
        var parts = key.split('.');
        return parts
            .map(function (part, i) { return i === 0 ? part : '[' + part + ']'; })
            .join('');
    }

    function showErrors(form, errors) {
        Object.keys(errors).forEach(function (key) {
            var message = errors[key][0];
            var input = form.querySelector('[name="' + dottedToBracket(key) + '"]')
                || form.querySelector('[name="' + key + '"]');
            if (input) {
                input.classList.add('is-invalid');
            }
            var feedback = form.querySelector('.invalid-feedback[data-field="' + key + '"]');
            if (feedback) {
                feedback.textContent = message;
            }
            notyf.failure(message);
        });
    }

    document.querySelectorAll('form.settings-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearErrors(form);

            var button = form.querySelector('button[type="submit"]');
            setLoading(button, true);

            axios.post(form.getAttribute('action'), new FormData(form))
                .then(function (response) {
                    var data = response.data || {};
                    notyf.success(data.message || 'Settings saved.');

                    // Branding returns the new logo URL — refresh the preview.
                    if (data.logo_url !== undefined) {
                        var preview = form.querySelector('[data-logo-preview]');
                        if (preview && data.logo_url) {
                            preview.src = data.logo_url;
                            preview.classList.remove('d-none');
                        }
                    }
                })
                .catch(function (error) {
                    var res = error.response;
                    if (res && res.status === 422 && res.data && res.data.errors) {
                        showErrors(form, res.data.errors);
                    } else {
                        notyf.failure((res && res.data && res.data.message) || 'An error occurred while saving.');
                    }
                })
                .finally(function () {
                    setLoading(button, false);
                });
        });
    });
})();
