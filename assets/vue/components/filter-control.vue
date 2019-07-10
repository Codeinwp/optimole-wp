<template>
    <div :id="'filter-type-'+type">
        <div class="field    ">
            <label class="label column has-text-grey-dark">
                <span v-if="type==='lazyload'">{{strings.exclude_title_lazyload}}</span>
                <span v-if="type==='optimize'">{{strings.exclude_title_optimize}}</span>
            </label>
        </div>
        <div class="field  columns">
            <div class="column is-paddingless is-full ">
                <div class="list">
                    <div class="list-item exclusion-filter" v-for="(item, index) in filters[this.FILTER_TYPES.EXT]">
                        <div class="control">
                            <div class="tags is-centered has-addons">
                                <a class="tag  is-marginless is-link has-text-left"><i>{{strings.exclude_ext_desc}}</i>
                                    <strong>{{index}}</strong></a>
                                <a class="tag  is-marginless  is-delete"
                                   @click="removeRule(FILTER_TYPES.EXT,index)"></a>
                            </div>
                        </div>
                    </div>
                    <div class="list-item exclusion-filter" v-for="(item, index) in filters[this.FILTER_TYPES.URL]">
                        <div class="control">
                            <div class="tags is-centered has-addons">
                                <a class="tag  is-marginless  is-link has-text-left"><i>{{strings.exclude_url_desc}}</i>
                                    <strong>{{index}}</strong></a>
                                <a class="tag  is-marginless  is-delete"
                                   @click="removeRule(FILTER_TYPES.URL,index)"></a>
                            </div>
                        </div>
                    </div>
                    <div class="list-item exclusion-filter"
                         v-for="(item, index) in filters[this.FILTER_TYPES.FILENAME]">
                        <div class="control">
                            <div class="tags is-centered has-addons">
                                <a class="tag  is-marginless   is-link has-text-left"><i>{{strings.exclude_filename_desc}}</i>
                                    <strong>{{index}}</strong></a>
                                <a class="tag  is-marginless  is-delete"
                                   @click="removeRule(FILTER_TYPES.FILENAME,index)"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="field columns">
            <div class="field has-addons column has-addons-centered">
                <p class="control ">
                            <span class="select  is-small">
                              <select @change="changeFilterType($event)">
                                <option :value="FILTER_TYPES.FILENAME">{{strings.filter_filename}}</option>
                                <option :value="FILTER_TYPES.EXT">{{strings.filter_ext}}</option>
                                <option :value="FILTER_TYPES.URL">{{strings.filter_url}}</option>
                              </select>
                            </span>
                </p>
                <p class="control">
                    <a class="button is-small is-static">
                        {{getFilterOperator()}}
                    </a>
                </p>
                <p class="control">
                            <span v-if="this.selected_filter===this.FILTER_TYPES.EXT" class="select  is-small">
                                <select v-model="selected_value">
                                    <option value="svg">.SVG</option>
                                    <option value="png">.JPG</option>
                                    <option value="jpg">.PNG</option>
                                </select>
                            </span>
                    <input v-else v-model="selected_value" class="input is-small" type="text" placeholder="word">
                </p>
                <p class="control">
                    <a class="button is-primary  is-small" :class="this.$store.state.loading ? 'is-loading'  : '' "
                       @click="saveRule()">
                        {{strings.add_filter}}
                    </a>
                </p>
            </div>
        </div>
        <p class="has-text-centered " v-if="this.selected_filter === FILTER_TYPES.URL">
            <i>For homepage use <strong>home</strong> keyword.</i>
        </p>
    </div>
</template>

<script>
	import Vue from 'vue';

	export default {
		name: "filter-control",
		props: ['type'],
		data() {
			return {
				strings: optimoleDashboardApp.strings.options_strings,
				all_strings: optimoleDashboardApp.strings,
				showSave: false,
				selected_filter: 'filename',
				selected_value: '',
				filter_operator: optimoleDashboardApp.strings.options_strings.filter_operator_contains,
				FILTER_TYPES: {
					EXT: 'extension',
					URL: 'page_url',
					FILENAME: 'filename'
				}
			}
		},
		mounted: function () {

		},
		methods: {
			saveRule: function () {
				let filter = {};
				filter[this.type] = {};
				filter[this.type][this.selected_filter] = {};
				filter[this.type][this.selected_filter][this.selected_value] = true;

				if (this.selected_value.length < 3) {
					return;
				}
				this.$store.dispatch('saveSettings', {
					settings: {
						filters: filter
					}
				});
			},
			removeRule: function (rule_type, value) {
				let filter = {};
				filter[this.type] = {};
				filter[this.type][rule_type] = {};
				filter[this.type][rule_type][value] = 'false';
				this.$store.dispatch('saveSettings', {
					settings: {
						filters: filter
					}
				});
			},
			changeFilterType: function (event) {

				this.selected_value = '';


				if (event.target.value === this.FILTER_TYPES.EXT) {
					this.selected_value = 'svg';
					this.filter_operator = this.strings.filter_operator_is;
				}

				if (event.target.value === this.FILTER_TYPES.URL) {
					this.filter_operator = this.strings.filter_operator_contains;
				}

				if (event.target.value === this.FILTER_TYPES.FILENAME) {
					this.filter_operator = this.strings.filter_operator_contains;
				}

				this.selected_filter = event.target.value;

			},
			getFilterOperator() {
				return this.filter_operator;
			}

		},
		computed: {
			filters() {
				return this.$store.state.site_settings.filters[this.type];
			}
		}
	}
</script>

<style scoped>

</style>