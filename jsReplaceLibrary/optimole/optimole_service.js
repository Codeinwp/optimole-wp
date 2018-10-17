export default class {

	constructor() {
		this.serviceDomain = '.opt.loc'

		this.serviceData =  {
			key: "undefined",
			quality: "auto",
		}

		if ( typeof optimoleData !== 'undefined' && optimoleData === Object( optimoleData ) ) {
			this.serviceData = { ...this.serviceData, ...optimoleData }
		}
	}

	_cdnKey = function() {
		return this.serviceData.key.toLowerCase()
	}

	_sortByKey = ( unordered ) => {
		const ordered = {};
		Object.keys( unordered ).sort().forEach( ( key ) => {
			ordered[key] = unordered[key];
		} );
		return ordered;
	}

	getImageCDNUrl ( url, args ) {
		if ( url.includes( this.serviceDomain ) ) {
			return url;
		}

		const cdnUrl = `http://${this._cdnKey()}${this.serviceDomain}`;
		const compressLevel = this.serviceData.quality;
		let urlParts = url.split( '://' );

		let scheme = urlParts[0];
		let path = urlParts[1];

		if ( args.width !== 'auto' ) {
			args.width = Math.round( args.width )
		}

		if ( args.height !== 'auto' ) {
			args.height = Math.round( args.height )
		}

		let payload = {
			'path': encodeURIComponent( path.replace( /\/$/, '' ) ),
			'scheme': scheme,
			'width': args.width.toString(),
			'height': args.height.toString(),
			'quality': compressLevel.toString()
		}
		payload = this._sortByKey( payload );

		let newUrl = [
			cdnUrl,
			payload.width,
			payload.height,
			payload.quality,
			payload.scheme,
			path
		];

		return newUrl.join( '/' );
	}

	/**
	 * Update images
	 *
	 */
	updateImages() {
		let images = document.getElementsByTagName( 'img' );
		for( let image of images ) {
			this.lazyLoadImage( image );
		}
	}

	/**
	 * Load remaining images after images listed by the IntersectionObserver
	 * have been loaded.
	 *
	 * @public
	 * @param entries
	 */
	deferImages( entries ) {
		let loaded = 0;
		let inView = 0;

		let self = this;

		entries.forEach( function( entry ) {
			if ( entry.isIntersecting ) {
				inView++;
			}
			if ( entry.target.dataset.optLazyLoaded === "true" ) {
				loaded++;
			}
		} );

		if ( loaded >= inView ) {
			entries.forEach( function( entry ) {
				let isLazyLoaded = entry.target.dataset.optLazyLoaded;
				if ( ! entry.isIntersecting && ( isLazyLoaded === undefined || isLazyLoaded !== "true" ) ) {
					self.lazyLoadImage( entry.target );
				}
			} );
		}
	}

	/**
	 * Check if the available image is of worst quality than required
	 * by the client sizes.
	 *
	 * @public
	 * @param image
	 * @returns {boolean}
	 */
	requiresBetterQuality( image ) {
		if ( image.dataset.optOtimizedWidth === undefined || image.dataset.optOptimizedHeight === undefined ) {
			return true;
		}
		if ( parseInt( image.dataset.optOtimizedWidth ) <= parseInt( image.clientWidth ) ) {
			return true
		}

		return parseInt( image.dataset.optOtimizedHeight ) <= parseInt( image.clientHeight );


	}

	/**
	 * Lazy load image, replace src once new image is available.
	 *
	 * @public
	 * @param image
	 * @param entries
	 */
	lazyLoadImage( image, entries = null ) {
		let self = this

		let originalImageInlineStyle = image.style;
		let pixelRatio = window.devicePixelRatio;
		let containerWidth = image.clientWidth
		let optWidth = ( image.attributes.width ) ? image.attributes.width.value : 'auto';
		let optHeight = ( image.attributes.height ) ? image.attributes.height.value : 'auto';

		let containerHeight = ( optWidth === 'auto' || optHeight === 'auto' ) ? image.clientHeight : parseInt( optHeight/optWidth * containerWidth )

		image.style.width = containerWidth + "px";
		image.style.height = containerHeight + "px";

		if ( image.src === undefined || image.src === '' ) {
			image.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAMLCwgAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==';
		}

		containerWidth = parseInt( containerWidth * pixelRatio )
		containerHeight = parseInt( containerHeight * pixelRatio )
		if ( image.dataset.optSrc !== undefined && this.requiresBetterQuality( image ) ) {
			let optSrc = this.getImageCDNUrl( image.dataset.optSrc, { "width": containerWidth, "height": containerHeight } )
			let downloadingImage = new Image();

			downloadingImage.onload = async function(){
				if ( this.complete ) {
					image.classList.add( 'optml_lazyload_img' );
					image.src = this.src;
					image.style.removeProperty( 'width' )
					image.style.removeProperty( 'height' )
					image.dataset.optLazyLoaded = "true";
					image.style = originalImageInlineStyle;
					if ( entries != null ) {
						self.deferImages( entries );
					}

					image.dataset.optOtimizedWidth = `${containerWidth}`;
					image.dataset.optOptimizedHeight = `${containerHeight}`;
				}
			};
			optSrc = optSrc.replace( `/${optWidth}/`, `/${containerWidth}/` )
			optSrc = optSrc.replace( `/${optHeight}/`, `/${containerHeight}/` )
			downloadingImage.src = optSrc;
		}
		if ( image.dataset.optLazyLoaded ) {
			image.style = originalImageInlineStyle;
		}
	}
}