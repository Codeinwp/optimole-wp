<template>
	<div>
		<div class="conflicts-table">
			<div v-if="!noConflicts">
				<h3 class="has-text-centered">{{strings.title}}</h3>
				<table class="table is-striped is-hoverable is-fullwidth">
					<thead>
					<tr>
						<th class="optml-conflict-message-heading">{{strings.message}}</th>
					</tr>
					</thead>
					<tbody>
					<tr v-for="(item, index) in conflictData">
						<td>
                            <div class="notification" :class="conflictClass( item.severity )">
                                <button class="delete" style="box-sizing: border-box;"></button>
                                {{item.message}}
                            </div>
                        </td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
		<table class="table is-striped is-hoverable is-fullwidth" v-if="noConflicts">
			<thead>
			<tr>
				<th class="optml-image-heading has-text-centered" v-html="strings.no_conflicts_found"></th>
			</tr>
			</thead>
		</table>
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
            }
		}
	}
</script>