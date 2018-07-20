<template>
	<div class="cdn-details">
		<hr/>
		<div class="account level has-text-centered">
			<div class="level-left">
				<span class="label level-item">{{strings.logged_in_as}}:</span>
				<p class="details level-item tag is-rounded is-primary is-medium">
					<img :src="userData.picture" class="image is-32x32 is-rounded" :alt="userData.display_name">{{userData.display_name}}
				</p>
			</div>
			<div class="level-right">
				<span class="label level-item">{{strings.private_cdn_url}}:</span>
				<p class="details level-item tag is-rounded is-primary is-medium">{{userData.cdn_key}}.i.optimole.com</p>
			</div>
		</div>
		<hr/>
		<div class="level stats">
			<div class="level-left">
				<div class="level-item">
					<div class="tags has-addons">
						<span class="tag is-medium is-light">{{strings.usage}}:</span>
						<span class="tag is-medium is-info">{{this.userData.usage | mbToGb}} GB</span>
					</div>
				</div>
			</div>
			<h4 class="level-item is-size-4 is-marginless has-text-weight-bold">
				{{computedPercentage()}}%
			</h4>
			<div class="level-right">
				<div class="level-item">
					<div class="tags has-addons">
						<span class="tag is-medium is-light">{{strings.quota}}:</span>
						<span class="tag is-medium is-primary">{{this.userData.quota | mbToGb}} GB</span>
					</div>
				</div>
			</div>
		</div>
		<progress class="progress is-success" :value="this.userData.usage" :max="this.userData.quota">60%</progress>

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
			}
		},
		filters: {
			mbToGb( value ) {
				return (value / 1000).toFixed( 3 );
			}
		}
	}
</script>