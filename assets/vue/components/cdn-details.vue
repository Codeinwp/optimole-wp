<template>
	<div class="cdn-details" style="padding-top: 1.5em;">

			<div class="optml-side-by-side" style="margin-bottom:14%;">
        <div class="optml-circle"></div>
        <div class="is-size-7 optml-gray has-text-left" style="position: relative; left: 24%; top: 12px; ">
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
      <p class="has-text-weight-bold">{{strings.your_api_key}}:</p>
    </div>
    <div class="control is-large">
      <input type="password" :disabled="true" name="api_key" class="optml-light-background is-large" style="width: 100%; height: 40px; font-size:3.5em; vertical-align: middle; color: black; letter-spacing: -0.158em;" value="11111111111111">
    </div>
    <div class="control is-large" style="height: 37px; margin: 4% 0 7% 0;">
      <button  class="optml-button " style="height: 37px; font-size: 0.8em; margin: 2% 0 4% 0; padding: 2%;">{{strings.disconnect_btn}}</button>
    </div>
    <div class=" is-hidden-desktop-only is-hidden-tablet-only is-hidden-mobile" style="margin-top: 7%; color:white !important; background-color: #577BF9;" v-if="this.$store.state.userData.plan === 'free'">
    <div style="margin: 4%;">
        <p class="is-size-5 has-text-centered has-text-weight-bold">
          {{strings.upgrade.title_long}}
        </p>
    </div>
      <ul class="is-size-6 upgrade">
        <li><span class="dashicons dashicons-yes-alt "></span>{{strings.upgrade.reason_1}}</li>
        <li><span class="dashicons dashicons-yes-alt "></span>{{strings.upgrade.reason_2}}</li>
        <li><span class="dashicons dashicons-yes-alt "></span>{{strings.upgrade.reason_3}}</li>
        <li><span class="dashicons dashicons-yes-alt "></span>{{strings.upgrade.reason_4}}</li>
      </ul>
      <div class="is-size-5" style="background: rgba(0, 0, 0, 0.43);border-radius: 4px;">
        {{strings.upgrade.cta}}
      </div>
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