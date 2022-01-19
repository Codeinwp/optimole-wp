<template>
		<div class="field api-key-field optml-light-background" style="padding: 2%; display: inline-block; width: 57%;">
        <div class="has-text-weight-bold has-text-left is-size-6" style="margin-bottom: 4%;">
          {{!isConnected ? strings.add_api : strings.your_api_key}}
        </div>
        <div class="optml-side-by-side">
          <div v-if="isConnected" ><span class="dashicons dashicons-yes-alt" style="color: #5F9D61; margin: 7px 7px 0 0;"></span></div>
          <div class="control api-key-control" style="width: 75%">
            <input type="text"  name="api_key" class="input optml-font-size-medium"
                :class="validKey ? '' : 'is-danger'" :placeholder="strings.api_key_placeholder"
                v-model="apiKey">
          </div>

            <div v-if="! isConnected" class="button optml-button-style-1 optml-margin-left optml-font-size-medium"
                @click="connect" :class="{ 'is-loading' : this.$store.state.isConnecting }">
              <span>{{strings.connect_btn}}</span>
            </div>
            <div v-else class="optml-button optml-button-style-2 optml-margin-left" style="position: relative;"
                    @click="disconnect" :class="{ 'is-loading' : this.$store.state.isConnecting }">
              <span>{{strings.disconnect_btn}}</span>
            </div>

		    </div>
        <p v-if="! validKey" class="help is-danger">
          {{connectionError}}
        </p>
      <app-selection-form></app-selection-form>
    </div>


</template>

<script>

import AppSelectionForm from "./app-selection-form.vue";
	export default {
		name: 'api-key-form',
    components: {AppSelectionForm},
		data() {
			return {
				apiKey: this.$store.state.apiKey ? this.$store.state.apiKey : '',
				connected: this.$store.state.connected,
				strings: optimoleDashboardApp.strings,

				isLoading: false
			}
		},
		mounted:function(){
		},

		computed: {
			validKey() {
				return this.$store.state.apiKeyValidity;
			},
			connectionError() {
				return this.$store.state.connectError;
			},
			isConnected() {
				return this.$store.state.connected;
			},
		},
		methods: {
			connect: function () {
				this.$store.dispatch( 'connectOptimole', {
					apiKey: this.apiKey,
				} );
			},
			disconnect: function () {
				this.apiKey = '';
				this.$store.dispatch( 'disconnectOptimole', {
				} );
			},
		}
	}
</script>
