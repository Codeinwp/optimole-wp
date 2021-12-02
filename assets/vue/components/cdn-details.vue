<template>
	<div class="cdn-details">
		<hr/>
		<div class="has-text-centered">
			<div>
				<span class="label level-item">{{strings.logged_in_as}}:</span>
				<p class="details level-item tags has-addons">
					<span class="tag is-light">{{userData.display_name}}</span>
					<span class="tag is-paddingless"><img :src="userData.picture" class="image is-24x24 is-rounded"
					                                      :alt="userData.display_name"></span>
				</p>
			</div>
      <hr/>
			<div>
				<span class="label level-item">{{strings.private_cdn_url}}:</span>
				<p class="details level-item tag is-light">{{connectedDomain}}</p>
			</div>
		</div>
		<hr/>

		<div  v-if="this.userData.visitors_limit && parseInt( this.userData.visitors_limit ) > 0" class="level stats">
			<div class="level-left">
				<div class="level-item">
					<div class="tags has-addons">
						<span class="tag is-info">{{strings.usage}}:</span>
						<span class="tag">{{this.userData.visitors_pretty}} </span>
					</div>
				</div>
			</div>
			<h4 class="level-item is-size-5 is-marginless has-text-grey">
				{{computedPercentageVisitors()}}%
			</h4>
			<div class="level-right">
				<div class="level-item">
					<div class="tags has-addons">
						<span class="tag is-info">{{strings.quota}}:</span>
						<span class="tag">{{this.userData.visitors_limit_pretty}} (<a href="https://docs.optimole.com/article/1134-how-optimole-counts-the-number-of-visitors" target="_blank">?</a>)</span>
					</div>
				</div>
			</div>
		</div>
		<div   v-else class="level stats">
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
		
		<div class="level-right">
			
			<div class="level-item optml-refresh-wrapper">
			<a class="button is-small is-warning" v-if="this.$store.state.loading"><span class="icon"><i
					class="dashicons dashicons-backup is-size-6"></i></span> <span>{{strings.updating_stats_cta}}</span>
			</a>
			<a class="button is-small is-info" v-else v-on:click="requestUpdate"><span class="icon"><i
					class="dashicons dashicons-image-rotate  is-size-6"></i></span> <span>{{strings.refresh_stats_cta}}</span>
			</a>
			</div>
		</div>
		<hr/>
		<progress v-if="this.userData.visitors_limit && parseInt( this.userData.visitors_limit ) > 0" class="progress is-success" :value="this.userData.visitors" :max="this.userData.visitors_limit">60%</progress>
		<progress v-else class="progress is-success" :value="this.userData.usage" :max="this.userData.quota">60%</progress>

	</div>
</template>

<script>
	export default {
		name: "cdn-details",
		data() {
			return {
				strings: optimoleDashboardApp.strings
			}
		},
		computed:{
		  connectedDomain() {
		    if ( this.userData.domain !== undefined && this.userData.domain !== '' ) {
		      return this.userData.domain;
        }
		    return this.userData.cdn_key + '.i.optimole.com';
      },
			userData:function(){

                return  this.$store.state.userData;
			}
		},
		methods: {
			computedPercentage() {
				return ((this.userData.usage / this.userData.quota) * 100).toFixed(2);
			},
			computedPercentageVisitors() {
				return ((this.userData.visitors / this.userData.visitors_limit) * 100).toFixed(2);
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
	.optml-refresh-wrapper i.dashicons{
		width:auto;
		height:auto;
	}
</style>