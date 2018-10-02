<template>
	<div class="card">
		<app-header></app-header>
		<div class="card-content">
			<div class="content">
				<connect-layout v-if="! this.$store.state.connected"></connect-layout>
				<transition name="fade" mode="out-in">
					<div v-if="this.$store.state.connected">
						<hr/>
						<options></options>
						<cdn-details v-if="this.$store.state.userData"></cdn-details>
						<hr/>
						<last-images :status="fetchStatus"></last-images>
					</div>
				</transition>
			</div>
		</div>
		
		<div class="level-right">
			<p class="level-item"><a href="https://optimole.com" target="_blank">Optimole v{{strings.version}}</a></p>
			<p class="level-item"><a href="https://optimole.com/terms/" target="_blank">{{strings.terms_menu}}</a></p>
			<p class="level-item"><a href="https://optimole.com/privacy-policy/" target="_blank">{{strings.privacy_menu}}</a></p>
			<p class="level-item"><a :href="'https://speedtest.optimole.com/?url=' + home " target="_blank">{{strings.testdrive_menu}}</a></p>
		</div>
	</div>
</template>

<script>
	import AppHeader from './app-header.vue';
	import CdnDetails from './cdn-details.vue';
	import ConnectLayout from './connect-layout.vue';
	import Options from './options.vue';
	import LastImages from './last-images.vue';

	export default {
		components: {ConnectLayout, LastImages}
	}
	module.exports = {
		name: 'app',
		data() {
			return {
				strings: optimoleDashboardApp.strings,
				home: optimoleDashboardApp.home_url,
				fetchStatus: false
			}
		},
		components: {
			AppHeader,
			Options,
			ConnectLayout,
			CdnDetails,
			LastImages
		},
		mounted() {
			let self = this;
			if (this.$store.state.connected) {
				this.$store.dispatch('retrieveOptimizedImages', {waitTime: 0, component: null});
				self.fetchStatus = true;
			}
		}
	}
</script>
<style lang="sass-loader">
	@import '../../css/style.scss';

</style>