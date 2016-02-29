$(function(){
	$reuploadLink = $('#mw-imagepage-reupload-link a');
	$reuploadLink.attr("href", '/wiki/Special:Videos?reupload=1&filename='+mw.config.get('wgTitle'));

	mw.loader.using('schema.MediaViewer', function(){
		var url = mw.config.get('wgVideoLink');
		var source = mw.config.get('wgVideoSource');
		$mainButton = $('.mw-mmv-view-expanded.mw-ui-button.mw-ui-icon.mw-ui-icon-before');
		$mainButton.click(function(e){
			e.preventDefault();
			window.location.assign(this.href);
		});
		$mainButton.attr("href", url);
		$mainButton.html('在'+ source + '中查看');
		$mainButton.attr("target","_blank");
		$secondaryButton = $('.mw-mmv-view-config.mw-ui-button.mw-ui-icon.mw-ui-icon-element');
		$secondaryButton.remove();

		// console.log($('.mw-mmv-view-expanded.mw-ui-button.mw-ui-icon.mw-ui-icon-before').length);
		// $('.mw-mmv-view-expanded.mw-ui-button.mw-ui-icon.mw-ui-icon-before').attr("href", url);
		// $('.mw-mmv-view-config.mw-ui-button.mw-ui-icon.mw-ui-icon-element').remove();
		// console.log(mw.mmv);
	});


});
