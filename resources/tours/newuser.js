// Guided Tour to help new users 
 
( function ( window, document, $, mw, gt ) {
    var hasEditSectionAtLoadTime, editSectionSelector = '.mw-editsection-visualeditor',
        tabMessages, editTabText, editSectionText, editPageDescription,
        editSectionDescription, tour, introStep, editSectionStep,
        pointSavePageStep,
            // Work around jQueryMsg issue (\u00A0 is a non-breaking space (i.e. &nbsp;))
            NBSP = '\u00A0';
 
    function shouldShowForPage() {
        // Excludes pages outside the main namespace and pages with editing restrictions
        // Should be 'pages that are not in content namespaces'.
        // However, the list of content namespaces isn't currently exposed to JS.
        return ( mw.config.get( 'wgCanonicalNamespace' ) === '' && mw.config.get( 'wgIsProbablyEditable' ) );
    }
 
    // If we shouldn't show it, don't initialize the guiders
    if ( !shouldShowForPage() ) {
        return;
    }
    $('.subnav').addClass('alwaysDown');

    function hasEditSection() {
		return $( editSectionSelector ).length > 0;
	}

	function handleVeChange( transitionEvent ) {
		var isSaveButtonDisabled;
		if ( transitionEvent.type === gt.TransitionEvent.MW_HOOK ) {
		   if ( transitionEvent.hookName === 've.toolbarSaveButton.stateChanged' ) {
		       isSaveButtonDisabled = transitionEvent.hookArguments[0];
		       if ( !isSaveButtonDisabled ) {
		           return pointSavePageStep;
		       }
		   }

		   return gt.TransitionAction.HIDE;
		}
	}
    hasEditSectionAtLoadTime = $( editSectionSelector ).length > 0;
	tabMessages = mw.config.get( 'wgVisualEditorConfig' ).tabMessages;

	editTabText = mw.message( 'vector-view-edit' ).parse();
	if ( tabMessages.editappendix !== null ) {
		editTabText += NBSP + mw.message( tabMessages.editappendix ).parse();
	}
	editPageDescription = mw.message( 'guidedtour-tour-newuser-edit-page-description', editTabText ).parse();

	editSectionText = mw.message( 'editsection' ).parse();
	if ( tabMessages.editsectionappendix !== null ) {
		editSectionText += NBSP + mw.message( tabMessages.editsectionappendix ).parse();
	}
	editSectionDescription = mw.message(
		'guidedtour-tour-firsteditve-edit-section-description', editSectionText
	).parse();

	tour = new gt.TourBuilder( {
		name: 'newuser',
		// Specify that we want logging for this tour
		shouldLog: true
	} );
 
	tour.firstStep( {
		name: 'intro',
		titlemsg: 'guidedtour-tour-newuser-welcome-title',
		descriptionmsg: 'guidedtour-tour-newuser-welcome-description',
		overlay: true,
		// This indicates that we don't want an automatic next button,
		// even though we are specifying which step comes next.
		allowAutomaticNext: false,
		width: 300,
		buttons: [ 
		{
			// Custom logic to specify a button and its behavior
			// depending on whether there are sections on the page.
			action: 'next',
			onclick: function () {
				//mw.libs.guiders.next();
			}
		} ]
	} )
	// At certain times, called transition points, the callback passed to .transition
	// will be called.  At those times, this tour checks if the user is editing.  If so,
	// the tour returns 'preview', indicating that the tour should transition to the
	// 'preview' step automatically.
	.transition( function () {
		if ( gt.isEditing() ) {
			return 'preview';
		}
	} )
	.next( 'showSidebar' );

	tour.step({
		name: 'showSidebar',
		titlemsg: 'guidedtour-tour-newuser-showsidebar-title',
		descriptionmsg: 'guidedtour-tour-newuser-showsidebar-description',
		position: 'right',
		attachTo: '#menu-toggle',
		autoFocus: true,
		width: 300	
	})
	.next( 'showTOC')
	.back( 'intro');

	tour.step({
		name: 'showTOC',
		titlemsg: 'guidedtour-tour-newuser-showtoc-title',
		descriptionmsg: 'guidedtour-tour-newuser-showtoc-description',
		position: 'right',
		attachTo: '.toc-sidebar',
		autoFocus: true,
		width: 300
	})
	.next( 'showInfobox')
	.back( 'showSidebar');

	tour.step({
		name: 'showInfobox',
		titlemsg: 'guidedtour-tour-newuser-showinfobox-title',
		descriptionmsg: 'guidedtour-tour-newuser-showinfobox-description',
		position: 'left',
		attachTo: '.infobox',
		autoFocus: true,
		width: 300
	})
	.next( 'veedit' ) 
	.back( 'showTOC');

	tour.step( {
	       name: 'veedit',
	       titlemsg: 'guidedtour-tour-newuser-veedit-title',
	       description: editPageDescription,
	       position: 'right',
	       attachTo: '#ca-ve-edit',
	       autoFocus: false,
	       allowAutomaticNext: true,
	       allowAutomaticOkay: false,
	       width: 300
	   // Tour-level listeners would avoid repeating this for two steps
	   } ).listenForMwHooks( 've.activationComplete', 've.toolbarSaveButton.stateChanged' )
	       .transition( handleVeChange )
	       .next( 'edit' )
	       .back( 'showInfobox');

    tour.step( {
       name: 'edit',
       titlemsg: 'guidedtour-tour-newuser-edit-title',
       descriptionmsg: 'guidedtour-tour-newuser-edit-description',
       attachTo: '#ca-huiji-edit',
       position: 'right',
       autoFocus: false,
       allowAutomaticNext: false,
       buttons: [{ action: 'end'}],
       width: 300
   } )
   .transition( function () {
       if ( gt.isEditing() ) {
           return 'preview';
       }
   } )
   .back( 'veedit');
 

	tour.step( {
       name: 'preview',
       titlemsg: 'guidedtour-tour-firstedit-preview-title',
       descriptionmsg: 'guidedtour-tour-firstedit-preview-description',
       attachTo: '#wpPreview',
       autoFocus: true,
       position: 'top',
       closeOnClickOutside: false
   } )
   .transition( function () {
       if ( gt.isReviewing() ) {
           return 'save';
       } else if ( !gt.isEditing() ) {
           return gt.TransitionAction.END;
       }
   } )
   .next( 'save' );
 
   tour.step( {
       name: 'save',
       titlemsg: 'guidedtour-tour-firstedit-save-title',
       descriptionmsg: 'guidedtour-tour-firstedit-save-description',
       attachTo: '#wpSave',
       autoFocus: true,
       position: 'top',
       closeOnClickOutside: false
   } )
   .transition( function () {
       if ( !gt.isReviewing() ) {
           return gt.TransitionAction.END;
       }
   } )
   .back( 'preview' );

   pointSavePageStep = tour.step( {
       name: 'pointSavePage',
       titlemsg: 'guidedtour-tour-firstedit-save-title',
       descriptionmsg: 'guidedtour-tour-firsteditve-save-description',
       attachTo: '.ve-ui-toolbar-saveButton',
       position: 'bottomRight',
       closeOnClickOutside: false
   } ).listenForMwHooks( 've.deactivationComplete' )
       .transition( function () {
           if ( !gt.isEditing() ) {
               return gt.TransitionAction.END;
           }
       } );
 
} ( window, document, jQuery, mediaWiki, mediaWiki.guidedTour ) );
