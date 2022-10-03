<template>
	<div>
		<!-- Show media cloud toggle-->
		<div class="field  columns">
			<label class="label column has-text-grey-dark">
				{{strings.enable_cloud_images_title}}

				<p class="optml-settings-desc-margin has-text-weight-normal" v-html="strings.enable_cloud_images_desc">
				</p>
			</label>

			<div class="column is-1 ">
				<toggle-button :class="'has-text-dark'"
											 v-model="cloudImagesStatus"
											 :disabled="this.$store.state.loading"
											 :width="37"
											 :height="20"
											 color="#577BF9"></toggle-button>
			</div>
		</div>
		<hr/>
		<!--Sites select-->
		<div  id="filters-list" v-if="this.site_settings.cloud_images === 'enabled'">
			<div class="columns  ">
				<div>

					<div class="field   columns ">
						<div class="field has-addons column has-addons-centered optml-fit-content optml-flex-column">
						<label class="label   column has-text-grey-dark no-padding-right ">
							{{strings.cloud_site_title}}
							<p class="optml-settings-desc-margin has-text-weight-normal">
								{{strings.cloud_site_desc}}
							</p>
						</label>

						<div class="column is-6 optml-fit-content" style="margin-left: 2%;">
							<div class="field columns optml-light-background optml-fit-content" style="min-width: 450px; padding: 1% 2%;">
								<div class="field has-addons column has-addons-centered optml-filters-content">
									<p>
														<span>
															<select @change="changeSite($event)" class="optml-text-input-border">
																 <option  value="all" disabled selected>{{strings.select_site}}</option>
																 <option  v-for="site in sites" :value="site">{{site}}</option>
															</select>
														</span>
									</p>



									<div class="optml-button optml-fit-content" style="background-color: #577BF9; position: relative; white-space: nowrap; height: fit-content; padding: 1.4%;color: white;margin: 0 0 0 4%;" :class="this.$store.state.loading ? 'is-loading'  : '' "
											 @click="saveSite()">
										{{strings.add_site}}
									</div>

								</div>
							</div>
							<div class="field  columns optml-flex-column" style="margin-top: 4%;">
								<div v-if="cloud_sites.length > 0" class="optml-gray has-text-weight-bold field" style="white-space: nowrap; margin-left: 2%; font-size: 0.75rem;"> {{strings.selected_sites_title}}: </div>

								<div class="column is-paddingless is-full">
									<div class="list">
										<div class="exclusion-filter" style="margin-bottom: 2% !important;" v-for="site in cloud_sites">
											<div>
												<div class="tags has-addons">
													<div class="tag  optml-light-background  is-link has-text-left">
														{{site}}
														<a @click="removeSite(site)">
															<svg xmlns="http://www.w3.org/2000/svg" width="13" height="16" viewBox="0 0 13 16" fill="none">
																<path fill-rule="evenodd" clip-rule="evenodd" d="M9 2H12C12.6 2 13 2.4 13 3V4H0V3C0 2.4 0.5 2 1 2H4C4.2 0.9 5.3 0 6.5 0C7.7 0 8.8 0.9 9 2ZM8 2C7.8 1.4 7.1 1 6.5 1C5.9 1 5.2 1.4 5 2H8ZM11.1 15.1L12 5H1L1.9 15.1C2 15.6 2.4 16 2.9 16H10.1C10.6 16 11.1 15.6 11.1 15.1Z" fill="#757296"/>
															</svg>
														</a>
													</div>

												</div>
											</div>
										</div>
									</div>
								</div>

							</div>
						</div>

						</div>
				 </div>




				</div>
			</div>

		<hr/>
		</div>


		<!-- Show offload media toggle-->
		<div class="field  columns">
			<label class="label column has-text-grey-dark">
				{{strings.enable_offload_media_title}}

				<p class="optml-settings-desc-margin has-text-weight-normal">
					{{strings.enable_offload_media_desc}}
				</p>
			</label>

			<div class="column is-1 ">
				<toggle-button :class="'has-text-dark'"
											 v-model="offloadMediaStatus"
											 :disabled="this.$store.state.loading"
											 :width="37"
											 :height="20"
											 color="#577BF9"></toggle-button>
			</div>

		</div>



		<!--Rollback on disable notice-->
		<div class="field  columns optml-flex-column optml-restore-notice-background" v-if="this.showOffloadDisabled">
			<label class="label column">
				<div style="margin-bottom: 2%;">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M8 0C12.42 0 16 3.58 16 8C16 12.42 12.42 16 8 16C3.58 16 0 12.42 0 8C0 3.58 3.58 0 8 0ZM9.64527 9L10 3H7L7.35473 9H9.64527ZM10 11.5115C10 11.9809 9.86364 12.3473 9.61189 12.6107C9.34965 12.874 8.98252 13 8.51049 13C8.03846 13 7.67133 12.874 7.3986 12.6107C7.13636 12.3473 7 11.9809 7 11.5115C7 11.0305 7.12587 10.6641 7.38811 10.4008C7.65035 10.1374 8.01748 10 8.51049 10C9.0035 10 9.37063 10.1374 9.62238 10.4008C9.87413 10.6641 10 11.0305 10 11.5115Z" fill="#282828"/>
					</svg>
					{{strings.offload_disable_warning_title}}
				</div>


				<p class="has-text-weight-normal">
					{{strings.offload_disable_warning_desc}}
				</p>
			</label>

			<div class="column is-3 " style="padding-top:0; margin-top: -1%; ">
				<p class="control ">
														<span class="select  is-small">
															<select  @change="selectValue($event)" class="optml-text-input-border">
																<option  selected value="yes_rollback">{{strings.yes}}</option>
																<option  value="no_rollback">{{strings.no}}</option>
															</select>
														</span>
				</p>
			</div>

		</div>


		<!--Offload on enable notice-->
		<div class="field  columns optml-flex-column optml-restore-notice-background" v-if="this.showOffloadEnabled">
			<label class="label column">


				<p class="has-text-weight-normal" v-html="strings.offload_enable_info_desc">

				</p>
			</label>

		</div>
		<hr/>

		<!-- Sync Media button -->
		<div :class="{ 'saving--option' : this.$store.state.loading }" class="field  is-fullwidth columns " v-if="this.site_settings.offload_media==='enabled'">
			<label class="label column has-text-grey-dark">
				{{strings.sync_title}}

				<p class="optml-settings-desc-margin has-text-weight-normal">
					{{strings.sync_desc}}
				</p>
			</label>
			<div class="column is-2 is-right" style="position: relative;">
				<button @click="callSync('offload_images')" class="optml-button is-small optml-button-page-position "
								:class="this.$store.state.loadingSync ? 'is-loading'  : '' " :disabled="this.$store.state.loadingRollback || this.$store.state.loadingSync">
					{{strings.sync_media}}
				</button>
			</div>
		</div>


		<div class="field columns optml-light-background" v-if="this.$store.state.loadingSync">
			<div class="column optml-media-progress-labels">

				<label class="label has-text-grey-dark">
					<span>{{strings.sync_media_progress}}</span>
					<div style="float: right;"><svg class="optml-spin" xmlns="http://www.w3.org/2000/svg" width="18" height="14" viewBox="0 0 18 14" fill="none">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M9 2.5C6.9 2.5 5.1 4 4.7 6H7L3.5 10L0 6H2.1C2.6 2.6 5.5 0 9 0C11.1 0 13 0.9 14.2 2.3L12.5 4.3C11.7 3.2 10.5 2.5 9 2.5ZM13.3 8H11L14.5 4L18 8H15.9C15.4 11.4 12.5 14 9 14C6.9 14 5 13.1 3.8 11.6L5.5 9.7C6.3 10.8 7.5 11.5 9 11.5C11.1 11.5 12.8 10 13.3 8Z" fill="#577BF9"/>
					</svg>
					</div>
				</label>
				<div style="position:relative;">
					<progress class="progress is-info optml-progress" :value="this.$store.state.pushedImagesProgress" :max="maxTime"></progress>
				</div>

				<label v-if="this.$store.state.estimatedTime == 0" class="label has-text-grey-dark">
					<span>{{strings.calculating_estimated_time}}</span>
				</label>


				<label v-else class="label has-text-grey-dark has-text-weight-normal" >
					<span>{{strings.estimated_time}}: <strong>{{this.$store.state.estimatedTime}} {{strings.minutes}}</strong></span>
				</label>

			</div>
		</div>

		<div class="field  columns" v-if="this.$store.state.offloadLibraryLink === true">
			<label class="label column">
				{{strings.sync_media_link}} <a :href="this.$store.state.queryArgs.url">{{strings.here}}</a>
			</label>
		</div>
		<!--Sync error notice-->
		<div class="notification optml-warning is-size-6 optml-side-by-side" v-if="this.$store.state.errorMedia === 'offload_images'">

				<div>
					<svg style="position: relative; top:10%;" width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M8.9581 0C13.5192 0 17.2136 3.58 17.2136 8C17.2136 12.42 13.5192 16 8.9581 16C4.39696 16 0.702637 12.42 0.702637 8C0.702637 3.58 4.39696 0 8.9581 0ZM10.6558 9L11.0219 3H7.92606L8.29211 9H10.6558ZM11.0219 11.5115C11.0219 11.9809 10.8811 12.3473 10.6213 12.6107C10.3507 12.874 9.97188 13 9.48478 13C8.99768 13 8.61882 12.874 8.33739 12.6107C8.06677 12.3473 7.92606 11.9809 7.92606 11.5115C7.92606 11.0305 8.05595 10.6641 8.32656 10.4008C8.59717 10.1374 8.97603 10 9.48478 10C9.99353 10 10.3724 10.1374 10.6322 10.4008C10.892 10.6641 11.0219 11.0305 11.0219 11.5115Z" fill="#AF3535"/>
					</svg>
				</div>
			<div style="margin-left:1%; ">
				<div><strong>{{strings.sync_media_error}} </strong></div>
				<div>{{strings.sync_media_error_desc}}</div>
				</div>
		</div>
		<hr v-if="this.site_settings.offload_media==='enabled'"/>
		<!--Rollback conflicts notice-->
		<div class="field  columns optml-flex-column optml-restore-notice-background" v-if="this.showConflictNotice">
			<label class="label column">


				<p class="has-text-weight-normal"> {{strings.offload_conflicts_part_1 }} </p>
				<div v-for="(item, index) in getOffloadConflicts">
					<p style = "margin-bottom:10px;"> {{item}}</p>
				</div>
				<p class="has-text-weight-normal"> {{strings.offload_conflicts_part_2 + "!" }} </p>

			</label>
			<a :class="is_loading ? 'is-loading' : '' "
				 class="is-pulled-right button optml-conflict-done is-small is-link"
				 v-on:click="dismissOffloadConflicts()"><span v-if="!is_loading" class="dashicons dashicons-yes"></span>{{conflictStrings.conflict_close}}</a>
			<div class=" is-clearfix"></div>

		</div>

		<!-- Rollback Media button -->
		<div class="field  is-fullwidth columns " v-if="this.site_settings.offload_media==='enabled' || this.$store.state.loadingRollback">
			<label class="label column has-text-grey-dark">
				{{strings.rollback_title}}

				<p class="is-italic has-text-weight-normal">
					{{strings.rollback_desc}}
				</p>
			</label>
			<div class="column is-2 is-right" style="position: relative;">
				<button @click="callSync('rollback_images')" class="optml-button optml-button-page-position is-primary is-small "
								:class="this.$store.state.loadingRollback ? 'is-loading'  : '' " :disabled="this.$store.state.loadingSync || this.$store.state.loadingRollback || this.showConflictNotice">
					{{strings.rollback_media}}
				</button>
			</div>
		</div>


		<div class="field columns optml-light-background" v-if="this.$store.state.loadingRollback">
			<div class="column optml-media-progress-labels">

				<label class="label has-text-grey-dark">
					<span>{{strings.rollback_media_progress}}</span>
					<div style="float: right;"><svg class="optml-spin" xmlns="http://www.w3.org/2000/svg" width="18" height="14" viewBox="0 0 18 14" fill="none">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M9 2.5C6.9 2.5 5.1 4 4.7 6H7L3.5 10L0 6H2.1C2.6 2.6 5.5 0 9 0C11.1 0 13 0.9 14.2 2.3L12.5 4.3C11.7 3.2 10.5 2.5 9 2.5ZM13.3 8H11L14.5 4L18 8H15.9C15.4 11.4 12.5 14 9 14C6.9 14 5 13.1 3.8 11.6L5.5 9.7C6.3 10.8 7.5 11.5 9 11.5C11.1 11.5 12.8 10 13.3 8Z" fill="#577BF9"/>
					</svg>
					</div>
				</label>
				<div style="position:relative;">
					<progress class="progress is-info optml-progress" :value="this.$store.state.pushedImagesProgress" :max="maxTime"></progress>
				</div>

				<label v-if="this.$store.state.estimatedTime == 0" class="label has-text-grey-dark">
					<span>{{strings.calculating_estimated_time}}</span>
				</label>


				<label v-else class="label has-text-grey-dark has-text-weight-normal" >
					<span>{{strings.estimated_time}}: <strong>{{this.$store.state.estimatedTime}} {{strings.minutes}}</strong></span>
				</label>

			</div>
		</div>
		<div class="field  columns" v-if="this.$store.state.rollbackLibraryLink === true">
			<label class="label column">
				{{strings.rollback_media_link}} <a :href="this.$store.state.queryArgs.url">{{strings.here}}</a>
			</label>
		</div>
		<!--Rollback error notice-->
		<div class="notification optml-warning is-size-6 optml-side-by-side" v-if="this.$store.state.errorMedia === 'rollback_images'">

			<div>
				<svg style="position: relative; top:10%;" width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M8.9581 0C13.5192 0 17.2136 3.58 17.2136 8C17.2136 12.42 13.5192 16 8.9581 16C4.39696 16 0.702637 12.42 0.702637 8C0.702637 3.58 4.39696 0 8.9581 0ZM10.6558 9L11.0219 3H7.92606L8.29211 9H10.6558ZM11.0219 11.5115C11.0219 11.9809 10.8811 12.3473 10.6213 12.6107C10.3507 12.874 9.97188 13 9.48478 13C8.99768 13 8.61882 12.874 8.33739 12.6107C8.06677 12.3473 7.92606 11.9809 7.92606 11.5115C7.92606 11.0305 8.05595 10.6641 8.32656 10.4008C8.59717 10.1374 8.97603 10 9.48478 10C9.99353 10 10.3724 10.1374 10.6322 10.4008C10.892 10.6641 11.0219 11.0305 11.0219 11.5115Z" fill="#AF3535"/>
				</svg>
			</div>
			<div style="margin-left:1%; ">
				<div><strong>{{strings.rollback_media_error}} </strong></div>
				<div>{{strings.rollback_media_error_desc}}</div>
			</div>
		</div>

		<div class="field  is-fullwidth columns ">
			<div class="column is-left">
				<button @click="saveChanges()" class="button optml-button-style-1"
								:class="this.$store.state.loading ? 'is-loading'  : '' " :disabled="!showSave">
					{{strings.save_changes}}
				</button>
			</div>
		</div>

	</div>


