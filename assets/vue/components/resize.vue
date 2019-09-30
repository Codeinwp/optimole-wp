<template>
    <div :class="{ 'saving--option' : this.$store.state.loading }">
        <div class="field  columns">
            <label class="label column has-text-grey-dark">
                {{strings.enable_resize_smart_title}}
                <p class="is-italic has-text-weight-normal">
                    {{strings.enable_resize_smart_desc}}
                </p>
            </label>
            <div class="column is-3">
                <toggle-button :class="'has-text-dark'"
                               v-model="resizeSmart"
                               :disabled="this.$store.state.loading"
                               :labels="{checked: strings.enabled, unchecked: strings.disabled}"
                               :width="80"
                               :height="25"
                               color="#008ec2"></toggle-button>
            </div>

        </div>
        <div class="field  columns">
            <label class="label column has-text-grey-dark">
                {{strings.enable_retina_title}}
                <p class="is-italic has-text-weight-normal">
                    {{strings.enable_retina_desc}}
                </p>
            </label>
            <div class="column is-3">
                <toggle-button :class="'has-text-dark'"
                               v-model="retinaReady"
                               :disabled="this.$store.state.loading"
                               :labels="{checked: strings.enabled, unchecked: strings.disabled}"
                               :width="80"
                               :height="25"
                               color="#008ec2"></toggle-button>
            </div>

        </div>
        <div class="field   columns ">
            <label class="label   column has-text-grey-dark no-padding-right ">
                {{strings.size_title}}
                <p class="is-italic has-text-weight-normal">
                    {{strings.size_desc}}
                </p>
            </label>

            <div class="column is-6 ">
                <div class="columns">
                    <div class="field column is-narrow has-addons">
                        <p class="control">
                            <a class="button is-small is-static">
                                {{strings.width_field}}
                            </a>
                        </p>
                        <p class="control ">
                            <input v-model="widthStatus" class="input is-small" type="number" min="100"
                                   max="10000">
                        </p>
                    </div>
                    <div class="field column is-small has-addons">
                        <p class="control">
                            <a class="button is-small is-static">
                                {{strings.height_field}}
                            </a>
                        </p>
                        <p class="control  ">
                            <input v-model="heightStatus" class="input is-small" type="number" min="100"
                                   max="10000">
                        </p>
                    </div>
                </div>
            </div>
        </div>
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