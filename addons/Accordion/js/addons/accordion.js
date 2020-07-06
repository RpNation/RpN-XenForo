if (typeof variable !== 'object') Accordion = {};
!function($, window, document, _undefined)
{
	"use strict";

	Accordion.Accordion = XF.Element.newHandler({
		init: function()
		{
			var $e = this.$target,
				d = $e.attr('data-duration');

			$e.children('dd:not(.accordion-slide-open)').hide();
			$e.children('dt').click(function () {
				//e.preventDefault(); // If Slide Menu Title has an URL, it will prevent a new page to be opened.
				var $this = $(this),
					$target = $this.next(),
					activeClass = 'accordion-slide-active';

				if (!$target.hasClass(activeClass)) {
					$e.children('.accordion > dt').removeClass(activeClass);
					$this.addClass(activeClass);
					$e.children('.accordion > dd').removeClass(activeClass).slideUp(d);
					$target.addClass(activeClass).slideDown(d);
				} else if ($target.hasClass(activeClass)) {
					$this.removeClass(activeClass);
					$target.removeClass(activeClass).slideUp(d);
				}
			});
		}
	});

	XF.Element.register('accordion', 'Accordion.Accordion');
} (jQuery, window, document);