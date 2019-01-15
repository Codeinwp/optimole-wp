<template>
	<div class="compare-wrapper" @mouseenter="removeInitial">
		<div class="compare">
			
			<span class="compare_label compare_original">{{this.first_label}}</span>
			<span class="compare_label compare_optimized">{{this.second_label}}</span>
			<div class="compare__content " :class="{'initial':!initial}" :style="{'width': width}">
				<slot name="first"></slot>
			
			</div>
			
			<resize-observer @notify="handleResize" style="display: none"></resize-observer>
			<div class="handle-wrap" :style="{left:`calc(${compareWidth + '%'} - var(--handle-line-width) / 2`}">
				<div class="handle-icon">
					<svg class="handle__arrow handle__arrow--l" xmlns="http://www.w3.org/2000/svg" width="24"
					     height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
					     stroke-linecap="round" stroke-linejoin="round">
						<polyline points="15 18 9 12 15 6"></polyline>
					</svg>
					<svg class="handle__arrow handle__arrow--r" xmlns="http://www.w3.org/2000/svg" width="24"
					     height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
					     stroke-linecap="round" stroke-linejoin="round">
						<polyline points="9 18 15 12 9 6"></polyline>
					</svg>
				
				</div>
				<span class="handle-line"></span>
			</div>
			
			<div class="compare-overlay" :class="{'initial':!initial}" :style="{width:`calc(${compareWidth + '%'})`}">
				<div class="compare-overlay__content" :style="{ 'width': width}">
					<slot name="second"></slot>
				</div>
			</div>
		
		</div>
		<input type="range" min="0" max="100" :step="step" class="compare__range" :value="compareWidth"
		       @input="handleInput" tabindex="-1">
	
	</div>
</template>
<script>

	export default {
		name: 'image_diff',
		props: {
			value: {default: 50},
			first_label: {
				type: String,
				default: ''
			},
			second_label: {
				type: String,
				default: ''
			},
			step: {default: '.1'}
		},
		data() {
			return {
				width: null,
				initial: false,
				compareWidth: this.value,
			}
		},
		watch: {
			value() {
				this.compareWidth = this.value
			}
		},
		mounted() {
			this.width = this.getContainerWidth();

		},
		methods: {
			removeInitial() {
				this.initial = true;
			},
			handleInput(e) {
				this.compareWidth = e.target.value
				this.$emit('input', e.target.value)
			},
			handleResize() {
				const w = this.getContainerWidth();
				if (w === this.width)
					return;
				this.width = w;
			},
			getContainerWidth() {
				return window.getComputedStyle(this.$el, null).getPropertyValue('width')
			},
		}
	}
</script>
<style>
	:root {
		--handle-bg: #4a4a4a;
		--handle-width: 30px;
		--handle-height: 30px;
		--handle-chevron-size: 20px;
		
		--handle-line-bg: #4a4a4a;
		--handle-line-width: 2px;
		--handle-line-height: 100%;
		
		--z-index-handle: 5;
		--z-index-handle-line: 4;
		--z-index-range-input: 6;
	}
	
	.compare-wrapper {
		position: relative;
	}
	
	.compare,
	.compare__content {
		position: relative;
		height: 100%;
	}
	
	.compare-overlay.initial,
	.compare__content.initial {
		opacity: 0.2;
	}
	
	.compare-overlay,
	.compare__content {
		-webkit-transition: opacity .3s ease-in-out;
		-moz-transition: opacity .3s ease-in-out;
		-ms-transition: opacity .3s ease-in-out;
		-o-transition: opacity .3s ease-in-out;
		transition: opacity .3s ease-in-out;
	}
	
	.compare-overlay {
		position: absolute;
		overflow: hidden;
		height: 100%;
		top: 0;
	}
	
	.compare-overlay__content {
		position: relative;
		height: 100%;
		width: 100%;
	}
	
	.handle__arrow {
		position: absolute;
		width: var(--handle-chevron-size);
	}
	
	.handle__arrow--l {
		left: 0;
	}
	
	.handle__arrow--r {
		right: 0;
	}
	
	.handle-wrap {
		display: flex;
		align-items: center;
		justify-content: center;
		position: absolute;
		top: 50%;
		height: 100%;
		transform: translate(-50%, -50%);
		z-index: var(--z-index-handle);
	}
	
	.handle-icon {
		display: flex;
		align-items: center;
		justify-content: center;
		color: white;
		background: var(--handle-bg);
		border-radius: 50%;
		width: var(--handle-width);
		height: var(--handle-height);
	}
	
	.handle-line {
		content: '';
		position: absolute;
		top: 0;
		width: var(--handle-line-width);
		height: 100%;
		background: var(--handle-line-bg);
		z-index: var(--z-index-handle-line);
		pointer-events: none;
		user-select: none;
	}
	
	.compare__range {
		position: absolute;
		cursor: ew-resize;
		left: calc(var(--handle-width) / -2);
		width: calc(100% + var(--handle-width));
		transform: translatey(-50%);
		top: calc(50%);
		z-index: var(--z-index-range-input);
		-webkit-appearance: none;
		height: var(--handle-height);
		/* debugging purposes only */
		background: rgba(0, 0, 0, .4);
		opacity: .0;
	}
	
	.object-fit-cover {
		object-fit: cover;
	}
	
	.compare_optimized {
		right: 5px;
	}
	
	.compare_original {
		left: 5px;
	}
	
	.compare_label {
		position: absolute;
		z-index: 999;
		background: #444;
		opacity: 0.6;
		padding: 0.5rem;
		border-radius: 0.2rem;
		color: #fff;
		top: 10px;
		
	}
</style>