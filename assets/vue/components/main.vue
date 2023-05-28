<template>
		<div class="columns is-desktop" style="min-width: 480px;" v-bind:style="maxWidth">

				<div class="column  ">
						<div class="card">
								<div class="card-content">
										<div class="content">
												<!--Connecting-->
												<transition name="fade" mode="out-in">
														<div v-if="this.$store.state.connected && this.$store.state.hasApplication && this.$store.state.is_loaded && !showDisconnect">
															<div class="tabs is-left is-medium optml-tabs optml-font overflow-mobile">
																		<ul class="is-marginless ">
																				<li :class="tab === 'dashboard' ? 'is-active' : ''">
																						<a @click="changeTab('dashboard')" class="is-size-5">
																								<span class="tab-text is-size-5-mobile is-size-5-touch">
																									{{strings.dashboard_menu_item}}
																								</span>
																							<svg width="110%" height="4" viewBox="0 0 110 4" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
																								<rect width="110%" height="4" rx="2" fill="#577BF9"/>
																							</svg>

																						</a>


																				</li>
																				<li :class="tab === 'conflicts' ? 'is-active' : ''" v-if="conflictCount > 0">
																						<a @click="changeTab('conflicts')" class="is-size-5">
																								<span class="tab-text is-size-5-mobile is-size-5-touch">{{strings.conflicts_menu_item}}</span>
																							<svg width="110%" height="6" viewBox="0 0 100 6" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
																								<rect width="110%" height="6" rx="2" fill="#D54222"/>
																							</svg>
																						</a>
																				</li>
																				<li :class="tab === 'settings' ? 'is-active' : ''">
																						<a @click="changeTab('settings')" class="is-size-5  ">
																								<span class="tab-text is-size-5-mobile is-size-5-touch">{{strings.settings_menu_item}}</span>
																							<svg width="110%" height="4" viewBox="0 0 110 4" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
																								<rect width="110%" height="4" rx="2" fill="#577BF9"/>
																							</svg>
																						</a>
																				</li>
																		</ul>

																</div>

																<div class="is-tab" v-if="tab === 'dashboard' "
																		 :class="remove_images ? 'no-images' : '' ">
																		<div class="notification is-success optml-side-by-side" v-if="strings.notice_just_activated.length > 0 && user_status === 'active' ">
																			<div><span style="position: relative; top:4%;" class="dashicons dashicons-cloud-saved"></span></div> <div style="margin-left:1%; line-height: 168.75%; " v-html="strings.notice_just_activated"></div>
																		</div>
																		<!--Disabled notice-->
																		<div class="notification optml-warning is-size-6 optml-side-by-side" v-if="user_status === 'inactive'"  >
																			<div>
																				<svg style="position: relative; top:10%;" width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
																						 <path fill-rule="evenodd" clip-rule="evenodd" d="M8.9581 0C13.5192 0 17.2136 3.58 17.2136 8C17.2136 12.42 13.5192 16 8.9581 16C4.39696 16 0.702637 12.42 0.702637 8C0.702637 3.58 4.39696 0 8.9581 0ZM10.6558 9L11.0219 3H7.92606L8.29211 9H10.6558ZM11.0219 11.5115C11.0219 11.9809 10.8811 12.3473 10.6213 12.6107C10.3507 12.874 9.97188 13 9.48478 13C8.99768 13 8.61882 12.874 8.33739 12.6107C8.06677 12.3473 7.92606 11.9809 7.92606 11.5115C7.92606 11.0305 8.05595 10.6641 8.32656 10.4008C8.59717 10.1374 8.97603 10 9.48478 10C9.99353 10 10.3724 10.1374 10.6322 10.4008C10.892 10.6641 11.0219 11.0305 11.0219 11.5115Z" fill="#AF3535"/>
																				</svg>
																			</div>
																			<div style="margin-left:1%; line-height: 168.75%; " v-html="strings.notice_disabled_account">
																			</div>
																		</div>
																		<metrics></metrics>
																<hr/>



																<last-images class="optml-hide-on-mobile" :status="fetchStatus" v-if="! remove_images"></last-images>
																</div>
																<div class="is-tab" v-if=" tab === 'settings'">
																		<options></options>
																</div>
																<div class="is-tab" v-if=" tab === 'conflicts'">
																		<conflicts></conflicts>
																</div>
														</div>
												</transition>

										</div>
								</div>
						</div>
				</div>
		</div>
</template>

<script>
	import LastImages from './last-images.vue';
	import Conflicts from './conflicts.vue';
	import Options from "./options.vue";
	import Watermarks from "./watermarks.vue";
	import Metrics from "./metrics.vue";
	module.exports = {
		name: 'app',
		data() {
			return {
				strings: optimoleDashboardApp.strings,
				remove_images: optimoleDashboardApp.remove_latest_images === 'yes',
				fetchStatus: false,
				tab: 'dashboard',
				connecting: optimoleDashboardApp.assets_url + 'img/connecting.png',
			}
		},
		computed: {
			conflictCount() {
				return this.$store.state.conflicts.count || 0
			},
			is_connected() {
				return this.$store.state.connected && this.$store.state.hasApplication;
			},
			is_loaded() {
				return this.$store.state.is_loaded;
			},
			showDisconnect() {
				return this.$store.state.showDisconnect;
			},
			user_status() {
				return this.$store.state.userStatus;
			}
		},
		components: {
			Options,
			Watermarks,
			Conflicts,
			LastImages,
			Metrics
		},
		mounted() {
			let self = this;
			if (this.$store.state.connected && this.$store.state.hasApplication) {
				this.$store.dispatch('retrieveOptimizedImages', {waitTime: 0, component: null});
				this.$store.dispatch('retrieveConflicts', {waitTime: 0, component: null});
				self.fetchStatus = true;
			}
			const urlSearchParams = new URLSearchParams(window.location.search);
			const params = Object.fromEntries(urlSearchParams.entries());
			let images = [];
			if ( Object.prototype.hasOwnProperty.call(params, 'optimole_action') ) {
				for (let i=0; i < 20; i++) {
						if ( Object.prototype.hasOwnProperty.call(params, i) ) {
							if (!isNaN(params[i])) {
								images[i] = parseInt( params[i], 10 );
							}
						}
				}
				params.images = images;
				params.url = window.location.href.split('?')[0] + ( Object.prototype.hasOwnProperty.call(params, "paged") ? "?paged=" + params['paged'] : "" );
				console.log(params.url);
				this.$store.state.queryArgs = params;
				this.changeTab('settings');
			}
			if ( this.$store.state.autoConnect !== 'no' ) {
				this.triggerLoading();
				this.createAndConnect( this.$store.state.autoConnect );
			}
		},
		methods: {
			createAndConnect: function ( email ) {
				this.$store.dispatch('registerOptimole', {
					email: email,
					autoConnect: true,
				}).then((response) => {
					if ( response.code !== 'success' ) {
						this.$store.state.autoConnect = 'no';

						if ( response.message ) {
							this.$store.state.autoConnectError = response.message;
						}
						return;
					}
				})
			},
			changeTab: function (value) {
				this.tab = value;
			},

		}

	}
</script>