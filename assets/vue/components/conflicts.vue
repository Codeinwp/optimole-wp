<template>
	<div>
		<div class="conflicts-table">
            <h3 class="has-text-centered">{{strings.title}}</h3>
			<div v-if="!noConflicts">
				<table class="table is-striped is-hoverable is-fullwidth">
					<tbody>
					<tr v-for="(item, index) in conflictData">
						<td>
                            <div class="notification" :class="conflictClass( item.severity )">
                                <button class="delete" style="box-sizing: border-box;" v-on:click="dismissConflict( item.id )"></button>
                                {{item.message}}
                            </div>
                        </td>
					</tr>
					</tbody>
				</table>
			</div>
            <div v-if="noConflicts">
                <table class="table is-striped is-hoverable is-fullwidth">
                    <thead>
                    <tr>
                        <th class="optml-image-heading has-text-centered" v-html="strings.no_conflicts_found"></th>
                    </tr>
                    </thead>
                </table>
            </div>
		</div>
	</div>
</template>

<script>

	export default {
		name: "conflicts",
		data() {
			return {
				home_url: optimoleDashboardApp.home_url,
				strings: optimoleDashboardApp.strings.conflicts,
			}
		},
		computed: {
			noConflicts() {
				return this.$store.state.conflicts.count === 0
            },
			conflictData() {
				return this.$store.state.conflicts.conflicts !== null ? this.$store.state.conflicts.conflicts : [];
			},
		},
		methods: {
			conflictClass( type ) {
				if ( type === 'high' ) {
					return 'is-danger';
				}
				if ( type === 'medium' ) {
					return 'is-warning';
				}
				return 'is-info';
            },
			dismissConflict( conflictID ) {
				this.$store.dispatch('dismissConflict', {conflictID: conflictID, component: this});
			}
		}
	}
</script>