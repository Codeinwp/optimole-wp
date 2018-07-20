<template>
	<div class="card">
		<app-header></app-header>
		<div class="card-content">
			<div class="content">
				<p class="is-size-6" v-html="account_needed"></p>
				<api-key-form></api-key-form>
				<transition name="fade" mode="out-in">
					<cdn-details v-if="this.$store.state.userData"></cdn-details>
				</transition>
			</div>
		</div>
	</div>
</template>

<script>
	import AppHeader from './app-header.vue';
	import ApiKeyForm from './api-key-form.vue';
	import CdnDetails from './cdn-details.vue';

	module.exports = {
		name: 'app',
		data() {
			return {
				strings: optimoleDashboardApp.strings,
			}
		},
		computed: {
			account_needed() {
				return this.strings.account_needed
			},
		},
		components: {
			AppHeader,
			ApiKeyForm,
			CdnDetails
		}
		,
	}
</script>
<style lang="sass-loader">
	@import '../../css/style.scss';

</style>