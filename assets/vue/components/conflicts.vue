<template>
	<div>
		<div class="conflicts-table">
			<h6 v-if="!noConflicts" class="has-text-centered">{{strings.title}}</h6>
			<div v-if="!noConflicts">

				<div class="row" v-for="(item, index) in conflictData">
					<div class="column">
						<conflict-item v-bind:item=item v-bind:is_loading="false"></conflict-item>
					</div>
				</div>
			</div>
			<div v-if="noConflicts">
				<table class="table is-striped is-hoverable is-fullwidth">
					<tbody>
					<tr>
						<th class="optml-image-heading has-text-centered" v-html="strings.no_conflicts_found"></th>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</template>

<script>

	import ConflictItem from "./conflict-item.vue";

	export default {
		name: "conflicts",
		components: {ConflictItem},
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
		}
	}
</script>
