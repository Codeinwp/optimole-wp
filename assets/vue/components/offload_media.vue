<template>
  <div>
    <!-- Sync Media button -->
    <div :class="{ 'saving--option' : this.$store.state.loading }" class="field  is-fullwidth columns ">
      <label class="label column has-text-grey-dark">
        {{strings.sync_title}}

        <p class="is-italic has-text-weight-normal">
          {{strings.sync_desc}}
        </p>
      </label>
      <div class="column is-3 is-right">
        <button @click="callSync('offload_images')" class="button is-primary is-small "
                :class="this.$store.state.loading ? 'is-loading'  : '' " :disabled="this.$store.state.loadingRollback">
          {{strings.sync_media}}
        </button>
      </div>
    </div>

    <div class="field columns" v-if="this.$store.state.loading">
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
    <!-- Rollback Media button -->
    <div class="field  is-fullwidth columns ">
      <label class="label column has-text-grey-dark">
        {{strings.rollback_title}}

        <p class="is-italic has-text-weight-normal">
          {{strings.rollback_desc}}
        </p>
      </label>
      <div class="column is-3 is-right">
        <button @click="callSync('rollback_images')" class="button is-primary is-small "
                :class="this.$store.state.loadingRollback ? 'is-loading'  : '' " :disabled="this.$store.state.loading">
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
      new_data: {},
    }
  },
  mounted: function () {
  },
  methods: {
    callSync : function ( action ) {
      this.$store.dispatch('callSync', { action: action});
    }
  },
  computed: {
  }
}
</script>

<style scoped>

</style>