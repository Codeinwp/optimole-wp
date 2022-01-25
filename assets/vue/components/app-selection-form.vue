<template>
	<div style="margin-top: 2.5%;" v-if="availableApps">
		<div class="field has-addons app-selection-field">
			<label v-if="isConnected"
						 class="label api-key-label has-text-grey-dark is-size-6" style="white-space:nowrap;">{{strings.select + " " + strings.your_domain}}</label>
			<div class="control app-selection-control" style="width: 58.4%;">
        <span class="select is-fullwidth">
					<select v-model="selected_app">
							<option v-for="( app, index ) in availableApps.available_apps" :key="index" :value="index">{{app.domain}}</option>
					</select>
        </span>
			</div>
			<div class="button optml-button-style-1" style=" margin-left: 2%; padding: 2% 3.4%;"
								@click="selectDomain" :class="{ 'is-loading' : this.$store.state.isConnecting }">
					<span>{{strings.select}} </span>
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