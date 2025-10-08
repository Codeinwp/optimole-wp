
export {};

declare global {
  var optimoleDashboardApp: IOptimoleDashboardApp;
}

// @see https://transform.tools/json-to-typescript
export interface IOptimoleDashboardApp {
  strings: Strings
  assets_url: string
  dam_url: string
  connection_status: string
  has_application: string
  user_status: string
  available_apps: AvailableApp[]
  api_key: string
  routes: Routes
  language: string
  nonce: string
  user_data: UserData
  remove_latest_images: string
  current_user: CurrentUser
  site_settings: SiteSettings
  offload_limit: string
  home_url: string
  optimoleHome: string
  optimoleDashHome: string
  optimoleDashBilling: string
  days_since_install: string
  is_offload_media_available: string
  auto_connect: string
  cron_disabled: string
  submenu_links: SubmenuLink[]
  bf_notices: any[]
  spc_banner: SpcBanner
  show_exceed_plan_quota_notice: string
  report_issue_url: string
}

export interface Strings {
  optimole: string
  version: string
  terms_menu: string
  privacy_menu: string
  testdrive_menu: string
  service_details: string
  dashboard_title: string
  banner_title: string
  banner_description: string
  quick_action_title: string
  connect_btn: string
  disconnect_btn: string
  select: string
  your_domain: string
  add_api: string
  your_api_key: string
  looking_for_api_key: string
  refresh_stats_cta: string
  updating_stats_cta: string
  api_key_placeholder: string
  account_needed_heading: string
  account_needed_sub_heading: string
  account_needed_trust_badge: string
  account_needed_setup_time: string
  invalid_key: string
  keep_connected: string
  cloud_library: string
  image_storage: string
  disconnect_title: string
  disconnect_desc: string
  email_address_label: string
  steps_connect_api_title: string
  register_btn: string
  secure_connection: string
  step_one_api_title: string
  optml_dashboard: string
  steps_connect_api_desc: string
  api_exists: string
  back_to_register: string
  back_to_connect: string
  error_register: string
  invalid_email: string
  connected: string
  connecting: string
  not_connected: string
  usage: string
  quota: string
  logged_in_as: string
  private_cdn_url: string
  existing_user: string
  notification_message_register: string
  premium_support: string
  account_needed_title: string
  account_needed_subtitle_1: string
  account_needed_subtitle_3: string
  account_needed_subtitle_2: string
  account_needed_subtitle_4: string
  account_needed_footer: string
  account_connecting_title: string
  account_connecting_subtitle: string
  notice_just_activated: string
  notice_api_not_working: string
  notice_disabled_account: string
  signup_terms: string
  dashboard_menu_item: string
  settings_menu_item: string
  help_menu_item: string
  settings_exclusions_menu_item: string
  settings_resize_menu_item: string
  settings_compression_menu_item: string
  advanced_settings_menu_item: string
  general_settings_menu_item: string
  lazyload_settings_menu_item: string
  watermarks_menu_item: string
  conflicts_menu_item: string
  conflicts: Conflicts
  upgrade: Upgrade
  neve: Neve
  metrics: Metrics
  quick_actions: QuickActions
  options_strings: OptionsStrings
  help: Help
  watermarks: Watermarks
  latest_images: LatestImages
  csat: Csat
  cron_error: string
  cancel: string
  optimization_status: OptimizationStatus
  optimization_tips: string
  native_lazy_load_warning: string
  contact_support: {
    title_prefix: string
    disable_lazy_load_scaling: string
    disable_image_scaling: string
    enable_native_lazy_load: string
  }
}

export interface Conflicts {
  title: string
  message: string
  conflict_close: string
  no_conflicts_found: string
}

export interface Upgrade {
  title: string
  title_long: string
  reason_1: string
  reason_2: string
  reason_3: string
  reason_4: string
  cta: string
}

export interface Neve {
  is_active: string
  byline: string
  reason_1: string
  reason_2: string
  reason_3: string
  reason_4: string
  reason_5: string
}

export interface Metrics {
  metricsTitle1: string
  metricsSubtitle1: string
  metricsTitle2: string
  metricsSubtitle2: string
  metricsTitle3: string
  metricsSubtitle3: string
  metricsTitle4: string
  metricsSubtitle4: string
}

