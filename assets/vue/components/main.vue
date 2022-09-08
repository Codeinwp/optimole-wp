<template>
		<div class="columns is-desktop" style="min-width: 480px;" v-bind:style="maxWidth">

				<div class="column  ">
						<div class="card">
								<app-header></app-header>
								<div class="card-content">
										<div class="content">
												<div v-if="showDisconnect">
													<div class="has-text-weight-bold" style="font-size: 19px; margin-bottom: 20px;">{{strings.disconnect_title}}</div>
													<div class="optml-line-height" style="font-size: 16px; margin-bottom: 20px;">{{strings.disconnect_desc}}</div>
													<div class="optml-side-by-side optml-settings-desc-margin">
														<div @click="keepConnected" class="button optml-button optml-button-style-2 optml-position-relative" > {{strings.keep_connected}}</div>
														<div @click="disconnect" :class="this.$store.state.loading ? 'is-loading'  : '' " class="button optml-button-style-1 optml-position-relative" style="margin-left: 20px; background-color: #e77777; border-color: #E77777;"> {{strings.disconnect_btn}}</div>

													</div>

												</div>
												<connect-layout v-if="!this.is_connected && this.$store.state.autoConnect === 'no'"></connect-layout>

												<!--Connecting-->
												<transition name="slide-fade">
													<div class="optml-side-by-side" style="align-items: center; justify-content: center; margin-right: 4%;" v-if="((!showDisconnect && this.is_connected ) ||  this.$store.state.autoConnect !== 'no') && ! this.$store.state.is_loaded" id="optml-loader">
														<div>
															<figure class="image">
																<img :src="connecting" >
															</figure>
														</div>
														<div>
																<div class="columns">
																		<div class="column">

																				<transition name="slide-fade">
																						<div class="has-text-left is-size-4 has-text-weight-bold">Connecting to Optimole</div>

																				</transition>
																			<transition name="slide-fade">

																				<div class="has-text-left is-size-6" style="margin: 4% 0 1% 0;">Sit tight while we connect you to the Dashboard</div>
																			</transition>
																		</div>
																</div>
																<div class="columns optml-flex-column" style="margin:1%;">

																				<progress id="optml-progress-bar" class="progress is-small is-success optml-custom-label-margin" :value="this.timer" max="25"></progress>
																				<div class="is-size-7" style="padding-top: 7px;">{{this.getProgressMessage()}}</div>

																</div>
														</div>
													</div>
												</transition>
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
																				<!-- Refresh stats -->
																				<li style="margin-left:auto; position:relative; right:1%;">
																						<div class="level-item">

																							<span class="is-size-7 has-text-weight-bold optml-gray optml-hide-on-mobile" style="margin-right: 20px;">{{ this.$store.state.loading ?  strings.updating_stats_cta : strings.refresh_stats_cta}}</span>

																							<a style="padding:0; margin:0;">
																							<svg v-bind:class="{ 'optml-spin': this.$store.state.loading }" width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg" style="visibility: visible"  v-on:click="requestUpdate">
																								<path d="M17.0002 12.2747L15.4255 10.7L17.0002 9.12537" stroke="#6F6F6F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
																								<path d="M28.3137 5.6863C34.5621 11.9347 34.5621 22.0653 28.3137 28.3137C22.0653 34.5621 11.9347 34.5621 5.6863 28.3137C-0.562099 22.0653 -0.562099 11.9347 5.6863 5.6863C11.9347 -0.5621 22.0653 -0.5621 28.3137 5.6863Z" fill="#EDF0FF"/>
																								<path d="M28.3137 5.6863C34.5621 11.9347 34.5621 22.0653 28.3137 28.3137C22.0653 34.5621 11.9347 34.5621 5.6863 28.3137C-0.562099 22.0653 -0.562099 11.9347 5.6863 5.6863C11.9347 -0.5621 22.0653 -0.5621 28.3137 5.6863" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
																								<path d="M18.4014 23.1266C17.9494 23.2306 17.4841 23.296 17.0001 23.296C13.5228 23.296 10.7041 20.4773 10.7041 17C10.7041 15.5826 11.1894 14.2893 11.9788 13.2373" stroke="#577BF9" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
																								<path d="M22.0213 20.7626C22.8106 19.7106 23.296 18.4173 23.296 17C23.296 13.5226 20.4773 10.704 17 10.704C16.516 10.704 16.0506 10.7693 15.5986 10.8733" stroke="#577BF9" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
																								<path d="M17 21.7252L18.5747 23.2999L17 24.8746" stroke="#577BF9" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
																							</svg>
																							</a>
																					</div>
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

								<div class="level-right">
										<p class="level-item"><a href="https://optimole.com" target="_blank">Optimole
												v{{strings.version}}</a></p>
										<p class="level-item"><a href="https://optimole.com/terms/"
																						 target="_blank">{{strings.terms_menu}}</a></p>
										<p class="level-item"><a href="https://optimole.com/privacy-policy/" target="_blank">{{strings.privacy_menu}}</a>
										</p>
										<p class="level-item"><a :href="'https://optimole.com/test-drive?url=' + home " target="_blank">{{strings.testdrive_menu}}</a>
										</p>
								</div>
						</div>
				</div>
				<!--right side-->
				<div v-if="is_connected" class="column is-narrow">


						<div class="card optml-upgrade">
							<cdn-details v-if="this.$store.state.userData"></cdn-details>
						</div>
					<div class=" is-hidden-desktop-only is-hidden-tablet-only is-hidden-mobile" style="padding-bottom:8%; margin-top: -4%; color:white !important; background-color: #577BF9;" v-if="this.$store.state.userData.plan === 'free'">
						<div style="margin: 4% 4% 7.5% 4%; padding-top: 10%;" >
							<p class="is-size-5 has-text-centered has-text-weight-bold">
								{{strings.upgrade.title_long}}
							</p>
						</div>
						<div style="position:relative;">
						<ul class="is-size-6 upgrade" style="margin-left: 10%;">
							<li><span class="dashicons dashicons-yes-alt" style="margin: 0 2% 1% 0;"></span>{{strings.upgrade.reason_1}}</li>
							<li><span class="dashicons dashicons-yes-alt " style="margin: 0 2% 1% 0;"></span>{{strings.upgrade.reason_2}}</li>
							<li><span class="dashicons dashicons-yes-alt " style="margin: 0 2% 1% 0;"></span>{{strings.upgrade.reason_3}}</li>
							<li><span class="dashicons dashicons-yes-alt " style="margin: 0 2% 1% 0;"></span>{{strings.upgrade.reason_4}}</li>
						</ul>
							<figure class="image is-hidden-touch" style="position: absolute; left: 50%; top: 17%;">
								<img :src="logo" :alt="strings.optimole + ' ' + strings.service_details" >
							</figure>
						</div>
						<a class="is-size-6 optml-button" href="https://optimole.com/pricing" target="_blank" style="display:block; position:relative; color:white !important; margin:8% 10% 0 10%; padding: 4% 10% 4% 10%; text-align:center;background: rgba(0, 0, 0, 0.43);border-radius: 4px;">
							{{strings.upgrade.cta}}
						</a>
					</div>
				</div>
		</div>
