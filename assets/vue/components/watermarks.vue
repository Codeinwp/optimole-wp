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
				},]
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
		methods: {
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
		}
	}
</script>

<style scoped>
</style>