export interface QuickActions {
  speed_test_title: string
  speed_test_desc: string
  speed_test_link: string
  clear_cache_images: string
  clear_cache: string
  offload_images: string
  offload_images_desc: string
  advance_settings: string
  configure_settings: string
}

export interface OptionsStrings {
  compression_mode: string
  compression_mode_speed_optimized: string
  compression_mode_quality_optimized: string
  compression_mode_custom: string
  compression_mode_speed_optimized_desc: string
  compression_mode_quality_optimized_desc: string
  compression_mode_custom_desc: string
  best_format_title: string
  best_format_desc: string
  add_filter: string
  add_site: string
  admin_bar_desc: string
  auto_q_title: string
  cache_desc: string
  cache_title: string
  clear_cache_notice: string
  image_size_notice: string
  clear_cache_images: string
  clear_cache_assets: string
  connect_step_0: string
  connect_step_1: string
  connect_step_2: string
  connect_step_3: string
  disabled: string
  enable_avif_title: string
  enable_avif_desc: string
  enable_bg_lazyload_desc: string
  enable_bg_lazyload_title: string
  enable_video_lazyload_desc: string
  enable_video_lazyload_title: string
  enable_noscript_desc: string
  enable_noscript_title: string
  enable_gif_replace_title: string
  enable_offload_media_title: string
  enable_offload_media_desc: string
  enable_cloud_images_title: string
  enable_cloud_images_desc: string
  enable_image_replace: string
  enable_lazyload_placeholder_desc: string
  lazyload_behaviour_title: string
  lazyload_behaviour_desc: string
  lazyload_behaviour_all: string
  lazyload_behaviour_all_desc: string
  lazyload_behaviour_viewport: string
  lazyload_behaviour_viewport_desc: string
  lazyload_behaviour_fixed: string
  lazyload_behaviour_fixed_desc: string
  enable_lazyload_placeholder_title: string
  enable_network_opt_desc: string
  enable_network_opt_title: string
  enable_resize_smart_desc: string
  enable_resize_smart_title: string
  enable_retina_desc: string
  enable_retina_title: string
  enable_limit_dimensions_desc: string
  enable_limit_dimensions_title: string
  enable_limit_dimensions_notice: string
  enable_badge_title: string
  enable_badge_settings: string
  enable_badge_show_icon: string
  enable_badge_position: string
  badge_position_text_1: string
  badge_position_text_2: string
  enable_badge_description: string
  image_sizes_title: string
  enabled: string
  exclude_class_desc: string
  exclude_ext_desc: string
  exclude_filename_desc: string
  exclude_desc_optimize: string
  exclude_title_lazyload: string
  exclude_desc_lazyload: string
  exclude_title_optimize: string
  exclude_url_desc: string
  name: string
  cropped: string
  exclude_url_match_desc: string
  exclude_first: string
  images: string
  exclude_first_images_title: string
  exclude_first_images_desc: string
  filter_class: string
  filter_ext: string
  filter_filename: string
  filter_operator_contains: string
  filter_operator_matches: string
  filter_operator_is: string
  filter_url: string
  filter_helper: string
  gif_replacer_desc: string
  height_field: string
  add_image_size_button: string
  add_image_size_desc: string
  here: string
  hide: string
  high_q_title: string
  image_1_label: string
  image_2_label: string
  lazyload_desc: string
  filter_length_error: string
  scale_desc: string
  low_q_title: string
  medium_q_title: string
  no_images_found: string
  native_desc: string
  option_saved: string
  ml_quality_desc: string
  quality_desc: string
  quality_selected_value: string
  quality_slider_desc: string
  quality_title: string
  strip_meta_title: string
  strip_meta_desc: string
  replacer_desc: string
  sample_image_loading: string
  save_changes: string
  show: string
  selected_sites_title: string
  selected_sites_desc: string
  selected_all_sites_desc: string
  select_all_sites_desc: string
  select_site: string
  cloud_site_title: string
  cloud_site_desc: string
  toggle_ab_item: string
  toggle_lazyload: string
  toggle_scale: string
  toggle_native: string
  on_toggle: string
  off_toggle: string
  view_sample_image: string
  watch_placeholder_lazyload: string
  watch_placeholder_lazyload_example: string
  watch_desc_lazyload: string
  smart_loading_title: string
  smart_loading_desc: string
  browser_native_lazy: string
  viewport_detection: string
  placeholders_color: string
  auto_scaling: string
  lightweight_native: string
  watch_title_lazyload: string
  width_field: string
  crop: string
  toggle_cdn: string
  cdn_desc: string
  enable_css_minify_title: string
  css_minify_desc: string
  enable_js_minify_title: string
  js_minify_desc: string
  sync_title: string
  rollback_title: string
  sync_desc: string
  rollback_desc: string
  sync_media: string
  rollback_media: string
  sync_media_progress: string
  estimated_time: string
  calculating_estimated_time: string
  images_processing: string
  active_optimize_exclusions: string
  active_lazyload_exclusions: string
  minutes: string
  stop: string
  show_logs: string
  hide_logs: string
  view_logs: string
  rollback_media_progress: string
  rollback_media_error: string
  rollback_media_error_desc: string
  remove_notice: string
  sync_media_error: string
  sync_media_link: string
  rollback_media_link: string
  sync_media_error_desc: string
  offload_disable_warning_title: string
  offload_disable_warning_desc: string
  offload_enable_info_desc: string
  offload_conflicts_part_1: string
  offload_conflicts_part_2: string
  offloading_success: string
  rollback_success: string
  offloading_radio_legend: string
  offload_radio_option_rollback_title: string
  offload_radio_option_rollback_desc: string
  offload_radio_option_offload_title: string
  offload_radio_option_offload_desc: string
  offload_limit_reached: string
  select: string
  yes: string
  no: string
  lazyload_placeholder_color: string
  clear: string
  settings_saved: string
  settings_saved_error: string
  cache_cleared: string
  cache_cleared_error: string
  offloading_start_title: string
  offloading_start_description: string
  offloading_start_action: string
  offloading_stop_title: string
  offloading_stop_description: string
  offloading_stop_action: string
  rollback_start_title: string
  rollback_start_description: string
  rollback_start_action: string
  rollback_stop_title: string
  rollback_stop_description: string
  rollback_stop_action: string
  cloud_library_btn_text: string
  cloud_library_btn_link: string
  exceed_plan_quota_notice_title: string
  exceed_plan_quota_notice_description: string
  exceed_plan_quota_notice_start_action: string
  exceed_plan_quota_notice_secondary_action: string
  visual_settings: string
  extended_features: string
  global_option: string
  not_recommended: string
  viewport_skip_images_notice: string
}

