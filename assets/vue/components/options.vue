<template>
	<div class=" container " :class="{ 'saving--option' : this.$store.state.loading }">
		
		<div class="field  columns">
			<label class="label column has-text-grey-dark">
				{{strings.enable_image_replace}}
				<p class="is-italic has-text-weight-normal">
					{{strings.replacer_desc}}
				</p>
			</label>
			<div class="column ">
				<toggle-button :class="'has-text-dark'"
				               v-model="getReplacerStatus"
				               :disabled="this.$store.state.loading"
				               :labels="{checked: strings.enabled, unchecked: strings.disabled}"
				               :width="80"
				               :height="25"
				               color="#008ec2"></toggle-button>
			</div>
		
		</div>
		<div class="field  is-fullwidth columns">
			<label class="label column has-text-grey-dark">
				{{strings.toggle_ab_item}}
				<p class="is-italic has-text-weight-normal">
					{{strings.admin_bar_desc}}
				</p>
			</label>
			
			<div class="column ">
				<toggle-button :class="'has-text-dark'"
				               v-model="adminBarItemStatus"
				               :disabled="this.$store.state.loading"
				               :labels="{checked: strings.show, unchecked: strings.hide}"
				               :width="80"
				               :height="25"
				               color="#008ec2"></toggle-button>
			</div>
		</div>

		<div class="field  is-fullwidth columns">
			<label class="label column has-text-grey-dark">
				{{strings.toggle_lazyload}}
				<p class="is-italic has-text-weight-normal">
					{{strings.lazyload_desc}}
				</p>
			</label>

			<div class="column ">
				<toggle-button :class="'has-text-dark'"
							   v-model="lazyLoadStatus"
							   :disabled="this.$store.state.loading"
							   :labels="{checked: strings.enabled, unchecked: strings.disabled}"
							   :width="80"
							   :height="25"
							   color="#008ec2"></toggle-button>
			</div>
		</div>
		
		<div class="field  is-fullwidth columns n">
			<label class="label is-half column has-text-grey-dark no-padding-right ">
				{{strings.size_title}}
				<p class="is-italic has-text-weight-normal">
					{{strings.size_desc}}
				</p>
			</label>
			
			<div class="column is-paddingless">
				<div class="columns">
					<div class="field column is-narrow has-addons">
						<p class="control">
							<a class="button is-small is-static">
								{{strings.width_field}}
							</a>
						</p>
						<p class="control ">
							<input v-model="widthStatus" class="input is-small" type="number" min="100"
							       max="10000">
						</p>
					</div>
					<div class="field column is-small has-addons">
						<p class="control">
							<a class="button is-small is-static">
								{{strings.height_field}}
							</a>
						</p>
						<p class="control  ">
							<input v-model="heightStatus" class="input is-small" type="number" min="100"
							       max="10000">
						</p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="field  columns">
			<label class="label column has-text-grey-dark">
				{{strings.quality_title}}
				<p class="is-italic has-text-weight-normal">
					{{strings.quality_desc}}
				</p>
			</label>
			<div class="column  buttons ">
				<div class="field columns  ">
					<div class="column  field has-addons">
						<p class="control">
							<a @click="changeQuality('auto')"
							   :class="{ 'is-info':isActiveQuality ( 'auto'), '  is-selected':site_settings.quality === 'auto'  }"
							   class="button   is-small is-rounded">
								<span class="icon dashicons dashicons-marker"></span>
								<span>{{strings.auto_q_title}}</span>
							</a>
						</p>
						
						<p class="control">
							<a @click="changeQuality('low')"
							   :class="{  'is-info':isActiveQuality( 'low' ), ' is-selected':site_settings.quality === 'low'  }"
							   class="button   is-small">
								<span class="icon dashicons dashicons-minus  "></span>
								<span>{{strings.low_q_title}}</span>
							</a>
						</p>
						
						<p class="control">
							<a @click="changeQuality('medium')"
							   :class="{  'is-info': isActiveQuality( 'medium' ), '  is-selected':site_settings.quality === 'medium'  }"
							   class="button   is-small">
								<span class="icon dashicons dashicons-controls-pause"></span>
								<span class=" ">{{strings.medium_q_title}}</span>
							</a>
						</p>
						<p class="control">
							<a @click="changeQuality('high')"
							   :class="{  'is-info': isActiveQuality ('high'), 'is-selected':site_settings.quality === 'high'   }"
							   class="button    is-rounded is-small">
								<span class="icon dashicons dashicons-menu"></span>
								<span>{{strings.high_q_title}}</span>
							</a>
						</p>
					</div>
					<p class="control column has-text-centered-desktop has-text-left-touch  ">
						<a @click="saveChanges()" class="button is-small is-success "
						   :class="{'is-loading':loading}">
							<span class="dashicons dashicons-yes icon"></span>
							<span>	{{strings.save_changes}}</span>
						</a>
					</p>
				</div>
			</div>
		</div>
		<div v-if="loading_images" class="has-text-centered subtitle ">{{strings.sample_image_loading}}<span
				class="loader has-text-black-bis icon is-small"></span></div>
		<div v-else-if="sample_images.id && sample_images.original_size > 0">
			<p class="title has-text-centered is-5 is-size-6-mobile">{{strings.quality_slider_desc}}</p>
			<div class="columns is-centered is-vcentered is-multiline is-mobile">
				
				<div class="column visual-compare  is-half-fullhd is-half-desktop is-three-quarters-touch is-12-mobile  ">
					<div class="is-full progress-wrapper">
						
						<p class="subtitle is-size-6 compress-optimization-ratio-done has-text-centered"
						   v-if="compressionRatio > 0">
							<strong>{{( 100 - compressionRatio )}}%</strong> smaller </p>
						<p class="subtitle  compress-optimization-ratio-nothing is-size-6 has-text-centered" v-else>
							{{all_strings.latest_images.same_size}}
						</p>
						<progress class="  progress is-large is-success "
						          :value="compressionRatio"
						          :max="100">
						</progress>
						<hr/>
					
					</div>
					<Image_diff class="is-fullwidth" value="50" :first_label="strings.image_1_label"
					            :second_label="strings.image_2_label">
						<img slot="first" :src="sample_images.optimized">
						<img slot="second" :src="sample_images.original">
					
					</Image_diff>
				
				</div>
			
			</div>
		</div>
		<div v-else-if=" sample_images.id < 0">
			<p class="title has-text-centered is-5 is-size-6-mobile">{{strings.no_images_found}}</p></div>
	
	</div>

