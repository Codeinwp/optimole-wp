<template>
  <div :class="{ 'saving--option' : this.$store.state.loading }">
    <div class="field  columns">
      <label class="label column has-text-grey-dark">
        {{ strings.enable_network_opt_title }}
        <p class="is-italic has-text-weight-normal">
          {{ strings.enable_network_opt_desc }}
        </p>
      </label>
      <div class="column is-3">
        <br>
        <toggle-button
          v-model="networkStatus"
          :class="'has-text-dark'"
          :disabled="this.$store.state.loading"
          :labels="{checked: strings.enabled, unchecked: strings.disabled}"
          :width="80"
          :height="25"
          color="#008ec2"
        />
      </div>
    </div>

    <!--CSS & JS Assets load toggle -->
    <div class="field  is-fullwidth columns">
      <label class="label column has-text-grey-dark">
        {{ strings.toggle_cdn }}

        <p class="is-italic has-text-weight-normal">
          {{ strings.cdn_desc }}
        </p>
      </label>

      <div class="column is-3 ">
        <toggle-button
          v-model="cdnStatus"
          :class="'has-text-dark'"
          :disabled="this.$store.state.loading"
          :labels="{checked: strings.enabled, unchecked: strings.disabled}"
          :width="80"
          :height="25"
          color="#008ec2"
        />
      </div>
    </div>
    <!-- GIF replacement toggle button -->
    <div class="field  columns">
      <label class="label column has-text-grey-dark">
        {{ strings.enable_gif_replace_title }}

        <p class="is-italic has-text-weight-normal">
          {{ strings.gif_replacer_desc }}
        </p>
      </label>

      <div class="column is-3 ">
        <toggle-button
          v-model="gifReplacementStatus"
          :class="'has-text-dark'"
          :disabled="this.$store.state.loading"
          :labels="{checked: strings.enabled, unchecked: strings.disabled}"
          :width="80"
          :height="25"
          color="#008ec2"
        />
      </div>
    </div>

    <!-- Use avif toggle button -->
    <div class="field  columns">
      <label class="label column has-text-grey-dark">
        {{ strings.enable_avif_title }}

        <p class="is-italic has-text-weight-normal">
          {{ strings.enable_avif_desc }}
        </p>
      </label>

      <div class="column is-3 ">
        <toggle-button
          v-model="avifStatus"
          :class="'has-text-dark'"
          :disabled="this.$store.state.loading"
          :labels="{checked: strings.enabled, unchecked: strings.disabled}"
          :width="80"
          :height="25"
          color="#008ec2"
        />
      </div>
    </div>

    <div class="field  columns">
      <label class="label column has-text-grey-dark">
        {{ strings.quality_title }}
        <p class="is-italic has-text-weight-normal">
          {{ strings.quality_desc }}
        </p>
      </label>
    </div>
    <div class="field  columns">
      <div class="column">
        <vue-slider
          v-model="new_data.quality"
          v-bind="sliderOptions"
          @change="() => { showSave=true;showSample=true }"
        />
      </div>
    </div>
    <div class="field  is-fullwidth columns ">
      <div class="column is-left">
        <button
          class="button is-success is-small "
          :class="this.$store.state.loading ? 'is-loading' : '' "
          :disabled="!showSave"
          @click="saveChanges()"
        >
          {{ strings.save_changes }}
        </button>
        <a
          v-if="showSample"
          href="#"
          class="view-sample-image is-link"
          @click="newSample(false)"
        >{{ strings.view_sample_image }}
        </a>
      </div>
    </div>
    <div
      v-if="showComparison"
      class="sample-image"
    >
      <div
        v-if="loading_images"
        class="has-text-centered subtitle "
      >
        {{ strings.sample_image_loading }}<span
          class="loader has-text-black-bis icon is-small"
        />
      </div>
      <div v-else-if="sample_images.id && sample_images.original_size > 0">
        <div class="columns is-centered is-vcentered is-multiline is-mobile">
          <a
            class="button is-small is-pulled-right"
            @click="newSample(true)"
          >
            <span class="icon dashicons dashicons-image-rotate" />
          </a>
          <div class="column visual-compare  is-half-fullhd is-half-desktop is-three-quarters-touch is-12-mobile  ">
            <div class="is-full progress-wrapper">
              <p
                v-if="compressionRatio > 0"
                class="subtitle is-size-6 compress-optimization-ratio-done has-text-centered"
              >
                <strong>{{ ( 100 - compressionRatio ) }}%</strong> smaller
              </p>
              <p
                v-else
                class="subtitle  compress-optimization-ratio-nothing is-size-6 has-text-centered"
              >
                {{ all_strings.latest_images.same_size }}
              </p>
              <progress
                class="  progress is-large is-success "
                :value="compressionRatio"
                :max="100"
              />
              <hr>
            </div>
            <Image_diff
              class="is-fullwidth"
              value="50"
              :first_label="strings.image_1_label"
              :second_label="strings.image_2_label"
            >
              <img
                slot="first"
                :src="sample_images.optimized"
              >
              <img
                slot="second"
                :src="sample_images.original"
              >
            </Image_diff>
          </div>
        </div>
        <p class="title has-text-centered is-6">
          {{ strings.quality_slider_desc }}
        </p>
      </div>
      <div v-else-if=" sample_images.id < 0">
        <p class="title has-text-centered is-5 is-size-6-mobile">
          {{ strings.no_images_found }}
        </p>
      </div>
    </div>
  </div>
