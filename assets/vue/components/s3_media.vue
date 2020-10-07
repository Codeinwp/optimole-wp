<template>
  <div :class="{ 'saving--option' : this.$store.state.loading }">
    <!-- Clear Cache button -->
    <div class="field  is-fullwidth columns ">
      <label class="label column has-text-grey-dark">
        {{strings.sync_title}}

        <p class="is-italic has-text-weight-normal">
          {{strings.sync_desc}}
        </p>
      </label>
      <div class="column is-3 is-right">
        <button @click="syncMedia()" class="button is-primary is-small "
                :class="this.$store.state.loading ? 'is-loading'  : '' ">
          {{strings.sync_media}}
        </button>
      </div>
    </div>

    <div class="field columns" v-if="showProgress">
      <div class="column">
        <label class="label has-text-grey-dark">
          <span>{{strings.sync_media_progress}}</span>
<!--          <p class="is-italic has-text-weight-normal">-->
<!--            {{strings.}}-->
<!--          </p>-->
        </label>
        <progress class="progress is-large" :value="this.$store.state.pushedImagesProgress" :max="maxTime"></progress>
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
      showProgress: false,
      new_data: {},
    }
  },
  mounted: function () {
  },
  methods: {
    syncMedia: function () {
      this.showProgress = true;
      this.$store.dispatch('syncMedia', {});
    }

  },
  computed: {
    site_settings() {
      return this.$store.state.site_settings;
    }
  }
}
</script>

<style scoped>

</style>