</template>

<script>
	import AppHeader from './app-header.vue';
	import CdnDetails from './cdn-details.vue';
	import ConnectLayout from './connect-layout.vue';
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
				home: optimoleDashboardApp.home_url,
				remove_images: optimoleDashboardApp.remove_latest_images === 'yes',
				fetchStatus: false,
				step_no: 0,
				timer: 0,
				max_time: 25,
				loading_percent: 0,
				tab: 'dashboard',
				logo: optimoleDashboardApp.assets_url + 'img/logo2.png',
				connecting: optimoleDashboardApp.assets_url + 'img/connecting.png',
			}
		},
		computed: {
			maxWidth() {
				if ((!this.is_connected || this.showDisconnect) && this.$store.state.autoConnect === 'no') {
					return {
						'max-width':  '1000px'
					}
				}
			},
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
			AppHeader,
			Options,
			Watermarks,
			ConnectLayout,
			CdnDetails,
			Conflicts,
			LastImages,
			Metrics
		},
		watch: {
			is_connected: function (val) {
				console.log(val);
				if(val && ! this.is_loaded) {
					this.triggerLoading();
				}
			}
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
			disconnect () {
				this.$store.dispatch( 'disconnectOptimole', {
				} );
			},
			createAndConnect: function ( email ) {
				this.$store.dispatch('registerOptimole', {
					email: email,
					autoConnect: true,
				}).then(() => {

				})
			},
			keepConnected () {
				this.$store.commit('toggleShowDisconnectNotice', false);
			},
			requestUpdate() {
				this.$store.dispatch('requestStatsUpdate', {waitTime: 0, component: null});
			},
			changeTab: function (value) {
				this.tab = value;
			},
			getProgressMessage() {
				let message = '';

				if (this.step_no === 0) {
					message = this.strings.options_strings.connect_step_0;
				}
				if (this.step_no === 1) {
					message = this.strings.options_strings.connect_step_1;
				}
				if (this.step_no === 2) {
					message = this.strings.options_strings.connect_step_2;
				}
				if (this.step_no === 3) {
					message = this.strings.options_strings.connect_step_3;
				}

				return `${message} (${this.loading_percent}%)`

			},

			doLoading() {
				this.timer ++;

				if (this.timer >= this.max_time) {
					this.$store.commit('toggleIsServiceLoaded', true);
					this.timer = 0;
					this.step_no = 0;
					this.loading_percent = 0;
					return;
				}
				this.loading_percent = (Math.floor((this.timer / this.max_time) * 100));
								if(this.loading_percent > ((this.step_no + 1 ) * 30) ){
									this.step_no ++ ;
								}
				if (this.timer < this.max_time) {
					this.triggerLoading();
				}
			},
			triggerLoading: function () {
				setTimeout(() => {
					this.doLoading()
				}, 1000 )
			}

		}

	}
