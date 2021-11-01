<template>
    <div :class="{ 'saving--option' : this.$store.state.loading }">
        <!--Enable image replacement button -->
        <div class="field  columns">
            <label class="label column has-text-grey-dark">
                {{strings.enable_image_replace}}
                <p class="is-italic has-text-weight-normal">
                    {{strings.replacer_desc}}
                </p>
            </label>
            <div class="column is-3">
                <toggle-button :class="'has-text-dark'"
                               v-model="getReplacerStatus"
                               :disabled="this.$store.state.loading"
                               :labels="{checked: strings.enabled, unchecked: strings.disabled}"
                               :width="80"
                               :height="25"
                               color="#008ec2"></toggle-button>
            </div>

        </div>

        <hr/>
        <!--Lazy load toggle -->
        <div class="field  is-fullwidth columns" :class="{'is-field-disabled':isReplacerOff }">
            <label class="label column has-text-grey-dark">
                {{strings.toggle_lazyload}}

                <p class="is-italic has-text-weight-normal">
                    {{strings.lazyload_desc}}
                </p>
            </label>

            <div class="column is-3 ">
                <toggle-button :class="'has-text-dark'"
                               v-model="lazyLoadStatus"
                               :disabled="this.$store.state.loading"
                               :labels="{checked: strings.enabled, unchecked: strings.disabled}"
                               :width="80"
                               :height="25"
                               color="#008ec2"></toggle-button>
            </div>
        </div>

        <hr/>

        <!-- Show report toggle-->
        <div class="field  columns">
            <label class="label column has-text-grey-dark">
                {{strings.enable_report_title}}

                <p class="is-italic has-text-weight-normal">
                    {{strings.enable_report_desc}}
                </p>
            </label>

            <div class="column is-3 ">
                <toggle-button :class="'has-text-dark'"
                               v-model="reportScriptStatus"
                               :disabled="this.$store.state.loading"
                               :labels="{checked: strings.enabled, unchecked: strings.disabled}"
                               :width="80"
                               :height="25"
                               color="#008ec2"></toggle-button>
            </div>
        </div>

        <hr/>

        <!-- Clear Cache images button -->
        <div class="field  is-fullwidth columns " :class="{'is-field-disabled':isReplacerOff }">
            <label class="label column has-text-grey-dark">
                {{strings.cache_title}}

                <p class="is-italic has-text-weight-normal">
                    {{strings.cache_desc}}
                </p>
            </label>
            <div class="column is-3 is-right">
                <button @click="clearCache()" class="button is-primary is-small "
                        :class="this.$store.state.loading ? 'is-loading'  : '' ">
                    {{strings.clear_cache_images}}
                </button>
            </div>
        </div>

        <hr v-if="isAssetsOn"/>

        <!-- Clear Cache assets button -->
        <div class="field  is-fullwidth columns " v-if="isAssetsOn">
          <label class="label column has-text-grey-dark">
            {{strings.cache_title_assets}}

            <p class="is-italic has-text-weight-normal">
              {{strings.cache_desc_assets}}
            </p>
          </label>
          <div class="column is-3 is-right">
            <button @click="clearCache('assets')" class="button is-primary is-small "
                    :class="this.$store.state.loading ? 'is-loading'  : '' ">
              {{strings.clear_cache_assets}}
            </button>
          </div>
        </div>
        <!-- Save changes button -->
        <div class="field  is-fullwidth columns ">
            <div class="column is-left">
                <button @click="saveChanges()" class="button is-success is-small "
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