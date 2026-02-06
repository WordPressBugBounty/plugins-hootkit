(function( $ ) {
	"use strict";

	/*** Icon Picker ***/

	$.fn.hootWidgetIconPicker = function() {
		return this.each(function() {

			var $self       = $(this),
				$picker_box = $self.siblings('.hoot-icon-picker-box'),
				$button     = $self.siblings('.hoot-icon-picked'),
				$preview    = $button.children('i'),
				$holder     = $picker_box.find('.hoot-icon-picker-hold'),
				$icons      = $picker_box.find('i');

			$button.on( "click", function() {
				$picker_box.trigger('hootclick');
			});

			$picker_box.on( "hootclick", function() {
				var $box = $(this),
					isOpen = $box.data('open');
				if ( isOpen ) {
					$box.data('open', false).hide();
					if ( $holder.length ) {
						$holder.html('');
					}
					$( 'body' ).off( 'mousedown', pboxautoclose );
				} else {
					$box.data('open', true).show();
					if ( $holder.length ) {
						var iconshtml = '',
							iconvalue = $self.val();
						if ( typeof hootkitDataFontawesome !== 'undefined' && typeof hootkitDataFontawesome.icons === 'object' ) {
							var sections = typeof hootkitDataFontawesome.sections === 'object' ? hootkitDataFontawesome.sections : {};
							$.each( hootkitDataFontawesome.icons, function( s_key, s_array ) {
								iconshtml += ( typeof sections[ s_key ] !== 'undefined' ) ? '<h4>' + sections[ s_key ] + '</h4>' : ( ( typeof s_key === 'string' ) ? '<h4>' + s_key.charAt(0).toUpperCase() + s_key.slice(1) + '</h4>'	: '<p></p>' );
								iconshtml += '<div class="hoot-icon-picker-list">';
								if ( typeof s_array === 'object' && s_array.length ) {
									$.each( s_array, function( iconkey, iconclass ) {
										var selected = ( iconvalue == iconclass ) ? ' selected' : '';
										iconshtml += '<i class="' + iconclass + selected + '" data-value="' + iconclass + '" data-category="' + s_key + '"></i>';
									});
								}
								iconshtml += '</div>';
							});
						}
						$holder.html( iconshtml );
					}
					$( 'body' ).on( 'mousedown', pboxautoclose );
				}
			});

			$icons.on("click", function() {
				handleIconClick($(this), $picker_box, $preview, $self);
			});
			$holder.on("click", "i", function() {
				handleIconClick($(this), $picker_box, $preview, $self);
			});

		});
	};
	function pboxautoclose(e) {
		if (!$(e.target).closest('.hoot-icon-picker-box, .hoot-icon-picked').length) {
			$('.hoot-icon-picker-box').filter(function() { return $(this).data('open') === true; }).trigger('hootclick');
		}
	}
	function handleIconClick($icon, $picker_box, $preview, $self) {
		var iconvalue = $icon.data('value');
		$picker_box.find('i').removeClass('selected');
		var selected = (!$icon.hasClass('cmb-icon-none')) ? 'selected' : '';
		if ( iconvalue !== 0 ) $icon.addClass(selected); // Dont add to 'Remove icon' button when it is the one being clicked
		$preview.removeClass().addClass(iconvalue);
		$self.val(iconvalue).trigger('change');
		$picker_box.trigger('hootclick');
	}

	/*** Image Upload ***/

	$.fn.hootWidgetImageUpload = function() {
		return this.each(function() {
			if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {

				var $button   = $(this),
					$input    = $button.siblings('.hoot-image'),
					$preview  = $button.children('.hoot-image-selected-img'),
					$remove   = $button.siblings('.hoot-image-remove');

				$remove.on( "click", function(e) {
					e.preventDefault();
					$input.val('');
					$input.trigger('change');
					$preview.css('background-image', 'none');
				});

				$button.on( "click", function(e) {
					// e.preventDefault();
					// wp.media.editor.send.attachment = function(props, attachment) {
					// 	$input.val(attachment.id);
					// 	$inputurl.val(attachment.url);
					// 	$preview.css('background-image', 'url('+attachment.url+')');
					// };
					// wp.media.editor.open($button);
					// return false;

					var frame = $button.data('frame');

					// If the media frame already exists, reopen it.
					if ( frame ) {
						frame.open();
						return false;
					}

					// Create the media frame.
					frame = wp.media( {
						title: $button.data('title'), // Set the title of the modal.
						library: {
							// Tell the modal to show only images.
							type: $button.data('library').split(',').map(function(v){ return v.trim() })
						},
						button: {
							text: $button.data('update'), // Set the text of the button.
							close: false // Tell the button not to close the modal
						}
					} );

					// Store the frame
					$button.data('frame', frame);

					// When an image is selected, run a callback.
					frame.on( 'select', function() {
						// Grab the selected attachment.
						var attachment = frame.state().get('selection').first().attributes;
						// Update Image ID
						$input.val(attachment.id);
						$input.trigger('change');
						// Update Image URL
						var imageurl = '';
						if(typeof attachment.sizes !== 'undefined'){
							if(typeof attachment.sizes.thumbnail !== 'undefined')
								imageurl = attachment.sizes.thumbnail.url;
							else
								imageurl = attachment.sizes.full.url;
						} else {
							imageurl = attachment.icon;
						}
						$preview.css('background-image', 'url('+imageurl+')');
						// Close Frame
						frame.close();
					} );

					// Finally, open the modal.
					frame.open();

					return false;
				});

			}
		});
	};

	/*** Collpaser ***/

	$.fn.hootWidgetCollapser = function() {
		return this.each(function() {
			var $self      = $(this),
				$collapser = $self.siblings('.hoot-collapse-body');
			$self.on( "click", function() {
				$collapser.toggle();
			});
		});
	};

	/*** Setup Widget ***/

	$.fn.hootSetupWidget = function() {

		var setupAdd = function( $container, widgetClass, dynamic ){
			// Add Group Item
			$container.find('.hoot-widget-field-group-add').each( function() {
				var $addGroup   = $(this),
					$itemList   = $addGroup.siblings('.hoot-widget-field-group-items'),
					groupID     = $addGroup.parent('.hoot-widget-field-group').data('id'),
					itemNumber  = $('#hoot-groupdata-'+widgetClass).data('groupwgtnumber'),
					// newItemHtml = window.hoot_widget_helper[widgetClass][groupID],
					newItemData = $('#hoot-groupdata-'+widgetClass).data('grouphtml'),
					newItemHtml = ( typeof newItemData !== 'undefined' ) ? $(newItemData)[0][groupID] : '' ;

				$addGroup.on( "click", function() {
					var iterator = parseInt( $(this).data('iterator') ),
						widgetnumber = $(this).data('widgetnumber'), // Dont parseInt since SITEORIGIN uses non numerical widget numbers
						limit = parseInt( $(this).data('limit') );
					if ( limit ) {
						var limitmsg = $(this).data('limitmsg'),
							added = $(this).siblings('.hoot-widget-field-group-items').children().length;
						if ( added+1 >= limit ) $(this).addClass('maxreached');
						if ( added >= limit ) {
							if ( limitmsg ) alert(limitmsg);
							return false;
						}
					};
					iterator++;
					$(this).data('iterator', iterator);
					var newItem = newItemHtml.trim().replace(/975318642/g, iterator).replace( new RegExp(itemNumber, 'g'), widgetnumber);

					var $newItem = $(newItem);
					setupToggle( $newItem );
					setupRemove( $newItem );
					$newItem.find('.hoot-icon').hootWidgetIconPicker();
					$newItem.find('.hoot-image-selected').hootWidgetImageUpload();
					$newItem.find('.hoot-color').wpColorPicker();
					$newItem.find('.hoot-select2').select2();
					//init( $newItem, widgetClass, true ); // @todo
					$itemList.append($newItem);
					if ( $itemList.hasClass('issortable') ) $itemList.sortable('refresh');
					$addGroup.closest('.hoot-widget-form').find('input').filter(":first").trigger('change');
					// $addGroup.prev('input.hoot-widget-field-group-placeholder').trigger('change');
				});
			});
			// Collapse/Expand All Groups Items
			$container.find('.hoot-widget-field-group-top').click( function(){
				$(this).toggleClass('open');
				if( $(this).is('.open') )
					$(this).siblings('.hoot-widget-field-group-items').find('.hoot-widget-field-group-item-form').show();
				else
					$(this).siblings('.hoot-widget-field-group-items').find('.hoot-widget-field-group-item-form').hide();
			});
		};

		var setupToggle = function( $container ) {
			// Make groups collapsible
			$container.find('.hoot-widget-field-group-item-top').on( "click", function() {
				$(this).siblings('.hoot-widget-field-group-item-form').toggle();
			});
		};

		var setupRemove = function( $container ) {
			// Make group items removable
			$container.find('.hoot-widget-field-group-remove').on( "click", function() {
				// $(this).closest('.hoot-widget-field-group-items').siblings('input.hoot-widget-field-group-placeholder').trigger('change');
				$(this).closest('.hoot-widget-form').find('input').filter(":first").trigger('change');
				$(this).closest('.hoot-widget-field-group-items').siblings('.hoot-widget-field-group-add').removeClass('maxreached');
				$(this).closest('.hoot-widget-field-group-item').remove();
			});
		};

		return this.each( function(i, el) {
			var $self       = $(el),
				widgetClass = $self.data('class'),
				$group      = $self.find('.hoot-widget-field-group-items');
				if ( $self.data('hoot-form-setup') === true ) return true;
				var $container = $self.closest('.widget');
				if ( $container.length > 0 && $container.attr('id') !== undefined && $container.attr('id').indexOf("__i__") > -1 ) return true; // needed for Classic Widgets screen -> no id in Legacy_Widget_Block_WP5.8 hence check for undefined values (=> works for page builder as well)

			$self.find('.hoot-icon').hootWidgetIconPicker();
			$self.find('.hoot-image-selected').hootWidgetImageUpload();
			$self.find('.hoot-color').wpColorPicker();
			$self.find('.hoot-select2').select2();

			$self.find('.hoot-collapse-head').hootWidgetCollapser();
			if ( $group.hasClass('issortable') ) $group.sortable({ handle: ".fa-arrows-alt", placeholder: "hoot-widget-field-sortlistitem-placeholder", forcePlaceholderSize: true });

			setupAdd( $self, widgetClass, false );
			setupToggle( $self );
			setupRemove( $self );

			// All done.
			$self.trigger('hootwidgetformsetup').data('hoot-form-setup', true);
		});

	};

	/*** Initialize Stuff ***/

	// Initialize existing hoot forms
	// $('.hoot-widget-form').hootSetupWidget();
	$('body.widgets-php:not(.block-editor-page) .hoot-widget-form').hootSetupWidget();

	// Legacy_Widget_Block_WP5.8 - Adding new widget and existing widgets
	// @ref https://github.com/WordPress/gutenberg/blob/trunk/docs/how-to-guides/widgets/legacy-widget-block.md
	$(document).on( 'widget-added widget-updated', function ( event, $widget ) {
		$widget.find('.hoot-widget-form').hootSetupWidget();
	});


	/*** Widgets as Shortcodes ***/

	$.fn.hootkitSC = function() {
		return this.each( function(i, el) {
			var $self       = $(el),
				sidebarID   = $self.closest('.widgets-holder-wrap').find('.sidebar-name').parent().attr('id'),
				widgetID    = $self.find('input.widget-id').val(),
				is_instance = widgetID.indexOf("__i__") > -1;

			// Skip if not hootkit shortcode sidebar (also skips available-widgets and wp_inactive_widgets)
			if ( sidebarID !== 'hootkit-widgets-sc' ) {
				// dragged from HK-SC-sidebar to non HK-SC-sidebar
				if ( $self.data('hootkit-sc') === true ) {
					$self.data('hootkit-sc', false);
					$self.find('.widget-inside .hootkit-widgetsc').remove();
				}
				return true;
			}
			// Skip if already set up
			if ( $self.data('hootkit-sc') === true ) return true;
			$self.find('.widget-inside .hootkit-widgetsc').remove();
			var codeMsg = is_instance ? '<code>Save the widget to generate shortcode</code>' : '<code class="widgetsccode">[hootkitwidget id="'+widgetID+'"]</code>';
			$self.find('.widget-inside').prepend('<div class="hootkit-widgetsc"><div class="widgetscnotice">Add this shortcode to display widget</div><div class="widgetsccopied">Copied to clipboard.</div>' + codeMsg + '</div>');

			// All done - set data only once the widget is saved (not on instance creation)
			if ( ! is_instance ) {
				$self.trigger('hootkitsc').data('hootkit-sc', true);
			}
		});

	};

	if ( $('body').hasClass('widgets-php') && $('#hootkit-widgets-sc').length > 0 ) {
		$('body.widgets-php:not(.block-editor-page) .widget').hootkitSC();
		$(document).on( 'widget-added widget-updated', function ( event, $widget ) {
			$widget.hootkitSC();
		});
		$('.widgets-sortables').on('sortstop', function (event, ui) {
			var $widget = ui.item;
			$widget.hootkitSC();
		});
		$(document).on('click', '.hootkit-widgetsc', function () {
			var $container = $(this),
				code = $container.find('code.widgetsccode')[0];
			if (!code) return;
			var range = document.createRange();
			range.selectNodeContents(code);
			var sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange(range);
			var text = code.textContent;
			navigator.clipboard.writeText(text).then(function () {
				$container.addClass('hkcopied');
				setTimeout(() => { $container.removeClass('hkcopied'); }, 3000);
			});
		});
	}

}(jQuery));