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
          <div class="optml-side-by-side" style="justify-content: center; padding-right: 15%;">

                <div @click="clearCache()" class="optml-button is-small is-3 is-center" style="position: relative; margin-right: 2%;"
                        :class="this.$store.state.loading ? 'is-loading'  : '' ">
                    {{strings.clear_cache_images}}
                </div>

              <div v-if="isAssetsOn" @click="clearCache('assets')" class="optml-button is-small is-3 is-center" style="position: relative;"
                      :class="this.$store.state.loading ? 'is-loading'  : '' ">
                {{strings.clear_cache_assets}}
              </div>

          </div>
          <div class="column optml-gray" style="margin-top: 1%;">
            <svg style="position: relative; top:10%;" width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.9581 0C13.5192 0 17.2136 3.58 17.2136 8C17.2136 12.42 13.5192 16 8.9581 16C4.39696 16 0.702637 12.42 0.702637 8C0.702637 3.58 4.39696 0 8.9581 0ZM10.6558 9L11.0219 3H7.92606L8.29211 9H10.6558ZM11.0219 11.5115C11.0219 11.9809 10.8811 12.3473 10.6213 12.6107C10.3507 12.874 9.97188 13 9.48478 13C8.99768 13 8.61882 12.874 8.33739 12.6107C8.06677 12.3473 7.92606 11.9809 7.92606 11.5115C7.92606 11.0305 8.05595 10.6641 8.32656 10.4008C8.59717 10.1374 8.97603 10 9.48478 10C9.99353 10 10.3724 10.1374 10.6322 10.4008C10.892 10.6641 11.0219 11.0305 11.0219 11.5115Z" fill="#AF3535"/>
            </svg>
            Clearing cached resources will temporarily impact site</div>
        </div>


          <!-- Save changes button -->
          <div class="field  is-fullwidth columns ">
            <div class="column is-left">
              <button @click="saveChanges()" class="button is-info is-small "
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