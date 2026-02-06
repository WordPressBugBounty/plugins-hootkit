jQuery(document).ready(function($) {
	"use strict";

	if( 'undefined' == typeof hootkitSettingsData )
		window.hootkitSettingsData = {};

	var $settingstabs = $( '#hootkit-settings .hootkit-tab');
	$settingstabs.on('click',function(e){
		e.preventDefault();
		var targetid = $(this).data('tabid'),
			$tabblocks = $('#hootkit-settings .hoot-tabblock'),
			$target = $('#hoot-tabblock-'+targetid);
		if ( $target.length ) {
			$settingstabs.removeClass('hootactive');
			$settingstabs.filter('[data-tabid="'+targetid+'"]').addClass('hootactive');
			$tabblocks.removeClass('hootactive');
			$target.addClass('hootactive');
		}
		// Update the URL with the new tab parameter
		var newUrl = new URL(window.location.href);
		newUrl.searchParams.set('view', targetid);
		history.replaceState(null, null, newUrl.toString());
		var $refererInput = $('.hootabt-module.hootabt-active input[name="_wp_http_referer"]');
		if ($refererInput.length) {
			var refererUrl = new URL($refererInput.val(), window.location.origin);
			refererUrl.searchParams.set('view', targetid);
			$refererInput.val(refererUrl.pathname + refererUrl.search);
		}
	} );

	/*** Form ***/

	var $submit = $('#hk-submit'),
		$form = $('#hootkit-settings'),
		initRefreshVals = $form.find('[data-refreshadmin]').serialize(); // snapshot of controls that require refresh

	var $widgetssc = $('.hk-mod-widgets-as-sc');
	$('#hootkit-settings .bettertoggle').click( function(e){
		var origText =    ( hootkitSettingsData && hootkitSettingsData.strings && hootkitSettingsData.strings.default ) || 'Save Changes';
		$submit.removeClass('disabled hootkit-ok hootkit-notok').text( origText );
		var $input = $(this).siblings('input[type=checkbox]');
		$input.click();
		if ( $input.val() === 'classic-widgets' ) {
			if ( $input.is(':checked') )
				$widgetssc.removeClass('hk-mod-inactive').find('input[type="checkbox"]').prop('checked', true);
			else
				$widgetssc.addClass('hk-mod-inactive').find('input[type="checkbox"]').prop('checked', false);
		}
	});

	$submit.click( function(e){
		e.preventDefault();

		var formvalues = $form.serialize();
		var activeText =  ( hootkitSettingsData && hootkitSettingsData.strings && hootkitSettingsData.strings.process ) || 'Processing...',
			successText = ( hootkitSettingsData && hootkitSettingsData.strings && hootkitSettingsData.strings.success ) || 'Settings Saved',
			errorText =   ( hootkitSettingsData && hootkitSettingsData.strings && hootkitSettingsData.strings.error ) || 'Error!';

		if ( $submit.is('.disabled, .updating-message') )
			return;
		$submit.addClass( 'updating-message disabled' ).html( activeText );
		$form.children('.hoot-tabblock').addClass('disabled');

		$.ajax({
			method: 'POST',
			url: hootkitSettingsData.ajaxurl, // url with nonce GET param
			data: { 'handle' : 'setactivemods', 'values' : formvalues },
			success: function( data ){
				console.log(data);
				if ( data.setactivemods == true ) {
					$submit.addClass( 'hootkit-ok' ).html( successText );
					var newRefreshVals = $form.find('[data-refreshadmin]').serialize();
					if ( initRefreshVals !== newRefreshVals ) {
						// controls that require refresh have changed! This includes controls like demoimport which can turn tabs on/off
						var newUrl = new URL(window.location.href);
						newUrl.searchParams.set('settingssave', 1);
						history.replaceState(null, null, newUrl.toString());
						window.location.reload();
					}
				} else {
					$submit.addClass( 'hootkit-notok' ).html( errorText );
				}
			},
			error: function( data ){
				$submit.addClass( 'hootkit-notok' ).html( errorText );
			},
			complete: function( data ){
				$submit.removeClass( 'updating-message disabled' );
				$form.children('.hoot-tabblock').removeClass('disabled');
			}
		});

	});

	/*** Admin Footer ***/

	$('.hootkit-rateus').click( function(e){
		e.preventDefault();
		var url      = $(this).attr('href') ,
			doneText = $(this).data('rated');
		window.open( url, '_blank' );
		$('#footer-left').text( doneText );
		$.post( hootkitSettingsData.ajaxfooterurl );
	});

});