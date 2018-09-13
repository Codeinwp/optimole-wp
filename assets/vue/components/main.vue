<template>
	<div class="card">
		<app-header></app-header>
		<div class="card-content">
			<div class="content">
				<p v-html="strings.account_needed"></p>
				<api-key-form></api-key-form>
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
	</div>
</template>

<script>
	import AppHeader from './app-header.vue';
	import ApiKeyForm from './api-key-form.vue';
	import CdnDetails from './cdn-details.vue';
	import Options from './options.vue';
	import LastImages from './last-images.vue';

	export default {
		components: { LastImages }
	}
	module.exports = {
		name: 'app',
		data() {
			return {
				strings: optimoleDashboardApp.strings,
				fetchStatus: false
			}
		},
		components: {
			AppHeader,
			ApiKeyForm,
			Options,
			CdnDetails,
			LastImages
		},
		mounted() {
			let self = this;
			if ( this.$store.state.connected ) {
				this.$store.dispatch( 'retrieveOptimizedImages', { waitTime: 0, component: null } );
				self.fetchStatus = true;
			}
		}
	}
</script>
<style lang="sass-loader">
	@import '../../css/style.scss';

</style>