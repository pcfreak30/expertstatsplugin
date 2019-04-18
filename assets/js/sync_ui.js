(function ($) {
	$(document).on('tab_changed', function (e, tab) {
		var submit = $('#submit').parent();
		if ('sync' === tab) {
			submit.fadeOut();
			return;
		}
		submit.fadeIn();
	})
	$(function () {
		$('#sync .button-primary').on('click', startSync);
	})

	function startSync () {

	}
})(jQuery);