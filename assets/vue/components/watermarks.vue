<template>
	<div>
		<h4>{{strings.add_desc}}</h4>
		<div class="field  columns">
			<div class="column" v-for="file in files">
                <span class="tag">
                    <i>{{file.name}}</i>
                    <i v-if="!file.active && !file.success && file.error === ''"
                       class="dashicons dashicons-yes icon has-text-grey-light"></i>
                    <i v-else-if="file.active" class="dashicons dashicons-marker icon spin has-text-warning"></i>
                    <i v-else-if="!file.active && file.success"
                       class="dashicons dashicons-yes icon has-text-success"></i>
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
					:size="1024 * 1024 * 10"
					v-model="files"
					@input-filter="inputFilter"
					@input-file="inputFile"
					:disabled="loading"
					ref="upload">
				<i class="dashicons dashicons-plus icon"></i>
				{{strings.upload}}
			</file-upload>
			<br/><br/><span class="tag is-danger" v-if="is_error">{{error_message}}</span>
		</div>
		<hr/>
		<div class="box">
			<h3><span class="dashicons dashicons-menu"></span> {{strings.list_header}} </h3>
			<small><i>{{strings.max_allowed}}</i></small>
			
			<div class="optimized-images">
				<div v-if="!noImages">
					<h3 class="has-text-centered">{{strings.last}} {{strings.optimized_images}}</h3>
					<table class="table is-striped is-hoverable is-fullwidth">
						<thead>
						<tr>
							<th class="optml-image-heading">{{strings.id}}</th>
							<th class="optml-image-heading">{{strings.image}}</th>
							<th class="optml-image-heading">{{strings.action}}</th>
						</tr>
						</thead>
						<tbody>
						<tr v-for="(item, index) in watermarkData">
							<td><code>#{{item.ID}}</code></td>
							<td><img :src="item.guid" class="optml-image-watermark" width="50"/></td>
							<td width="50">
								<a @click="removeWatermark(item.ID)" class="button is-small is-danger is-rounded"
								   :class="{'is-loading':loading}">
									<span class="dashicons dashicons-no-alt icon"></span>
								</a>
							</td>
						</tr>
						</tbody>
					</table>
					
					<span class="tag is-success" v-if="loading">
					{{strings.loading_remove_watermark}}
					</span>
				</div>
			</div>
			<table class="table is-striped is-hoverable is-fullwidth" v-if="noImages">
				<thead>
				<tr>
					<th class="optml-image-heading has-text-centered" v-html="strings.no_images_found"></th>
				</tr>
				</thead>
			</table>
			<hr/>
			<h3><span class="dashicons dashicons-grid-view"></span> {{strings.settings_header}} </h3>
			<br/>
			<div class="field  is-fullwidth columns">
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
								<select title="Watermark Selection" v-model="selectedWatermark">
									<option value="-1">No watermark</option>
									<option v-for="(item, index) in watermarkData" :value="item.ID">#({{item.ID}})
									</option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="field  is-fullwidth columns">
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
					<div class="field columns is-gapless is-marginless ">
						<div class="is-fullwidth  optml-layout-grid">
							<a @click="changePosition('nowe')"
							   :class="{ 'is-info':isActivePosition ('nowe'), '  is-selected':watermarkSettings.position === 'nowe'  }"
							   class="grid-button  " :title="strings.pos_nowe_title">
							</a>
							<a @click="changePosition('no')"
							   :class="{ 'is-info':isActivePosition ('no'), '  is-selected':watermarkSettings.position === 'no'  }"
							   class="grid-button  " :title="strings.pos_no_title">
							</a>
							<a @click="changePosition('noea')"
							   :class="{ 'is-info':isActivePosition ('noea'), '  is-selected':watermarkSettings.position === 'noea'  }"
							   class="grid-button" :title="strings.pos_noea_title">
							</a>
							<a @click="changePosition('we')"
							   :class="{ 'is-info':isActivePosition ('we'), '  is-selected':watermarkSettings.position === 'we'  }"
							   class="grid-button" :title="strings.pos_we_title">
							</a>
							<a @click="changePosition('ce')"
							   :class="{ 'is-info':isActivePosition ('ce'), '  is-selected':watermarkSettings.position === 'ce'  }"
							   class="grid-button" :title="strings.pos_ce_title">
							</a>
							<a @click="changePosition('ea')"
							   :class="{ 'is-info':isActivePosition ('ea'), '  is-selected':watermarkSettings.position === 'ea'  }"
							   class="grid-button" :title="strings.pos_ea_title">
							</a>
							<a @click="changePosition('sowe')"
							   :class="{ 'is-info':isActivePosition ('sowe'), '  is-selected':watermarkSettings.position === 'sowe'  }"
							   class="grid-button" :title="strings.pos_sowe_title">
							</a>
							<a @click="changePosition('so')"
							   :class="{ 'is-info':isActivePosition ('so'), '  is-selected':watermarkSettings.position === 'so'  }"
							   class="grid-button" :title="strings.pos_so_title">
							</a>
							<a @click="changePosition('soea')"
							   :class="{ 'is-info':isActivePosition ('soea'), '  is-selected':watermarkSettings.position === 'soea'  }"
							   class="grid-button" :title="strings.pos_soea_title">
							</a>
						</div>
					</div>
					<br/>
				</div>
			</div>
			
			<div class="field  is-fullwidth columns ">
				<label class="label is-half column has-text-grey-dark no-padding-right ">
					{{strings.offset_title}}
					<p class="is-italic has-text-weight-normal">
						{{strings.offset_desc}}
					</p>
				</label>
				
				<div class="column is-paddingless">
					<div class="columns">
						<div class="field column is-narrow   has-addons">
							<p class="control">
								<a class="button is-small is-static">
									{{strings.offset_x_field}}
								</a>
							</p>
							<p class="control ">
								<input v-model="watermarkX" class="input is-small" type="number">
							</p>
						</div>
						<div class="field column is-narrow  has-addons">
							<p class="control">
								<a class="button is-small is-static">
									{{strings.offset_y_field}}
								</a>
							</p>
							<p class="control ">
								<input v-model="watermarkY" class="input is-small" type="number">
							</p>
						</div>
					</div>
				</div>
			</div>
			
			<div class="field  is-fullwidth columns">
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
					guid: '',
				},],
				watermarkSettings: this.$store.state.site_settings.watermark,
				newData: {}
			}
		},
		mounted() {
			if (this.$store.state.optimizedImages.length > 0) {
				return;
			}
			this.selectedWatermark = this.watermarkSettings.id;
			this.$store.dispatch('retrieveWatermarks', {component: this});
		},
		computed: {
			watermarkOpacity: {
				set(value) {
					if (parseInt(value) < 0) {
						this.$store.commit('updateWatermark', {opacity: 0})
						this.newData.wm_opacity = this.watermarkSettings.opacity
						return;
					}
					if (parseInt(value) > 100) {
						opacity = 1;
						this.$store.commit('updateWatermark', {opacity: 1})
						this.newData.wm_opacity = this.watermarkSettings.opacity
						return;
					}

					this.$store.commit('updateWatermark', {opacity: parseFloat(parseInt(value) / 100)})
					this.newData.wm_opacity = this.watermarkSettings.opacity
				},
				get() {
					return Math.round(this.watermarkSettings.opacity * 100)
				}
			},
			watermarkScale: {
				set(value) {
					if (parseInt(value) < 0) {
						this.$store.commit('updateWatermark', {scale: 0})
						this.newData.wm_scale = this.watermarkSettings.scale
						return;
					}
					if (parseInt(value) > 300) {
						this.$store.commit('updateWatermark', {scale: 3})
						this.newData.wm_scale = this.watermarkSettings.scale
						return;
					}

					this.$store.commit('updateWatermark', {scale: parseFloat(parseInt(value) / 100)})
					this.newData.wm_scale = this.watermarkSettings.scale
				},
				get() {
					return Math.round(this.watermarkSettings.scale * 100)
				}
			},
			watermarkX: {
				set(value) {
					this.$store.commit('updateWatermark', {x_offset: parseInt(value)})
					this.newData.wm_x = this.watermarkSettings.x_offset
				},
				get() {
					return this.watermarkSettings.x_offset
				}
			},
			watermarkY: {
				set(value) {
					this.$store.commit('updateWatermark', {y_offset: parseInt(value)})
					this.newData.wm_y = this.watermarkSettings.y_offset
				},
				get() {
					return this.watermarkSettings.y_offset
				}
			},
			selectedWatermark: {
				set(value) {
					this.$store.commit('updateWatermark', {id: parseInt(value)})
					this.newData.wm_id = this.watermarkSettings.id
				},
				get() {
					return this.watermarkSettings.id
				}
			},
			loading() {
				return this.$store.state.loading;
			}
		},
		methods: {
			saveChanges: function () {
				this.$store.dispatch('saveSettings', {
					settings: this.newData
				});
			},
			changePosition: function (value) {
				this.$store.commit('updateWatermark', {position: value})
				this.newData.wm_position = this.watermarkSettings.position;
			},
			isSelectedWatermark(id) {
				return this.$store.state.site_settings.watermark.id === id
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
				if (newFile && !oldFile) {
					// add
					this.is_error = false;
					this.error_message = '';
					this.$refs.upload.active = true
				}
				if (newFile && oldFile) {
					if (newFile.response.code && newFile.response.code === 'error') {
						this.is_error = true;
						this.error_message = newFile.response.data;
						return;
					}
					this.$store.dispatch('retrieveWatermarks', {component: this});
					
				}
				if (newFile && oldFile && newFile.success !== oldFile.success) {
					this.$refs.upload.clear();
				}
				if (!newFile && oldFile) {
					// remove
				}

			},
			removeWatermark(postID) {
				this.$store.dispatch('removeWatermark', {postID: postID, component: this});
			}
		},
	}
</script>

<style scoped>
	.optml-layout-grid .grid-button.is-selected {
		background: #4a4a4a;
	}
	
	.optml-layout-grid .grid-button {
		width: 50px;
		height: 50px;
		display: inline-block;
		margin: 0;
		padding: 0;
		border-radius: 9px;
		border: 5px solid #4a4a4a;
	}
	
	.optml-layout-grid {
		width: 200px;
		
	}
</style>