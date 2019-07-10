<template>
	<div class="columns is-desktop">

		<div class="column  ">
			<div class="card">
				<app-header></app-header>
				<div class="card-content">
					<div class="content">
						<connect-layout v-if="! this.$store.state.connected"></connect-layout>
						<transition name="fade" mode="out-in">
							<div v-if="this.$store.state.connected">
								<div class="tabs is-left is-boxed is-medium">
									<ul class="is-marginless optml-tabs">
										<li :class="tab === 'dashboard' ? 'is-active' : ''">
											<a @click="changeTab('dashboard')" class="is-size-6-mobile">
												<span class="icon is-size-6-mobile is-size-6-tablet  dashicons dashicons-admin-home"></span>
												<span class="is-size-6-mobile is-size-6-touch ">{{strings.dashboard_menu_item}}</span>
											</a>
										</li>
										<li :class="tab === 'conflicts' ? 'is-active' : ''"  v-if="conflictCount > 0">
											<a @click="changeTab('conflicts')" class="is-size-6-mobile">
												<span class="icon is-size-6-mobile  is-size-6-tablet dashicons dashicons-warning"></span>
												<span class="is-size-6-mobile is-size-6-touch">{{strings.conflicts_menu_item}}</span>&nbsp;
												<span class="tag is-rounded is-danger">{{conflictCount}}</span>
											</a>
										</li>
										<li :class="tab === 'settings' ? 'is-active' : ''">
											<a @click="changeTab('settings')" class="is-size-6-mobile  ">
												<span class="icon is-size-6-mobile   is-size-6-tablet dashicons dashicons-admin-settings"></span>
												<span class="is-size-6-mobile is-size-6-touch">{{strings.settings_menu_item}}</span>
											</a>
										</li>

									</ul>
								</div>

								<div class="is-tab" v-if="tab === 'dashboard' " :class="remove_images ? 'no-images' : '' ">
									<div class="notification is-success" v-if="strings.notice_just_activated.length > 0"
									     v-html="strings.notice_just_activated"></div>
									<api-key-form></api-key-form>
									<cdn-details v-if="this.$store.state.userData"></cdn-details>
									<hr/>
									<last-images :status="fetchStatus" v-if="! remove_images"></last-images>
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
		<div v-if="this.$store.state.connected && this.$store.state.userData.plan === 'free' " class="column is-narrow is-hidden-desktop-only is-hidden-tablet-only is-hidden-mobile">
			<div class="card optml-upgrade">
				<div class="card-header">
					<h3 class="is-size-5 card-header-title"><span class="dashicons dashicons-chart-line"></span>  {{strings.upgrade.title}}</h3>
				</div>
				<div class="card-content">
					<ul>
						<li><span class="dashicons dashicons-yes"></span>{{strings.upgrade.reason_1}}</li>
						<li><span class="dashicons dashicons-yes"></span>{{strings.upgrade.reason_2}}</li>
						<li><span class="dashicons dashicons-yes"></span>{{strings.upgrade.reason_3}}</li>
					</ul>
				</div>
				<div class="card-footer  ">
					<div class="card-footer-item">
					<a href="https://optimole.com#pricing" target="_blank" class="button is-centered is-small is-success"><span class="dashicons dashicons-external"></span>{{strings.upgrade.cta}}</a>
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

	module.exports = {
		name: 'app',
		data() {
			return {
				strings: optimoleDashboardApp.strings,
				home: optimoleDashboardApp.home_url,
				remove_images: optimoleDashboardApp.remove_latest_images === 'yes',
				fetchStatus: false,
				tab: 'dashboard'
			}
		},
        computed: {
			conflictCount() {
				return this.$store.state.conflicts.count || 0
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
			LastImages
		},
		mounted() {
			let self = this;
			if (this.$store.state.connected) {
				this.$store.dispatch('retrieveOptimizedImages', {waitTime: 0, component: null});
				this.$store.dispatch('retrieveConflicts', {waitTime: 0, component: null});
				self.fetchStatus = true;
			}
		},
		methods: {
			changeTab: function (value) {
				this.tab = value;

			}
		}

	}
</script>
<style lang="sass-loader">
	@import '../../css/style.scss';

	#optimole-app .tabs a {
		margin-bottom: -4px;
	}

	#optimole-app .optml-upgrade {
		min-width: 200px;
	}

	#optimole-app .is-tab.no-images{
		min-height: 400px;
	}
	#optimole-app .is-tab {
		min-height: 700px;
	}
	.optml-tabs{
		position: relative;
	}
	.optml-tabs .optml-conflicts-tabs{
		position: absolute;
		right: 0;
		top: 0;
	}
</style>
