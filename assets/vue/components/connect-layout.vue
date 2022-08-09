<template>
	<section class="is-clearfix optml-line-height">
		<div class="notification is-danger" v-if="isRestApiWorking" v-html="strings.notice_api_not_working"></div>
		<div class="section" v-if="showApiKey">
			<div class="notification is-success" v-if="from_register">
				{{strings.notification_message_register}}
			</div>
			<div style="display: inline-block; margin: auto 41px;">
				<div class="is-size-5 has-text-weight-bold" style="margin-bottom: 2%;" v-html="strings.steps_connect_api_title"> </div>
				<div class="is-size-6 optml-line-height" style="margin-bottom: 2%;" v-html="strings.steps_connect_api_desc"></div>
			</div>
			<api-key-form></api-key-form>
			<div style="margin-top: 2%" class="has-text-centered"><a @click="toggleApiForm" v-if="showApiKey" href="#" aria-current="page">{{strings.back_to_connect}}</a></div>
		</div>
		<div class="columns   is-vcentered is-desktop " v-else>
			<div class="column  has-text-left is-fluid  is-hidden-touch">
				<div class="hero">
					<div class="hero-body content">
						<p class="title">{{strings.account_needed_heading}}</p>
						<p style="margin-top: 5%;" class="subtitle " v-html="strings.account_needed_title"></p>
						<div class="  is-hidden-touch">
							<div class="columns  is-vcentered  optml-side-by-side">

								<div class="column optml-side-by-side">
									<div class=" is-narrow is-hidden-touch column " style="background-color: white; color:#577BF9 !important; margin-right: 7px;margin-left: -10px;">
										<span class="dashicons dashicons-yes-alt" style="margin-top: 2px;"></span>
									</div>
									<p class="subtitle column is-size-6 is-vcentered has-text-left" style="margin-left: -3%;"
										 v-html="strings.account_needed_subtitle_1"></p>
								</div>
							</div>
							<div class="columns  is-vcentered optml-side-by-side">

								<div class="column optml-side-by-side">
									<div class=" is-narrow is-hidden-touch column" style="background-color: white; color:#577BF9 !important; margin-right: 7px;margin-left: -10px;">
										<span class="dashicons dashicons-yes-alt" style="margin-top: 2px;"></span>
									</div>
									<p class="subtitle column is-size-6 is-vcentered has-text-left" style="margin-left: -3%;"
										 v-html="strings.account_needed_subtitle_2"></p>
								</div>
							</div>
							<div class="columns  is-vcentered " style="position: relative;top:70%;">
								<div class="column optml-side-by-side" style="margin-top: 10%;">
									<div><svg width="62" height="62" viewBox="0 0 62 62" fill="none" xmlns="http://www.w3.org/2000/svg">
										<rect width="62" height="62" rx="31" fill="#577BF9"/>
										<path d="M45.125 31.5C45.125 39.0249 39.0249 45.125 31.5 45.125V47.125C40.1294 47.125 47.125 40.1294 47.125 31.5H45.125ZM31.5 45.125C23.9751 45.125 17.875 39.0249 17.875 31.5H15.875C15.875 40.1294 22.8706 47.125 31.5 47.125V45.125ZM17.875 31.5C17.875 23.9751 23.9751 17.875 31.5 17.875V15.875C22.8706 15.875 15.875 22.8706 15.875 31.5H17.875ZM31.5 17.875C39.0249 17.875 45.125 23.9751 45.125 31.5H47.125C47.125 22.8706 40.1294 15.875 31.5 15.875V17.875ZM37 31.5C37 34.5376 34.5376 37 31.5 37V39C35.6421 39 39 35.6421 39 31.5H37ZM31.5 37C28.4624 37 26 34.5376 26 31.5H24C24 35.6421 27.3579 39 31.5 39V37ZM26 31.5C26 28.4624 28.4624 26 31.5 26V24C27.3579 24 24 27.3579 24 31.5H26ZM31.5 26C34.5376 26 37 28.4624 37 31.5H39C39 27.3579 35.6421 24 31.5 24V26ZM41.1343 20.4515L35.3891 26.1967L36.8033 27.6109L42.5485 21.8657L41.1343 20.4515ZM35.3891 36.8033L41.1343 42.5485L42.5485 41.1343L36.8033 35.3891L35.3891 36.8033ZM27.6109 26.1967L21.8657 20.4515L20.4515 21.8657L26.1967 27.6109L27.6109 26.1967ZM26.1967 35.3891L20.4515 41.1343L21.8657 42.5485L27.6109 36.8033L26.1967 35.3891Z" fill="white"/>
									</svg></div>

									<div class="subtitle is-size-6 is-vcentered has-text-left" style="position: relative; padding-left: 10px; top: -17%; width: 70%;">
										<div class="has-text-weight-bold" style="margin-bottom: 7px;">Need help?</div>
										<div v-html="strings.account_needed_subtitle_3"></div>
										 </div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="column is-5-desktop is-full-touch  optml-light-background" style="padding: 2% 3% 3% 2.5%">
				<p v-html="strings.account_needed_title" class="is-size-6 has-text-centered is-hidden-desktop"></p>
				<div class="field" >
					<label for="optml-email" class="label title is-size-5   is-12">{{strings.email_address_label}}
						:</label>
					<div class="control   is-12 is-small ">
						<input name="optml-email" id="optml-email" class="input is-fullwidth is-info" style="font-size: 16px;"
									 type="email"
									 v-model="email"/>
					</div>
					
					<p class="help is-danger" v-if="error" v-html="strings.error_register"></p>
					<p class="help is-danger" v-else-if="email_registered" v-html="email_registered"></p>
				</div>
				<div class="field   ">
					<div class="control ">
						<div class="    has-text-centered-mobile">
							<button @click="registerAccount" class="button optml-button-style-1 is-fullwidth optml-button-px-padding"
											:class="isLoading ? 'is-loading' :'' ">
								<span>{{strings.register_btn}}</span>
							</button>
						</div>
						<hr/>
						<div class="is-right has-text-centered-mobile has-text-right">
							<div class="is-size-6 is-vcentered has-text-left has-text-weight-bold" style="margin-bottom: 6%;">{{strings.existing_user}} ?</div>

							<button @click="toggleApiForm" class="button  is-fullwidth is-outlined optml-button-px-padding has-text-weight-bold" style="background-color: #757296; font-size: 14px; color: white;">
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
				email_registered: false,
				showRegisterField: false,
				from_register: false,
				autoConnect: false,

			}
		},
		mounted: function () {
			if ( this.$store.state.autoConnect !== 'no' ) {
				this.email = this.$store.state.autoConnect;
				this.autoConnect = true;
				this.registerAccount();
			}
		},
		computed: {
			isLoading: function () {
				return this.$store.state.loading;
			},
			isRestApiWorking: function () {
				return this.$store.state.apiError;
			}
		},
		methods: {
			toggleApiForm: function () {
				this.error = false;
				this.from_register = false;
				this.showApiKey = !this.showApiKey;
			},
			registerAccount: function ( ) {
				this.error = false;
				this.$store.dispatch('registerOptimole', {
					email: this.email,
					autoConnect: this.autoConnect,
				}).then((response) => {
					if ( response.code === 'email_registered') {
						this.email_registered = response.message;
						return;
					}
					if (response.code === 'success') {
						this.showApiKey = true;
						this.from_register = true;
					} else {
						this.error = true;
					}
				})
			}
		}
	}
</script>
<style scoped>
	input, .notification .delete, button {
		box-sizing: border-box !important;
	}
	#optimole-app .subtitle {
		line-height: 1.5em;
		word-break: keep-all !important;
	}

</style>