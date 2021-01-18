<template>
  <div>
    <!-- Show media cloud toggle-->
    <div class="field  columns">
      <label class="label column has-text-grey-dark">
        {{strings.enable_cloud_images_title}}

        <p class="is-italic has-text-weight-normal">
          {{strings.enable_cloud_images_desc}}
        </p>
      </label>

      <div class="column is-3 ">
        <toggle-button :class="'has-text-dark'"
                       v-model="cloudImagesStatus"
                       :disabled="this.$store.state.loading"
                       :labels="{checked: strings.enabled, unchecked: strings.disabled}"
                       :width="80"
                       :height="25"
                       color="#008ec2"></toggle-button>
      </div>
    </div>
    <!--Sites select-->
    <div  id="filters-list" v-if="this.site_settings.cloud_images === 'enabled'">
      <div class="columns  ">
        <div>
          <div class="field" v-if="cloud_sites.length > 0">
            <label class="label column has-text-grey-dark">
              <span>{{strings.selected_sites_title}}</span>
            </label>
          </div>

          <div class="field  columns">
            <div class="column is-paddingless is-full ">
              <div class="list">
                <div class="list-item exclusion-filter" v-for="site in cloud_sites">
                  <div class="control">
                    <div class="tags is-centered has-addons">
                      <a class="tag  is-marginless is-link has-text-left" v-if="site === 'all'">
                        <strong>{{strings.selected_all_sites_desc}}</strong>
                      </a>
                      <a class="tag  is-marginless is-link has-text-left" v-else>
                        <i>{{strings.selected_sites_desc}}</i>
                        <strong>{{site}}</strong>
                      </a>
                      <a class="tag  is-marginless  is-delete"
                         @click="removeSite( site )"></a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="field   columns ">
            <div class="field has-addons column has-addons-centered">
            <label class="label   column has-text-grey-dark no-padding-right ">
              {{strings.cloud_site_title}}
              <p class="is-italic has-text-weight-normal">
                {{strings.cloud_site_desc}}
              </p>
            </label>

            <div class="column is-6 ">
              <div class="columns">
                <div class="field has-addons column has-addons-centered">
                  <p class="control ">
                            <span class="select  is-small">
                              <select @change="changeSite($event)">
                                <option  value="all">All</option>
                                <option  v-for="site in sites" :value="site">{{site}}</option>
                              </select>
                            </span>
                  </p>
                  <p class="control">
                    <a class="button is-primary  is-small" :class="this.$store.state.loading ? 'is-loading'  : '' "
                       @click="saveSite()">
                      {{strings.add_site}}
                    </a>
                  </p>
                </div>
              </div>
            </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Show offload media toggle-->
    <div class="field  columns">
      <label class="label column has-text-grey-dark">
        {{strings.enable_offload_media_title}}

        <p class="is-italic has-text-weight-normal">
          {{strings.enable_offload_media_desc}}
        </p>
      </label>

      <div class="column is-3 ">
        <toggle-button :class="'has-text-dark'"
                       v-model="offloadMediaStatus"
                       :disabled="this.$store.state.loading"
                       :labels="{checked: strings.enabled, unchecked: strings.disabled}"
                       :width="80"
                       :height="25"
                       color="#008ec2"></toggle-button>
      </div>

    </div>


    <!--Rollback on disable notice-->
    <div class="field  columns" v-if="this.showOffloadDisabled">
      <label class="label column" style="color: #dc143c !important;">
        {{strings.offload_disable_warning_title}}

        <p class="is-italic has-text-weight-normal">
          {{strings.offload_disable_warning_desc}}
        </p>
      </label>
      <hr>
      <div class="column is-3 ">
        <p class="control ">
                            <span class="select  is-small">
                              <select  @change="selectValue($event)">
                                <option disabled selected value="" > {{ strings.select }} </option>
                                <option  value="yes_rollback">{{strings.yes}}</option>
                                <option  value="no_rollback">{{strings.no}}</option>
                              </select>
                            </span>
        </p>
      </div>

    </div>
    <!-- Sync Media button -->
    <div :class="{ 'saving--option' : this.$store.state.loading }" class="field  is-fullwidth columns " v-if="this.site_settings.offload_media==='enabled'">
      <label class="label column has-text-grey-dark">
        {{strings.sync_title}}

        <p class="is-italic has-text-weight-normal">
          {{strings.sync_desc}}
        </p>
      </label>
      <div class="column is-3 is-right">
        <button @click="callSync('offload_images')" class="button is-primary is-small "
                :class="this.$store.state.loadingSync ? 'is-loading'  : '' " :disabled="this.$store.state.loadingRollback">
          {{strings.sync_media}}
        </button>
      </div>
    </div>

    <div class="field columns" v-if="this.$store.state.loadingSync">
      <div class="column">
        <label class="label has-text-grey-dark">
          <span>{{strings.sync_media_progress}}</span>
        </label>
        <progress class="progress is-large" :value="this.$store.state.pushedImagesProgress" :max="maxTime"></progress>
      </div>
    </div>
    <!-- Rollback Media button -->
    <div class="field  is-fullwidth columns " v-if="this.site_settings.offload_media==='enabled' || this.$store.state.loadingRollback">
      <label class="label column has-text-grey-dark">
        {{strings.rollback_title}}

        <p class="is-italic has-text-weight-normal">
          {{strings.rollback_desc}}
        </p>
      </label>
      <div class="column is-3 is-right">
        <button @click="callSync('rollback_images')" class="button is-primary is-small "
                :class="this.$store.state.loadingRollback ? 'is-loading'  : '' " :disabled="this.$store.state.loadingSync">
          {{strings.rollback_media}}
        </button>
      </div>
    </div>

    <div class="field columns" v-if="this.$store.state.loadingRollback">
      <div class="column">
        <label class="label has-text-grey-dark">
          <span>{{strings.rollback_media_progress}}</span>
        </label>
        <progress class="progress is-large" :value="this.$store.state.pushedImagesProgress" :max="maxTime"></progress>
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
  name: "media",
  data() {
    return {
      strings: optimoleDashboardApp.strings.options_strings,
      all_strings: optimoleDashboardApp.strings,
      maxTime: 100,
      showSave: false,
      showOffloadDisabled : false,
      offloadDisableOptions : [],
      new_data: {},
    }
  },
  mounted: function () {
  },
  methods: {
    selectValue: function (event) {
      this.showSave = true;
      this.select_rollback = event.target.value;
    },
    changeSite: function (event) {
      this.selected_site = event.target.value;
    },
    saveSite: function () {
      let  site = {
        'all': true
      };
      if ( typeof this.selected_site !== 'undefined') {
        site['all'] = false;
        site[this.selected_site] = true;
      }

      this.$store.dispatch('saveSettings', {
        settings: {
          cloud_sites: site
        }
      });
    },
    callSync : function ( action ) {
      this.$store.dispatch('callSync', { action: action});
    },
    saveChanges: function () {
      this.showOffloadDisabled = false;
      if ( this.select_rollback === 'yes_rollback' ) {
        this.callSync('rollback_images' );
      }
      this.$store.dispatch('saveSettings', {
        settings: this.new_data
      });
    },
    removeSite: function ( site ) {
      let update_sites = {};
      update_sites[site] = false;
      this.$store.dispatch('saveSettings', {
        settings: {
          cloud_sites: update_sites
        }
      });
    },
  },
  computed: {
    site_settings() {
      return this.$store.state.site_settings;
    },
    sites() {
      return this.$store.state.site_settings.whitelist_domains;
    },
    cloud_sites() {
      let sites_array = [];
      let sites_object = this.$store.state.site_settings.cloud_sites;
      for(let site of Object.keys(sites_object)) {
        if (sites_object [site] === 'true' ) {
          sites_array.push(site);
          if ( site === 'all' ) {
            return  sites_array;
          }
        }
      }
      return sites_array;
    },
    offloadMediaStatus: {
      set: function (value) {
        this.showSave = !!value;
        this.new_data.offload_media = value ? 'enabled' : 'disabled';
        this.showOffloadDisabled = ! value;
      },
      get: function () {
        return !(this.site_settings.offload_media === 'disabled');

      }
    },
    cloudImagesStatus: {
      set: function (value) {
        this.showSave = true;
        this.new_data.cloud_images = value ? 'enabled' : 'disabled';
      },
      get: function () {
        return !(this.site_settings.cloud_images === 'disabled');

      }
    }
  }
}
</script>

<style scoped>

</style>