</script>
<style lang="sass-loader">
		@import '../../css/style.scss';

		@media ( min-width: 769px ) {
			#optimole-app .overflow-mobile {
				overflow-x: hidden !important;
			}
		}
		#optimole-app .tabs li a {
				border: none;
				display: flex;
				flex-direction: column;
				align-items: center;
				line-height: 150%;
				font-weight: bold;
				margin-bottom: -7%;
				color: #282828 !important;
				text-decoration: none !important;
				max-width:110px;

		}

		#optimole-app .tabs li:not(.is-active) svg{
			visibility: hidden;
		}


		 #optimole-app .tab-text {
			margin-bottom: 7%;
		}

		#optimole-app .optml-upgrade {
				min-width: 260px;
				max-width: 320px;
		}

		#optimole-app .is-tab.no-images {
				min-height: 400px;
		}

		#optimole-app .is-tab {
				min-height: 700px;
		}

		.optml-tabs {
				position: relative;
		}

		.optml-tabs .optml-conflicts-tabs {
				position: absolute;
				right: 0;
				top: 0;
		}

		#optml-loader {
				min-height: 400px;
		}

		.optml-font {
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
			color: #282828;
		}


		.slide-fade-enter-active {
				transition: all .3s ease;
		}

		.slide-fade-leave-active {
				transition: all .8s cubic-bezier(1.0, 0.5, 0.8, 1.0);
		}

		.slide-fade-enter, .slide-fade-leave-to {
				transform: translateX(10px);
				opacity: 0;
		}

		#optml-progress-bar.progress.optml-progres-0:indeterminate {
				background-image: linear-gradient(to right, #5180C1 5%, #dbdbdb 5%);
		}

		#optml-progress-bar.progress.optml-progres-1:indeterminate {
				background-image: linear-gradient(to right, #5180C1 10%, #dbdbdb 10%);
		}

		#optml-progress-bar.progress.optml-progres-2:indeterminate {
				background-image: linear-gradient(to right, #5180C1 20%, #dbdbdb 20%);
		}

		#optml-progress-bar.progress.optml-progres-3:indeterminate {
				background-image: linear-gradient(to right, #5180C1 30%, #dbdbdb 30%);
		}

		#optml-progress-bar.progress.optml-progres-4:indeterminate {
				background-image: linear-gradient(to right, #5180C1 40%, #dbdbdb 40%);
		}

		#optml-progress-bar.progress.optml-progres-5:indeterminate {
				background-image: linear-gradient(to right, #5180C1 50%, #dbdbdb 50%);
		}

		#optml-progress-bar.progress.optml-progres-6:indeterminate {
				background-image: linear-gradient(to right, #5180C1 60%, #dbdbdb 60%);
		}

		#optml-progress-bar.progress.optml-progres-7:indeterminate {
				background-image: linear-gradient(to right, #5180C1 70%, #dbdbdb 70%);
		}

		#optml-progress-bar.progress.optml-progres-8:indeterminate {
				background-image: linear-gradient(to right, #5180C1 80%, #dbdbdb 80%);
		}

		#optml-progress-bar.progress.optml-progres-9:indeterminate {
				background-image: linear-gradient(to right, #5180C1 90%, #dbdbdb 90%);
		}
		.notification .dashicons-external{
				text-decoration: none;
				font-size: 18px;
		}

		.optml-neve li {

			margin-top: 3px !important;
			margin-bottom: 3px !important;
		}

		.optml-neve p.optml-nv-byline {
			margin-top: 5px !important;
			margin-bottom: 15px !important;
		}

		.optml-neve {
			padding: 0.5rem !important;
		}

		.optml-neve .card-content {
			padding: 1rem !important;
		}
</style>
