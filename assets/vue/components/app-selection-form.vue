<template>
	<div style="margin-top: .5rem;" v-if="availableApps">
		<div class="field has-addons app-selection-field">
			<label v-if="isConnected"
						 class="label api-key-label has-text-grey-dark">Domain:</label>
			<div class="control is-expanded app-selection-control">
        <span class="select is-fullwidth">
					<select v-model="selected_app">
							<option v-for="( app, index ) in availableApps.available_apps" :key="index" :value="index">{{app.domain}}</option>
					</select>
        </span>
			</div>
			<div class="control">
				<button class="button button is-success  "
								@click="selectDomain" :class="{ 'is-loading' : this.$store.state.isConnecting }">
					<span class="icon"><i class="dashicons dashicons-cloud-saved"></i></span>
					<span>Select Domain</span>
				</button>
			</div>
		</div>
		</div>
	</div>
</template>

<script>
export default {
	name: "app-selection-form",
	data() {
		return {
			connected: this.$store.state.connected,
			strings: optimoleDashboardApp.strings,
			selected_app: 0,
		}
	},
	computed: {
		isConnected() {
			return this.$store.state.connected;
		},
		availableApps() {
			return this.$store.state.availableApps;
		},
    activeApp() {
		  if ( this.$store.state.availableApps !== null && this.$store.state.availableApps !== undefined && this.$store.state.availableApps.available_apps !== undefined ) {
        return this.$store.state.availableApps.available_apps[this.selected_app].key;
      }
		  return "";
    }
	},
  methods: {
    selectDomain:  function () {
      this.$store.dispatch( 'selectOptimoleDomain', {
        apiKey: this.$store.state.apiKey,
        application: this.activeApp,
      } );
    }
  }
}
</script>

<style scoped>

</style>