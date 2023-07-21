import apiFetch from '@wordpress/api-fetch';

class MessageHandler {
	constructor() {
		this.siteUrl = window.location.origin;
		this.allowedType = 'om-dam';
		this.messageListener = this.handlePostMessage.bind( this );
		this.hasListeners = false;
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
     * @param {boolean} append Append to the selection.
     */
	setMultiple( multiple = false, append = false ) {
		this.context = multiple ? 'multiple' : 'single';
		this.append = append;
	}

	/**
     * Set the media frame.
     * @param {wp.media.View} mediaFrame
     */
	setMediaFrame( mediaFrame ) {
		this.mediaFrame = mediaFrame;
	}

	/**
     * Attach the listeners.
     */
	attachListeners() {
		if ( this.hasListeners ) {
			return;
		}
		const self = this;

		// We attach the listeners if the modal is loaded.
		if ( this.frame.classList.contains( 'loaded' )  ) {
			window.addEventListener( 'message', self.messageListener );
			this.hasListeners = true;

			return;
		}

		// Wait for the frame to load.
		this.frame.addEventListener( 'load', () => {
			document.querySelector( '.om-dam-loader' ).style.display = 'none';
			this.frame.style.display = '';
			this.frame.classList.add( 'loaded' );
			window.addEventListener( 'message', self.messageListener );

			this.hasListeners = true;
		});
	}

	/**
     * Detach the listeners.
     */
	detachListeners() {
		if ( ! this.hasListeners ) {
			return;
		}
		window.removeEventListener( 'message', this.messageListener );
		this.hasListeners = false;
	}

	/**
     * Handle the post message.
     */
	handlePostMessage( event ) {
		if ( ! this.isValidEvent( event ) ) {
			return;
		}

		if ( 'getUrl' === event.data.action ) {
			this.sendSiteUrl();
			return;
		}

		if ( 'importImages' === event.data.action ) {
			if ( 1 > event.data.images.length ) {
				return;
			}

			this.insertImages( event.data.images );
		}
	}

	/**
	 * Insert the images.
	 *
	 * @param {array} images The images to insert.
	 * @return {void}
	 */
	async insertImages( images ) {
		const route = window.optmlMediaModal.routes['insert_images'];

		this.sendMessage({ status: 'importing' });

		const result = await apiFetch({
			method: 'POST',
			path: route,
			data: {
				images
			}
		});


		if ( ! result.code || 'success' !== result.code ) {
			this.sendMessage({ error: 'import' });

			return;
		}

		if ( ! result.data || 1 > result.data.length ) {
			this.sendMessage({ error: 'importResponse' });

			return;
		}

		// Current selection.
		const selection = this.mediaFrame.state().get( 'selection' );

		// Only reset if not in append mode.
		if ( ! this.append ) {
			selection.reset();
		}

		let attachmentsQueue = [];

		Object.values( result.data ).forEach( ( attachmentId, idx ) => {

			// Fetch the attachment to add it to the selection.
			const attachment = wp.media.attachment( attachmentId );
			attachment.fetch().then( ( r ) => {
				attachmentsQueue.push( r );

				if ( idx === result.data.length - 1 ) {
					this.sendMessage({ status: 'done' });

					// Add the attachments to the selection.
					selection.add( attachmentsQueue );

					// Simulate click on the select button.
					this.clickInsertButton();
				}
			});
		});

	}

	/**
	 * Simulate click on the select button.
	 *
	 * A bit hacky, but there wasn't another way.
	 *
	 * @return {void}
	 */
	clickInsertButton() {
		let buttons = document.querySelectorAll( '.media-button-select, .media-button-insert, .media-button-gallery' );

		// We need to do this in a hacky way.
		// Some modals render buttons multiple times, in different sidebars, but only one of them actually works.
		buttons.forEach( btn => {
			if ( btn.disabled ) {
				btn.disabled = false;
			}

			setTimeout( () => {
				this.sendMessage({ status: 'done' });
				btn.click();
			}, 250 );
		});
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
