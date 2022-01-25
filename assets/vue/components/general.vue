<template>
    <div :class="{ 'saving--option' : this.$store.state.loading }">
        <!--Enable image replacement button -->
        <div class="field  columns">
            <label class="label column has-text-grey-dark optml-custom-label-margin">
                {{strings.enable_image_replace}}
                <p class="has-text-weight-normal optml-settings-desc-margin">
                    {{strings.replacer_desc}}
                </p>
            </label>
            <div class="column is-1">
                <toggle-button :class="'has-text-dark'"
                               v-model="getReplacerStatus"
                               :disabled="this.$store.state.loading"
                               :width="37"
                               :height="20"
                               color="#577BF9"></toggle-button>
            </div>

        </div>

        <hr/>
        <!--Lazy load toggle -->
        <div class="field  is-fullwidth columns" :class="{'is-field-disabled':isReplacerOff }">
            <label class="label column has-text-grey-dark optml-custom-label-margin">
                {{strings.toggle_lazyload}}

                <p class="has-text-weight-normal optml-settings-desc-margin">
                    {{strings.lazyload_desc}}
                </p>
            </label>

            <div class="column is-1 ">
                <toggle-button :class="'has-text-dark'"
                               v-model="lazyLoadStatus"
                               :disabled="this.$store.state.loading"
                               :width="37"
                               :height="20"
                               color="#577BF9"></toggle-button>
            </div>
        </div>

        <hr/>

        <!-- Show report toggle-->
        <div class="field  columns">
            <label class="label column has-text-grey-dark optml-custom-label-margin">
                {{strings.enable_report_title}}

                <p class=" has-text-weight-normal optml-settings-desc-margin">
                    {{strings.enable_report_desc}}
                </p>
            </label>

            <div class="column is-1 ">
                <toggle-button :class="'has-text-dark'"
                               v-model="reportScriptStatus"
                               :disabled="this.$store.state.loading"
                               :width="37"
                               :height="20"
                               color="#577BF9"></toggle-button>
            </div>
        </div>

        <hr/>

        <!-- Clear Cache images button -->
        <div class="field  is-fullwidth columns optml-flex-column"  :class="{'is-field-disabled':isReplacerOff }">
            <label class="label column has-text-grey-dark optml-custom-label-margin">
                {{strings.cache_title}}

                <p class="has-text-weight-normal optml-settings-desc-margin">
                    {{strings.cache_desc}}
                </p>
            </label>
          <div class="optml-side-by-side" style="justify-content: left; padding-left: 0.75rem">

                <div @click="clearCache()" class="button optml-button optml-button-style-2 is-center optml-position-relative" style="margin-right: 2%;"
                        :class="this.$store.state.loading ? 'is-loading'  : '' ">
                    {{strings.clear_cache_images}}
                </div>

              <div v-if="isAssetsOn" @click="clearCache('assets')" class="button optml-button optml-button-style-2 is-center optml-position-relative"
                   :class="this.$store.state.loading ? 'is-loading'  : '' ">
                {{strings.clear_cache_assets}}
              </div>

          </div>

            <label class="label column ">
              <p class="has-text-weight-normal optml-restore-notice-background" style="padding: 2%;">{{strings.clear_cache_notice}}</p>
            </label>
        </div>


          <!-- Save changes button -->
          <div class="field  is-fullwidth columns ">
            <div class="column is-left">
              <button @click="saveChanges()" class="button optml-button-style-1"
                      :class="this.$store.state.loading ? 'is-loading'  : '' " :disabled="!showSave">
                {{strings.save_changes}}
              </button>
            </div>
          </div>
    </div>


</template>

<script>
    export default {
        name: "general",
        components: {},
        data() {
            return {
                strings: optimoleDashboardApp.strings.options_strings,
                all_strings: optimoleDashboardApp.strings,
                showNotification: false,
                showSave: false,
                isAssetsOn: false,
                isReplacerOff: false,
                isLazyLoadOff : false,
                new_data: {},

            }
        },
        mounted: function () {
            this.isReplacerOff = (this.site_settings.image_replacer === 'disabled');
            this.isAssetsOn = (this.site_settings.cdn === 'enabled');


          this.$emit('update-status', !this.isReplacerOff);
        },
        methods: {
            clearCache: function ( type = '' ) {
                this.$store.dispatch('clearCache', { type: type });
            },
            saveChanges: function () {
                this.$store.dispatch('saveSettings', {
                    settings: this.new_data
                });
            }

        },
        computed: {
            site_settings() {
                return this.$store.state.site_settings;
            },
            getReplacerStatus: {
                get: function () {
                    return !(this.site_settings.image_replacer === 'disabled');
                },
                set: function (value) {
                    this.showSave = true;
                    this.isReplacerOff = !value;
                    this.$emit('update-status', value);
                    this.new_data.image_replacer = value ? 'enabled' : 'disabled'
                }
            },
            lazyLoadStatus: {
                set: function (value) {
                    this.showSave = true;
                    this.isLazyLoadOff = !value;
                    this.new_data.lazyload = value ? 'enabled' : 'disabled';
                },
                get: function () {
                    return !(this.site_settings.lazyload === 'disabled');
                } 
            },
            reportScriptStatus: {
                set: function (value) {
                    this.showSave = true;
                    this.new_data.report_script = value ? 'enabled' : 'disabled';
                },
                get: function () {
                    return !(this.site_settings.report_script === 'disabled');

                }
            }
        }
    }
</script>

<style scoped>
    .is-field-disabled {
        opacity: 0.5;
    }

</style>