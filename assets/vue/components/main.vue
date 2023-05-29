<template>
	<!--Connecting-->
	<transition name="fade" mode="out-in">
					<div class="is-tab" v-if=" tab === 'settings'">
							<options></options>
					</div>
	</transition>
</template>

<script>
	import Options from "./options.vue";
	import Watermarks from "./watermarks.vue";
	module.exports = {
		name: 'app',
		computed: {
			conflictCount() {
				return this.$store.state.conflicts.count || 0
			},
		},
		components: {
			Options,
			Watermarks,
		},
		mounted() {

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