<template>
    <div>
        <h4>{{strings.add_desc}}</h4>
        <div class="field  columns">
            <div class="column" v-for="file in files">
                <span class="tag">
                    <i>{{file.name}}</i>
                    <i v-if="!file.active && !file.success && file.error === ''" class="dashicons dashicons-yes icon has-text-grey-light"></i>
                    <i v-else-if="file.active" class="dashicons dashicons-marker icon spin has-text-warning"></i>
                    <i v-else-if="!file.active && file.success" class="dashicons dashicons-yes icon has-text-success"></i>
                    <i v-else class="dashicons dashicons-no-alt icon has-text-danger"></i>
                </span>
            </div>
        </div>
        <div class="column ">
            <file-upload
                    class="button is-secondary is-rounded"
                    :post-action=" global.root + '/add_watermark'"
                    :headers="{'X-WP-Nonce': global.nonce}"
                    extensions="gif,jpg,jpeg,png,webp"
                    accept="image/png,image/gif,image/jpeg,image/webp"
                    :multiple="true"
                    :size="1024 * 1024 * 10"
                    v-model="files"
                    @input-filter="inputFilter"
                    @input-file="inputFile"
                    :disabled="loading"
                    ref="upload">
                <i class="dashicons dashicons-plus icon"></i>
                {{strings.upload}}
            </file-upload>
        </div>
        <div class="notification is-danger" v-if="is_error">{{error_message}}</div>
        <hr/>
        <div class="optimized-images" v-if="! loading ">
            <div v-if="!noImages">
                <h3 class="has-text-centered">{{strings.last}} {{strings.optimized_images}}</h3>
                <table class="table is-striped is-hoverable is-fullwidth">
                    <thead>
                    <tr>
                        <th class="optml-image-heading">{{strings.id}}</th>
                        <th class="optml-image-heading">{{strings.image}}</th>
                        <th class="optml-image-heading">{{strings.name}}</th>
                        <th class="optml-image-heading">{{strings.type}}</th>
                        <th class="optml-image-heading">{{strings.action}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item, index) in watermarkData">
                        <td>{{item.ID}}</td>
                        <td><img :src="item.guid" class="optml-image"/></td>
                        <td>{{item.post_title}}</td>
                        <td>{{item.post_mime_type}}</td>
                        <td>
                            <a @click="removeWatermark(item.ID)" class="button is-small is-danger is-rounded"
                               :class="{'is-loading':loading}">
                                <span class="dashicons dashicons-no-alt icon"></span>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-else>
            <iframe width="1" height="1" :src="home_url" style="visibility: hidden"></iframe>
            <h6 class="has-text-centered">{{strings.loading_latest_images}}</h6>
            <progress class="progress is-large" :value="startTime" :max="maxTime"></progress>
        </div>
        <table class="table is-striped is-hoverable is-fullwidth" v-if="noImages">
            <thead>
            <tr>
                <th class="optml-image-heading has-text-centered" v-html="strings.no_images_found"></th>
            </tr>
            </thead>
        </table>
        <div class="box">
            <div class="field  is-fullwidth columns n">
                <label class="label is-half column has-text-grey-dark no-padding-right ">
                    {{strings.wm_title}}
                    <p class="is-italic has-text-weight-normal">
                        {{strings.wm_desc}}
                    </p>
                </label>

                <div class="column is-paddingless">
                    <div class="columns">
                        <div class="field column is-narrow">
                            <div class="select">
                                <select title="Watermark Selection" v-model="watermarkSettings.id" @change="selectWatermark()">
                                    <option value="0">No watermark</option>
                                    <option v-for="(item, index) in watermarkData" :value="item.ID">{{item.post_title}}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field  is-fullwidth columns n">
                <label class="label is-half column has-text-grey-dark no-padding-right ">
                    {{strings.opacity_title}}
                    <p class="is-italic has-text-weight-normal">
                        {{strings.opacity_desc}}
                    </p>
                </label>

                <div class="column is-paddingless">
                    <div class="columns">
                        <div class="field column is-narrow has-addons">
                            <p class="control">
                                <a class="button is-small is-static">
                                    {{strings.opacity_field}}
                                </a>
                            </p>
                            <p class="control ">
                                <input v-model="watermarkOpacity" class="input is-small" type="number" min="0"
                                       max="100">
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field  columns">
                <label class="label column has-text-grey-dark">
                    {{strings.position_title}}
                    <p class="is-italic has-text-weight-normal">
                        {{strings.position_desc}}
                    </p>
                </label>
                <div class="column  buttons ">
                    <div class="field columns is-gapless is-multiline">
                        <div class="column is-one-third">
                            <p class="control">
                                <a @click="changePosition('nowe')"
                                   :class="{ 'is-info':isActivePosition ('nowe'), '  is-selected':watermarkSettings.position === 'nowe'  }"
                                   class="button is-small is-rounded">
                                    <span class="icon dashicons dashicons-arrow-left plus-45deg"></span>
                                    <span>{{strings.pos_nowe_title}}</span>
                                </a>
                            </p>
                        </div>
                        <div class="column is-one-third">
                            <p class="control">
                                <a @click="changePosition('no')"
                                   :class="{ 'is-info':isActivePosition ('no'), '  is-selected':watermarkSettings.position === 'no'  }"
                                   class="button is-small is-rounded">
                                    <span class="icon dashicons dashicons-arrow-up"></span>
                                    <span>{{strings.pos_no_title}}</span>
                                </a>
                            </p>
                        </div>
                        <div class="column is-one-third">
                            <p class="control">
                                <a @click="changePosition('noea')"
                                   :class="{ 'is-info':isActivePosition ('noea'), '  is-selected':watermarkSettings.position === 'noea'  }"
                                   class="button is-small is-rounded">
                                    <span class="icon dashicons dashicons-arrow-right minus-45deg"></span>
                                    <span>{{strings.pos_noea_title}}</span>
                                </a>
                            </p>
                        </div>
                        <div class="column is-one-third">
                            <p class="control">
                                <a @click="changePosition('we')"
                                   :class="{ 'is-info':isActivePosition ('we'), '  is-selected':watermarkSettings.position === 'we'  }"
                                   class="button is-small is-rounded">
                                    <span class="icon dashicons dashicons-arrow-left"></span>
                                    <span>{{strings.pos_we_title}}</span>
                                </a>
                            </p>
                        </div>
                        <div class="column is-one-third">
                            <p class="control">
                                <a @click="changePosition('ce')"
                                   :class="{ 'is-info':isActivePosition ('ce'), '  is-selected':watermarkSettings.position === 'ce'  }"
                                   class="button is-small is-rounded">
                                    <span class="icon dashicons dashicons-marker"></span>
                                    <span>{{strings.pos_ce_title}}</span>
                                </a>
                            </p>
                        </div>
                        <div class="column is-one-third">
                            <p class="control">
                                <a @click="changePosition('ea')"
                                   :class="{ 'is-info':isActivePosition ('ea'), '  is-selected':watermarkSettings.position === 'ea'  }"
                                   class="button is-small is-rounded">
                                    <span class="icon dashicons dashicons-arrow-right"></span>
                                    <span>{{strings.pos_ea_title}}</span>
                                </a>
                            </p>
                        </div>
                        <div class="column is-one-third">
                            <p class="control">
                                <a @click="changePosition('sowe')"
                                   :class="{ 'is-info':isActivePosition ('sowe'), '  is-selected':watermarkSettings.position === 'sowe'  }"
                                   class="button is-small is-rounded">
                                    <span class="icon dashicons dashicons-arrow-left minus-45deg"></span>
                                    <span>{{strings.pos_sowe_title}}</span>
                                </a>
                            </p>
                        </div>
                        <div class="column is-one-third">
                            <p class="control">
                                <a @click="changePosition('so')"
                                   :class="{ 'is-info':isActivePosition ('so'), '  is-selected':watermarkSettings.position === 'so'  }"
                                   class="button is-small is-rounded">
                                    <span class="icon dashicons dashicons-arrow-down"></span>
                                    <span>{{strings.pos_so_title}}</span>
                                </a>
                            </p>
                        </div>
                        <div class="column is-one-third">
                            <p class="control">
                                <a @click="changePosition('soea')"
                                   :class="{ 'is-info':isActivePosition ('soea'), '  is-selected':watermarkSettings.position === 'soea'  }"
                                   class="button is-small is-rounded">
                                    <span class="icon dashicons dashicons-arrow-right plus-45deg"></span>
                                    <span>{{strings.pos_soea_title}}</span>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field  is-fullwidth columns n">
                <label class="label is-half column has-text-grey-dark no-padding-right ">
                    {{strings.offset_title}}
                    <p class="is-italic has-text-weight-normal">
                        {{strings.offset_desc}}
                    </p>
                </label>

                <div class="column is-paddingless">
                    <div class="columns">
                        <div class="field column is-narrow has-addons">
                            <p class="control">
                                <a class="button is-small is-static">
                                    {{strings.offset_x_field}}
                                </a>
                            </p>
                            <p class="control ">
                                <input v-model="watermarkSettings.offset_x" class="input is-small" type="number">
                            </p>
                        </div>
                        <div class="field column is-narrow has-addons">
                            <p class="control">
                                <a class="button is-small is-static">
                                    {{strings.offset_y_field}}
                                </a>
                            </p>
                            <p class="control ">
                                <input v-model="watermarkSettings.offset_y" class="input is-small" type="number">
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field  is-fullwidth columns n">
                <label class="label is-half column has-text-grey-dark no-padding-right ">
                    {{strings.scale_title}}
                    <p class="is-italic has-text-weight-normal">
                        {{strings.scale_desc}}
                    </p>
                </label>

                <div class="column is-paddingless">
                    <div class="columns">
                        <div class="field column is-narrow has-addons">
                            <p class="control">
                                <a class="button is-small is-static">
                                    {{strings.scale_field}}
                                </a>
                            </p>
                            <p class="control ">
                                <input v-model="watermarkScale" class="input is-small" type="number" min="0"
                                       max="100">
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <p class="control column has-text-centered-desktop has-text-left-touch  ">
                <a @click="saveChanges()" class="button is-small is-success "
                   :class="{'is-loading':loading}">
                    <span class="dashicons dashicons-yes icon"></span>
                    <span>	{{strings.save_changes}}</span>
                </a>
            </p>
        </div>
    </div>
