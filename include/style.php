<?php

if(!defined('ABSPATH'))
{
	header("Content-Type: text/css; charset=utf-8");

	$folder = str_replace("/wp-content/plugins/mf_front_end_admin/include", "/", dirname(__FILE__));

	require_once($folder."wp-load.php");
}

echo "@media all
{";

	$setting_fea_content_width = get_option('setting_fea_content_width');

	if($setting_fea_content_width > 0)
	{
		echo ".is_desktop #mf-content > div
		{
			max-width: ".$setting_fea_content_width."px;
		}";
	}

	echo ".page-template-template_admin article section.logged_in
	{
		margin-bottom: 1em;
	}

	.aside .widget.fea_menu
	{
		font-size: unset;
	}

	.fea_menu > ul
	{
		display: -webkit-box;
		display: -ms-flexbox;
		display: -webkit-flex;
		display: flex;
		-webkit-box-flex-wrap: wrap;
		-webkit-flex-wrap: wrap;
		-ms-flex-wrap: wrap;
		flex-wrap: wrap;
		list-style: none;
	}

		.is_tablet .fea_menu > ul, .is_desktop .fea_menu > ul
		{
			font-size: 1.2em;
		}

		.fea_menu > ul > li
		{
			background: #eee;
			border-radius: .5em;
			display: block;
			-webkit-box-flex: 0 0 10em;
			-webkit-flex: 0 0 10em;
			-ms-flex: 0 0 10em;
			flex: 0 0 10em;
			margin: 0 1em 1em 0;
			position: relative;
			text-align: center;
		}

			.is_mobile .fea_menu > ul > li
			{
				-webkit-box-flex: 0 0 23%;
				-webkit-flex: 0 0 23%;
				-ms-flex: 0 0 23%;
				flex: 0 0 23%;
				margin: 0 2% 2% 0;
			}

			.fea_menu > ul > li:last-of-type
			{
				margin-right: 0;
			}

			.fea_menu > ul > li.active
			{
				background: #d6d6d6;
			}

				.fea_menu li a, .fea_menu li > span
				{
					border-bottom: 0;
					display: block;
					padding: 1em .5em;
				}

					.fea_menu li a.active, .fea_menu li a:hover
					{
						text-shadow: 0 0 1em rgba(0, 0, 0, .3);
					}

						.fea_menu li i
						{
							color: rgba(0, 0, 0, .6);
							display: block;
							font-size: 3em;
						}

							.fea_menu li i + span
							{
								color: rgba(0, 0, 0, .6);
								display: inline-block;
								margin-top: .5em;
							}

			.fea_menu ul ul
			{
				background: #fff;
				border-radius: .5em;
				box-shadow: 0 0 1em rgba(0, 0, 0, .3);
				display: none;
				list-style: none;
				position: absolute;
				width: 100%;
				z-index: 1;
			}

				.fea_menu li.open ul
				{
					display: block;
				}

				.fea_menu ul ul:before
				{
					border: .7em solid transparent;
					border-bottom-color: transparent;
					border-bottom-color: #fff;
					content: '';
					left: 50%;
					position: absolute;
					top: -1.3em;
					transform: translateX(-50%);
				}

				.fea_menu ul ul li
				{
					margin-top: 0;
				}

					.fea_menu ul ul li + li a
					{
						padding-top: .5em;
					}

	.admin_container
	{
		min-height: 10em;
		position: relative;
	}

		#wrapper .tablenav-pages .displaying-num
		{
			margin-right: .5em;
		}

			#wrapper .tablenav-pages .button i
			{
				display: block;
				margin: .3em 0 .2em;
			}

			#wrapper .tablenav-pages .form_textfield
			{
				display: inline-block;
				width: 3em;
			}

				#wrapper .tablenav-pages .form_textfield input
				{
					border-color: transparent;
					line-height: 1;
					text-align: center;
				}

					#wrapper .tablenav-pages .form_textfield input:hover, #wrapper .tablenav-pages .form_textfield input:focus
					{
						border-color: #e1e1e1;
					}

		.admin_container h1 .form_button
		{
			display: inline-block;
			position: relative;
			top: -.2em;
		}

			.admin_container h1 .form_button a
			{
				display: inline-block;
				font-size: .5em;
				margin-left: .5em;
				font-weight: normal;
				border-radius: .5em;
				background: #ccc;
				padding: .3em .5em;
				line-height: 1.3;
			}

		.is_mobile .admin_container form
		{
			display: -webkit-box;
			display: -ms-flexbox;
			display: -webkit-flex;
			display: flex;
			-moz-box-direction: column;
			-webkit-box-direction: column;
			-ms-flexbox-direction: column;
			flex-direction: column;
		}

			.admin_container .context_normal
			{
				clear: left;
				float: left;
				width: 60%;
			}

			.admin_container .context_side
			{
				clear: right;
				float: right;
				width: calc(40% - .8em);
			}

			.is_mobile .admin_container .context_normal, .is_mobile .admin_container .context_side
			{
				-webkit-box-ordinal-group: 1;
				-webkit-order: 1;
				-ms-flex-order: 1;
				order: 1;
				width: 100%;
			}

				.is_mobile .admin_container .context_normal + .context_side
				{
					-webkit-box-ordinal-group: 2;
					-webkit-order: 2;
					-ms-flex-order: 2;
					order: 2;
				}

				.admin_container .meta_box
				{
					border: 1px solid #ccc;
					border-radius: .3em;
					margin-bottom: .8em;
					overflow: hidden;
				}

					.admin_container .meta_box h2
					{
						border-bottom: 1px solid #ccc;
						margin-bottom: 0 !important;
						padding: 0 .5em .5em;
					}

					.admin_container .meta_box > div
					{
						padding: .5em;
					}

						.admin_container .description
						{
							font-size: .8em;
							font-style: italic;
						}
}";