export interface Help {
  section_one_title: string
  section_two_title: string
  section_two_sub: string
  get_support_title: string
  get_support_desc: string
  get_support_cta: string
  feat_request_title: string
  feat_request_desc: string
  feat_request_cta: string
  feedback_title: string
  feedback_desc: string
  feedback_cta: string
  account_title: string
  account_item_one: string
  account_item_two: string
  account_item_three: string
  image_processing_title: string
  image_processing_item_one: string
  image_processing_item_two: string
  image_processing_item_three: string
  api_title: string
  api_item_one: string
  api_item_two: string
  api_item_three: string
}

export interface Watermarks {
  image: string
  loading_remove_watermark: string
  max_allowed: string
  list_header: string
  settings_header: string
  no_images_found: string
  id: string
  name: string
  type: string
  action: string
  upload: string
  add_desc: string
  wm_title: string
  wm_desc: string
  opacity_field: string
  opacity_title: string
  opacity_desc: string
  position_title: string
  position_desc: string
  pos_nowe_title: string
  pos_no_title: string
  pos_noea_title: string
  pos_we_title: string
  pos_ce_title: string
  pos_ea_title: string
  pos_sowe_title: string
  pos_so_title: string
  pos_soea_title: string
  offset_x_field: string
  offset_y_field: string
  offset_title: string
  offset_desc: string
  scale_field: string
  scale_title: string
  scale_desc: string
  save_changes: string
}

