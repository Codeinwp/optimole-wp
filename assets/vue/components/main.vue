<template>
  <div class="columns is-desktop">

    <div class="column  ">
      <div class="card">
        <app-header></app-header>
        <div class="card-content">
          <div class="content">
            <connect-layout v-if="!this.$store.state.connected"></connect-layout>

            <transition name="slide-fade">
              <div v-if="this.$store.state.connected && ! this.$store.state.is_loaded" id="optml-loader">
                <div class="columns">
                  <div class="column">

                    <transition name="slide-fade">
                      <h4 class="has-text-centered">{{ this.getProgressMessage() }}</h4>
                    </transition>
                  </div>
                </div>
                <div class="columns">
                  <div class=" column is-vertical-center ">
                    <progress id="optml-progress-bar" class="progress is-medium is-info"
                              :class="'optml-progres-'+(Math.floor(this.loading_percent/10))"
                              max="100"></progress>
                  </div>
                </div>
                <iframe :src="home" style="opacity:0;"></iframe>
              </div>
            </transition>
            <transition name="fade" mode="out-in">
              <div v-if="this.$store.state.connected && this.$store.state.is_loaded">
                <div class="tabs is-left is-boxed is-medium">
                  <ul class="is-marginless optml-tabs">
                    <li :class="tab === 'dashboard' ? 'is-active' : ''">
                      <a @click="changeTab('dashboard')" class="is-size-6-mobile">
                        <span class="icon is-size-6-mobile is-size-6-tablet  dashicons dashicons-admin-home"></span>
                        <span class="is-size-6-mobile is-size-6-touch ">{{ strings.dashboard_menu_item }}</span>
                      </a>
                    </li>
                    <li :class="tab === 'conflicts' ? 'is-active' : ''" v-if="conflictCount > 0">
                      <a @click="changeTab('conflicts')" class="is-size-6-mobile">
                        <span class="icon is-size-6-mobile  is-size-6-tablet dashicons dashicons-warning"></span>
                        <span class="is-size-6-mobile is-size-6-touch">{{ strings.conflicts_menu_item }}</span>&nbsp;
                        <span class="tag is-rounded is-danger">{{ conflictCount }}</span>
                      </a>
                    </li>
                    <li :class="tab === 'settings' ? 'is-active' : ''">
                      <a @click="changeTab('settings')" class="is-size-6-mobile  ">
                        <span
                            class="icon is-size-6-mobile   is-size-6-tablet dashicons dashicons-admin-settings"></span>
                        <span class="is-size-6-mobile is-size-6-touch">{{ strings.settings_menu_item }}</span>
                      </a>
                    </li>

                  </ul>
                </div>

                <div class="is-tab" v-if="tab === 'dashboard' "
                     :class="remove_images ? 'no-images' : '' ">
                  <div class="notification is-success"
                       v-if="strings.notice_just_activated.length > 0 && user_status === 'active' "
                       v-html="strings.notice_just_activated"></div>
                  <div class="notification is-danger" v-if="user_status === 'inactive' "
                       v-html="strings.notice_disabled_account"></div>
                  <api-key-form></api-key-form>
                  <cdn-details v-if="this.$store.state.userData"></cdn-details>
                  <hr/>
                  <last-images :status="fetchStatus" v-if="! remove_images"></last-images>
                </div>
                <div class="is-tab" v-if=" tab === 'settings'">
                  <options></options>
                </div>
                <div class="is-tab" v-if=" tab === 'conflicts'">
                  <conflicts></conflicts>
                </div>
              </div>
            </transition>

          </div>
        </div>

        <div class="level-right">
          <p class="level-item"><a href="https://optimole.com" target="_blank">Optimole
            v{{ strings.version }}</a></p>
          <p class="level-item"><a href="https://optimole.com/terms/"
                                   target="_blank">{{ strings.terms_menu }}</a></p>
          <p class="level-item"><a href="https://optimole.com/privacy-policy/"
                                   target="_blank">{{ strings.privacy_menu }}</a>
          </p>
          <p class="level-item"><a :href="'https://speedtest.optimole.com/?url=' + home "
                                   target="_blank">{{ strings.testdrive_menu }}</a>
          </p>
        </div>
      </div>
    </div>
    <div
        class="column is-narrow is-hidden-desktop-only is-hidden-tablet-only is-hidden-mobile"
        v-if="(this.$store.state.connected && this.$store.state.userData.plan === 'free')">
      <div class="card optml-upgrade">
        <div class="card-header">
          <h3 class="is-size-5 card-header-title"><span class="dashicons dashicons-chart-line"></span>
            {{ strings.upgrade.title }}</h3>
        </div>
        <div class="card-content">
          <ul>
            <li><span class="dashicons dashicons-yes"></span>{{ strings.upgrade.reason_1 }}</li>
            <li><span class="dashicons dashicons-yes"></span>{{ strings.upgrade.reason_2 }}</li>
            <li><span class="dashicons dashicons-yes"></span>{{ strings.upgrade.reason_3 }}</li>
            <li><span class="dashicons dashicons-yes"></span>{{ strings.upgrade.reason_4 }}</li>
          </ul>
        </div>
        <div class="card-footer  ">
          <div class="card-footer-item">
            <a href="https://optimole.com/pricing" target="_blank"
               class="button is-centered is-small is-success"><span
                class="dashicons dashicons-external"></span>{{ strings.upgrade.cta }}</a>
          </div>
        </div>
      </div>
      <div class="card optml-neve" v-if="strings.neve.is_active !== 'yes'">
        <div class="card-content">
          <p class=" is-size-5 optml-nv-title ">Neve</p>
          <p class="optml-nv-byline">{{ strings.neve.byline }}</p>
          <ul>
            <li><span class="dashicons dashicons-yes"></span>{{ strings.neve.reason_1 }}</li>
            <li><span class="dashicons dashicons-yes"></span>{{ strings.neve.reason_2 }}</li>
            <li><span class="dashicons dashicons-yes"></span>{{ strings.neve.reason_3 }}</li>
            <li><span class="dashicons dashicons-yes"></span>{{ strings.neve.reason_4 }}</li>
            <li><span class="dashicons dashicons-yes"></span>{{ strings.neve.reason_5 }}</li>
          </ul>
        </div>
        <div class="card-footer  ">
          <div class="card-footer-item">
            <a href="https://themeisle.com/themes/neve/" target="_blank"
               class="button is-centered is-small is-info"><span
                class="dashicons dashicons-external"></span>View more</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import AppHeader from './app-header.vue';
