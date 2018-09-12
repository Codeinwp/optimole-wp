<template>
	<div>
		<div class="field has-addons api-key-field">
			<label v-if="isConnected"
					class="label api-key-label has-text-grey-dark">{{strings.api_key_placeholder}}:</label>
			<div class="control is-expanded api-key-control">
				<input :type="isConnected ? 'password' : 'text'" :disabled="isConnected" name="api_key" class="input is-small"
						:class="validKey ? '' : 'is-danger'" :placeholder="strings.api_key_placeholder"
						v-model="apiKey">
			</div>
			<div class="control">
				<button v-if="! isConnected" class="button button is-success is-small"
						@click="connect" :class="{ 'is-loading' : this.$store.state.isConnecting }">
					<span class="icon"><i class="dashicons dashicons-admin-plugins"></i></span>
					<span>{{strings.connect_btn}}</span>
				</button>
				<button v-else class="button is-danger is-small" @click="disconnect"
						:class="{ 'is-loading' : this.$store.state.isConnecting }">
					<span class="icon"><i class="dashicons dashicons-dismiss"></i></span>
					<span>{{strings.disconnect_btn}}</span>
				</button>
			</div>
		</div>
		<p v-if="! validKey" class="help is-danger">
			{{strings.invalid_key}}
		</p>
	</div>
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
		computed: {
			validKey() {
				return this.$store.state.apiKeyValidity;
			},
			isConnected() {
				return this.$store.state.connected;
			},
		},
		methods: {
			connect: function () {
				this.$store.dispatch( 'connectOptimole', {
					req: 'Connect to OptiMole',
					apiKey: this.apiKey,
				} );
			},
			disconnect: function () {
				this.apiKey = '';
				this.$store.dispatch( 'disconnectOptimole', {
					req: 'Disconnect from OptiMole',
				} );
			},
		}
	}
</script>