<template>
	<div class="field-body" :class="{ 'saving--option' : this.$store.state.loading }">
		<div class="field is-horizontal">
			<label class="label has-text-grey-dark">{{strings.enable_image_replace}}:</label>
			<toggle-button @change="toggleOption('image_replacer')"
			               :value="imageReplacerStatus"
			               :disabled="this.$store.state.loading"
			               :labels="{checked: strings.enabled, unchecked: strings.disabled}"
			               :width="75"
			               color="#008ec2"></toggle-button>
		</div>
		<div class="field is-horizontal">
			<label class="label has-text-grey-dark">{{strings.toggle_ab_item}}:</label>
			<toggle-button @change="toggleOption('admin_bar_item')"
			               v-model="adminBarItem"
			               :disabled="this.$store.state.loading"
			               :labels="{checked: strings.show, unchecked: strings.hide}"
			               :width="60"
			               color="#008ec2"></toggle-button>
		</div>
	
	</div>

</template>

<script>
	export default {
		name: "options",
		data() {
			return {
				strings: optimoleDashboardApp.strings.options_strings,
				adminBarItem: optimoleDashboardApp.admin_bar_item,
				imageReplacer: optimoleDashboardApp.image_replacer,
			}
		},
		methods: {
			toggleOption: function (optionKey) {
				this.$store.dispatch('toggleSetting', {
					req: 'Toggle ' + optionKey,
					option_key: optionKey,
					type: 'toggle',
				})
			},
		},
		computed: {
			adminBarItemStatus: function () {
				return !(this.adminBarItem === 'disabled');
			},

			imageReplacerStatus() {
				return !(this.imageReplacer === 'disabled');
			}
		}
	}
</script>

<style scoped>
	.saving--option {
		opacity: .75;
	}
	
	.field:nth-child(even) {
		justify-content: flex-end;
	}
</style>