import CdnDetails from './cdn-details.vue';
import ConnectLayout from './connect-layout.vue';
import LastImages from './last-images.vue';
import Conflicts from './conflicts.vue';
import ApiKeyForm from "./api-key-form.vue";
import Options from "./options.vue";
import Watermarks from "./watermarks.vue";

module.exports = {
  name: 'app',
  data() {
    return {
      strings: optimoleDashboardApp.strings,
      home: optimoleDashboardApp.home_url,
      user_status: optimoleDashboardApp.user_status,
      remove_images: optimoleDashboardApp.remove_latest_images === 'yes',
      fetchStatus: false,
      step_no: 0,
      timer: 0,
      max_time: 25,
      loading_percent: 0,
      tab: 'dashboard'
    }
  },
  computed: {
    conflictCount() {
      return this.$store.state.conflicts.count || 0
    },
    is_connected() {
      return this.$store.state.connected;
    },
    is_loaded() {
      return this.$store.state.is_loaded;
    }
  },
  components: {
    AppHeader,
    Options,
    Watermarks,
    ConnectLayout,
    ApiKeyForm,
    CdnDetails,
    Conflicts,
    LastImages
  },
  watch: {
    is_connected: function (val) {
      console.log(val);
      if (val && !this.is_loaded) {
        this.triggerLoading();
      }
    }
  },
  mounted() {
    let self = this;
    if (this.$store.state.connected) {
      this.$store.dispatch('retrieveOptimizedImages', {waitTime: 0, component: null});
      this.$store.dispatch('retrieveConflicts', {waitTime: 0, component: null});
      self.fetchStatus = true;
    }
  },
  methods: {
    changeTab: function (value) {
      this.tab = value;
    },
    getProgressMessage() {
      let message = '';

      if (this.step_no === 0) {
        message = this.strings.options_strings.connect_step_0;
      }
      if (this.step_no === 1) {
        message = this.strings.options_strings.connect_step_1;
      }
      if (this.step_no === 2) {
        message = this.strings.options_strings.connect_step_2;
      }
      if (this.step_no === 3) {
        message = this.strings.options_strings.connect_step_3;
      }

      return `${message} (${this.loading_percent}%)`

    },

    doLoading() {
      this.timer++;

      if (this.timer >= this.max_time) {
        this.$store.commit('toggleIsServiceLoaded', true);
        this.timer = 0;
        this.step_no = 0;
        this.loading_percent = 0;
        return;
      }
      this.loading_percent = (Math.floor((this.timer / this.max_time) * 100));
      if (this.loading_percent > ((this.step_no + 1) * 30)) {
        this.step_no++;
      }
      if (this.timer < this.max_time) {
        this.triggerLoading();
      }
    },
    triggerLoading: function () {
      setTimeout(() => {
        this.doLoading()
      }, 1000)
    }

  }

}
</script>
<style lang="sass-loader">
@import '../../css/style.scss';

