<template>
	<div class="cdn-details">
		<hr/>
		<div class="account level has-text-centered">
			<div class="level-left">
				<span class="label level-item">{{strings.logged_in_as}}:</span>
				<p class="details level-item tags has-addons">
					<span class="tag is-light">{{userData.display_name}}</span>
					<span class="tag is-paddingless"><img :src="userData.picture" class="image is-24x24 is-rounded" :alt="userData.display_name"></span>
				</p>
			</div>
			<div class="level-right">
				<span class="label level-item">{{strings.private_cdn_url}}:</span>
				<p class="details level-item tag is-light">{{userData.cdn_key}}.i.optimole.com</p>
			</div>
		</div>
		<hr/>
		<div class="level stats">
			<div class="level-left">
				<div class="level-item">
					<div class="tags has-addons">
						<span class="tag is-info">{{strings.usage}}:</span>
						<span class="tag">{{this.userData.usage_pretty}}</span>
					</div>
				</div>
			</div>
			<h4 class="level-item is-size-5 is-marginless has-text-grey">
				{{computedPercentage()}}%
			</h4>
			<div class="level-right">
				<div class="level-item">
					<div class="tags has-addons">
						<span class="tag is-info">{{strings.quota}}:</span>
						<span class="tag">{{this.userData.quota_pretty}}</span>
					</div>
				</div>
			</div>
		</div>
		<progress class="progress is-success" :value="this.userData.usage" :max="this.userData.quota">60%</progress>
        <div class="level-right">
            <button class="button is-small is-info" v-on:click="requestUpdate"><span class="icon"><i class="dashicons dashicons-image-rotate"></i></span> <span>Refresh Stats</span></button>
        </div>
	</div>
</template>

<script>
	export default {
		name: "cdn-details",
		data() {
			return {
				userData: this.$store.state.userData,
				strings: optimoleDashboardApp.strings
			}
		},
		methods: {
			computedPercentage() {
				return ((this.userData.usage / this.userData.quota) * 100).toFixed( 2 );
			},
            requestUpdate() {
	            this.$store.dispatch('requestStatsUpdate', {waitTime: 0, component: null});
            }
		},
	}
</script>

<style scoped>
	#optimole-app .label {
		margin-top: 0;
	}
</style>