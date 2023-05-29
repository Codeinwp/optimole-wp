<template>
    <div class="columns" style="margin-top: 1.7%;">
        <aside id="optml-settings-menu" class="menu column  is-2-fullhd is-3-desktop is-3-tablet">
            <ul class="optml-menu optml-settings-submenu is-marginless">
                <li><a @click="changeTab('general')" href="#" :class="tab === 'general' ? 'is-active' : ''"
                       :title="all_strings.general_settings_menu_item+' '+all_strings.settings_menu_item">{{all_strings.general_settings_menu_item}}</a>
                </li>
                <li :class="advancedOpen ? 'is-active' : '' " class="optml-advanced-settings optml-fill-container" :class="isDisabled ? 'is-menu-disabled' : '' "><a @click="ToggleAdvanced()" href="#" :class="advancedOpen ? 'is-active' : '' "
                                                                      title="General settings"><span>{{all_strings.advanced_settings_menu_item}}</span>
                    <span class="   dashicons advanced-link "
                          :class="advancedOpen ? 'dashicons-arrow-up-alt2' : 'dashicons-arrow-down-alt2'"></span>
                </a>
                    <!--Advanced-->
                    <ul class="optml-sublist optml-settings-submenu is-marginless "
                        :class=" ! advancedOpen ? 'is-hidden' : '' ">
                        <li :class="tab === 'compression' ? 'optml-light-background' : ''"><a class="optml-fit-content" @click="changeTab('compression')" href="#"
                               :title="all_strings.settings_compression_menu_item+' '+all_strings.settings_menu_item">{{all_strings.settings_compression_menu_item}}</a>
                        </li>
                        <li :class="tab === 'resize' ? 'is-active optml-light-background' : ''"><a class="optml-fit-content"  @click="changeTab('resize')" href="#"
                               :title="all_strings.settings_resize_menu_item+' '+all_strings.settings_menu_item">{{all_strings.settings_resize_menu_item}}</a>
                        </li>

                        <li :class="tab === 'lazyload' ? 'optml-light-background' : ''" v-if="this.$store.state.site_settings.lazyload==='enabled'"><a class="optml-fit-content"  @click="changeTab('lazyload')" href="#"
                               :title="all_strings.lazyload_settings_menu_item+' '+all_strings.lazyload_settings_menu_item">{{all_strings.lazyload_settings_menu_item}}</a>
                        </li>
                        <li :class="tab === 'exclusions' ? 'optml-light-background' : ''"><a   class="optml-fit-content"  href="#"
                               @click="changeTab('exclusions')" href="#"
                               :title="all_strings.settings_exclusions_menu_item+' '+all_strings.settings_menu_item">{{all_strings.settings_exclusions_menu_item}}</a>
                        </li>
                    </ul>
                </li>
              <!--                    Offload media menu-->
              <li v-if="is_offload_media_available === 'yes'"><a @click="changeTab('offload_media')" href="#" style=""
                                                                 :class="tab === 'offload_media' ? 'is-active' : ''"
                                                                 :title="all_strings.offload_media_settings_menu_item+' '+all_strings.offload_media_settings_menu_item">{{all_strings.offload_media_settings_menu_item}}

              </a>

              </li>
            </ul>
          <hr/>
        </aside>
        <div :class="[{ 'saving--option' : this.$store.state.loading, 'is-tab-disabled':isDisabled},'is-tab-'+tab] "
             class="  column">
            <div class="subtab-content">
                <General v-if="tab === 'general' " @update-status="updateGlobalState"></General>
                <Compression v-if="tab === 'compression' "></Compression>
                <Watermarks v-if="tab ==='watermark'"></Watermarks>
                <Resize v-if="tab ==='resize'"></Resize>
                <Lazyload v-if="tab ==='lazyload'"></Lazyload>
                <Media v-if="tab ==='offload_media'"></Media>
                <Exclusions v-if="tab ==='exclusions'"></Exclusions>
            </div>
        </div>
    </div>
</template>

