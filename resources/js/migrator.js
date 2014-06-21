var Megaphone = {};

(function($) {

Megaphone.Migrator = Garnish.Base.extend(
{
	$graphic: null,
	$status: null,
	$errorDetails: null,
	data: null,
	strings: null,

	operation: null,

	init: function(operation, remote, key)
	{
		this.$graphic = $('#graphic');
		this.$status = $('#status');

		this.operation = operation;

		this.data = {
			remote: remote,
			key: key
		};

		this.postActionRequest('megaphone/'+ this.operation +'/prepare');
	},

	updateStatus: function(msg)
	{
		this.$status.html(msg);
	},

	showError: function(msg)
	{
		this.updateStatus(msg);
		this.$graphic.addClass('error');
	},

	postActionRequest: function(action)
	{
		var data = {
			data: this.data
		};

		Craft.postActionRequest(action, data, $.proxy(function(response, textStatus, jqXHR)
		{
			console.log(textStatus);
			console.log(response);
			if (textStatus == 'success')
			{
				this.onSuccessResponse(response);
			}
			else
			{
				this.onErrorResponse(jqXHR);
			}

		}, this), {
			complete: $.noop
		});
	},

	onSuccessResponse: function(response)
	{
		if (response.data)
		{
			this.data = response.data;
		}

		if (response.errorDetails)
		{
			this.$errorDetails = response.errorDetails;
		}

		if (response.nextStatus)
		{
			this.updateStatus(response.nextStatus);
		}

		if (response.nextAction)
		{
			this.postActionRequest('megaphone/' + this.operation + '/' + response.nextAction);
		}

		if (response.finished)
		{
			var rollBack = false;

			if (response.rollBack)
			{
				rollBack = true;
			}

			this.onFinish(response.returnUrl, rollBack);
		}
	},

	onErrorResponse: function(jqXHR)
	{
		this.$graphic.addClass('error');
		var errorText = Craft.t('An error has occurred.') + '<br /><p>' + jqXHR.statusText + '</p><br /><p>' + jqXHR.responseText + '</p>';

		this.updateStatus(errorText);
	},

	onFinish: function(returnUrl, rollBack)
	{
		if (this.$errorDetails)
		{
			this.$graphic.addClass('error');
			var errorText = Craft.t('Megaphone was unable to migrate the database :(') + '<br /><p>';

			if (rollBack)
			{
				errorText += Craft.t('The database has been restored to the state it was in before the attempted migration.') + '</p><br /><p>';
			}
			else
			{
				errorText += Craft.t('The database has not been touched.') + '</p><br /><p>';
			}

			errorText += this.$errorDetails + '</p>';
			errorText += '<p><a href="' + Craft.getCpUrl() + '" class="btn">Back</a></p>';
			this.updateStatus(errorText);
		}
		else
		{
			this.updateStatus(Craft.t('All done!'));
			this.$graphic.addClass('success');

			// Redirect to the Dashboard in half a second
			setTimeout(function() {
				if (returnUrl) {
					window.location = Craft.getUrl(returnUrl);
				}
				else {
					window.location = Craft.getUrl('dashboard');
				}
			}, 500);
		}
	}
});


})(jQuery);
