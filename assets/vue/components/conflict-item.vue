<template>
	<div class="notification" :class="conflictClass( item.severity )">
		<div v-html="item.message"></div>

		<a :class="is_loading ? 'is-loading' : '' "
				class="is-pulled-right button optml-conflict-done is-small is-link is-inverted is-outlined"
				v-on:click="dismissConflict( item.id )"><span v-if="!is_loading" class="dashicons dashicons-yes"></span>{{strings.conflict_close}}</a>
		<div class=" is-clearfix"></div>
	</div>
</template>

<script>

	export default {
		name: "conflict-item",
		props: {
			item:{
				type: Object
			},
			is_loading:{
				type: Boolean,
				default: false
			}
		},
		data() {
			return {
				home_url: optimoleDashboardApp.home_url,
				strings: optimoleDashboardApp.strings.conflicts,
			}
		},
		methods: {
			conflictClass(type) {
				if (type === 'high') {
					return 'is-danger';
				}
				if (type === 'medium') {
					return 'is-info';
				}
				return 'is-primary';
			},
			dismissConflict(conflictID) {
				this.is_loading = true;
				this.$store.dispatch('dismissConflict', {conflictID: conflictID, component: this});
			}
		}
	}
</script>
<style scoped>
	#optimole-app .optml-conflict-done.button.is-link.is-outlined.is-loading::after {
		border-color: transparent transparent #fff #fff !important;
	}

	.optml-conflict-done:hover {
		background-color: transparent !important;
		color: #fff !important;
		border-color: transparent !important;
	}
</style>
