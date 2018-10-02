<template>
	<section class="is-clearfix">
		<nav class="breadcrumb" aria-label="breadcrumbs" v-if="showApiKey">
			<ul>
				<li><a @click="toggleApiForm" href="#">{{strings.back_to_register}}</a></li>
				<li class="is-active is-marginless" v-if="showApiKey"><a href="#" aria-current="page">{{strings.back_to_connect}}</a>
				</li>
			</ul>
		</nav>
		<div class="section" v-if="showApiKey">
			
			<api-key-form></api-key-form>
			<br/>
			<div class="columns">
				
				<div class="column  columns is-vcentered ">
					
					<span class="dashicons dashicons-share column is-2 is-size-3"></span>
					<div class="is-pulled-left column is-10">
						<p class="title is-size-5">1. {{strings.step_one_api_title}}</p>
						<p class="subtitle is-size-6" v-html="strings.step_one_api_desc"></p>
					</div>
				</div>
				<div class="column  columns is-vcentered ">
					
					<span class="dashicons dashicons-admin-plugins column is-2 is-size-3"></span>
					<div class="is-pulled-left column is-10">
						<p class="title is-size-5">2. {{strings.step_two_api_title}}</p>
						<p class="subtitle is-size-6">{{strings.step_two_api_desc}}</p>
					</div>
				</div>
			</div>
		
		</div>
		<div v-else class="columns  is-vcentered is-desktop ">
			<div class="column   has-text-left is-fluid  is-hidden-touch">
				<div class="hero">
					<div class="hero-body content">
						<p class="title">Sign-up</p>
						<p class="subtitle " v-html="strings.account_needed_title"></p>
						<div class="  is-hidden-touch">
							<div class="columns  is-vcentered  ">
								<div class=" is-narrow is-hidden-touch column">
									<span class="dashicons   icon dashicons-format-image is-size-4 "></span>
								</div>
								<div class="column">
									<p class="subtitle column is-size-6 is-vcentered has-text-left"><br/>
										{{strings.account_needed_subtitle_1}}</p>
								</div>
							</div>
							<div class="columns  is-vcentered">
								<div class=" is-narrow is-hidden-touch column">
									<span class="dashicons   icon dashicons-plus is-size-4 "></span>
								</div>
								<div class="column">
									<p class="subtitle column is-size-6 is-vcentered has-text-left"><br/>
										{{strings.account_needed_subtitle_2}}</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="column    ">
				<p v-html="strings.account_needed_title" class="is-size-6 has-text-centered is-hidden-desktop"></p>
				<div class="field     ">
					<label for="optml-email" class="label title is-size-5   is-12">{{strings.email_address_label}}
						:</label>
					<div class="control   is-12 is-small has-icons-left ">
						<input name="optml-email" id="optml-email" class="input is-medium is-fullwidth is-danger"
						       type="email"
						       v-model="email"/>
						<span class="icon is-small is-left dashicons dashicons-email"></span>
					
					</div>
					
					<p class="help is-danger" v-if="error" v-html="strings.error_register"></p>
				</div>
				<div class="field   is-desktop ">
					<div class="control columns ">
						<div class="column has-text-centered-mobile">
							<button @click="registerAccount" class="button    is-small is-primary  "
							        :class="isLoading ? 'is-loading' :'' ">
								<span class="icon dashicons dashicons-admin-users"></span>
								<span>{{strings.register_btn}}</span>
							</button>
						</div>
						<div class="column has-text-centered-mobile has-text-right">
							<button @click="toggleApiForm" class="button  is-small  is-outlined is-info">
								<span class="icon dashicons dashicons-admin-network is-small"></span>
								<span>{{strings.api_exists}}</span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>

<script>

	import ApiKeyForm from "./api-key-form.vue";

	export default {
		name: 'connect-layout',
		components: {ApiKeyForm},
		data() {
			return {
				email: optimoleDashboardApp.current_user.email,
				strings: optimoleDashboardApp.strings,
				showApiKey: false,
				error: false,
			}
		},
		computed: {
			isLoading: function () {
				return this.$store.state.loading;
			}
		},
		methods: {
			toggleApiForm: function () {
				this.error = false;
				this.showApiKey = !this.showApiKey;
			},
			registerAccount: function () {
				this.error = false;
				this.$store.dispatch('registerOptimole', {
					email: this.email,
				}).then((response) => {
					if (response.code === 'success') {
						this.showApiKey = true;
					} else {
						this.error = true;
					}
				})
			}
		}
	}
</script>
<style scoped>
	input {
		box-sizing: border-box !important;
	}
</style>