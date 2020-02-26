var AdminApp = Backbone.Router.extend(
{
	routes:
	{
		"admin/profile/:action": "handle_profile",
		/*"admin/registration/:action": "handle_registration",*/
		"admin/posts/:action": "handle_posts",
		"admin/posts/:action/:action": "handle_posts",
	},

	handle_profile: function(action)
	{
		myAdminView.loadPage(script_fea_models.api_url, "admin/profile/" + action);
	},

	handle_posts: function(action, action2)
	{
		if(action2 != null)
		{
			action += "/" + action2;
		}

		myAdminView.loadPage(script_fea_models.api_url, "admin/posts/" + action);
	}
});

new AdminApp();