/**
 * @tableofcontents
 *
 * 1. auto resize
 * 2. enable indent
 * 3. unmask password
 * 4. validate form
 * 5. validate search
 * 6. init
 */

(function ($)
{
	'use strict';

	/** @section 1. auto resize */

	$.fn.autoResize = function (options)
	{
		/* extend options */

		if (rs.plugins.autoResize.options !== options)
		{
			options = $.extend({}, rs.plugins.autoResize.options, options || {});
		}

		/* return this */

		return this.each(function ()
		{
			/* listen for focus and input */

			$(this).on('focus input', function ()
			{
				var textarea = this,
					value = textarea.value,
					newlines = value.split(options.eol).length;

				/* newlines hack */

				if (textarea.rows < newlines)
				{
					textarea.rows = newlines;
				}

				/* general resize */

				while (textarea.clientHeight === textarea.scrollHeight && textarea.rows > 1)
				{
					textarea.rows -= 1;
				}
				while (textarea.clientHeight < textarea.scrollHeight && textarea.rows < options.limit)
				{
					textarea.rows += 1;
				}
			}).css(
			{
				overflow: options.overflow,
				resize: options.resize
			});
		});
	};

	/** @section 2. enable indent */

	$.fn.enableIndent = function (options)
	{
		/* extend options */

		if (rs.plugins.enableIndent.options !== options)
		{
			options = $.extend({}, rs.plugins.enableIndent.options, options || {});
		}

		/* return this */

		return this.each(function ()
		{
			/* listen for keydown */

			$(this).on('keydown', function (event)
			{
				var textarea = this,
					textareaValue = textarea.value,
					selectionStart = textarea.selectionStart,
					selectionEnd = textarea.selectionEnd,
					selectionText = textareaValue.slice(selectionStart, selectionEnd),
					selectionBefore = textareaValue.slice(0, selectionStart),
					selectionAfter = textareaValue.slice(selectionEnd),
					eol = options.eol,
					indent = options.indent,
					counter = 0;

				if ('selectionStart' in textarea)
				{
					if (event.which === 9)
					{
						/* remove indent */

						if (event.shiftKey)
						{
							/* if selection */

							if (selectionText.length)
							{
								textarea.value = selectionBefore + selectionText.replace(window.RegExp(eol + indent, 'g'), function ()
								{
									counter++;
									return eol;
								}).replace(indent, function ()
								{
									counter++;
									return false;
								}) + selectionAfter;
								textarea.selectionEnd = selectionEnd - (counter * indent.length);
								textarea.selectionStart = selectionStart;
							}

							/* else without selection */

							else if (textareaValue.slice(selectionStart - indent.length).indexOf(indent) === 0)
							{
								textarea.value = textareaValue.slice(0, selectionStart - indent.length) + textareaValue.slice(selectionStart);
								textarea.selectionStart = textarea.selectionEnd = selectionStart - indent.length;
							}
						}

						/* else add indent */

						else
						{
							/* if selection */

							if (selectionText.length)
							{
								textarea.value = selectionBefore + indent + selectionText.replace(window.RegExp(eol, 'g'), function ()
								{
									counter++;
									return eol + indent;
								}) + selectionAfter;
								counter++;
								textarea.selectionEnd = selectionEnd + (counter * indent.length);
								textarea.selectionStart = selectionStart;
							}

							/* else without selection */

							else
							{
								textarea.value = selectionBefore + indent + selectionText + selectionAfter;
								textarea.selectionStart = textarea.selectionEnd = selectionStart + indent.length;
							}
						}
						event.preventDefault();
					}
				}
			});
		});
	};

	/** @section 3. unmask password */

	$.fn.unmaskPassword = function (options)
	{
		/* extend options */

		if (rs.plugins.unmaskPassword.options !== options)
		{
			options = $.extend({}, rs.plugins.unmaskPassword.options, options || {});
		}

		/* return this */

		return this.each(function ()
		{
			/* listen for keydown and blur */

			$(this).on('keydown blur', function (event)
			{
				var field = this;

				if (event.ctrlKey && event.altKey && event.which === options.keyCode.unmask)
				{
					field.type = 'text';
				}
				else
				{
					field.type = 'password';
				}
			});
		});
	};

	/** @section 4. validate form */

	$.fn.validateForm = function (options)
	{
		/* extend options */

		if (rs.plugins.validateForm.options !== options)
		{
			options = $.extend({}, rs.plugins.validateForm.options, options || {});
		}

		/* return this */

		return this.each(function ()
		{
			/* validate form */

			$(this).on('submit input related', function (event)
			{
				var form = $(this),
					buttonSubmit = form.find(options.element.buttonSubmit),
					field = form.find(options.element.field),
					fieldAll = field,
					prefix = form.filter('[class^="rs-admin"]').length ? 'rs-admin-' : 'rs-';

				/* filter related fields */

				if (event.type === 'related')
				{
					field = field.filter('[data-related]').removeAttr('data-related');
				}

				/* else focused fields */

				else if (event.type === 'input')
				{
					field = field.filter(':focus');
				}

				/* validate fields */

				field.each(function ()
				{
					var that = $(this),
						thatNative = that[0],
						thatEditable = that.attr('contenteditable'),
						thatLabel = that.siblings('label'),
						className = prefix + 'js-note-error ' + prefix + 'field-note ' + prefix + 'is-error',
						validity = 'valid',
						thatValue = '',
						thatRequired = '',
						message = '';

					/* editable content */

					if (thatEditable)
					{
						thatValue = that.html();

						/* check empty value */

						if (!thatValue)
						{
							validity = 'invalid';
							message = rs.language.input_empty + rs.language.point;
						}
					}

					/* missing support */

					else if (!rs.support.checkValidity)
					{
						thatValue = that.val();
						thatRequired = that.attr('required');

						/* check required value */

						if (thatRequired && !thatValue)
						{
							validity = 'invalid';
							message = rs.language.input_empty + rs.language.point;
						}
					}

					/* use native validation */

					else if (!thatNative.checkValidity())
					{
						validity = 'invalid';
						message = thatNative.validationMessage;
					}

					/* handle invalid */

					if (validity === 'invalid')
					{
						that.addClass(className).trigger('invalid');
						if (message && options.message)
						{
							thatLabel.addClass(prefix + 'label-message').attr('data-message', message);
						}
					}

					/* else handle valid */

					else
					{
						that.removeClass(className).trigger('valid');
						if (options.message)
						{
							thatLabel.removeClass(prefix + 'label-message').removeAttr('data-message');
						}
					}
				});

				/* trigger error and prevent submit */

				if (fieldAll.hasClass(prefix + 'js-note-error'))
				{
					form.trigger('error');
					buttonSubmit.attr('disabled', 'disabled');

					/* auto focus on submit */

					if (event.type === 'submit' && options.autoFocus)
					{
						fieldAll.filter('.' + prefix + 'js-note-error').first().focus();
					}

					/* vibrate feedback */

					if (event.type === 'submit' && rs.support.vibrate && typeof options.vibrate === 'number')
					{
						window.navigator.vibrate(options.vibrate);
					}
					event.preventDefault();
				}

				/* else trigger success */

				else
				{
					form.trigger('success');
					buttonSubmit.removeAttr('disabled');
				}
			})
			.on('unvalidate reset', function ()
			{
				var form = $(this),
					buttonSubmit = form.find(options.element.buttonSubmit),
					field = form.find(options.element.field);

				field.removeClass('rs-js-note-error rs-is-error');
				field.siblings('label').removeClass('rs-label-message').removeAttr('data-message');
				buttonSubmit.removeAttr('disabled');
			})
			.attr('novalidate', 'novalidate');
		});
	};

	/** @section 5. validate search */

	$.fn.validateSearch = function (options)
	{
		/* extend options */

		if (rs.plugins.validateSearch.options !== options)
		{
			options = $.extend({}, rs.plugins.validateSearch.options, options || {});
		}

		/* return this */

		return this.each(function ()
		{
			/* listen for submit */

			$(this).on('submit', function (event)
			{
				var form = $(this),
					field = form.find(options.element.field),
					fieldValue = field.val(),
					fieldPlaceholder = field.attr('placeholder'),
					message = rs.language.input_incorrect + rs.language.exclamation_mark,
					timeout = '';

				/* prevent multiple timeout */

				if (fieldPlaceholder === message)
				{
					clearTimeout(timeout);
					event.preventDefault();
				}

				/* else prematurely terminate search */

				else if (fieldValue.length < 3)
				{
					field.val(null).attr('placeholder', message);
					timeout = setTimeout(function ()
					{
						field.attr('placeholder', fieldPlaceholder).focus();
					}, options.duration);
					event.preventDefault();
				}
			});
		});
	};

	/** @section 6. init */

	$(function ()
	{
		if (rs.plugins.autoResize.init)
		{
			$(rs.plugins.autoResize.selector).autoResize(rs.plugins.autoResize.options);
		}
		if (rs.plugins.enableIndent.init)
		{
			$(rs.plugins.enableIndent.selector).enableIndent();
		}
		if (rs.plugins.unmaskPassword.init)
		{
			$(rs.plugins.unmaskPassword.selector).unmaskPassword(rs.plugins.unmaskPassword.options);
		}
		if (rs.plugins.validateForm.init)
		{
			$(rs.plugins.validateForm.selector).validateForm(rs.plugins.validateForm.options);
		}
		if (rs.plugins.validateSearch.init)
		{
			$(rs.plugins.validateSearch.selector).validateSearch(rs.plugins.validateSearch.options);
		}
	});
})(window.jQuery);