</template>

<script>


import VueSlider from 'vue-slider-component'

import Image_diff from "./image_diff.vue";

import 'vue-slider-component/theme/antd.css'
function qualityNumberToString( value ) {
	if ( value === 90 ) {
		return optimoleDashboardApp.strings.options_strings.high_q_title;
	}
	if ( value === 75 ) {
		return optimoleDashboardApp.strings.options_strings.medium_q_title;
	}
	if ( value === 55 ) {
		return optimoleDashboardApp.strings.options_strings.low_q_title;
	}
	return value;
}
function qualityStringToNumber( value ) {

	if( typeof value === 'number' ){
		return value;
	}
	if ( value === 'auto' ) {
		return 90;
	}
	if ( value === 'high_c' ) {
		return 90;
	}
	if ( value === 'medium_c' ) {
		return 75;
	}
	if ( value === 'low_c' ) {
		return 55;
	}
	return 90;
}
export default {
	name: "Compression",
	components: {VueSlider,Image_diff},
	data() {
		return {
			strings: optimoleDashboardApp.strings.options_strings,
			all_strings: optimoleDashboardApp.strings,
			showSave: false,
			showSample: false,
			loading_images: false,
			showComparison: false,
			new_data: {
				quality: qualityStringToNumber( this.$store.state.site_settings.quality ),
				network_optimization:  this.$store.state.site_settings.network_optimization
			},
			sliderOptions: {
				processStyle: {
					backgroundColor: '#3273dc'
				},
				contained: true,
				dotStyle: {
					borderColor: '#3273dc'
				},
				min: 50,
				max: 100,
				tooltipFormatter: function ( value ) {
					return qualityNumberToString( value );
				},
				marks: function ( value ) {
					let style = {
						width: '12px',
						height: '12px',
						display: 'block',
						backgroundColor: '#3273dc',
						borderColor: '#3273dc',
						boxShadow: 'none',
						transform: 'translate(-4px, -4px)'
					};
					let text = qualityNumberToString( value );
					if( typeof text === 'number' ){
						return false;
					}
					return {
						label: text,
						style: style
					};
				}
			}
		}
	},
	computed: {
		site_settings() {
			return this.$store.state.site_settings;
		},
		gifReplacementStatus: {
			set: function ( value ) {
				this.showSave = true;
				this.new_data.img_to_video = value ? 'enabled' : 'disabled';
			},
			get: function () {
				return !( this.site_settings.img_to_video === 'disabled' );

			}
		},
		avifStatus: {
			set: function ( value ) {
				this.showSave = true;
				this.new_data.avif = value ? 'enabled' : 'disabled';
			},
			get: function () {
				return !( this.site_settings.avif === 'disabled' );

			}
		},
		cdnStatus: {
			set: function ( value ) {
				this.showSave = true;
				this.new_data.cdn = value ? 'enabled' : 'disabled';
			},
			get: function () {
				return !( this.site_settings.cdn === 'disabled' );
			}
		},
		compressionRatio() {
			return ( parseFloat( this.sample_images.optimized_size / this.sample_images.original_size ) * 100 ).toFixed( 0 );
		},
		sample_images() {
			return this.$store.state.sample_rate;
		},
		networkStatus: {
			set: function ( value ) {
				this.showSave=true;
				this.new_data.network_optimization = value ? 'enabled' : 'disabled';
			},
			get: function () {
				return !( this.site_settings.network_optimization === 'disabled' );
			}
		}
	},
	mounted: function () {
	},
	methods: {
		saveChanges: function () {
			this.$store.dispatch( 'saveSettings', {
				settings: this.new_data
			} );
		},
		newSample: function( force ) {
			if ( this.showComparison !== true ) {
				this.showComparison = true
			}
			this.updateSampleImage( this.new_data.quality, force  ? 'yes' : 'no' );
			return false;
		},
		updateSampleImage: function ( value, force = 'no' ) {
			this.$store.dispatch( 'sampleRate', {
				quality: value,
				force: force,
				component: this,
			} ).then(
				( response ) => {

					setTimeout( () => {
						this.showNotification = false;
					}, 1000 )
				},
				( response ) => {

				}
			);
		},
	}
}
</script>

<style scoped>
		.sample-image{
				margin-top:30px;
		}
		.view-sample-image{

				line-height: 32px;
				margin-left: 20px;
		}
</style>