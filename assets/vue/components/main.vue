<template>
	<!--Connecting-->
	<transition name="fade" mode="out-in">

		<li :class="tab === 'conflicts' ? 'is-active' : ''" v-if="conflictCount > 0">
			<a @click="changeTab('conflicts')" class="is-size-5">
					<span class="tab-text is-size-5-mobile is-size-5-touch">{{strings.conflicts_menu_item}}</span>
				<svg width="110%" height="6" viewBox="0 0 100 6" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
					<rect width="110%" height="6" rx="2" fill="#D54222"/>
				</svg>
			</a>
		</li>

					<last-images class="optml-hide-on-mobile" :status="fetchStatus" v-if="! remove_images"></last-images>

					<div class="is-tab" v-if=" tab === 'settings'">
							<options></options>
					</div>

					<div class="is-tab" v-if=" tab === 'conflicts'">
							<conflicts></conflicts>
					</div>
	</transition>
</template>

<script>
	import LastImages from './last-images.vue';
	import Conflicts from './conflicts.vue';
	import Options from "./options.vue";
	import Watermarks from "./watermarks.vue";
	import Metrics from "./metrics.vue";
	module.exports = {
		name: 'app',
		data() {
			return {
				strings: optimoleDashboardApp.strings,
				remove_images: optimoleDashboardApp.remove_latest_images === 'yes',
				fetchStatus: false,
				tab: 'dashboard',
				connecting: optimoleDashboardApp.assets_url + 'img/connecting.png',
			}
		},
		computed: {
			conflictCount() {
				return this.$store.state.conflicts.count || 0
			},
		},
		components: {
			Options,
			Watermarks,
			Conflicts,
			LastImages,
		},
		mounted() {
			let self = this;
			if (this.$store.state.connected && this.$store.state.hasApplication) {
				this.$store.dispatch('retrieveOptimizedImages', {waitTime: 0, component: null});
				this.$store.dispatch('retrieveConflicts', {waitTime: 0, component: null});
				self.fetchStatus = true;
			}
			const urlSearchParams = new URLSearchParams(window.location.search);
			const params = Object.fromEntries(urlSearchParams.entries());
			let images = [];
			if ( Object.prototype.hasOwnProperty.call(params, 'optimole_action') ) {
				for (let i=0; i < 20; i++) {
						if ( Object.prototype.hasOwnProperty.call(params, i) ) {
							if (!isNaN(params[i])) {
								images[i] = parseInt( params[i], 10 );
							}
						}
				}
				params.images = images;
				params.url = window.location.href.split('?')[0] + ( Object.prototype.hasOwnProperty.call(params, "paged") ? "?paged=" + params['paged'] : "" );
				console.log(params.url);
				this.$store.state.queryArgs = params;
				this.changeTab('settings');
			}
			if ( this.$store.state.autoConnect !== 'no' ) {
				this.triggerLoading();
				this.createAndConnect( this.$store.state.autoConnect );
			}
		},
		methods: {
			createAndConnect: function ( email ) {
				this.$store.dispatch('registerOptimole', {
					email: email,
					autoConnect: true,
				}).then((response) => {
					if ( response.code !== 'success' ) {
						this.$store.state.autoConnect = 'no';

						if ( response.message ) {
							this.$store.state.autoConnectError = response.message;
						}
						return;
					}
				})
			},
			changeTab: function (value) {
				this.tab = value;
			},

		}

	}
</script>