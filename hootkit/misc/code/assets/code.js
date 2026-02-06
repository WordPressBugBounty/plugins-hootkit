jQuery(document).ready(function($) {
	"use strict";

	$('#hoot-code .bettertoggle').click( function(e){
		$(this).siblings('input[type=checkbox]').click();
	});

	$('.hoot-codetab-togglehead').click( function(e){
		$(this).parent('.hoot-codetab-toggle').toggleClass('hoot-codetab-toggleexpand');
		$(this).siblings('.hoot-codetab-togglebox').slideToggle();
	});

	var initdone = {},
		$navtabs = $('#hootabt-tabs .hootabt-tab'),
		$codetabs = $( '#hoot-codetabs .hoot-codetab'),
		initfirst = function() { setTimeout( function() {
			var $activetab = $codetabs.filter('.hootactive');
			if ( $activetab.length ) { $activetab.trigger('click'); }
			else $codetabs.first().trigger('click');
		}, 100 ); };
	if ( $navtabs.filter('[data-inpage="code"]').hasClass('hootabt-active') ) {
		initfirst();
	}
	$navtabs.on('click',function(e){
		if ( $(this).data('inpage') === 'code' ) { initfirst(); }
	} );

	$codetabs.on('click',function(e){
		e.preventDefault();

		var targetid = $(this).data('codeid'),
			$codeblocks = $('#hoot-code .hoot-codeblock'),
			$target = $('#hoot-codeblock-'+targetid);
		if ( $target.length ) {
			$codetabs.removeClass('hootactive');
			$codetabs.filter('[data-codeid="'+targetid+'"]').addClass('hootactive');
			$codeblocks.removeClass('hootactive');
			$target.addClass('hootactive');
			if ( ! initdone[targetid] &&
				typeof wp === 'object' && typeof wp.codeEditor === 'object' && typeof wp.codeEditor.initialize === 'function'
			 ) {
				var $editor = $target.find('.hoot-codeeditor');
				if ( $editor.length ) {
					var csettings = targetid === 'customphp' ? hootCodeMirrorSettingsOpen : hootCodeMirrorSettings;
					wp.codeEditor.initialize( $editor, csettings );
					initdone[targetid] = true;
				}
			}
		}

		// Update the URL with the new tab parameter
		var newUrl = new URL(window.location.href);
		newUrl.searchParams.set('codetab', targetid);
		history.replaceState(null, null, newUrl.toString());
		var $refererInput = $('.hootabt-module.hootabt-active input[name="_wp_http_referer"]');
		if ($refererInput.length) {
			var refererUrl = new URL($refererInput.val(), window.location.origin);
			refererUrl.searchParams.set('codetab', targetid);
			$refererInput.val(refererUrl.pathname + refererUrl.search);
		}

	} );

});