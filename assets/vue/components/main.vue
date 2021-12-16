<template>
		<div class="columns is-desktop">

				<div class="column  ">
						<div class="card">
								<app-header></app-header>
								<div class="card-content">
										<div class="content">
												<connect-layout v-if="!this.is_connected "></connect-layout>

												<transition name="slide-fade">
														<div v-if="this.is_connected && ! this.$store.state.is_loaded" id="optml-loader">
																<div class="columns">
																		<div class="column">

																				<transition name="slide-fade">
																						<h4 class="has-text-centered">{{this.getProgressMessage()}}</h4>
																				</transition>
																		</div>
																</div>
																<div class="columns">
																		<div class=" column is-vertical-center ">
																				<progress id="optml-progress-bar" class="progress is-medium is-info"
																									:class="'optml-progres-'+(Math.floor(this.loading_percent/10))"
																									max="100"></progress>
																		</div>
																</div>
																<iframe :src="home" style="opacity:0;" ></iframe>
														</div>
												</transition>
												<transition name="fade" mode="out-in">
														<div v-if="this.$store.state.connected && this.$store.state.hasApplication && this.$store.state.is_loaded">
																<div class="tabs is-left is-medium optml-tabs optml-font">
																		<ul class="is-marginless ">
																				<li :class="tab === 'dashboard' ? 'is-active' : ''">
																						<a @click="changeTab('dashboard')" class="is-size-5">
																								<span class="tab-text is-size-5">
                                                  {{strings.dashboard_menu_item}}
                                                </span>
                                              <svg width="110%" height="4" viewBox="0 0 110 4" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                                                <rect width="110%" height="4" rx="2" fill="#577BF9"/>
                                              </svg>

                                            </a>


																				</li>
																				<li :class="tab === 'conflicts' ? 'is-active' : ''" v-if="conflictCount > 0">
																						<a @click="changeTab('conflicts')" class="is-size-5">
																								<span class="tab-text is-size-5">{{strings.conflicts_menu_item}}</span>
                                              <svg width="110%" height="6" viewBox="0 0 100 6" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                                                <rect width="110%" height="6" rx="2" fill="#D54222"/>
                                              </svg>
																						</a>
																				</li>
																				<li :class="tab === 'settings' ? 'is-active' : ''">
																						<a @click="changeTab('settings')" class="is-size-5  ">
																								<span class="tab-text is-size-5">{{strings.settings_menu_item}}</span>
                                              <svg width="110%" height="4" viewBox="0 0 110 4" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                                                <rect width="110%" height="4" rx="2" fill="#577BF9"/>
                                              </svg>
																						</a>
																				</li>
                                        <!-- Refresh stats -->
                                        <li style="position: absolute; right: 1%;">
                                            <div class="level-item">

                                              <span class="is-size-7" style="font-weight: bold; color: #626262;">{{ this.$store.state.loading ?  strings.updating_stats_cta : strings.refresh_stats_cta}}</span>

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
																		<div class="notification is-success" v-if="strings.notice_just_activated.length > 0 && user_status === 'active' "
																				 v-html="strings.notice_just_activated"></div>
																		<div class="notification is-danger" v-if="user_status === 'inactive' "
																				 v-html="strings.notice_disabled_account"></div>
                                    <metrics></metrics>


<!--
	<last-images :status="fetchStatus" v-if="! remove_images"></last-images>-->
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
										<p class="level-item"><a :href="'https://speedtest.optimole.com/?url=' + home " target="_blank">{{strings.testdrive_menu}}</a>
										</p>
								</div>
						</div>
				</div>
				<div v-if="is_connected && this.$store.state.userData.plan === 'free' "
						 class="column is-narrow is-hidden-desktop-only is-hidden-tablet-only is-hidden-mobile">

<!--          right side-->
						<div class="card optml-upgrade">
              <cdn-details v-if="this.$store.state.userData"></cdn-details>
              <api-key-form></api-key-form>
								<div class="card-header">
										<h3 class="is-size-5 card-header-title"><span class="dashicons dashicons-chart-line"></span>
												{{strings.upgrade.title}}</h3>
								</div>
								<div class="card-content">
										<ul>
												<li><span class="dashicons dashicons-yes"></span>{{strings.upgrade.reason_1}}</li>
												<li><span class="dashicons dashicons-yes"></span>{{strings.upgrade.reason_2}}</li>
												<li><span class="dashicons dashicons-yes"></span>{{strings.upgrade.reason_3}}</li>
												<li><span class="dashicons dashicons-yes"></span>{{strings.upgrade.reason_4}}</li>
										</ul>
								</div>
								<div class="card-footer  ">
										<div class="card-footer-item">
												<a href="https://optimole.com/pricing" target="_blank"
													 class="button is-centered is-small is-success"><span
																class="dashicons dashicons-external"></span>{{strings.upgrade.cta}}</a>
										</div>
								</div>
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
	import ApiKeyForm from "./api-key-form.vue";
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
				tab: 'dashboard'
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
      user_status() {
        return this.$store.state.userStatus;
      }
		},
		components: {
			AppHeader,
			Options,
			Watermarks,
			ConnectLayout,
			ApiKeyForm,
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
		},
		methods: {
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
		}

    #optimole-app .tabs li:not(.is-active) svg{
      visibility: hidden;
    }


     #optimole-app .tab-text {
      margin-bottom: 7%;
    }

		#optimole-app .optml-upgrade {
				min-width: 200px;
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
				margin-top: 150px;
		}

    .optml-font {
      font-family: -apple-system, BlinkMacSystemFont, sans-serif;
      color: #282828;
    }
    @keyframes spin {
      100% {  transform: rotate(-359deg); }
      0% { transform: rotate(0deg); }

    }
    .optml-spin {
      animation: spin 2s linear infinite;
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
