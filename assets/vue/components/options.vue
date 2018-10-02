<template>
	<div class=" container " :class="{ 'saving--option' : this.$store.state.loading }">
		<div class="columns" v-if="showNotification">
			<div class="notification  column is-one-quarter is-success">
				{{strings.option_saved}}
			</div>
		</div>
		
		<div class="field  columns">
			<label class="label column has-text-grey-dark">
				{{strings.enable_image_replace}}
				<p class="is-italic has-text-weight-normal">
					{{strings.replacer_desc}}
				</p>
			</label>
			<div class="column ">
				<toggle-button @change="toggleOption('image_replacer')" :class="'has-text-dark'"
				               :value="imageReplacerStatus"
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
				<toggle-button :class="'has-text-dark'" @change="toggleOption('admin_bar_item')"
				               v-model="adminBarItemStatus"
				               :disabled="this.$store.state.loading"
				               :labels="{checked: strings.show, unchecked: strings.hide}"
				               :width="80"
				               :height="25"
				               color="#008ec2"></toggle-button>
			</div>
		</div>
		<div class="field  columns">
			<label class="label column has-text-grey-dark">
				{{strings.quality_title}}
				<p class="is-italic has-text-weight-normal">
					{{strings.quality_desc}}
				</p>
			</label>
			<div class="column ">
				<div class="field has-addons">
					
					<p class="control">
						<a @click="changeQuality('auto')" :class="{ 'is-info':( quality_saved === 'auto' ) }"
						   class="button is-small is-rounded">
							<span class="icon is-small dashicons-admin-customizer dashicons"></span>
							<span>{{strings.auto_q_title}}</span>
						</a>
					</p>
					
					<p class="control">
						<a @click="changeQuality('low')" :class="{ 'is-info':( quality_saved === 'low' ) }"
						   class="button   is-small">
							<span class="icon is-small dashicons dashicons-arrow-right"></span>
							<span>{{strings.low_q_title}}</span>
						</a>
					</p>
					
					<p class="control">
						<a @click="changeQuality('medium')" :class="{ 'is-info':( quality_saved === 'medium' ) }"
						   class="button   is-small">
							<span class="icon is-small dashicons dashicons-controls-play"></span>
							<span>{{strings.medium_q_title}}</span>
						</a>
					</p>
					<p class="control">
						<a @click="changeQuality('high')" :class="{ 'is-info':( quality_saved === 'high' ) }"
						   class="button    is-rounded is-small">
							<span class="icon is-small dashicons dashicons-controls-forward"></span>
							<span>{{strings.high_q_title}}</span>
						</a>
					</p>
				</div>
			</div>
		</div>
		
		<p class="title has-text-centered is-5"> See two sample image which will help you choose the right quality of
			the compression.</p>
		<div class="field  columns" v-if="sample_images.id > 0">
			<twenty-twenty
					:before="sample_images.optimized"
					:after="sample_images.original">
			</twenty-twenty>
			
		</div>
	
	</div>

</template>

<script scoped >
	
	import TwentyTwenty from 'vue-twentytwenty'

	export default {
		name: "options",

		components: {
			TwentyTwenty
		},
		data() {
			return {
				strings: optimoleDashboardApp.strings.options_strings,
				adminBarItem: optimoleDashboardApp.admin_bar_item,
				imageReplacer: optimoleDashboardApp.image_replacer,
				quality_saved: optimoleDashboardApp.quality,
				showNotification: false,
			}
		},
		mounted: function () {
			this.updateSampleImage(this.quality_saved);
		},
		methods: {
			toggleOption: function (optionKey) {
				this.$store.dispatch('toggleSetting', {
					req: 'Toggle ' + optionKey,
					option_key: optionKey,
					type: 'toggle',
				}).then((response) => {
					this.showNotification = true;
					setTimeout(() => {
						this.showNotification = false;
					}, 1000)
				}, (response) => {
				})
			},
			changeQuality: function (value) {
				this.updateSampleImage(value);
				this.quality_saved = value;
			},
			updateSampleImage: function (value) {
				this.$store.dispatch('sampleRate', {
					qulity: value,
				}).then(
					(response) => {
						this.showNotification = true;

						setTimeout(() => {
							this.showNotification = false;
						}, 1000)
					},
					(response) => {

					}
				);
			}

		},
		computed: {
			adminBarItemStatus: {
				set: function (value) {
					this.adminBarItem = value;
					if (value) {
						document.getElementById("wp-admin-bar-optml_image_quota").style.display = 'block';
					} else {
						document.getElementById("wp-admin-bar-optml_image_quota").style.display = 'none';
					}
				},
				get: function () {
					return !(this.adminBarItem === 'disabled');
				}
			},

			sample_images() {
				return this.$store.state.sample_rate;
			},
			imageReplacerStatus() {
				return !(this.imageReplacer === 'disabled');
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
	
	#optimole-app .image img {
		
		max-height: 300px;
		width: auto;
	}
	
	.field:nth-child(even) {
		justify-content: flex-end;
	}
</style>