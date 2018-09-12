<template>
	<div class="optimized-images">
		<h6></h6>
		<div v-if="imageData">
			<table class="table is-striped is-hoverable is-fullwidth">
				<thead>
				<tr>
					<th>{{strings.image}}</th>
					<th>{{strings.compression}}</th>
				</tr>
				</thead>
				<tbody>
				<tr v-for="(item, index) in imageData">
					<td><img :src="item.url" class="image"/></td>
					<td>{{compressionRate(item.ex_size, item.new_size)}}</td>
				</tr>
				</tbody>
			</table>
		</div>
		<div v-if="loading" class="loader"></div>
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
		mounted() {
			this.$store.dispatch( 'retrieveOptimizedImages', this );
		},
		computed: {
			imageData() {
				return this.$store.state.optimizedImages
			},
		},
		methods: {
			compressionRate( oldSize, newSize ) {

				return toString( newSize / oldSize * 100 ) + '%';
			}
		}
	}
</script>

<style scoped>
	.loader {
		margin: 0 auto;
	}
</style>