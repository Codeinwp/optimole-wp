<script>
	module.exports = {
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
		},
	}
</script>