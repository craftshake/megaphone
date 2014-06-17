(function($) {

$('#resetKey').on("click", function () {
	Craft.postActionRequest('megaphone/resetKey', function(response) {
	    $('#connectionString').val(Craft.getSiteUrl() + 'admin/actions/megaphone/run?key=' + response.key);
	});
});

$('#connectionString').attr('readonly', true);
$('#connectionString').on("click", function () {
	$(this).select();
})

})(jQuery);
