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

		this._eventCleanupFunction = null;
	}

	static get observedAttributes() {
		return [ 'video-src', 'primary-color', 'loop', 'hide-controls' ];
	}

	attributeChangedCallback( name, oldValue, newValue ) {
		if ( 'video-src' === name && newValue ) {
			this.videoSrc = newValue;
		}

		if ( 'primary-color' === name && newValue ) {
			this.primaryColor = newValue;
		}

		if ( 'loop' === name && newValue ) {
			this.loop = newValue;
		}

		if ( 'hide-controls' === name && newValue ) {
			this.hideControls = newValue;
		}

		if ( this.isConnected ) {
			console.log( 'attributeChangedCallback' );
			this.render();
			this.setupEventListeners();

		}
	}

	connectedCallback() {
		this.render();
		this.setupEventListeners();
	}

	disconnectedCallback() {
		if ( this._eventCleanupFunction ) {
			this._eventCleanupFunction();
		}
	}

	render() {
		this.innerHTML = `
        <div class="optml-player-container">
					<video src="${this.videoSrc}" class="optml-video ${this.hideClass}"></video>
          <div class="optml-spinner"><span class="optml-ic">${this.getIcon( this.icons.spinner )}</span></div>
          <button class="optml-video-lg-play ${this.hideClass}" aria-label="${this.strings.play}">${this.getIcon( this.icons.play )}</button>
					<div class="optml-controls ${'true' === this.hideControls ? this.hideClass : ''}">
						<button class="optml-play-pause" aria-label="${this.strings.play}">${this.getIcon( this.icons.play )}</button>
						<div class="optml-progress-container">
							<div class="optml-progress-bar"></div>
							<div class="optml-scrubber"></div>
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

		if ( this.loop && 'true' === this.loop ) {
			video.loop = true;
		}

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

		const playPauseBtnClickHandler = () => {
			if ( video.paused ) {
				video.play();
			} else {
				video.pause();
			}
		};

		const toggleFullscreen = () => {
			if ( document.fullscreenElement ) {
				document.exitFullscreen();
			} else {
				playerContainer.requestFullscreen();
			}
		};

		const handleVideoPlay = () => {
			videoLgPlayBtnIcon.classList.add( this.opacityZeroClass );
			playPauseBtn.innerHTML = this.getIcon( this.icons.pause );
			playPauseBtn.setAttribute( 'aria-label', this.strings.pause );
			videoLgPlayBtn.setAttribute( 'aria-label', this.strings.pause );
		};

		const handleVideoPause = () => {
			videoLgPlayBtnIcon.classList.remove( this.opacityZeroClass );
			playPauseBtn.innerHTML = this.getIcon( this.icons.play );
			playPauseBtn.setAttribute( 'aria-label', this.strings.play );
			videoLgPlayBtn.setAttribute( 'aria-label', this.strings.play );
		};

		const handleVideoLoadedMetadata = () => {
			spinner.classList.add( this.hideClass );
			videoLgPlayBtn.classList.remove( this.hideClass );
			video.classList.remove( this.hideClass );
		};

		const handleVideoTimeUpdate = () => {
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
		};

		const handleTimestampHover = ( e ) => {
			if (  this.isDragging ) {
				return;
			}
			const rect = progressContainer.getBoundingClientRect();
			const pos = ( e.clientX - rect.left ) / rect.width;
			const timePos = pos * video.duration;

			timeTooltip.textContent = this.formatTime( timePos );
			timeTooltip.style.left = `${e.clientX - rect.left}px`;
		};

		const handleDragOnProgressContainer = ( e ) => {
			this.isDragging = true;

			// Immediately update scrubber to mouse position
			this.updateScrubberPosition( e.clientX );

			// Pause video while dragging for smoother experience
			this.wasPlaying = ! video.paused;
			video.pause();

			document.addEventListener( 'mousemove', handleDrag );
			document.addEventListener( 'mouseup', handleDragEnd );
		};

		const handleScrubberDrag = ( e ) => {
			this.isDragging = true;
			e.stopPropagation(); // Prevent click event on progress bar

			// Pause video while dragging for smoother experience
			this.wasPlaying = ! video.paused;
			video.pause();

			document.addEventListener( 'mousemove', handleDrag );
			document.addEventListener( 'mouseup', handleDragEnd );
		};

		const handleFullscreenChange = () => {
			if ( document.fullscreenElement ) {
				fullscreenBtn.innerHTML = this.getIcon( this.icons.minimize );
				fullscreenBtn.setAttribute( 'aria-label', this.strings.exitFullscreen );
			} else {
				fullscreenBtn.innerHTML = this.getIcon( this.icons.maximize );
				fullscreenBtn.setAttribute( 'aria-label', this.strings.fullscreen );
			}
		};

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

		const handleMuteClick = () => {
			video.muted = ! video.muted;
			updateVolumeIcon();
			volumeSlider.value = video.muted ? 0 : video.volume;
		};

		const handleVolumeSliderInput = () => {
			video.volume = volumeSlider.value;
			video.muted = 0 === video.volume;
			updateVolumeIcon();
		};

		playPauseBtn.addEventListener( 'click', playPauseBtnClickHandler );
		videoLgPlayBtn.addEventListener( 'click', playPauseBtnClickHandler );
		video.addEventListener( 'play', handleVideoPlay );
		video.addEventListener( 'pause', handleVideoPause );
		video.addEventListener( 'loadedmetadata', handleVideoLoadedMetadata );
		video.addEventListener( 'timeupdate', handleVideoTimeUpdate );
		progressContainer.addEventListener( 'mousemove', handleTimestampHover );
		progressContainer.addEventListener( 'mousedown', handleDragOnProgressContainer );
		scrubber.addEventListener( 'mousedown', handleScrubberDrag );
		videoLgPlayBtn.addEventListener( 'dblclick', toggleFullscreen );
		fullscreenBtn.addEventListener( 'click', toggleFullscreen );
		document.addEventListener( 'fullscreenchange', handleFullscreenChange );
		muteBtn.addEventListener( 'click', handleMuteClick );
		volumeSlider.addEventListener( 'input', handleVolumeSliderInput );

		this._eventCleanupFunction = () => {
			console.log( 'cleanup for event listeners' );
			playPauseBtn.removeEventListener( 'click', playPauseBtnClickHandler );
			videoLgPlayBtn.removeEventListener( 'click', playPauseBtnClickHandler );
			video.removeEventListener( 'play', handleVideoPlay );
			video.removeEventListener( 'pause', handleVideoPause );
			video.removeEventListener( 'loadedmetadata', handleVideoLoadedMetadata );
			video.removeEventListener( 'timeupdate', handleVideoTimeUpdate );
			progressContainer.removeEventListener( 'mousemove', handleTimestampHover );
			progressContainer.removeEventListener( 'mousedown', handleDragOnProgressContainer );
			scrubber.removeEventListener( 'mousedown', handleScrubberDrag );
			videoLgPlayBtn.removeEventListener( 'dblclick', toggleFullscreen );
			fullscreenBtn.removeEventListener( 'click', toggleFullscreen );
			document.removeEventListener( 'fullscreenchange', handleFullscreenChange );
			muteBtn.removeEventListener( 'click', handleMuteClick );
			volumeSlider.removeEventListener( 'input', handleVolumeSliderInput );
		};
	}

	getIcon( iconId ) {
		return `<svg><use href="#${iconId}"></use></svg>`;
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
}

customElements.define( 'optimole-video-player', OptimoleVideoPlayer );
