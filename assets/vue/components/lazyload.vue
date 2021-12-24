<template>
		<div :class="{ 'saving--option' : this.$store.state.loading }">
				<div class="field columns">
						<label class="label column has-text-grey-dark">
								{{strings.enable_lazyload_placeholder_title}}
								<p class="is-italic has-text-weight-normal">
										{{strings.enable_lazyload_placeholder_desc}}
								</p>
						</label>
						<div class="column is-3">
								<toggle-button :class="'has-text-dark'"
															 v-model="lazyloadPlaceholder"
															 :disabled="this.$store.state.loading"
                               :width="37"
                               :height="20"
                               color="#577BF9"></toggle-button>
						</div>
				</div>
				<!--Rollback on disable notice-->
				<div class="field  columns">
					<label class="label column">
						{{strings.exclude_first_images_title}}

						<p class="is-italic has-text-weight-normal">
							{{strings.exclude_first_images_desc}}
						</p>
					</label>
					<hr>
					<div class="column is-3 ">
						<p class="control ">
																<input v-model="selectedValue" class="input is-small" type="number" min="0" @change="changedValue">

						</p>
					</div>

			</div>
				<!--Native lazy toggle-->
				<div class="field  is-fullwidth columns">
						<label class="label column has-text-grey-dark">
								{{strings.toggle_native}}

								<p class="is-italic has-text-weight-normal">
										{{strings.native_desc}}
								</p>
						</label>

						<div class="column is-3 ">
								<toggle-button :class="'has-text-dark'"
															 v-model="nativeLazyStatus"
															 :disabled="this.$store.state.loading"
                               :width="37"
                               :height="20"
                               color="#577BF9"></toggle-button>
						</div>
				</div>
				<div class="field  is-fullwidth columns">
						<label class="label column has-text-grey-dark">
								{{strings.toggle_scale}}

								<p class="is-italic has-text-weight-normal">
										{{strings.scale_desc}}
								</p>
						</label>

						<div class="column is-3 ">
								<toggle-button :class="'has-text-dark'"
															 v-model="scaleStatus"
															 :disabled="this.$store.state.loading"
                               :width="37"
                               :height="20"
                               color="#577BF9"></toggle-button>
						</div>
				</div>
				<div class="field columns">
						<label class="label column has-text-grey-dark">
								{{strings.enable_bg_lazyload_title}}
								<p class="is-italic has-text-weight-normal">
										{{strings.enable_bg_lazyload_desc}}
								</p>
						</label>
						<div class="column is-3">
								<toggle-button :class="'has-text-dark'"
															 v-model="lazyloadBgImages"
															 :disabled="this.$store.state.loading"
                               :width="37"
                               :height="20"
                               color="#577BF9"></toggle-button>
						</div>
				</div>
				<!--Video lazyload toggle-->
				<div class="field columns">
						<label class="label column has-text-grey-dark">
								{{strings.enable_video_lazyload_title}}
								<p class="is-italic has-text-weight-normal">
										{{strings.enable_video_lazyload_desc}}
								</p>
						</label>
						<div class="column is-3">
								<toggle-button :class="'has-text-dark'"
															 v-model="lazyloadVideo"
															 :disabled="this.$store.state.loading"
                               :width="37"
                               :height="20"
                               color="#577BF9"></toggle-button>
						</div>
				</div>
				<div class="field columns" v-if="showBgSelectors">
						<div class="column">
								<label class="label has-text-grey-dark">
										<span>{{strings.watch_title_lazyload}}</span>
										<p class="is-italic has-text-weight-normal">
												{{strings.watch_desc_lazyload}}
										</p>
								</label>
								<div>
										<textarea-autosize
														class="textarea is-secondary is-small"
														placeholder="e.g: .image, #item-id, div.with-background-image"
														v-model="lazyloadSelectors"
														:min-height="3"
														:max-height="350"
										></textarea-autosize>
								</div>
						</div>
				</div>
				<div class="field  is-fullwidth columns ">
						<div class="column is-left">
								<button @click="saveChanges()" class="button is-success is-small "
												:class="this.$store.state.loading ? 'is-loading'  : '' " :disabled="!showSave">
										{{strings.save_changes}}
								</button>
						</div>
				</div>

		</div>
</template>

<script>
	export default {
		name: "lazyload",
		data() {
			return {
				strings: optimoleDashboardApp.strings.options_strings,
				all_strings: optimoleDashboardApp.strings,
				showSave: false,
				showBgSelectors: this.$store.state.site_settings.bg_replacer=== 'enabled',
				new_data: {},
				selectedValue : this.$store.state.site_settings.skip_lazyload_images,
			}
		},
		mounted: function () {
		},
		methods: {
			saveChanges: function () {
				this.$store.dispatch('saveSettings', {
					settings: this.new_data
				});
			},
			changedValue: function () {
				this.new_data.skip_lazyload_images = this.selectedValue;
				this.showSave = true;
			}
		},
		computed: {
			site_settings() {
				return this.$store.state.site_settings;
			},
			lazyloadPlaceholder: {
				set: function (value) {
					this.showSave = true;
					this.new_data.lazyload_placeholder = value ? 'enabled' : 'disabled';
				},
				get: function () {
					return !(this.site_settings.lazyload_placeholder === 'disabled');
				}
			},
			lazyloadBgImages: {
				set: function (value) {
					this.showSave = true;
					this.new_data.bg_replacer = value ? 'enabled' : 'disabled';
					this.showBgSelectors = value;
				},
				get: function () {
					return !(this.site_settings.bg_replacer === 'disabled');
				}
			},
			lazyloadVideo: {
				set: function (value) {
					this.showSave = true;
					this.new_data.video_lazyload = value ? 'enabled' : 'disabled';
				},
				get: function () {
					return !(this.site_settings.video_lazyload === 'disabled');
				}
			},
			lazyloadSelectors: {
				set: function ( value ) {
							this.showSave = true;
					this.new_data.watchers = value;
				},
				get: function () {
					return this.site_settings.watchers;
				}
			},
			scaleStatus: {
				set: function (value) {
					this.showSave = true;
					this.new_data.scale = value ? 'disabled' : 'enabled';
				},
				get: function () {
					return (this.site_settings.scale === 'disabled');
				}

			},
			nativeLazyStatus: {
				set: function (value) {
					this.showSave = true;
					this.new_data.native_lazyload = value ? 'enabled' : 'disabled';
				},
				get: function () {
					return !(this.site_settings.native_lazyload === 'disabled');
				}
			},
		}
	}
</script>

<style scoped>

</style>