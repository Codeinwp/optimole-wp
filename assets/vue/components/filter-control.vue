<template>
    <div :id="'filter-type-'+type" class="optml-fit-content">
        <div class="field    ">
            <label class="label column has-text-grey-dark">
                <span v-if="type==='lazyload'">{{strings.exclude_title_lazyload}}</span>
                <span v-if="type==='optimize'">{{strings.exclude_title_optimize}}</span>
            </label>
        </div>
        <div class="field columns optml-light-background optml-fit-content" style="padding: 1% 5%;">
            <div class="field has-addons column has-addons-centered optml-filters-content">
                <p>
                            <span>
                              <select @change="changeFilterType($event)" class="optml-text-input-border">
                                <option :value="FILTER_TYPES.FILENAME">{{strings.filter_filename}}</option>
                                <option :value="FILTER_TYPES.EXT">{{strings.filter_ext}}</option>
                                <option :value="FILTER_TYPES.URL">{{strings.filter_url}}</option>
                                <option :value="FILTER_TYPES.CLASS">{{strings.filter_class}}</option>
                              </select>
                            </span>
                </p>
                <p style="margin: 0 3%;">

                        {{getFilterOperator()}}

                </p>
                <p>
                            <span v-if="this.selected_filter===this.FILTER_TYPES.EXT" >
                                <select v-model="selected_value" class="optml-text-input-border">
                                    <option value="svg">.SVG</option>
                                    <option value="jpg">.JPG</option>
                                    <option value="png">.PNG</option>
                                    <option value="gif">.GIF</option>
                                </select>
                            </span>
                    <input v-else v-model="selected_value" class="optml-textarea optml-text-input-border" type="text" placeholder="word">
                </p>

                    <div class="optml-button optml-fit-content" style="background-color: #577BF9; position: relative; white-space: nowrap; height: fit-content; padding: 1.4%;color: white;margin: 0 0 0 4%;" :class="this.$store.state.loading ? 'is-loading'  : '' "
                       @click="saveRule()">
                        {{strings.add_filter}}
                    </div>

            </div>
        </div>
        <p class="has-text-centered " v-if="this.selected_filter === FILTER_TYPES.URL">
            <i>For homepage use <strong>home</strong> keyword.</i>
        </p>
      <div class="field  columns optml-flex-column" style="margin-top: 4%;">
        <div v-if="anyFilter" class="optml-gray has-text-weight-bold" style="white-space: nowrap;"> {{strings.active_exclusions}}: </div>
        <div class="column is-paddingless is-full">
          <div class="list" style="margin-top: 4%;">
            <div class=" exclusion-filter" v-for="(item, index) in filters[this.FILTER_TYPES.EXT]">
              <div>
                <div class="tags has-addons">
                  <div class="tag  optml-light-background is-marginless is-link has-text-left"><p v-html="strings.exclude_ext_desc"></p>
                    &nbsp;<strong>{{index}}</strong>
                    <a @click="removeRule(FILTER_TYPES.EXT,index)">
                      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="16" viewBox="0 0 13 16" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9 2H12C12.6 2 13 2.4 13 3V4H0V3C0 2.4 0.5 2 1 2H4C4.2 0.9 5.3 0 6.5 0C7.7 0 8.8 0.9 9 2ZM8 2C7.8 1.4 7.1 1 6.5 1C5.9 1 5.2 1.4 5 2H8ZM11.1 15.1L12 5H1L1.9 15.1C2 15.6 2.4 16 2.9 16H10.1C10.6 16 11.1 15.6 11.1 15.1Z" fill="#757296"/>
                      </svg>
                    </a>
                  </div>

                </div>
              </div>
            </div>
            <div class=" exclusion-filter" v-for="(item, index) in filters[this.FILTER_TYPES.URL]">
              <div>
                <div class="tags  has-addons">
                  <div class="tag  optml-light-background is-marginless  is-link has-text-left"><p v-html="strings.exclude_url_desc"></p>
                    &nbsp;<strong>{{index}}</strong>
                    <a @click="removeRule(FILTER_TYPES.URL,index)">
                      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="16" viewBox="0 0 13 16" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9 2H12C12.6 2 13 2.4 13 3V4H0V3C0 2.4 0.5 2 1 2H4C4.2 0.9 5.3 0 6.5 0C7.7 0 8.8 0.9 9 2ZM8 2C7.8 1.4 7.1 1 6.5 1C5.9 1 5.2 1.4 5 2H8ZM11.1 15.1L12 5H1L1.9 15.1C2 15.6 2.4 16 2.9 16H10.1C10.6 16 11.1 15.6 11.1 15.1Z" fill="#757296"/>
                      </svg>
                    </a>
                  </div>

                </div>
              </div>
            </div>
            <div class="exclusion-filter"
                 v-for="(item, index) in filters[this.FILTER_TYPES.FILENAME]">
              <div>
                <div class="tags has-addons">
                  <div class="tag  optml-light-background is-marginless   is-link has-text-left"><p v-html="strings.exclude_filename_desc"></p>
                    &nbsp;<strong>{{index}}</strong>
                    <a @click="removeRule(FILTER_TYPES.FILENAME,index)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="16" viewBox="0 0 13 16" fill="none">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M9 2H12C12.6 2 13 2.4 13 3V4H0V3C0 2.4 0.5 2 1 2H4C4.2 0.9 5.3 0 6.5 0C7.7 0 8.8 0.9 9 2ZM8 2C7.8 1.4 7.1 1 6.5 1C5.9 1 5.2 1.4 5 2H8ZM11.1 15.1L12 5H1L1.9 15.1C2 15.6 2.4 16 2.9 16H10.1C10.6 16 11.1 15.6 11.1 15.1Z" fill="#757296"/>
                    </svg>
                    </a>

                  </div>

                </div>
              </div>
            </div>
            <div class="exclusion-filter"
                 v-for="(item, index) in filters[this.FILTER_TYPES.CLASS]">
              <div>
                <div class="tags has-addons">
                  <div class="tag optml-light-background is-marginless   is-link has-text-left"><p v-html="strings.exclude_class_desc"></p>
                    &nbsp;<strong>{{index}}</strong>
                    <a @click="removeRule(FILTER_TYPES.CLASS,index)" ><svg xmlns="http://www.w3.org/2000/svg" width="13" height="16" viewBox="0 0 13 16" fill="none">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M9 2H12C12.6 2 13 2.4 13 3V4H0V3C0 2.4 0.5 2 1 2H4C4.2 0.9 5.3 0 6.5 0C7.7 0 8.8 0.9 9 2ZM8 2C7.8 1.4 7.1 1 6.5 1C5.9 1 5.2 1.4 5 2H8ZM11.1 15.1L12 5H1L1.9 15.1C2 15.6 2.4 16 2.9 16H10.1C10.6 16 11.1 15.6 11.1 15.1Z" fill="#757296"/>
                    </svg>

                    </a>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
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
					FILENAME: 'filename',
					CLASS: 'class'
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

				if (event.target.value === this.FILTER_TYPES.CLASS) {
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
			},
      anyFilter () {

			  return Object.keys(this.filters[this.FILTER_TYPES.EXT]).length > 0
            || Object.keys(this.filters[this.FILTER_TYPES.CLASS]).length > 0
            || Object.keys(this.filters[this.FILTER_TYPES.URL]).length > 0
            || Object.keys(this.filters[this.FILTER_TYPES.FILENAME]).length > 0;
      }
		}
	}
</script>

<style scoped>

</style>