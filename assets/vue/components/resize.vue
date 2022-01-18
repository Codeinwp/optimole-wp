<template>
    <div :class="{ 'saving--option' : this.$store.state.loading }">
        <div class="field  columns">
            <label class="label column has-text-grey-dark optml-custom-label-margin">
                {{strings.enable_resize_smart_title}}
                <p class="optml-settings-desc-margin has-text-weight-normal">
                    {{strings.enable_resize_smart_desc}}
                </p>
            </label>
            <div class="column is-1">
                <toggle-button :class="'has-text-dark'"
                               v-model="resizeSmart"
                               :disabled="this.$store.state.loading"
                               :width="37"
                               :height="20"
                               color="#577BF9"></toggle-button>
            </div>

        </div>
        <hr/>
        <div class="field  columns">
            <label class="label column has-text-grey-dark optml-custom-label-margin">
                {{strings.enable_retina_title}}
                <p class="optml-settings-desc-margin has-text-weight-normal">
                    {{strings.enable_retina_desc}}
                </p>
            </label>
            <div class="column is-1">
                <toggle-button :class="'has-text-dark'"
                               v-model="retinaReady"
                               :disabled="this.$store.state.loading"
                               :width="37"
                               :height="20"
                               color="#577BF9"></toggle-button>
            </div>

        </div>
        <hr/>
        <div class="field   columns optml-flex-column" style="align-content: center;justify-content: center;">
            <label class="label   column has-text-grey-dark no-padding-right optml-custom-label-margin">
                {{strings.size_title}}
                <p class="optml-settings-desc-margin has-text-weight-normal">
                    {{strings.size_desc}}
                </p>
            </label>

            <div class="column is-6 ">
                <div class="columns">
                    <div class="field column is-narrow has-addons" style="align-items: center;">

                        <p class="optml-custom-label-margin">
                           {{strings.width_field}}
                        </p>

                        <input v-model="widthStatus" class="optml-textarea" type="number" min="100" max="10000">

                    </div>
                    <div class="field column is-narrow has-addons" style="align-items: center;  margin-bottom: 0.75rem;">
                        <p class="optml-custom-label-margin">
                           {{strings.height_field}}
                        </p>

                        <input class="optml-textarea" v-model="heightStatus" type="number" min="100" max="10000">
                    </div>
                </div>
            </div>
        </div>

        <div class="field  is-fullwidth columns ">
            <div class="column is-left">
                <button @click="saveChanges()" class="button optml-button-style-1 "
                        :class="this.$store.state.loading ? 'is-loading'  : '' " :disabled="!showSave">
                    {{strings.save_changes}}
                </button>
            </div>
        </div>

    </div>
</template>

<script>

    export default {
        name: "resize",
        data() {
            return {
                strings: optimoleDashboardApp.strings.options_strings,
                all_strings: optimoleDashboardApp.strings,
                showSave: false,
                new_data:{},
            }
        },
        mounted: function () {
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


            widthStatus: {
                set: function (value) {
                    this.showSave = true;
                    this.new_data.max_width = value;
                },
                get: function () {

                    return (this.site_settings.max_width);
                }
            },
            heightStatus: {
                set: function (value) {
                    this.showSave = true;
                    this.new_data.max_height = value;

                },
                get: function () {

                    return (this.site_settings.max_height);
                }
            },
            resizeSmart: {
                set: function (value) {
                    this.showSave = true;
                    this.new_data.resize_smart = value ? 'enabled' : 'disabled';
                },
                get: function () {
                    return !(this.site_settings.resize_smart === 'disabled');
                }
            },
            retinaReady: {
                set: function (value) {
                    this.showSave = true;
                    this.new_data.retina_images = value ? 'enabled' : 'disabled';
                },
                get: function () {
                    return !(this.site_settings.retina_images === 'disabled');
                }
            }
        }
    }
</script>

<style scoped>

</style>