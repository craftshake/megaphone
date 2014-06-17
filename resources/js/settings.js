(function($) {

$('#resetKey').click(function() {
	Craft.postActionRequest('megaphone/resetKey', function(response) {
	    $('#connectionString').val(Craft.getSiteUrl() + 'admin/actions/megaphone/run?key=' + response.key);
	});
});

})(jQuery);
