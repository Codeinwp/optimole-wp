class OptimoleVideoPlayer extends HTMLElement {
	constructor() {
		super();
		this.isDragging = false;
		this.hideClass = 'optml-vp-hide';
		this.opacityZeroClass = 'optml-vp-opacity-0';

		this.icons = {
			play: 'optml-play-icon',
			pause: 'optml-pause-icon',
			volumeHigh: 'optml-volume-high-icon',
			volumeLow: 'optml-volume-low-icon',
			volumeMute: 'optml-volume-mute-icon',
			maximize: 'optml-maximize-icon',
			minimize: 'optml-minimize-icon',
			spinner: 'optml-loader-icon'
		};

		this.strings = {
			play: OMVideoPlayerBlock.play,
			pause: OMVideoPlayerBlock.pause,
			mute: OMVideoPlayerBlock.mute,
			unmute: OMVideoPlayerBlock.unmute,
			fullscreen: OMVideoPlayerBlock.fullscreen,
			exitFullscreen: OMVideoPlayerBlock.exitFullscreen
		};
	}

	static get observedAttributes() {
		return [ 'video-src', 'primary-color' ];
	}

	attributeChangedCallback( name, oldValue, newValue ) {
		if ( 'video-src' === name && newValue ) {
			this.videoSrc = newValue;
		}

		if ( 'primary-color' === name && newValue ) {
			this.primaryColor = newValue;
		}

		if ( this.isConnected ) {
			this.render();
		}
	}

	connectedCallback() {
		this.render();
		this.setupEventListeners();
	}

	render() {
		this.innerHTML = `
        <div class="optml-player-container">
					<video src="${this.videoSrc}" class="optml-video ${this.hideClass}"></video>
          <div class="optml-spinner"><span class="optml-ic">${this.getIcon( this.icons.spinner )}</span></div>
          <button class="optml-video-lg-play ${this.hideClass}" aria-label="${this.strings.play}">${this.getIcon( this.icons.play )}</button>
					<div class="optml-controls">
						<button class="optml-play-pause" aria-label="${this.strings.play}">${this.getIcon( this.icons.play )}</button>
						<div class="optml-progress-container">
							<div class="optml-progress-bar"></div>
							<div class="optml-scrubber"></div>
							<div class="optml-chapter-markers"></div>
							<div class="optml-time-tooltip">0:00</div>
						</div>
						<div class="optml-time-display">0:00 / 0:00</div>
						<div class="optml-volume-container">
							<button class="optml-mute" aria-label="${this.strings.mute}">${this.getIcon( this.icons.volumeHigh )}</button>
							<input type="range" class="optml-volume-slider" min="0" max="1" step="0.1" value="1">
						</div>
						<button class="optml-fullscreen" aria-label="${this.strings.fullscreen}">${this.getIcon( this.icons.maximize )}</button>
					</div>
				</div>
		`;
	}

	formatTime( seconds ) {
		const minutes = Math.floor( seconds / 60 );
		const secs = Math.floor( seconds % 60 );
		return `${minutes}:${secs.toString().padStart( 2, '0' )}`;
	}

	updateScrubberPosition( clientX ) {
		const progressContainer = this.querySelector( '.optml-progress-container' );
		const progressBar = this.querySelector( '.optml-progress-bar' );
		const scrubber = this.querySelector( '.optml-scrubber' );
		const timeTooltip = this.querySelector( '.optml-time-tooltip' );
		const video = this.querySelector( '.optml-video' );

		const rect = progressContainer.getBoundingClientRect();
		let pos = ( clientX - rect.left ) / rect.width;

		// Clamp position between 0 and 1
		pos = Math.max( 0, Math.min( 1, pos ) );

		// Update progress bar and scrubber visually during drag
		progressBar.style.width = `${pos * 100}%`;
		scrubber.style.left = `${pos * 100}%`;

		// Update tooltip
		timeTooltip.textContent = this.formatTime( pos * video.duration );
		timeTooltip.style.left = `${pos * rect.width}px`;

		return pos;
	}

	setupEventListeners() {
		const playerContainer = this.querySelector( '.optml-player-container' );
		const video = this.querySelector( '.optml-video' );
		const playPauseBtn = this.querySelector( '.optml-play-pause' );
		const videoLgPlayBtn = this.querySelector( '.optml-video-lg-play' );
		const videoLgPlayBtnIcon = this.querySelector( '.optml-video-lg-play svg' );
		const progressContainer = this.querySelector( '.optml-progress-container' );
		const progressBar = this.querySelector( '.optml-progress-bar' );
		const scrubber = this.querySelector( '.optml-scrubber' );
		const timeDisplay = this.querySelector( '.optml-time-display' );
		const timeTooltip = this.querySelector( '.optml-time-tooltip' );
		const muteBtn = this.querySelector( '.optml-mute' );
		const volumeSlider = this.querySelector( '.optml-volume-slider' );
		const fullscreenBtn = this.querySelector( '.optml-fullscreen' );
		const spinner = this.querySelector( '.optml-spinner' );


		[ playPauseBtn, videoLgPlayBtn ].forEach( btn => {
			btn.addEventListener( 'click', () => {
				if ( video.paused ) {
					video.play();
				} else {
					video.pause();
				}
			});
		});

		// Add double-click event to large play button for fullscreen toggle
		videoLgPlayBtn.addEventListener( 'dblclick', () => {
			if ( document.fullscreenElement ) {
				document.exitFullscreen();
				fullscreenBtn.innerHTML = this.getIcon( this.icons.maximize );
				fullscreenBtn.setAttribute( 'aria-label', this.strings.fullscreen );
			} else {
				playerContainer.requestFullscreen();
				fullscreenBtn.innerHTML = this.getIcon( this.icons.minimize );
				fullscreenBtn.setAttribute( 'aria-label', this.strings.exitFullscreen );
			}
		});

		video.addEventListener( 'play', () => {
			videoLgPlayBtnIcon.classList.add( this.opacityZeroClass );
			playPauseBtn.innerHTML = this.getIcon( this.icons.pause );
			playPauseBtn.setAttribute( 'aria-label', this.strings.pause );
			videoLgPlayBtn.setAttribute( 'aria-label', this.strings.pause );
		});

		video.addEventListener( 'pause', () => {
			videoLgPlayBtnIcon.classList.remove( this.opacityZeroClass );
			playPauseBtn.innerHTML = this.getIcon( this.icons.play );
			playPauseBtn.setAttribute( 'aria-label', this.strings.play );
			videoLgPlayBtn.setAttribute( 'aria-label', this.strings.play );
		});

		video.addEventListener( 'loadedmetadata', () => {
			spinner.classList.add( this.hideClass );
			videoLgPlayBtn.classList.remove( this.hideClass );
			video.classList.remove( this.hideClass );
		});

		video.addEventListener( 'timeupdate', () => {
			if ( ! this.isDragging ) {
				const progress = ( video.currentTime / video.duration ) * 100;
				progressBar.style.width = `${progress}%`;
				scrubber.style.left = `${progress}%`;

				// Update time display
				const currentMinutes = Math.floor( video.currentTime / 60 );
				const currentSeconds = Math.floor( video.currentTime % 60 );
				const durationMinutes = Math.floor( video.duration / 60 );
				const durationSeconds = Math.floor( video.duration % 60 );

				timeDisplay.textContent = `${currentMinutes}:${currentSeconds.toString().padStart( 2, '0' )} / ${durationMinutes}:${durationSeconds.toString().padStart( 2, '0' )}`;
			}
		});

		// Timeline tooltip hover functionality
		progressContainer.addEventListener( 'mousemove', ( e ) => {
			if ( ! this.isDragging ) {
				const rect = progressContainer.getBoundingClientRect();
				const pos = ( e.clientX - rect.left ) / rect.width;
				const timePos = pos * video.duration;

				timeTooltip.textContent = this.formatTime( timePos );
				timeTooltip.style.left = `${e.clientX - rect.left}px`;
			}
		});

		// Handle both clicking on progress bar and starting a drag operation
		progressContainer.addEventListener( 'mousedown', ( e ) => {
			this.isDragging = true;

			// Immediately update scrubber to mouse position
			this.updateScrubberPosition( e.clientX );

			// Pause video while dragging for smoother experience
			this.wasPlaying = ! video.paused;
			video.pause();

			document.addEventListener( 'mousemove', handleDrag );
			document.addEventListener( 'mouseup', handleDragEnd );
		});

		// Scrubber can also initiate drag
		scrubber.addEventListener( 'mousedown', ( e ) => {
			this.isDragging = true;
			e.stopPropagation(); // Prevent click event on progress bar

			// Pause video while dragging for smoother experience
			this.wasPlaying = ! video.paused;
			video.pause();

			document.addEventListener( 'mousemove', handleDrag );
			document.addEventListener( 'mouseup', handleDragEnd );
		});

		const handleDrag = ( e ) => {
			if ( ! this.isDragging ) {
				return;
			}
			this.updateScrubberPosition( e.clientX );
		};

		const handleDragEnd = ( e ) => {
			if ( this.isDragging ) {

				// Final position update based on mouse position
				const pos = this.updateScrubberPosition( e.clientX );

				// Update video time
				video.currentTime = pos * video.duration;

				// Resume playback if it was playing before drag started
				if ( this.wasPlaying ) {
					video.play();
				}

				this.isDragging = false;
			}

			document.removeEventListener( 'mousemove', handleDrag );
			document.removeEventListener( 'mouseup', handleDragEnd );
		};

		muteBtn.addEventListener( 'click', () => {
			video.muted = ! video.muted;
			updateVolumeIcon();
			volumeSlider.value = video.muted ? 0 : video.volume;
		});

		volumeSlider.addEventListener( 'input', () => {
			video.volume = volumeSlider.value;
			video.muted = 0 === video.volume;
			updateVolumeIcon();
		});

		const updateVolumeIcon = () => {
			if ( video.muted || 0 === video.volume ) {
				muteBtn.innerHTML = this.getIcon( this.icons.volumeMute );
				muteBtn.setAttribute( 'aria-label', this.strings.unmute );
			} else if ( 0.5 > video.volume ) {
				muteBtn.innerHTML = this.getIcon( this.icons.volumeLow );
				muteBtn.setAttribute( 'aria-label', this.strings.mute );
			} else {
				muteBtn.innerHTML = this.getIcon( this.icons.volumeHigh );
				muteBtn.setAttribute( 'aria-label', this.strings.mute );
			}
		};

		fullscreenBtn.addEventListener( 'click', () => {
			if ( document.fullscreenElement ) {
				document.exitFullscreen();
				fullscreenBtn.innerHTML = this.getIcon( this.icons.maximize );
				fullscreenBtn.setAttribute( 'aria-label', this.strings.fullscreen );
			} else {
				playerContainer.requestFullscreen();
				fullscreenBtn.innerHTML = this.getIcon( this.icons.minimize );
				fullscreenBtn.setAttribute( 'aria-label', this.strings.exitFullscreen );
			}
		});

		// Handle full screen change to update icon
		document.addEventListener( 'fullscreenchange', () => {
			if ( document.fullscreenElement ) {
				fullscreenBtn.innerHTML = this.getIcon( this.icons.minimize );
				fullscreenBtn.setAttribute( 'aria-label', this.strings.exitFullscreen );
			} else {
				fullscreenBtn.innerHTML = this.getIcon( this.icons.maximize );
				fullscreenBtn.setAttribute( 'aria-label', this.strings.fullscreen );
			}
		});
	}

	getIcon( iconId ) {
		return `<svg><use href="#${iconId}"></use></svg>`;
	}
}

customElements.define( 'optimole-video-player', OptimoleVideoPlayer );