</template>

<script>
	import FileUpload from 'vue-upload-component'
	export default {
		name: "watermarks",
		components: {FileUpload},
		data() {
			return {
				global: optimoleDashboardApp,
				loading: false,
				startTime: 0,
				maxTime: 20,
				noImages: true,
				files: [],
				is_error: false,
				error_message: '',
				home_url: optimoleDashboardApp.home_url,
				strings: optimoleDashboardApp.strings.watermarks,
				watermarkData: [{
					ID: 1,
					post_title: '',
					post_mime_type: '',
					guid: '',
				},],
                watermarkSettings: {
					id: 0,
					opacity: 1,
                    position: 'ce',
                    offset_x: 0,
                    offset_y: 0,
                    scale: 1
                },
                newData: {}
			}
		},
		mounted() {
			if ( this.$store.state.optimizedImages.length > 0) {
				this.loading = false;
				return;
			}
			//this.doProgressBar();
			this.$store.dispatch('retrieveWatermarks', {component: this});
		},
        computed: {
	        watermarkOpacity: {
	        	set( value ) {
	        		if ( parseInt( value ) < 0 ) {
	        			this.watermarkSettings.opacity = 0;
	        			this.newData.wm_opacity = this.watermarkSettings.opacity
	        			return;
                    }
			        if ( parseInt( value ) > 100 ) {
				        this.watermarkSettings.opacity = 1;
				        this.newData.wm_opacity = this.watermarkSettings.opacity
				        return;
			        }

			        this.watermarkSettings.opacity = parseFloat( parseInt( value ) / 100 );
			        this.newData.wm_opacity = this.watermarkSettings.opacity
                },
                get() {
                    return Math.round( this.watermarkSettings.opacity * 100 )
                }
            },
	        watermarkScale: {
		        set( value ) {
			        if ( parseInt( value ) < 0 ) {
				        this.watermarkSettings.scale = 0;
				        this.newData.wm_scale = this.watermarkSettings.scale
				        return;
			        }
			        if ( parseInt( value ) > 300 ) {
				        this.watermarkSettings.scale = 3;
				        this.newData.wm_scale = this.watermarkSettings.scale
				        return;
			        }

			        this.watermarkSettings.scale = parseFloat( parseInt( value ) / 100 );
			        this.newData.wm_scale = this.watermarkSettings.scale
		        },
		        get() {
			        return Math.round( this.watermarkSettings.scale * 100 )
		        }
	        }
        },
		methods: {
			saveChanges: function () {
				this.$store.dispatch('saveSettings', {
					settings: this.newData
				});
			},
			changePosition: function (value) {
				this.watermarkSettings.position = value;
				this.newData.wm_position = this.watermarkSettings.position;
			},
            selectWatermark: function() {
	            this.newData.wm_id = this.watermarkSettings.id;
            },
			isActivePosition(pos) {
				return this.watermarkSettings.position === pos;
			},
			inputFilter(newFile, oldFile, prevent) {
				if (newFile && !oldFile) {
					// Before adding a file
					// Filter system files or hide files
					if (/(\/|^)(Thumbs\.db|desktop\.ini|\..+)$/.test(newFile.name)) {
						return prevent()
					}
					// Filter php html js file
					if (/\.(php5?|html?|jsx?)$/i.test(newFile.name)) {
						return prevent()
					}
				}
			},
			inputFile(newFile, oldFile) {
				this.is_error = false;
				this.error_message = '';
				if (newFile && !oldFile) {
					// add
					console.log('add', newFile)
					this.$refs.upload.active = true
					this.loading = true;
				}
				if (newFile && oldFile) {
					console.log('update', newFile)
					if ( newFile.response.data && newFile.response.data.error ) {
						this.is_error = true;
						this.error_message = newFile.response.data.error;
						this.loading = false;
						return;
					}
					this.$store.dispatch('retrieveWatermarks', {component: this});
					this.loading = false;
				}
				if (!newFile && oldFile) {
					// remove
					console.log('remove', oldFile)
				}
			},
			removeWatermark( postID ) {
				this.$store.dispatch('removeWatermark', { postID: postID, component: this});
			}
		},
	}
</script>

<style scoped>
    .plus-45deg {
        transform: rotate(45deg);
    }
    .minus-45deg {
        transform: rotate(-45deg);
    }
</style>