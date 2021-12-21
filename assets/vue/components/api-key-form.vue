<template>
		<div class="field api-key-field optml-light-background" style="padding: 2%; display: inline-block; width: 40%;">
        <div class="has-text-weight-bold has-text-left is-size-6" style="margin-bottom: 4%;">
          Add your API Key
        </div>
        <div class="optml-side-by-side" style="justify-content: center; ">
          <div class="control api-key-control" style="width: 70%">
            <input :type="isConnected ? 'password' : 'text'" :disabled="isConnected" name="api_key" class="input  "
                :class="validKey ? '' : 'is-danger'" :placeholder="strings.api_key_placeholder"
                v-model="apiKey">
          </div>
          <div class="control" style="display: inline-block;">
            <button v-if="! isConnected" class="button button is-info  "
                @click="connect" :class="{ 'is-loading' : this.$store.state.isConnecting }">
              <span>{{strings.connect_btn}}</span>
            </button>
          </div>
		    </div>
    </div>
		<p v-if="! validKey" class="help is-danger">
			{{connectionError}}
		</p>

</template>

<script>
	export default {
		name: 'api-key-form',
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
