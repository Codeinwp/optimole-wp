<template>
    <div class="columns">
        <aside id="optml-settings-menu" class="menu column  is-2-fullhd is-3-desktop is-3-tablet is-hidden-mobile">
            <ul class="menu-list optml-settings-submenu is-marginless">
                <li><a @click="changeTab('general')" href="#" :class="tab === 'general' ? 'is-active' : ''"
                       :title="all_strings.general_settings_menu_item+' '+all_strings.settings_menu_item">{{all_strings.general_settings_menu_item}}</a>
                </li>
                <li :class="isDisabled ? 'is-menu-disabled' : '' "><a @click="ToggleAdvanced()" href="#"
                                                                      title="General settings"><span>{{all_strings.advanced_settings_menu_item}}</span>
                    <span class="   dashicons advanced-link "
                          :class="advancedOpen ? 'dashicons-arrow-down-alt2' : 'dashicons-arrow-right-alt2'"></span>
                </a>
                    <ul class="menu-list optml-settings-submenu is-marginless "
                        :class=" ! advancedOpen ? 'is-hidden' : '' ">
                        <li><a @click="changeTab('compression')" href="#"
                               :class="tab === 'compression' ? 'is-active' : ''"
                               :title="all_strings.settings_compression_menu_item+' '+all_strings.settings_menu_item">{{all_strings.settings_compression_menu_item}}</a>
                        </li>
                        <li><a @click="changeTab('resize')" href="#"
                               :class="tab === 'resize' ? 'is-active' : ''"
                               :title="all_strings.settings_resize_menu_item+' '+all_strings.settings_menu_item">{{all_strings.settings_resize_menu_item}}</a>
                        </li>
<!--                        <li><a @click="changeTab('cssjs')" href="#"-->
<!--                               :class="tab === 'cssjs' ? 'cssjs' : ''"-->
<!--                               title="CSS/JS">CSS/JS</a>-->
<!--                        </li>-->
                        <li v-if="this.$store.state.site_settings.lazyload==='enabled'"><a @click="changeTab('lazyload')" href="#"
                               :class="tab === 'lazyload' ? 'is-active' : ''"
                               :title="all_strings.lazyload_settings_menu_item+' '+all_strings.lazyload_settings_menu_item">{{all_strings.lazyload_settings_menu_item}}</a>
                        </li>
                        <li><a href="#"
                               @click="changeTab('exclusions')" href="#"
                               :class="tab === 'exclusions' ? 'is-active' : ''"
                               :title="all_strings.settings_exclusions_menu_item+' '+all_strings.settings_menu_item">{{all_strings.settings_exclusions_menu_item}}</a>
                        </li>
<!--                        <li><a-->
<!--                                @click="changeTab('watermark')" href="#"-->
<!--                                :class="tab === 'watermark' ? 'is-active' : ''"-->
<!--                                :title="all_strings.watermarks_menu_item+' '+all_strings.settings_menu_item">{{all_strings.watermarks_menu_item}}-->
<!--                            <span-->
<!--                                    class=" optml-beta is-normal tag is-warning">Beta</span></a></li>-->
                    </ul>
                </li>
              <!--                    Offload media menu-->
              <li v-if="is_offload_media_available === 'yes'"><a @click="changeTab('offload_media')" href="#"
                                                                 :class="tab === 'offload_media' ? 'is-active' : ''"
                                                                 :title="all_strings.offload_media_settings_menu_item+' '+all_strings.offload_media_settings_menu_item">{{all_strings.offload_media_settings_menu_item}}
                <span class="optml-beta is-normal tag is-warning">Beta </span>
              </a>

              </li>
            </ul>
        </aside>
        <div :class="[{ 'saving--option' : this.$store.state.loading, 'is-tab-disabled':isDisabled},'is-tab-'+tab] "
             class="  column">
            <div class="subtab-content">
                <General v-if="tab === 'general' " @update-status="updateGlobalState"></General>
                <Compression v-if="tab === 'compression' "></Compression>
                <Watermarks v-if="tab ==='watermark'"></Watermarks>
<!--                <Cssjs v-if="tab ==='cssjs'"></Cssjs>-->
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
	import Cssjs from "./cssjs.vue";

	export default {
        name: "options",
        components: {Cssjs, Lazyload, Media, Exclusions, Resize, Watermarks, Compression, General},
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

    #optml-settings-menu > ul.menu-list.optml-settings-submenu {
        list-style: none;
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
    }

    .subtab-content {

        padding: 0.5em 0.75em;
    }

    #optml-settings-menu {
        padding-top: 23px;
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