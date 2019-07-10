<template>
    <div :class="{ 'saving--option' : this.$store.state.loading }">
        <div class="columns is-desktop" id="filters-list">
            <div class="column  ">
                <filter-control :type="'optimize'"></filter-control>
            </div>
            <div class="column  " v-if="this.$store.state.site_settings.lazyload === 'enabled'">
                <filter-control :type="'lazyload'"></filter-control>
            </div>
        </div>

    </div>
</template>

<script>

	import FilterControl from "./filter-control.vue";
	export default {
		name: "exclusions",
		components: {FilterControl},
		data() {
			return {
				strings: optimoleDashboardApp.strings.options_strings,
				all_strings: optimoleDashboardApp.strings,
                new_data: {}

			}
		},
		mounted: function () {
		},
		methods: {

		},
		computed: {
			site_settings() {
				return this.$store.state.site_settings;
			},


		}
	}
</script>

<style scoped>

</style>