class MessageHandler {
	constructor() {
		this.siteUrl = window.location.origin;
		this.allowedType = 'om-dam';
		this.messageListener = this.handlePostMessage.bind( this );
	}

	/**
     * Setup frame.
     *
     * @param {HTMLIFrameElement} frame The iframe element.
     */
	setFrame( frame ) {
		this.frame = frame;
	}

	/**
     * Set the context.
     *
     * @param {boolean} multiple Allow multiple selection.
     */
	setMultiple( multiple = false ) {
		this.context = multiple ? 'multiple' : 'single';
	}

	/**
	 * Set the media frame.
	 * @param mediaFrame
	 */
	setMediaFrame( mediaFrame ) {
		this.mediaFrame = mediaFrame;
	}

	/**
     * Attach the listeners.
     */
	attachListeners() {
		const self = this;

		// Wait for the frame to load.
		this.frame.addEventListener( 'load', () => {
			window.addEventListener( 'message', self.messageListener );
		});
	}

	/**
     * Detach the listeners.
     */
	detachListeners() {
		window.removeEventListener( 'message', this.messageListener );
	}

	/**
     * Handle the post message.
     */
	handlePostMessage( event ) {
		if ( ! this.isValidEvent( event ) ) {
			return;
		}

		switch ( event.data.action ) {
		case 'getUrl':
			this.sendSiteUrl();
			break;
		case 'singleSelect':
		case 'multiSelect':
			if ( 1 > event.data.images.length ) {
				break;
			}
			this.handleImageSelection( event.data.images );
			break;
		default:
			break;
		}
	}

	handleImageSelection( data ) {
		console.log( data );
		this.mediaFrame.trigger( 'select:activate' );
	}

	/**
     * Send the site url through postMessage.
     */
	sendSiteUrl() {
		this.sendMessage({ siteUrl: this.siteUrl, context: this.context });
	}

	/**
     * Send a message to the iframe.
     *
     * @param data
     */
	sendMessage( data = {}) {
		this.frame.contentWindow.postMessage({ type: 'om-dam', ...data }, '*' );
	}

	/**
     * Check if the event is valid.
     *
     * @param {Event} event The event.
     * @return {boolean} True if the event is valid.
     */
	isValidEvent( event ) {
		if ( ! event.data ) {
			return false;
		}

		if ( ! event.data.type ) {
			return false;
		}

		if ( this.allowedType !== event.data.type ) {
			return false;
		}

		if ( ! event.data.action ) {
			return false;
		}

		return true;
	}
}

export default MessageHandler;
