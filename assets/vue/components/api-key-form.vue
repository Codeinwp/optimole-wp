<template>
	<div>
		<div class="field has-addons">
			<label v-if="isConnected"
					class="label api-key-label has-text-grey-dark">{{strings.api_key_placeholder}}:</label>
			<div class="control is-expanded api-key-control">
				<input type="password" :disabled="isConnected" name="api_key" class="input is-small"
						:class="validKey ? '' : 'is-danger'" :placeholder="strings.api_key_placeholder"
						v-model="apiKey">
			</div>
			<div class="control">
				<button v-if="! isConnected" class="button button is-success is-small"
						@click="connect" :class="this.$store.state.loading ? 'is-loading' : ''">
					<span class="icon"><i class="dashicons dashicons-admin-plugins"></i></span>
					<span>{{strings.connect_btn}}</span>
				</button>
				<button v-else class="button is-danger is-small" @click="disconnect"
						:class="this.$store.state.loading ? 'is-loading' : ''">
					<span class="icon"><i class="dashicons dashicons-dismiss"></i></span>
					<span>{{strings.disconnect_btn}}</span>
				</button>
			</div>
		</div>
		<p v-if="! validKey" class="help is-danger">
			{{strings.invalid_key}}
		</p>
		<div class="field has-addons" v-if="isConnected">
			<label class="label has-text-grey-dark">{{strings.toggle_ab_item}}:</label>
			<toggle-button @change="enableAdminBar"
					:value="adminBarItemEnabled"
					:disabled="this.$store.state.loading"
					:labels="{checked: strings.show, unchecked: strings.hide}"
					:width="60"
					color="#e7602b"></toggle-button>
		</div>
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
				adminBarItem: optimoleDashboardApp.admin_bar_item,
			}
		},
		computed: {
			validKey() {
				return this.$store.state.apiKeyValidity;
			},
			isConnected() {
				return this.$store.state.connected;
			},
			adminBarItemEnabled() {
				if ( this.adminBarItem === 'disabled' ) {
					return false;
				}
				return true;
			}
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
			enableAdminBar: function () {
				this.$store.dispatch( 'enableAdminBarExtension', {
					req: 'Toggle admin bar item.',
				} );
			},
		}
	}
</script>