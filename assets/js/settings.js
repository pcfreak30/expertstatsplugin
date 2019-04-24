(function ($) {
	$(function () {
		$('.button-action').click(startSync);
	})

	function startSync () {
		var button = $(this);
		button.hide();
		var progressbar = $('#import_progress');
		var progressLabel = progressbar.children(".progress-label");
		var nonce = $(".wrap > #_wpnonce").val();

		progressbar.progressbar({
			value: false,
			change: function () {
				progressLabel.text(progressbar.progressbar('value') + '%');
			},
			complete: function () {
				progressLabel.text("Complete!").delay(3).fadeOut();
			}
		});

		function process (offset = 0) {
			return $.post(window.ajaxurl, { action: window.import_config.ajax_action, _wpnonce: nonce, offset: offset })
				.done(function (data) {
					if (data.done || data.error) {
						progressbar.progressbar('value', 100);
						button.show();
						if (data.error) {
							alert('ajax error: ' + data.error);
						}
						return Promise.resolve();
					}
					progressbar.progressbar('value', data.offset / data.total);
					return process(data.offset)
				}).fail(function () {
					alert('ajax error');
					button.show();
				});
		}

		progressbar.slideDown();
		process();
	}
})(jQuery);
