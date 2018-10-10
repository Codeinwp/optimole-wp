<template>
	<div class="card">
		<app-header></app-header>
		<div class="card-content">
			<div class="content">
				<connect-layout v-if="! this.$store.state.connected"></connect-layout>
				<transition name="fade" mode="out-in">
					<div v-if="this.$store.state.connected">
						<div class="tabs is-left is-boxed is-medium">
							<ul class="is-marginless">
								<li :class="tab === 'dashboard' ? 'is-active' : ''">
									<a @click="changeTab('dashboard')" class="is-size-6-mobile">
										<span class="icon is-size-6-mobile  dashicons dashicons-admin-home"></span>
										<span class="is-size-6-mobile ">{{strings.dashboard_menu_item}}</span>
									</a>
								</li>
								
								<li :class="tab === 'settings' ? 'is-active' : ''" >
									<a @click="changeTab('settings')" class="is-size-6-mobile">
										<span class="icon is-size-6-mobile  dashicons dashicons-admin-settings"></span>
										<span  class="is-size-6-mobile">{{strings.settings_menu_item}}</span>
									</a>
								</li>
							</ul>
						</div>
						
						<div class="is-tab" v-if="tab === 'dashboard' ">
							<api-key-form></api-key-form>
							<cdn-details v-if="this.$store.state.userData"></cdn-details>
							<hr/>
							<last-images :status="fetchStatus"></last-images>
						</div>
						<div class="is-tab" v-if=" tab === 'settings'" >
							<options></options>
						</div>
					</div>
				</transition>
			</div>
		</div>
		
		<div class="level-right">
			<p class="level-item"><a href="https://optimole.com" target="_blank">Optimole v{{strings.version}}</a></p>
			<p class="level-item"><a href="https://optimole.com/terms/" target="_blank">{{strings.terms_menu}}</a></p>
			<p class="level-item"><a href="https://optimole.com/privacy-policy/" target="_blank">{{strings.privacy_menu}}</a>
			</p>
			<p class="level-item"><a :href="'https://speedtest.optimole.com/?url=' + home " target="_blank">{{strings.testdrive_menu}}</a>
			</p>
		</div>
	</div>
</template>

<script>
	import AppHeader from './app-header.vue';
	import CdnDetails from './cdn-details.vue';
	import ConnectLayout from './connect-layout.vue';
	import LastImages from './last-images.vue';
	import ApiKeyForm from "./api-key-form.vue";
	import Options from "./options.vue";
 
	module.exports = {
		name: 'app',
		data() {
			return {
				strings: optimoleDashboardApp.strings,
				home: optimoleDashboardApp.home_url,
				fetchStatus: false,
				tab: 'dashboard'
			}
		},
		components: {
			AppHeader,
			Options,
			ConnectLayout,
			ApiKeyForm,
			CdnDetails,
			LastImages
		},
		mounted() {
			let self = this;
			if (this.$store.state.connected) {
				this.$store.dispatch('retrieveOptimizedImages', {waitTime: 0, component: null});
				self.fetchStatus = true;
			}
		},
		methods:{
			changeTab:function(value){
				this.tab = value;
				
			}
		}
		
	}
</script>
<style lang="sass-loader">
	@import '../../css/style.scss';
	#optimole-app .tabs a{
		margin-bottom: -4px;
	}
	#optimole-app .is-tab{
		min-height: 700px;
	}
</style>