</template>

<script>

export default {
	name: "media",
	data() {
		return {
			strings: optimoleDashboardApp.strings.options_strings,
			conflictStrings: optimoleDashboardApp.strings.conflicts,
			all_strings: optimoleDashboardApp.strings,
			maxTime: 100,
			showSave: false,
			showOffloadDisabled : false,
			showOffloadEnabled : false,
			showConflictNotice: false,
			offloadDisableOptions : [],
			select_rollback : 'yes_rollback',
			is_loading: false,
			new_data: {},
		}
	},
	mounted: function () {
		if ( Object.prototype.hasOwnProperty.call(this.$store.state.queryArgs, 'optimole_action') ) {
			this.callSync( this.$store.state.queryArgs.optimole_action, this.$store.state.queryArgs.images );
		}
	},
	methods: {
		selectValue: function (event) {
			this.showSave = true;
			this.select_rollback = event.target.value;
		},
		changeSite: function (event) {
			this.selected_site = event.target.value;
		},
		saveSite: function () {
			let  site = {
				'all': true
			};
			if ( typeof this.selected_site !== 'undefined') {
				site['all'] = false;
				site[this.selected_site] = true;
			}

			this.$store.dispatch('saveSettings', {
				settings: {
					cloud_sites: site
				}
			});
		},
		callSync : function ( action, imageIds = "none" ) {
			if ( action === 'rollback_images' ) {
				this.$store.commit('toggleCheckedOffloadConflicts', false);
				this.$store.commit('updateOffloadConflicts', {body: { data: [] }});
				this.$store.dispatch('getOffloadConflicts' );
				let interval = setInterval(function (savedThis) {
					if (savedThis.$store.state.checkedOffloadConflicts === true) {
						if (savedThis.$store.state.offloadConflicts.length === 0) {
							savedThis.$store.state.errorMedia = false;
							savedThis.$store.dispatch('callSync', {action: action, images: imageIds});
						} else {
							savedThis.showConflictNotice = true;
						}
						clearInterval(interval);
					}
				}, 1000, this);
			}
			else {
				this.$store.state.errorMedia = false;
				this.$store.dispatch('callSync', {action: action, images: imageIds});
			}
		},
		dismissOffloadConflicts() {
			this.is_loading = true;
			this.$store.commit('toggleCheckedOffloadConflicts', false);
			this.showConflictNotice = false;
			this.is_loading = false;
		},
		saveChanges: function () {
			this.showOffloadEnabled = false;
			if ( this.showOffloadDisabled  && this.select_rollback === 'yes_rollback'  ) {
				this.callSync('rollback_images' );
				this.select_rollback = 'no_rollback';
			}
			this.showOffloadDisabled = false;
			this.$store.dispatch('saveSettings', {
				settings: this.new_data
			});
		},
		removeSite: function ( site ) {
			let update_sites = {};
			update_sites[site] = false;

			if ( this.cloud_sites.length === 1 ) {
				update_sites['all'] = true;
			}

			this.$store.dispatch('saveSettings', {
				settings: {
					cloud_sites: update_sites
				}
			});
		},
	},
	computed: {
		site_settings() {
			return this.$store.state.site_settings;
		},
		showConflictNotice() {
			return this.showConflictNotice;
		},
		getOffloadConflicts () {
			return this.$store.state.offloadConflicts;
		},
		sites() {
			return this.$store.state.site_settings.whitelist_domains;
		},
		cloud_sites() {
			let sites_array = [];
			let sites_object = this.$store.state.site_settings.cloud_sites;
			for(let site of Object.keys(sites_object)) {
				if (sites_object [site] === 'true' ) {
					sites_array.push(site);
					if ( site === 'all' ) {
						return  sites_array;
					}
				}
			}
			return sites_array;
		},
		offloadMediaStatus: {
			set: function (value) {
				this.showSave = true;
				this.new_data.offload_media = value ? 'enabled' : 'disabled';
				this.showOffloadDisabled = this.site_settings.offload_media === 'enabled' && ! value;
				this.showOffloadEnabled = this.site_settings.offload_media === 'disabled' && !! value;
			},
			get: function () {
				return !(this.site_settings.offload_media === 'disabled');

			}
		},
		cloudImagesStatus: {
			set: function (value) {
				this.showSave = true;
				this.new_data.cloud_images = value ? 'enabled' : 'disabled';
			},
			get: function () {
				return !(this.site_settings.cloud_images === 'disabled');

			}
		}
	}
}
</script>

<style scoped>
button:disabled {
	opacity: 0.5;
}
</style>