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
        <div class="field  columns">

          <div class="columns"  style="width: 100%;margin-left: 3px;">
          <div class="optml-fill-container">
            <div class="field    ">
              <label class="label column has-text-grey-dark" style="margin:2% 0;">
                <span> {{this.strings.add_image_size_desc}} </span>
              </label>
              <label class="label column ">
                <p class="has-text-weight-normal optml-restore-notice-background" style="padding: 2%;">{{strings.image_size_notice}}</p>
              </label>
              <div class="fields column optml-light-background" style="padding: 1% 5%; margin: 0 1%;">
                <div class="field has-addons column has-addons-centered optml-filters-content">
                  <p class="optml-fill-container has-text-centered">

                    <input v-model="size_width" class="optml-textarea optml-text-input-border optml-fill-container has-text-left" type="number" min="1"  :placeholder="this.strings.width_field">
                  </p>

                  <p class="optml-fill-container has-text-centered" style="margin-left: 5%;">

                    <input v-model="size_height" class="optml-textarea optml-text-input-border optml-fill-container has-text-left" type="number" min="1" :placeholder="this.strings.height_field">
                  </p>


                  <div class="button optml-button-style-1 optml-fit-content" style="margin: 0 0 0 4%;" :class="this.$store.state.loading ? 'is-loading'  : '' "
                       @click="addImageSize" >
                    {{this.strings.add_image_size_button}}
                  </div>

                </div>
              </div>
            </div>

              <div v-if="anySize" class="label column has-text-grey-dark" style="white-space: nowrap;"> {{strings.image_sizes_title}}: </div>
                  <div class="column is-full optml-fill-container">
                        <div class=" exclusion-filter" v-for="(item, index) in sizes"   style="margin-bottom: 2%;">
                          <div>
                            <div class="tags  has-addons">
                              <div class="tag  optml-light-background is-marginless  is-link has-text-left" style="width: 97.8%;"><p v-html="strings.name"></p>
                                &nbsp;{{index}} &nbsp;&nbsp;&nbsp;&nbsp; <strong>{{strings.width_field}}</strong> : {{sizes[index].width}} &nbsp;&nbsp;&nbsp;&nbsp; <strong>{{strings.height_field}}</strong> : {{sizes[index].height}}
                                <a @click="removeSize(index)">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="16" viewBox="0 0 13 16" fill="none">
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
                size_width: '',
                size_height: '',
                crop: false,
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
          addImageSize: function () {
            if ('' !== this.size_height && '' !== this.size_width) {
              let size_name = 'optimole_' + this.size_width + '_' + this.size_height + '_crop';

              let new_size = {
                [size_name]: {
                  width: this.size_width,
                  height: this.size_height
                }
              };

              this.$store.dispatch('saveSettings', {
                settings: {
                  defined_image_sizes: new_size
                }
              });
            }
          },

          removeSize: function (size) {

            let remove_size = {
              [size]: 'remove'
            };

            this.$store.dispatch('saveSettings', {
              settings: {
                defined_image_sizes: remove_size
              }
            });
          },
        },
        computed: {
            site_settings() {
                return this.$store.state.site_settings;
            },

          sizes() {
            return this.$store.state.site_settings.defined_image_sizes;
          },
          anySize () {
            return Object.keys(this.$store.state.site_settings.defined_image_sizes).length > 0;
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