#optimole-app .tabs a {
  margin-bottom: -4px;
}

#optimole-app .optml-upgrade {
  min-width: 200px;
}

#optimole-app .is-tab.no-images {
  min-height: 400px;
}

#optimole-app .is-tab {
  min-height: 700px;
}

.optml-tabs {
  position: relative;
}

.optml-tabs .optml-conflicts-tabs {
  position: absolute;
  right: 0;
  top: 0;
}

#optml-loader {
  min-height: 400px;
  margin-top: 150px;
}

.slide-fade-enter-active {
  transition: all .3s ease;
}

.slide-fade-leave-active {
  transition: all .8s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}

.slide-fade-enter, .slide-fade-leave-to {
  transform: translateX(10px);
  opacity: 0;
}

#optml-progress-bar.progress.optml-progres-0:indeterminate {
  background-image: linear-gradient(to right, #5180C1 5%, #dbdbdb 5%);
}

#optml-progress-bar.progress.optml-progres-1:indeterminate {
  background-image: linear-gradient(to right, #5180C1 10%, #dbdbdb 10%);
}

#optml-progress-bar.progress.optml-progres-2:indeterminate {
  background-image: linear-gradient(to right, #5180C1 20%, #dbdbdb 20%);
}

#optml-progress-bar.progress.optml-progres-3:indeterminate {
  background-image: linear-gradient(to right, #5180C1 30%, #dbdbdb 30%);
}

#optml-progress-bar.progress.optml-progres-4:indeterminate {
  background-image: linear-gradient(to right, #5180C1 40%, #dbdbdb 40%);
}

#optml-progress-bar.progress.optml-progres-5:indeterminate {
  background-image: linear-gradient(to right, #5180C1 50%, #dbdbdb 50%);
}

#optml-progress-bar.progress.optml-progres-6:indeterminate {
  background-image: linear-gradient(to right, #5180C1 60%, #dbdbdb 60%);
}

#optml-progress-bar.progress.optml-progres-7:indeterminate {
  background-image: linear-gradient(to right, #5180C1 70%, #dbdbdb 70%);
}

#optml-progress-bar.progress.optml-progres-8:indeterminate {
  background-image: linear-gradient(to right, #5180C1 80%, #dbdbdb 80%);
}

#optml-progress-bar.progress.optml-progres-9:indeterminate {
  background-image: linear-gradient(to right, #5180C1 90%, #dbdbdb 90%);
}

.notification .dashicons-external {
  text-decoration: none;
  font-size: 18px;
}

.optml-neve li {

  margin-top: 3px !important;
  margin-bottom: 3px !important;
}

.optml-neve p.optml-nv-byline {
  margin-top: 5px !important;
  margin-bottom: 15px !important;
}

.optml-neve {
  padding: 0.5rem !important;
}

.optml-neve .card-content {
  padding: 1rem !important;
}
</style>
