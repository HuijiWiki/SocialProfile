jQuery( document ).ready( function() {
	var next = (function(){
		var config = {
			filter: jQuery('.user-home-feed').data('filter'),
			item_type: jQuery('.user-home-feed').data('item_type'),
			limit: jQuery('.user-home-feed').data('limit')
		}
		var username = mw.config.get('wgUserName');
		var filter = config.filter;
		var item_type = config.item_type;
		var limit = config.limit;
		var continuation = null;
		var showPlaceholder = function(){
			jQuery('.user-home-feed').append('<i class="placeholder fa fa-spinner fa-5x fa-spin"></i>');
		}
		var removePlaceholder = function(){
			jQuery('.placeholder').remove();
		}
		return function(){
			showPlaceholder();
			console.log(username+filter+item_type+limit+continuation);
			jQuery.post(
				mw.util.wikiScript(), {
					action: 'ajax',
					rs: 'wfUserActivityResponse',
					rsargs: [username, filter, item_type, limit, continuation]
				},
				function( data ) {
					var res = jQuery.parseJSON(data);
					if (res.success){
						console.log(res.earlierThan);
						removePlaceholder();
						jQuery('.user-home-feed').append(res.output);
						continuation = res.continuation;
					}
				}
			);			
		};
	})();
	next();
	jQuery( '#user-activity-more' ).on( 'click', function() {
		next();
	} );
} );