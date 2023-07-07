import MessageHandler from './messageHandler';

const extendFrame = ( frame ) => frame.extend({
	omDamMessageHandler: new MessageHandler(),
	bindHandlers: function() {
		frame.prototype.bindHandlers.apply( this, arguments );

		// When tab is opened, and template is created, render the content.
		this.on( 'content:create:optimole', this.renderContent, this );

		// When tab is deactivated, detach the listeners.
		this.on( 'content:deactivate:optimole', this.detach, this );

		// When tab is closed, detach the listeners.
		this.on( 'close', this.detach, this );
	},
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

	renderContent: function() {
		const mediaFrame = this;

		const ViewWrapper = wp.media.View.extend({
			template: wp.template( 'optimole-dam' ),
			className: 'om-dam-wrap',
			toolBar: null,
			render: function() {
				this.$el.html( this.template() );

				// Send the frame to the message handler.
				mediaFrame.omDamMessageHandler.setFrame( this.$el.find( '#om-dam' )[0]);

				// Send the multiple context to the message handler.
				mediaFrame.omDamMessageHandler.setMultiple( mediaFrame?.options?.multiple || false );

				// Send the media frame to the message handler.
				mediaFrame.omDamMessageHandler.setMediaFrame( mediaFrame );

				// Attach event listeners for postMessage events.
				mediaFrame.omDamMessageHandler.attachListeners();

				return this;
			}
		});

		this.damView = new ViewWrapper();
		this.content.set( this.damView );
	},

	detach: function() {

		this.omDamMessageHandler.detachListeners();
	}
});

export default extendFrame;
