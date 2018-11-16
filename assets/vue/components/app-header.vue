<template>
	<div>
		<div class="header has-text-centered level">
			
			<div class="level-left">
				<a class="logo level-item" href="https://optimole.com" target="_blank">
					<figure class="media">
						<img :src="logo" :alt="strings.optimole + ' ' + strings.service_details" width="75" height="75">
					</figure>
				</a>
				<h3 class="has-text-centered has-text-grey-dark is-size-4 level-item">
					<span class="has-text-weight-semibold">
					OptiMole - {{strings.service_details}}
					</span>
				</h3>
			</div>
			<div class="level-right">
				<div class="tags has-addons level-item">
					<span class="tag is-dark">{{strings.status}}</span>
					<span v-if="connected" class="tag is-success">{{strings.connected}}</span>
					<span v-else class="tag is-danger">{{strings.not_connected}}</span>
				</div>
			</div>
			
		</div>
		<hr/>
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