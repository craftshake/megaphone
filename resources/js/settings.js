(function($) {

$('#resetKey').on("click", function () {
	Craft.postActionRequest('megaphone/resetKey', function(response) {
	    $('#connectionString').val(Craft.getSiteUrl() + '?megaphone=' + response.key);
	});
});

$('#connectionString').attr('readonly', true);
$('#connectionString').on("click", function () {
	$(this).select();
})

})(jQuery);
