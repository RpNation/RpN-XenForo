if (typeof variable !== 'object') BbTabs = {};
!function($, window, document, _undefined)
{
	"use strict";

	BbTabs.BbTabs = XF.Click.newHandler({
		eventNameSpace: 'BbTabsClick',

		options: {
			tabContainerId: '',
			tabId: ''
		},

		init: function(e)
		{
		},

		click: function(e)
		{
			var tab = $(e.currentTarget);
			var container = $('#' + this.options.tabContainerId);
			container.children('.tabsBbTitles').children('.bbTab').removeClass('active');
			tab.parent().addClass("active");
			container.children('.tabsBbContent').children('.bbTabContent').css('display', 'none');
			container.children('.tabsBbContent').children('.bbTabContent#' + this.options.tabId).css('display', 'list-item');
		}
	});

	XF.Click.register('bbTab', 'BbTabs.BbTabs');
} (jQuery, window, document);