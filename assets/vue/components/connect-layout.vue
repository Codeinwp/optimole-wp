<template>
	<section class="is-clearfix optml-line-height">
		<div class="notification is-danger" v-if="isRestApiWorking" v-html="strings.notice_api_not_working"></div>
	</section>
</template>

<script>
	export default {
		computed: {
			isLoading: function () {
				return this.$store.state.loading;
			},
			isRestApiWorking: function () {
				return this.$store.state.apiError;
			}
		},
	}
</script>
<style scoped>
	input, .notification .delete, button {
		box-sizing: border-box !important;
	}
	#optimole-app .subtitle {
		line-height: 1.5em;
		word-break: keep-all !important;
	}

</style>