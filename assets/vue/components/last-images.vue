<template>
	<div>
		<div class="optimized-images" v-if="! loading ">
			<div v-if="!noImages">
				<h3 class="has-text-weight-bold is-size-6" style="margin-bottom: 4%;">{{strings.last}} {{strings.optimized_images}}</h3>
				<table class="table is-striped is-hoverable is-fullwidth">
					<tbody>
					<tr v-for="index in [0,5]">
						<td><a :href="imageData[index].url" target="_blank"><img :src="imageData[index].url" class="optml-image"/></a></td>
            <td><a :href="imageData[index+1].url" target="_blank"><img :src="imageData[index+1].url" class="optml-image"/></a></td>
            <td><a :href="imageData[index+2].url" target="_blank"><img :src="imageData[index+2].url" class="optml-image"/></a></td>
            <td><a :href="imageData[index+3].url" target="_blank"><img :src="imageData[index+3].url" class="optml-image"/></a></td>
            <td><a :href="imageData[index+4].url" target="_blank"><img :src="imageData[index+4].url" class="optml-image"/></a></td>



            <!--            <td><a :href="item.url" target="_blank"><img :src="item.url" class="optml-image"/></a></td>-->
<!--            <td><a :href="item.url" target="_blank"><img :src="item.url" class="optml-image"/></a></td>-->
<!--            <td><a :href="item.url" target="_blank"><img :src="item.url" class="optml-image"/></a></td>-->
<!--            <td><a :href="item.url" target="_blank"><img :src="item.url" class="optml-image"/></a></td>-->
<!--						<td><p-->
<!--								class="optml-ratio-feedback"-->
<!--								v-html="compressionRate(item.ex_size_raw, item.new_size_raw)"></p>-->
<!--						</td>-->
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

	export default {
		name: "last-images",
		data() {
			return {
				loading: true,
				startTime: 0,
				maxTime: 20,
				noImages: false,
				home_url: optimoleDashboardApp.home_url,
				strings: optimoleDashboardApp.strings.latest_images,
			}
		},
		props: {
			status,
		},
		mounted() {
			if ( this.$store.state.optimizedImages.length > 0) {
				this.loading = false;
				return;
			}
			this.doProgressBar();
			this.$store.dispatch('retrieveOptimizedImages', {waitTime: this.maxTime * 1000, component: this});
		},
		watch: {
			imageData: function () {
				if (this.imageData.length > 0) {
					this.startTime = this.maxTime;
					setTimeout(() => {
						this.loading = false
					}, 1000);
				}
			}
		},
		computed: {
			imageData() {

				return this.$store.state.optimizedImages !== null ? this.$store.state.optimizedImages : [];
			},
		},
		methods: {
			doProgressBar() {
				if (this.startTime === this.maxTime) {
					return;
				}
				this.startTime++;
				//console.log(this.startTime);
				setTimeout(this.doProgressBar, 1000);

			},
			compressionRate(oldSize, newSize) {
				let value = (parseFloat(oldSize / newSize * 100) - 100).toFixed(1);
				if (value < 1) {
					return this.strings.same_size;
				}
				if (value > 1 && value < 25) {
					return this.strings.small_optimization.replace('{ratio}', value.toString() + '%');
				}
				if (value > 25 && value < 100) {
					return this.strings.medium_optimization.replace('{ratio}', value.toString() + '%');
				}
				if (value > 100) {
					return this.strings.big_optimization.replace('{ratio}', (Math.floor((value / 10) + 10) / 10).toFixed(1).toString() + 'x');
				}
			}
		}
	}
</script>

<style scoped>
	.loader {
		margin: 0 auto;
		font-size: 10em;
		border-left: 2px solid #888 !important;
		border-bottom: 2px solid #888 !important;
		margin-top: 0.2em;
	}
	
	.progress::-webkit-progress-value {
		transition: width 0.5s ease;
	}
</style>