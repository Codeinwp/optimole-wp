<template>
	<div class="cdn-details" style="padding-top: 1.5em;">

			<div class="optml-side-by-side" style="margin-bottom:14%;">
				<div class="optml-circle" style="position: relative !important;" v-if="userData.display_name.length < 20"></div>
				<div class="is-size-6 optml-gray has-text-left"  style="position: relative; top: 12px; ">
				<p class="has-text-weight-bold">{{strings.logged_in_as}}:</p>
				<p>
					{{userData.display_name}}
				</p>
				</div>
			</div>
			<hr/>
			<div>
				<p class="is-size-7 has-text-weight-bold optml-gray has-text-left">{{strings.private_cdn_url}}:</p>
			</div>
		<div class="control is-large">
			<input type="text" :disabled="true" class="optml-light-background is-large optml-gray" style="width: 100%; height: 40px; font-size:1em; vertical-align: middle;" v-model="connectedDomain">
		</div>
		<div class="is-size-7 optml-gray has-text-left">
			<p class="has-text-weight-bold">{{strings.looking_for_api_key}}</p>
		</div>
		<div class="control" style="font-size: 14px !important" v-html="strings.optml_dashboard">

		</div>
		<div class="control is-large" style="height: 37px; margin: 4% 0 7% 0;">
			<a  class="button optml-button optml-button-style-2 " :class="this.$store.state.showDisconnect ? 'disabled' : ''" style="margin: 2.5% 0 4% 0;" @click="disconnect">{{strings.disconnect_btn}}</a>
		</div>





	</div>

</template>

<script>

	import ApiKeyForm from "./api-key-form.vue";

	export default {
		name: "cdn-details",
		data() {
			return {
				strings: optimoleDashboardApp.strings,
				apiKey: this.$store.state.apiKey ? this.$store.state.apiKey : '',
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
			disconnect: function () {
				this.$store.commit('toggleShowDisconnectNotice', true);
			},
		},
		components: {
			ApiKeyForm
		}
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