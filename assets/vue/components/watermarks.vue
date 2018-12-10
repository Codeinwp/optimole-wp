<template>
    <div>
        <div class="optimized-images" v-if="! loading ">
            <div v-if="!noImages">
                <h3 class="has-text-centered">{{strings.last}} {{strings.optimized_images}}</h3>
                <table class="table is-striped is-hoverable is-fullwidth">
                    <thead>
                    <tr>
                        <th class="optml-image-heading">{{strings.id}}</th>
                        <th class="optml-image-heading">{{strings.image}}</th>
                        <th class="optml-image-heading">{{strings.name}}</th>
                        <th class="optml-image-heading">{{strings.type}}</th>
                        <th class="optml-image-heading">{{strings.action}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item, index) in watermarkData">
                        <td>{{item.ID}}</td>
                        <td><img :src="item.guid" class="optml-image"/></td>
                        <td>{{item.post_title}}</td>
                        <td>{{item.post_mime_type}}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-else>
            <iframe width="1" height="1" :src="home_url" style="visibility: hidden"></iframe>
            <h6 class="has-text-centered">{{strings.loading_latest_images}}</h6>
            <progress class="progress is-large" :value="startTime" :max="maxTime"></progress>
        </div>
        <table class="table is-striped is-hoverable is-fullwidth" v-if="noImages">
            <thead>
            <tr>
                <th class="optml-image-heading has-text-centered" v-html="strings.no_images_found"></th>
            </tr>
            </thead>
        </table>
    </div>
</template>

<script>
	import Image_diff from "./image_diff.vue";

	export default {
		name: "options",
		components: { Image_diff},
		data() {
			return {
				loading: false,
				startTime: 0,
				maxTime: 20,
				noImages: false,

				home_url: optimoleDashboardApp.home_url,
				strings: optimoleDashboardApp.strings.watermarks,
				watermarkData: [{
					ID: 1,
					post_title: '',
					post_mime_type: '',
					guid: '',
				},]

			}
		},
		mounted() {
			if ( this.$store.state.optimizedImages.length > 0) {
				this.loading = false;
				return;
			}
			//this.doProgressBar();
			this.$store.dispatch('retrieveWatermarks', {waitTime: this.maxTime * 1000, component: this});
		},
	}
</script>

<style scoped>
</style>