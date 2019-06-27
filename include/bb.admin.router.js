var AdminApp = Backbone.Router.extend(
{
	routes:
	{
		"admin/profile/:action": "handle_profile",
		/*"admin/registration/:action": "handle_registration",*/
	},

	handle_profile: function(action)
	{
		myAdminView.loadPage(script_fea_models.api_url, "admin/profile/" + action);
	}
});

new AdminApp();