<template>
    <div :class="{ 'saving--option' : this.$store.state.loading }">


        <!-- CSS minify toggle button -->
        <div class="field  columns" :class="{'is-field-disabled':isReplacerOff }">
            <label class="label column has-text-grey-dark">
                {{strings.enable_css_minify_title}}

                <p class="is-italic has-text-weight-normal">
                    {{strings.css_minify_desc}}
                </p>
            </label>

            <div class="column is-3 ">
                <toggle-button :class="'has-text-dark'"
                               v-model="cssMinifyStatus"
                               :disabled="this.$store.state.loading"
                               :width="37"
                               :height="20"
                               color="#577BF9"></toggle-button>
            </div>
        </div>
        <!-- JS minify toggle button -->
        <div class="field  columns"  :class="{'is-field-disabled':isReplacerOff }">
            <label class="label column has-text-grey-dark">
                {{strings.enable_js_minify_title}}

                <p class="is-italic has-text-weight-normal">
                    {{strings.js_minify_desc}}
                </p>
            </label>

            <div class="column is-3 ">
                <toggle-button :class="'has-text-dark'"
                               v-model="jsMinifyStatus"
                               :disabled="this.$store.state.loading"
                               :width="37"
                               :height="20"
                               color="#577BF9"></toggle-button>
            </div>
        </div>
        <div class="field  is-fullwidth columns ">
            <div class="column is-left">
                <button @click="saveChanges()" class="button is-info is-small "
                        :class="this.$store.state.loading ? 'is-loading'  : '' " :disabled="!showSave">{{strings.save_changes}}
                </button>
                <a href="#" v-if="showSample" @click="newSample(false)" class="view-sample-image is-link">{{strings.view_sample_image}}
                </a>
            </div>
        </div>
    </div>
</template>

<script>


    export default {
        name: "cssjs",
        data() {
            return {
                strings: optimoleDashboardApp.strings.options_strings,
                all_strings: optimoleDashboardApp.strings,
                showSave: false,
                showSample: false,
				isReplacerOff: false,
                loading_images: false,
                showComparison: false,
				new_data: {},
            }
        },
        mounted: function () {
			this.isReplacerOff = (this.site_settings.cdn === 'disabled');
        },
        methods: {
            saveChanges: function () {
                this.$store.dispatch('saveSettings', {
                    settings: this.new_data
                });
            },
        },
        computed: {
            site_settings() {
                return this.$store.state.site_settings;
            },
	        cssMinifyStatus: {
		        set: function (value) {
			        this.showSave = true;
			        this.new_data.css_minify = value ? 'enabled' : 'disabled';
		        },
		        get: function () {
			        return !(this.site_settings.css_minify === 'disabled');
		        }
	        },

	        jsMinifyStatus: {
		        set: function (value) {
			        this.showSave = true;
			        this.new_data.js_minify = value ? 'enabled' : 'disabled';
		        },
		        get: function () {
			        return !(this.site_settings.js_minify === 'disabled');
		        }
	        },
        }
    }
</script>

<style scoped>
    .is-field-disabled {
        opacity: 0.5;
    }
</style>