export interface LatestImages {
  image: string
  no_images_found: string
  compression: string
  loading_latest_images: string
  last: string
  saved: string
  smaller: string
  optimized_images: string
  same_size: string
  small_optimization: string
  medium_optimization: string
  big_optimization: string
}

export interface Csat {
  title: string
  close: string
  heading_one: string
  heading_two: string
  heading_three: string
  low: string
  high: string
  feedback_placeholder: string
  skip: string
  submit: string
  thank_you: string
}

export interface OptimizationStatus {
  title: string
  statusTitle1: string
  statusSubTitle1: string
  statusTitle2: string
  statusSubTitle2: string
  statusTitle3: string
  statusSubTitle3: string
}

export interface AvailableApp {
  key: string
  status: string
  domain: string
  is_cname_assigned: string
  cf_ssl_registered: string
  certificate_arn: string
  limit_wl_sites: number
}

export interface Routes {
  update_option: string
  request_update: string
  check_redirects: string
  connect: string
  select_application: string
  register_service: string
  disconnect: string
  poll_optimized_images: string
  get_sample_rate: string
  upload_onboard_images: string
  number_of_images_and_pages: string
  clear_offload_errors: string
  get_offload_conflicts: string
  move_image: string
  poll_conflicts: string
  dismiss_conflict: string
  clear_cache_request: string
  insert_images: string
  dismiss_notice: string
  optimizations: string
}

export interface UserData {
  id: number
  display_name: string
  user_email: string
  picture: string
  validated: string
  cdn_key: string
  cdn_secret: string
  whitelist: string[]
  plan: string
  status: string
  visitors: number
  visitors_limit: number
  visitors_left: number
  visitors_pretty: string
  visitors_limit_pretty: string
  visitors_left_pretty: string
  app_count: number
  available_apps: AvailableApp2[]
  traffic: number
  images_number: number
  offload_limit: number
  offloaded_images: number
  compression_percentage: number
  saved_size: number
  domain: string
  is_cname_assigned: string
  extra_visits: boolean
  renews_on: number
  can_use_offloading: boolean
}

export interface AvailableApp2 {
  key: string
  status: string
  domain: string
  is_cname_assigned: string
  cf_ssl_registered: string
  certificate_arn: string
  limit_wl_sites: number
}

export interface CurrentUser {
  email: string
}

export interface SiteSettings {
  quality: string
  admin_bar_item: string
  lazyload: string
  network_optimization: string
  retina_images: string
  limit_dimensions: string
  limit_height: number
  limit_width: number
  lazyload_placeholder: string
  skip_lazyload_images: number
  bg_replacer: string
  video_lazyload: string
  resize_smart: string
  no_script: string
  lazyload_type: string
  compression_mode: string
  image_replacer: string
  cdn: string
  filters: Filters
  cloud_sites: CloudSites
  defined_image_sizes: any[]
  watchers: string
  watermark: Watermark
  img_to_video: string
  scale: string
  css_minify: string
  js_minify: string
  native_lazyload: string
  avif: string
  autoquality: string
  offload_media: string
  cloud_images: string
  strip_metadata: string
  whitelist_domains: string[]
  banner_frontend: string
  offloading_status: string
  rollback_status: string
  best_format: string
  offload_limit_reached: string
  placeholder_color: string
  show_offload_finish_notice: string
  show_badge_icon: string
  badge_position: string
}

export interface Filters {
  lazyload: Lazyload
  optimize: Optimize
}

export interface Lazyload {
  extension: any[]
  filename: any[]
  page_url: any[]
  page_url_match: any[]
  class: any[]
}

export interface Optimize {
  extension: any[]
  filename: any[]
  page_url: any[]
  page_url_match: any[]
  class: any[]
}

export interface CloudSites {
  all: string
  "localhost:10038": string
}

export interface Watermark {
  id: number
  opacity: number
  position: string
  x_offset: number
  y_offset: number
  scale: number
}

export interface SubmenuLink {
  href: string
  text: string
  hash: string
}

export interface SpcBanner {
  activate_url: string
  status: string
  banner_dismiss_key: string
  i18n: I18n
}

export interface I18n {
  dismiss: string
  title: string
  byline: string
  features: string[]
  cta: string
  activate: string
  installing: string
  activating: string
  activated: string
  error: string
}
