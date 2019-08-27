var AdminView = Backbone.View.extend(
{
	el: jQuery("body"),

	initialize: function()
	{
		this.model.on("change:redirect", this.do_redirect, this);
		this.model.on('change:message', this.display_message, this);
		this.model.on("change:next_request", this.next_request, this);
		this.model.on("change:admin_response", this.admin_response, this);
	},

	events:
	{
		"click nav li": "toggle_submenu",
		"click nav a": "change_view",
		"click .view_data i": "toggle_data_helper",
		"submit form": "submit_form",
	},

	toggle_submenu: function(e)
	{
		var dom_obj = jQuery(e.currentTarget);

		dom_obj.toggleClass('open').siblings("li").removeClass('open');
	},

	change_view: function(e)
	{
		this.hide_message();
		this.toggle_submenu(e);

		var dom_obj = jQuery(e.currentTarget),
			api_url = dom_obj.attr('data-api-url') || '';

		dom_obj.addClass('active').siblings("a").removeClass('active');
		dom_obj.parents("li").addClass('active').siblings("li").removeClass('active').children("a").removeClass('active');

		if(api_url != '')
		{
			var action = dom_obj.attr('href').replace('#', '');

			this.loadPage(api_url, action);
		}
	},

	toggle_data_helper: function(e)
	{
		var dom_obj = jQuery(e.currentTarget);

		this.toggle_data(dom_obj.parents("tr").siblings("tr").find(".view_data .fa-eye-slash"));
		this.toggle_data(dom_obj);
	},

	toggle_data: function(dom_obj)
	{
		dom_obj.toggleClass('fa-eye-slash fa-eye').parents("tr").next("tr").toggleClass('hide');
	},

	do_redirect: function()
	{
		var response = this.model.get('redirect');

		if(response != '')
		{
			location.href = response;

			this.model.set({'redirect': ''});
		}
	},

	hide_message: function()
	{
		jQuery(".error:not(.hide), .updated:not(.hide), .aside.left, .aside.right").addClass('hide');
	},

	display_message: function()
	{
		var response = this.model.get('message');

		if(response != '')
		{
			myAdminView.hide_message();

			if(this.model.get('success') == true)
			{
				jQuery(".updated.hide").removeClass('hide').children("p").html(response);
			}

			else
			{
				jQuery(".error.hide").removeClass('hide').children("p").html(response);
			}

			scroll_to_top();

			jQuery(".mf_form button[type='submit']").removeClass('disabled').removeClass('loading').removeAttr('disabled');

			this.model.set({'message': ''});
		}
	},

	next_request: function()
	{
		var response = this.model.get("next_request");

		if(response != '')
		{
			var api_url = this.model.get("api_url") || '';

			if(api_url != '')
			{
				this.loadPage(api_url, response);

				this.model.set({"next_request" : ""});
			}
		}
	},

	display_container: function(dom_container)
	{
		dom_container.removeClass('hide').siblings("div").addClass('hide');
	},

	loadPage: function(api_url, action)
	{
		this.hide_message();

		var dom_container = jQuery("#" + action.replace(/\//g, '_'));

		if(dom_container.length > 0)
		{
			this.display_container(dom_container);
		}

		else
		{
			jQuery(".admin_container .loading").removeClass('hide').siblings("div").addClass('hide');
		}

		this.model.getPage(api_url, action);
	},

	submit_form: function(e)
	{
		var dom_obj = jQuery(e.currentTarget),
			action = dom_obj.attr('data-action'),
			api_url = dom_obj.attr('data-api-url') || '';

		if(api_url != '')
		{
			this.model.submitForm(api_url, action, dom_obj.serialize());

			return false;
		}
	},

	admin_response: function()
	{
		var response = this.model.get('admin_response'),
			template = response.template,
			container = response.container,
			dom_template = jQuery("#template_" + template),
			dom_container = jQuery("#" + container),
			html = '';

		switch(template)
		{
			case 'admin_profile_edit':
				var html = _.template(dom_template.html())(response);

				dom_container.children("div").html(html);

				this.display_container(dom_container);

				if(typeof init_media_library === 'function')
				{
					init_media_library();
				}
			break;

			case 'admin_posts_list':
				var amount = response.list.length,
					dom_template = jQuery("#template_" + template),
					dom_container = jQuery("#" + template);

				if(amount > 0)
				{
					html = _.template(dom_template.html())(response);
				}

				else
				{
					html = _.template(jQuery("#template_" + template + "_message").html())('');
				}

				dom_container.children("div").html(html);

				myAdminView.display_container(dom_container);
			break;

			case 'admin_posts_edit':
				var self = this,
					dom_template = jQuery("#template_" + template),
					dom_container = jQuery("#" + template);

				html = _.template(dom_template.html())(response);

				dom_container.children("div").html(html);

				myAdminView.display_container(dom_container);

				if(typeof select_option === 'function')
				{
					select_option();
				}

				if(typeof render_required === 'function')
				{
					render_required();
				}
			break;
		}
	}
});

var myAdminView = new AdminView({model: new AdminModel()});