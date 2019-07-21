<template>
    <div :class="{ 'saving--option' : this.$store.state.loading }">
        <div  id="filters-list">
            <div class="columns  ">
                <filter-control :type="'optimize'"></filter-control>
            </div>
	        <hr/>
            <div class="columns  " v-if="this.$store.state.site_settings.lazyload === 'enabled'">
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