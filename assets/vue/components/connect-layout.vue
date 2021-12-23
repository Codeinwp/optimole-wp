<template>
	<section class="is-clearfix">
		<div class="notification is-danger" v-if="isRestApiWorking" v-html="strings.notice_api_not_working"></div>
		<div class="section" v-if="showApiKey" style="text-align: center;">
			<div class="notification is-success has-text-centered" v-if="from_register">
				{{strings.notification_message_register}}
			</div>
      <div class="has-text-centered" style="display: inline-block;">
        <div class="is-size-5 has-text-weight-bold" style="margin-bottom: 2%;" v-html="strings.steps_connect_api_title"> </div>
        <div class="is-size-6" style="margin-bottom: 2%;" v-html="strings.steps_connect_api_desc"></div>
      </div>
			<api-key-form></api-key-form>
      <div style="margin-top: 2%"><a @click="toggleApiForm" v-if="showApiKey" href="#" aria-current="page">{{strings.back_to_connect}}</a></div>
			<hr/>

		
		</div>
		<div class="columns   is-vcentered is-desktop " v-else>
			<div class="column  has-text-left is-fluid  is-hidden-touch">
				<div class="hero">
					<div class="hero-body content">
						<p class="title">{{strings.account_needed_heading}}</p>
						<p style="margin-top: 5%;" class="subtitle " v-html="strings.account_needed_title"></p>
						<div class="  is-hidden-touch">
							<div class="columns  is-vcentered  optml-side-by-side">
								<div class=" is-narrow is-hidden-touch column " style="background-color: white; color:#577BF9 !important;">
                  <span class="dashicons dashicons-yes-alt" style="margin: 0 0 1% 0;"></span>
								</div>
								<div class="column">
									<p class="subtitle column is-size-6 is-vcentered has-text-left" style="margin-left: -3%;"
										 v-html="strings.account_needed_subtitle_1"></p>
								</div>
							</div>
							<div class="columns  is-vcentered optml-side-by-side">
								<div class=" is-narrow is-hidden-touch column" style="background-color: white; color:#577BF9 !important;">
                  <span class="dashicons dashicons-yes-alt" style="margin: 0 0 1% 0;"></span>
								</div>
								<div class="column">

									<p class="subtitle column is-size-6 is-vcentered has-text-left" style="margin-left: -3%;"
										 v-html="strings.account_needed_subtitle_2"></p>
								</div>
							</div>
							<div class="columns  is-vcentered " style="position: relative;top:70%;">
								<div class="column optml-side-by-side" style="margin-top: 10%;">
                  <div class="optml-circle optml-light-background"></div>

									<div class="subtitle is-size-6 is-vcentered has-text-left" style="position: relative; left:3%; top: -17%; line-height: 87%;">
                    <div class="has-text-weight-bold">Need help?</div> <br>
                    <div v-html="strings.account_needed_subtitle_3"></div>
										 </div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="column is-4-desktop is-full-touch  optml-light-background" style="padding: 2% 3% 3% 2.5%">
				<p v-html="strings.account_needed_title" class="is-size-6 has-text-centered is-hidden-desktop"></p>
				<div class="field" >
					<label for="optml-email" class="label title is-size-5   is-12">{{strings.email_address_label}}
						:</label>
					<div class="control   is-12 is-small ">
						<input name="optml-email" id="optml-email" class="input is-medium is-fullwidth is-info"
									 type="email"
									 v-model="email"/>
          </div>
					
					<p class="help is-danger" v-if="error" v-html="strings.error_register"></p>
					<p class="help is-danger" v-else-if="email_registered" v-html="email_registered"></p>
				</div>
				<div class="field   ">
					<div class="control ">
						<div class="    has-text-centered-mobile">
							<button @click="registerAccount" class="button is-fullwidth is-medium is-info  "
											:class="isLoading ? 'is-loading' :'' ">
								<span>{{strings.register_btn}}</span>
							</button>
						</div>
						<hr/>
						<div class="is-right has-text-centered-mobile has-text-right">
              <div class="is-size-6 is-vcentered has-text-left has-text-weight-bold" style="margin-bottom: 6%;">{{strings.existing_user}} ?</div>

              <button @click="toggleApiForm" class="button  is-fullwidth is-medium  is-outlined" style="background-color: #757296;">
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
			registerAccount: function () {
				this.error = false;
				this.$store.dispatch('registerOptimole', {
					email: this.email,
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

</style>