<template>
	<div>
		<div class="header has-text-centered level" style="padding-top: 1em; padding-bottom: 3em;">
			
			<div class="level-left">
				<a class="logo level-item" href="https://optimole.com" target="_blank">
					<figure class="image is-64x64 is-hidden-touch">
						<img :src="logo" :alt="strings.optimole + ' ' + strings.service_details" >
					</figure>
					<figure class="image is-48x48 is-hidden-desktop">
						<img :src="logo" :alt="strings.optimole + ' ' + strings.service_details" >
					</figure>
				</a>
				<h3 class="has-text-centered has-text-dark is-size-4 is-size-3-widescreen is-size-5-touch level-item">
					<span class="has-text-weight-bold optml-font">
					Optimole
					</span>
				</h3>
			</div>
			<div class="level-right">
				<div class="tags has-addons level-item has-text-weight-bold optml-font">
					<span class="button is-size-7" style="border: none">{{strings.status}}</span>
					<span v-if="connected" class="button is-success is-size-7">{{strings.connected}}</span>
					<span v-else class="tag is-danger">{{strings.not_connected}}</span>
				</div>
			</div>
			
		</div>
	</div>
</template>

<script>
	export default {
		name: "app-header",
		data() {
			return {
				logo: optimoleDashboardApp.assets_url + 'img/logo.png',
				strings: optimoleDashboardApp.strings,
			}
		},
		computed: {
			connected() {
				return this.$store.state.connected;
			}
		}
	}
</script>

<style scoped>
	@media ( min-width: 769px ) {
		#optimole-app hr {
			margin: 0;
		}
	}
</style>