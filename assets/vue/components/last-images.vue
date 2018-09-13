<template>
	<div>
		<div class="optimized-images" v-if="imageData.length">
			<h3 class="has-text-centered">{{strings.last}} {{imageData.length}} {{strings.optimized_images}}:</h3>
			<table class="table is-striped is-hoverable is-fullwidth">
				<thead>
				<tr>
					<th>{{strings.image}}</th>
					<th>{{strings.compression}}</th>
				</tr>
				</thead>
				<tbody>
				<tr v-for="(item, index) in imageData">
					<td><a :href="item.url" target="_blank"><img :src="item.url" class="image"/></a></td>
					<td>{{compressionRate(item.ex_size_raw, item.new_size_raw)}}</td>
				</tr>
				</tbody>
			</table>
		</div>
		<div v-else class="loader"></div>
	</div>
</template>

<script>
	export default {
		name: "last-images",
		data() {
			return {
				loading: true,
				strings: optimoleDashboardApp.strings.latest_images,
			}
		},
		props: {
			status,
		},
		mounted() {
			if ( this.status === false ) {
				this.$store.dispatch( 'retrieveOptimizedImages', { waitTime: 10000, component: this } );
			}
		},
		computed: {
			imageData() {
				return this.$store.state.optimizedImages !== null ? this.$store.state.optimizedImages : [];
			},
		},
		methods: {
			compressionRate( oldSize, newSize ) {
				return parseFloat( newSize / oldSize * 100 ).toFixed( 1 ).toString() + '%';
			}
		}
	}
</script>

<style scoped>
	.loader {
		margin: 0 auto;
	}
</style>