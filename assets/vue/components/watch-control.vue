<template>
    <div :id="'watch-type-'+type">
        <div class="field">
            <label class="label column has-text-grey-dark">
                <span v-if="type==='lazyload'">{{strings.watch_title_lazyload}}</span>
            </label>
        </div>
        <div class="field  columns">
            <div class="column is-paddingless is-full ">
                <div class="list">
                    <div class="list-item exclusion-filter" v-for="(item, index) in watchers[this.WATCH_TYPES.CLASS]">
                        <div class="control">
                            <div class="tags is-centered has-addons">
                                <a class="tag  is-marginless is-link has-text-left"><i>{{strings.watch_class_desc}}</i>
                                    <strong>.{{index}}</strong></a>
                                <a class="tag  is-marginless  is-delete"
                                   @click="removeRule(WATCH_TYPES.CLASS,index)"></a>
                            </div>
                        </div>
                    </div>
                    <div class="list-item exclusion-filter"
                         v-for="(item, index) in watchers[this.WATCH_TYPES.ID]">
                        <div class="control">
                            <div class="tags is-centered has-addons">
                                <a class="tag  is-marginless   is-link has-text-left"><i>{{strings.watch_id_desc}}</i>
                                    <strong>#{{index}}</strong></a>
                                <a class="tag  is-marginless  is-delete"
                                   @click="removeRule(WATCH_TYPES.ID,index)"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="field columns">
            <div class="field has-addons column has-addons-centered">
                <p class="control ">
                            <span class="select is-small">
                              <select @change="changeWatchType($event)">
                                <option :value="WATCH_TYPES.CLASS">{{strings.watch_class}}</option>
                                <option :value="WATCH_TYPES.ID">{{strings.watch_id}}</option>
                              </select>
                            </span>
                </p>
                <p class="control">
                    <a class="button is-small is-static">
                        {{getFilterOperator()}}
                    </a>
                </p>
                <p class="control">
                    <input v-model="selected_value" class="input is-small" type="text" placeholder="word">
                </p>
                <p class="control">
                    <a class="button is-primary  is-small" :class="this.$store.state.loading ? 'is-loading'  : '' "
                       @click="saveRule()">
                        {{strings.add_watch}}
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>

<script>
	import Vue from 'vue';

	export default {
		name: "watch-control",
		props: ['type'],
		data() {
			return {
				strings: optimoleDashboardApp.strings.options_strings,
				all_strings: optimoleDashboardApp.strings,
				showSave: false,
				selected_watch: 'class',
				selected_value: '',
				watch_operator: optimoleDashboardApp.strings.options_strings.filter_operator_is,
				WATCH_TYPES: {
					ID: 'id',
					CLASS: 'class'
				}
			}
		},
		mounted: function () {

		},
		methods: {
			saveRule: function () {
				let watch = {};
				watch[this.type] = {};
				watch[this.type][this.selected_watch] = {};
				watch[this.type][this.selected_watch][this.selected_value] = true;

				if (this.selected_value.length < 3) {
					return;
				}
				this.$store.dispatch('saveSettings', {
					settings: {
						watchers: watch
					}
				});
			},
			removeRule: function (rule_type, value) {
				let watch = {};
				watch[this.type] = {};
				watch[this.type][rule_type] = {};
				watch[this.type][rule_type][value] = 'false';
				this.$store.dispatch('saveSettings', {
					settings: {
						watchers: watch
					}
				});
			},
			changeWatchType: function (event) {

				this.selected_value = '';

				if ( event.target.value === this.WATCH_TYPES.ID || event.target.value === this.WATCH_TYPES.CLASS ) {
					this.watch_operator = this.strings.filter_operator_is;
				}

				this.selected_watch = event.target.value;

			},
			getFilterOperator() {
				return this.watch_operator;
			}

		},
		computed: {
			watchers() {
				return this.$store.state.site_settings.watchers[this.type];
			}
		}
	}
</script>

<style scoped>

</style>