</template>

<script>
	import Image_diff from "./image_diff.vue";

	export default {
		name: "options",
		components: { Image_diff},
		data() {
			return {
				strings: optimoleDashboardApp.strings.options_strings,
				all_strings: optimoleDashboardApp.strings,
				showNotification: false,
				loading_images: false,
				showSave: false,
				new_data: {},

			}
		},
		mounted: function () {
			this.updateSampleImage(this.site_settings.quality);
		},
		methods: {

			changeQuality: function (value) {
				this.updateSampleImage(value);
				this.qualityStatus = value;
			},
			updateSampleImage: function (value) {
				this.$store.dispatch('sampleRate', {
					quality: value,
					component: this,
				}).then(
					(response) => {

						setTimeout(() => {
							this.showNotification = false;
						}, 1000)
					},
					(response) => {

					}
				);
			},
			saveChanges: function () {
				this.$store.dispatch('saveSettings', {
					settings: this.new_data
				});
			},
			isActiveQuality(q) {
				if (this.new_data && this.new_data.quality) {
					return this.new_data.quality === q;
				}
				return this.site_settings.quality === q;
			},

		},
		computed: {
			site_settings() {
				return this.$store.state.site_settings;
			},
			getReplacerStatus: {
				get: function () {
					return !(this.site_settings.image_replacer === 'disabled');
				},
				set: function (value) {
					this.new_data.image_replacer = value ? 'enabled' : 'disabled'
				}
			},
			adminBarItemStatus: {
				set: function (value) {
					this.new_data.admin_bar_item = value ? 'enabled' : 'disabled';
					if (value) {
						document.getElementById("wp-admin-bar-optml_image_quota").style.display = 'block';
					} else {
						document.getElementById("wp-admin-bar-optml_image_quota").style.display = 'none';
					}
				},
				get: function () {
					return !(this.site_settings.admin_bar_item === 'disabled');
				}
			},
			lazyLoadStatus: {
				set: function (value) {
					this.new_data.lazyload = value ? 'enabled' : 'disabled';
				},
				get: function () {
					return !(this.site_settings.lazyload === 'disabled');
				}
			},
			widthStatus: {
				set: function (value) {
					this.new_data.max_width = value;

				},
				get: function () {

					return (this.site_settings.max_width);
				}
			},
			heightStatus: {
				set: function (value) {
					this.new_data.max_height = value;

				},
				get: function () {

					return (this.site_settings.max_height);
				}
			},
			qualityStatus: {
				set: function (value) {
					this.new_data.quality = value;
				},
				get: function () {
					return (this.site_settings.quality);
				}
			},
			compressionRatio() {
				return (parseFloat(this.sample_images.optimized_size / this.sample_images.original_size) * 100).toFixed(0);
			},
			sample_images() {
				return this.$store.state.sample_rate;
			},
			loading() {
				return this.$store.state.loading;
			}

		}
	}
</script>

<style scoped>
	.saving--option {
		opacity: .75;
	}
	
	#optimole-app .notification {
		padding: 0.5rem;
	}
	
	#optimole-app .image {
		text-align: center;
	}
	
	#optimole-app .visual-compare img {
		width: 100%;
	}
	
	#optimole-app .icon.dashicons.dashicons-controls-pause {
		transform: rotate(90deg);
	}
	
	#optimole-app .image img {
		
		max-height: 300px;
		width: auto;
		
	}
	
	.field:nth-child(even) {
		justify-content: flex-end;
	}
	
	#optimole-app .button.is-selected:not(.is-info) span {
		color: #008ec2;
	}
	
	#optimole-app p.compress-optimization-ratio-done strong {
		
		color: #44464e;
	}
	
	#optimole-app p.compress-optimization-ratio-nothing,
	#optimole-app p.compress-optimization-ratio-done {
		position: absolute;
		right: 10px;
		color: #44464e;
		
		font-size: 0.9rem !important;
		line-height: 1.4rem;
	}
	
	#optimole-app p.compress-optimization-ratio-nothing {
		color: #fff;
		left: 20px;
	}
	
	#optimole-app .progress-wrapper {
		position: relative;
	}
</style>