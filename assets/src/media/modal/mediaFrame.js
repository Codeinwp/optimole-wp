import MessageHandler from './messageHandler';

/**
 * Extend the frame.
 */
const extendFrame = ( frame ) => frame.extend({

	/**
	 * Initialize the messageHandler.
	 */
	omDamMessageHandler: new MessageHandler(),

	/**
	 * Bind the handlers
 	 */
	bindHandlers: function() {
		frame.prototype.bindHandlers.apply( this, arguments );

		// When tab is opened, and template is created, render the content.
		this.on( 'content:render:optimole', this.renderContent, this );

		// When modal is opened, attach the listeners if they have been removed.
		this.on( 'open', this.reattach, this );

		// Append context for gallery is in add mode.
		this.on( 'toolbar:create:gallery-add', this.setAppend, this );

		// When tab is deactivated, modal closed, image selected, view is reset detach the listeners.
		this.on( 'content:deactivate:optimole', this.detach, this );
		this.on( 'reset', this.detach, this );
		this.on( 'select', this.detach, this );

		// Ensure the class is removed on closing.
		this.on( 'close', () => {
			this.toggleModalClass( false );
		}, this );

		// To debug the events above and others you can hook into the 'all' event.
		// this.on( 'all', ... );
	},

	/**
	 * Add the Optimole tab to the router.
	 * @param {wp.media.view.Router} routerView
	 */
	browseRouter: function( routerView ) {
		frame.prototype.browseRouter.apply( this, arguments );

		// Add the Optimole tab to the router.
		routerView.set({
			optimole: {
				text: 'Optimole',
				priority: 60
			}
		});
	},

	/**
	 * Render the Optimole tab using a wp.media.View
	 */
	renderContent: function() {
		const mediaFrame = this;

		const ViewWrapper = wp.media.View.extend({
			template: wp.template( 'optimole-dam' ),
			className: 'om-dam-wrap',
			toolBar: null,
			render: function() {
				this.$el.html( this.template() );

				// Attempt to remove the listeners if they are already attached.
				mediaFrame.omDamMessageHandler.detachListeners();

				// Send the frame to the message handler.
				mediaFrame.omDamMessageHandler.setFrame( this.$el.find( '#om-dam' )[0]);

				// Send the media frame to the message handler.
				mediaFrame.omDamMessageHandler.setMediaFrame( mediaFrame );

				// Send the multiple context to the message handler.
				mediaFrame.omDamMessageHandler.setMultiple( mediaFrame?.options?.multiple || false, mediaFrame.append || false );

				// Attach event listeners for postMessage events.
				mediaFrame.omDamMessageHandler.attachListeners();

				return this;
			}
		});

		this.damView = new ViewWrapper({ controller: this, model: this.state() });

		// Add the class on body.
		this.toggleModalClass( true );

		// Set the content of the frame to the Optimole tab.
		this.content.set( this.damView );
	},

	/**
	 * Detach the event handlers.
	 */
	detach: function() {
		this.omDamMessageHandler.detachListeners();
		this.toggleModalClass( false );
	},

	/**
	 * Reattach the event handlers.
	 */
	reattach: function() {
		if ( ! this.omDamMessageHandler.frame ) {
			return;
		}
		this.omDamMessageHandler.attachListeners();

		const { content } = this;

		if ( content.mode && 'optimole'  === content.mode() ) {
			this.toggleModalClass( true );
		}
	},

	/**
	 * Set append context - this is true when you add to a gallery.
 	 */
	setAppend: function() {
		this.append = true;
	},

	/**
	 * Add a class to the body to scope style when modal is opened.
	 *
	 * @param {boolean} add - Whether to add or remove the class.
	 */
	toggleModalClass: function( add = true ) {
		if ( add ) {
			document.body.classList.add( 'om-dam-modal' );

			return;
		}

		document.body.classList.remove( 'om-dam-modal' );
	}
});

export default extendFrame;