<script>
	import General from "./general.vue";
	import Compression from "./compression.vue";
	import Watermarks from "./watermarks.vue";
	import Resize from "./resize.vue";
	import Exclusions from "./exclusions.vue";
	import Lazyload from "./lazyload.vue";
  import Media from "./offload_media.vue";

	export default {
        name: "options",
        components: { Lazyload, Media, Exclusions, Resize, Watermarks, Compression, General},
        data() {
            return {
                strings: optimoleDashboardApp.strings.options_strings,
                all_strings: optimoleDashboardApp.strings,
                is_offload_media_available : optimoleDashboardApp.is_offload_media_available,
                showNotification: false,
                tab: 'general',
                isDisabled: false,
                advancedOpen: false
            }
        },
        mounted: function () {
          if ( Object.prototype.hasOwnProperty.call(this.$store.state.queryArgs, 'optimole_action') ) {
            this.changeTab('offload_media');
          }
        },
        methods: {
            ToggleAdvanced() {
                this.advancedOpen = !this.advancedOpen;

	            if (!this.advancedOpen) {
		            this.tab = 'general';
	            }
	            if (this.advancedOpen) {
		            this.tab = 'compression';
	            }
            },
            changeTab(value) {
              if ( ![ 'advanced', 'compression', 'resize', 'lazyload', 'exclusions' ].includes(value) )  {
                this.advancedOpen = false;
              }
              this.tab = value;
            },
            updateGlobalState(value) {
                this.isDisabled = !value;
            }

        },
        computed: {}
    }
</script>

<style scoped>

    #optml-settings-menu > ul.optml-menu.optml-settings-submenu {
        width: fit-content !important;
        display: flex;
        flex-direction: column;
    }
    #optml-settings-menu >  hr{
      visibility: hidden;
    }
    .optml-sublist {
      display: flex;
      flex-direction: column;
    }
    .optml-advanced-settings {
      width: 100%;
    }
    @media screen and (max-width: 768px) {
      #optml-settings-menu > ul.optml-menu.optml-settings-submenu {
        width: fit-content !important;
        display: flex;
        flex-direction: row;
        margin-top: 0;
        padding-top : 0;
      }
      #optml-settings-menu {
        padding-top: 0 !important;
      }
      #optml-settings-menu >  hr{
        visibility: visible;
        margin: 0 !important;
      }
      #optml-settings-menu > ul.optml-menu.optml-settings-submenu > li {
        margin-top: 0 !important;
        margin-left: 5% !important;
        height : fit-content;
      }
      .optml-sublist {
        display: flex;
        flex-direction: row;
        left: -87%;
        position: relative;

      }
      .optml-sublist > li {
        margin-right: 5% !important;
      }
      .optml-sublist > li > a {
        padding: 5px !important;
      }
      .optml-advanced-settings {
        width: 37% !important;
      }
      .optml-advanced-settings.is-active {
        width: 20% !important;
      }
    }
    ul.optml-settings-submenu {
      list-style: none !important;
    }
    #optml-settings-menu li a span.tag.optml-beta {
        border-radius: 2px;
        font-family: Verdana;
        font-size: .5rem;
        height: 2.25em;
        margin-left: .5em;
        vertical-align: text-bottom;
    }

    #optml-settings-menu > ul.menu-list > li > ul {
        margin-top: 10px;
        padding-top: 10px;
        border: none;
        font-size: 0.8rem;
        list-style: none;
        width: fit-content;
    }
    .optml-menu > li > a{
      color: black !important;
      display: block;
      padding: 0 20% 20px 0;
      list-style: none !important;
      box-shadow: none !important;
      font-weight: bold;
      width: fit-content;
      white-space: nowrap;
    }
    .optml-menu > li :hover {
      color: #3273dc !important;
    }
    .optml-menu > li > a.is-active {
      color: #3273dc !important;
    }

    .optml-menu > li {
      width: fit-content;
    }
    .optml-sublist > li {
        margin-top: 0 !important;
    }
    .optml-sublist > li > a {
      display: inline-block;
      color: black !important;
      padding: 7% 15% 7% 5%;
      width: 100%;
    }

    .subtab-content {

        padding: 0.5em 0.75em;
    }

    #optml-settings-menu {
        padding-top: 23px;
        width: fit-content !important;
        margin-right: 4%;
    }

    .subtab-content {
        padding-top: 10px;
    }

    .advanced-link {
        font-size: 100%;
        line-height: 20px;
    }

    #optimole-app .is-menu-disabled > ul > li > a {
        cursor: not-allowed;
    }

    .is-tab-disabled:not(.is-tab-general),
    .is-menu-disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
</style>