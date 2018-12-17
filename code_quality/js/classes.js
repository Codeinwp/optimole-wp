var classes = [
	{
		"name": "Optml_Lazyload",
		"interface": false,
		"abstract": false,
		"methods": [
			{
				"name": "instance",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "init",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "__clone",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "__wakeup",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		}
		],
		"nbMethodsIncludingGettersSetters": 4,
		"nbMethods": 4,
		"nbMethodsPrivate": 0,
		"nbMethodsPublic": 4,
		"nbMethodsGetter": 0,
		"nbMethodsSetters": 0,
		"wmc": 5,
		"ccn": 2,
		"ccnMethodMax": 2,
		"externals": [
			"self"
		],
		"parents": [],
		"lcom": 4,
		"length": 12,
		"vocabulary": 8,
		"volume": 36,
		"difficulty": 1.67,
		"effort": 60,
		"level": 0.6,
		"bugs": 0.01,
		"time": 3,
		"intelligentContent": 21.6,
		"number_operators": 2,
		"number_operands": 10,
		"number_operators_unique": 2,
		"number_operands_unique": 6,
		"cloc": 41,
		"loc": 64,
		"lloc": 23,
		"mi": 106.42,
		"mIwoC": 59.13,
		"commentWeight": 47.29,
		"kanDefect": 0.22,
		"relativeStructuralComplexity": 0,
		"relativeDataComplexity": 0,
		"relativeSystemComplexity": 0,
		"totalStructuralComplexity": 0,
		"totalDataComplexity": 0,
		"totalSystemComplexity": 0,
		"package": "   \\Optml\\Inc\\",
		"pageRank": 0.04,
		"afferentCoupling": 0,
		"efferentCoupling": 1,
		"instability": 1,
		"violations": {}
	},
	{
		"name": "Optml_Image",
		"interface": false,
		"abstract": false,
		"methods": [
			{
				"name": "__construct",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "set_defaults",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "get_url",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "get_signature",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		}
		],
		"nbMethodsIncludingGettersSetters": 4,
		"nbMethods": 4,
		"nbMethodsPrivate": 1,
		"nbMethodsPublic": 3,
		"nbMethodsGetter": 0,
		"nbMethodsSetters": 0,
		"wmc": 24,
		"ccn": 21,
		"ccnMethodMax": 16,
		"externals": [
			"InvalidArgumentException"
		],
		"parents": [],
		"lcom": 1,
		"length": 179,
		"vocabulary": 46,
		"volume": 988.72,
		"difficulty": 13.58,
		"effort": 13425.74,
		"level": 0.07,
		"bugs": 0.33,
		"time": 746,
		"intelligentContent": 72.81,
		"number_operators": 50,
		"number_operands": 129,
		"number_operators_unique": 8,
		"number_operands_unique": 38,
		"cloc": 96,
		"loc": 178,
		"lloc": 82,
		"mi": 79.84,
		"mIwoC": 34.46,
		"commentWeight": 45.38,
		"kanDefect": 0.85,
		"relativeStructuralComplexity": 4,
		"relativeDataComplexity": 1,
		"relativeSystemComplexity": 5,
		"totalStructuralComplexity": 16,
		"totalDataComplexity": 4,
		"totalSystemComplexity": 20,
		"package": "\\",
		"pageRank": 0.06,
		"afferentCoupling": 1,
		"efferentCoupling": 1,
		"instability": 0.5,
		"violations": {}
	},
	{
		"name": "Optml_Rest",
		"interface": false,
		"abstract": false,
		"methods": [
			{
				"name": "__construct",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "register",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "connect",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "response",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "register_service",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "get_sample_rate",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "fetch_sample_image",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "disconnect",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "poll_optimized_images",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "update_option",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		}
		],
		"nbMethodsIncludingGettersSetters": 10,
		"nbMethods": 10,
		"nbMethodsPrivate": 2,
		"nbMethodsPublic": 8,
		"nbMethodsGetter": 0,
		"nbMethodsSetters": 0,
		"wmc": 20,
		"ccn": 11,
		"ccnMethodMax": 4,
		"externals": [
			"WP_REST_Request",
			"Optml_Api",
			"Optml_Settings",
			"WP_REST_Response",
			"WP_REST_Request",
			"Optml_Api",
			"WP_Error",
			"WP_REST_Request",
			"WP_Query",
			"WP_REST_Request",
			"Optml_Settings",
			"WP_REST_Request",
			"Optml_Api",
			"WP_REST_Request",
			"Optml_Settings"
		],
		"parents": [],
		"lcom": 3,
		"length": 342,
		"vocabulary": 106,
		"volume": 2300.95,
		"difficulty": 9.55,
		"effort": 21963.6,
		"level": 0.1,
		"bugs": 0.77,
		"time": 1220,
		"intelligentContent": 241.05,
		"number_operators": 72,
		"number_operands": 270,
		"number_operators_unique": 7,
		"number_operands_unique": 99,
		"cloc": 73,
		"loc": 198,
		"lloc": 125,
		"mi": 69.64,
		"mIwoC": 29.24,
		"commentWeight": 40.4,
		"kanDefect": 0.64,
		"relativeStructuralComplexity": 81,
		"relativeDataComplexity": 1.67,
		"relativeSystemComplexity": 82.67,
		"totalStructuralComplexity": 810,
		"totalDataComplexity": 16.7,
		"totalSystemComplexity": 826.7,
		"package": "\\",
		"pageRank": 0.06,
		"afferentCoupling": 1,
		"efferentCoupling": 6,
		"instability": 0.86,
		"violations": {}
	},
	{
		"name": "Optml_Main",
		"interface": false,
		"abstract": false,
		"methods": [
			{
				"name": "__construct",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "instance",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "load_plugin_textdomain",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "activate",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "__clone",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "__wakeup",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		}
		],
		"nbMethodsIncludingGettersSetters": 6,
		"nbMethods": 6,
		"nbMethodsPrivate": 0,
		"nbMethodsPublic": 6,
		"nbMethodsGetter": 0,
		"nbMethodsSetters": 0,
		"wmc": 8,
		"ccn": 3,
		"ccnMethodMax": 2,
		"externals": [
			"self",
			"Optml_Replacer",
			"Optml_Rest",
			"Optml_Admin"
		],
		"parents": [],
		"lcom": 6,
		"length": 25,
		"vocabulary": 14,
		"volume": 95.18,
		"difficulty": 3,
		"effort": 285.55,
		"level": 0.33,
		"bugs": 0.03,
		"time": 16,
		"intelligentContent": 31.73,
		"number_operators": 10,
		"number_operands": 15,
		"number_operators_unique": 4,
		"number_operands_unique": 10,
		"cloc": 66,
		"loc": 108,
		"lloc": 42,
		"mi": 97.13,
		"mIwoC": 50.33,
		"commentWeight": 46.8,
		"kanDefect": 0.29,
		"relativeStructuralComplexity": 1,
		"relativeDataComplexity": 1,
		"relativeSystemComplexity": 2,
		"totalStructuralComplexity": 6,
		"totalDataComplexity": 6,
		"totalSystemComplexity": 12,
		"package": "\\",
		"pageRank": 0.04,
		"afferentCoupling": 0,
		"efferentCoupling": 4,
		"instability": 1,
		"violations": {}
	},
	{
		"name": "Optml_Admin",
		"interface": false,
		"abstract": false,
		"methods": [
			{
				"name": "__construct",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "inline_bootstrap_script",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "adds_body_classes",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "add_action_links",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "add_body_class",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "should_show_notice",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "add_notice",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "frontend_scripts",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "maybe_redirect",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "generator",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "daily_sync",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "add_dns_prefetch",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "add_dashboard_page",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "render_dashboard_page",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "enqueue",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "localize_dashboard_app",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "get_dashboard_strings",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "add_traffic_node",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		}
		],
		"nbMethodsIncludingGettersSetters": 18,
		"nbMethods": 18,
		"nbMethodsPrivate": 2,
		"nbMethodsPublic": 16,
		"nbMethodsGetter": 0,
		"nbMethodsSetters": 0,
		"wmc": 68,
		"ccn": 51,
		"ccnMethodMax": 12,
		"externals": [
			"Optml_Settings",
			"Optml_Api",
			"Optml_Settings"
		],
		"parents": [],
		"lcom": 6,
		"length": 656,
		"vocabulary": 292,
		"volume": 5372.52,
		"difficulty": 11.08,
		"effort": 59519.9,
		"level": 0.09,
		"bugs": 1.79,
		"time": 3307,
		"intelligentContent": 484.95,
		"number_operators": 139,
		"number_operands": 517,
		"number_operators_unique": 12,
		"number_operands_unique": 280,
		"cloc": 95,
		"loc": 369,
		"lloc": 272,
		"mi": 49.29,
		"mIwoC": 13.91,
		"commentWeight": 35.38,
		"kanDefect": 2.6,
		"relativeStructuralComplexity": 144,
		"relativeDataComplexity": 2.79,
		"relativeSystemComplexity": 146.79,
		"totalStructuralComplexity": 2592,
		"totalDataComplexity": 50.31,
		"totalSystemComplexity": 2642.31,
		"package": "\\",
		"pageRank": 0.06,
		"afferentCoupling": 1,
		"efferentCoupling": 2,
		"instability": 0.67,
		"violations": {}
	},
	{
		"name": "Optml_Manager",
		"interface": false,
		"abstract": false,
		"methods": [
			{
				"name": "init",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "parse_images_from_html",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "strip_header_from_content",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "instance",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "__clone",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "__wakeup",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		}
		],
		"nbMethodsIncludingGettersSetters": 6,
		"nbMethods": 6,
		"nbMethodsPrivate": 0,
		"nbMethodsPublic": 6,
		"nbMethodsGetter": 0,
		"nbMethodsSetters": 0,
		"wmc": 12,
		"ccn": 7,
		"ccnMethodMax": 5,
		"externals": [
			"self",
			"self"
		],
		"parents": [],
		"lcom": 6,
		"length": 51,
		"vocabulary": 22,
		"volume": 227.43,
		"difficulty": 6.75,
		"effort": 1535.16,
		"level": 0.15,
		"bugs": 0.08,
		"time": 85,
		"intelligentContent": 33.69,
		"number_operators": 15,
		"number_operands": 36,
		"number_operators_unique": 6,
		"number_operands_unique": 16,
		"cloc": 48,
		"loc": 94,
		"lloc": 47,
		"mi": 90.8,
		"mIwoC": 46.08,
		"commentWeight": 44.72,
		"kanDefect": 0.66,
		"relativeStructuralComplexity": 1,
		"relativeDataComplexity": 2.67,
		"relativeSystemComplexity": 3.67,
		"totalStructuralComplexity": 6,
		"totalDataComplexity": 16,
		"totalSystemComplexity": 22,
		"package": "\\",
		"pageRank": 0.04,
		"afferentCoupling": 0,
		"efferentCoupling": 1,
		"instability": 1,
		"violations": {}
	},
	{
		"name": "Optml_Settings",
		"interface": false,
		"abstract": false,
		"methods": [
			{
				"name": "__construct",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "sanitize_enabled_disabled",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "sanitize_size",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "sanitize_quality",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "parse_settings",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "is_connected",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "get",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "is_allowed",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "get_site_settings",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "get_quality",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "is_enabled",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "use_lazyload",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "get_cdn_url",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "reset",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "update",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		}
		],
		"nbMethodsIncludingGettersSetters": 15,
		"nbMethods": 15,
		"nbMethodsPrivate": 4,
		"nbMethodsPublic": 11,
		"nbMethodsGetter": 0,
		"nbMethodsSetters": 0,
		"wmc": 56,
		"ccn": 42,
		"ccnMethodMax": 9,
		"externals": [],
		"parents": [],
		"lcom": 1,
		"length": 283,
		"vocabulary": 49,
		"volume": 1588.96,
		"difficulty": 26.92,
		"effort": 42776.55,
		"level": 0.04,
		"bugs": 0.53,
		"time": 2376,
		"intelligentContent": 59.02,
		"number_operators": 97,
		"number_operands": 186,
		"number_operators_unique": 11,
		"number_operands_unique": 38,
		"cloc": 80,
		"loc": 241,
		"lloc": 161,
		"mi": 62.73,
		"mIwoC": 23.8,
		"commentWeight": 38.93,
		"kanDefect": 2,
		"relativeStructuralComplexity": 49,
		"relativeDataComplexity": 3.44,
		"relativeSystemComplexity": 52.44,
		"totalStructuralComplexity": 735,
		"totalDataComplexity": 51.63,
		"totalSystemComplexity": 786.63,
		"package": "\\",
		"pageRank": 0.46,
		"afferentCoupling": 4,
		"efferentCoupling": 0,
		"instability": 0,
		"violations": {}
	},
	{
		"name": "Optml_Api",
		"interface": false,
		"abstract": false,
		"methods": [
			{
				"name": "__construct",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "get_user_data",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "build_args",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "request",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "create_account",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "get_optimized_images",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "__clone",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "__wakeup",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		}
		],
		"nbMethodsIncludingGettersSetters": 8,
		"nbMethods": 8,
		"nbMethodsPrivate": 2,
		"nbMethodsPublic": 6,
		"nbMethodsGetter": 0,
		"nbMethodsSetters": 0,
		"wmc": 19,
		"ccn": 12,
		"ccnMethodMax": 9,
		"externals": [
			"Optml_Settings"
		],
		"parents": [],
		"lcom": 3,
		"length": 148,
		"vocabulary": 46,
		"volume": 817.49,
		"difficulty": 9.6,
		"effort": 7849.97,
		"level": 0.1,
		"bugs": 0.27,
		"time": 436,
		"intelligentContent": 85.13,
		"number_operators": 41,
		"number_operands": 107,
		"number_operators_unique": 7,
		"number_operands_unique": 39,
		"cloc": 79,
		"loc": 155,
		"lloc": 76,
		"mi": 81.66,
		"mIwoC": 36.96,
		"commentWeight": 44.7,
		"kanDefect": 1.01,
		"relativeStructuralComplexity": 9,
		"relativeDataComplexity": 2.56,
		"relativeSystemComplexity": 11.56,
		"totalStructuralComplexity": 72,
		"totalDataComplexity": 20.5,
		"totalSystemComplexity": 92.5,
		"package": "   \\Optimole\\Inc\\",
		"pageRank": 0.12,
		"afferentCoupling": 2,
		"efferentCoupling": 1,
		"instability": 0.33,
		"violations": {}
	},
	{
		"name": "Optml_Config",
		"interface": false,
		"abstract": false,
		"methods": [
			{
				"name": "init",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		}
		],
		"nbMethodsIncludingGettersSetters": 1,
		"nbMethods": 1,
		"nbMethodsPrivate": 0,
		"nbMethodsPublic": 1,
		"nbMethodsGetter": 0,
		"nbMethodsSetters": 0,
		"wmc": 11,
		"ccn": 11,
		"ccnMethodMax": 11,
		"externals": [
			"InvalidArgumentException",
			"InvalidArgumentException"
		],
		"parents": [],
		"lcom": 1,
		"length": 44,
		"vocabulary": 14,
		"volume": 167.52,
		"difficulty": 3.82,
		"effort": 639.64,
		"level": 0.26,
		"bugs": 0.06,
		"time": 36,
		"intelligentContent": 43.88,
		"number_operators": 16,
		"number_operands": 28,
		"number_operators_unique": 3,
		"number_operands_unique": 11,
		"cloc": 22,
		"loc": 52,
		"lloc": 30,
		"mi": 93,
		"mIwoC": 50.73,
		"commentWeight": 42.28,
		"kanDefect": 0.5,
		"relativeStructuralComplexity": 0,
		"relativeDataComplexity": 1,
		"relativeSystemComplexity": 1,
		"totalStructuralComplexity": 0,
		"totalDataComplexity": 1,
		"totalSystemComplexity": 1,
		"package": "\\",
		"pageRank": 0.06,
		"afferentCoupling": 1,
		"efferentCoupling": 1,
		"instability": 0.5,
		"violations": {}
	},
	{
		"name": "Optml_Replacer",
		"interface": false,
		"abstract": false,
		"methods": [
			{
				"name": "instance",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "init",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "should_replace",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "set_properties",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "extract_domain_from_urls",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "init_html_replacer",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "filter_image_downsize",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "image_sizes",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "wp_crop_to_optml",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "validate_image_sizes",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "strip_image_size_maybe",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "get_image_url",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "check_mimetype",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "normalize_quality",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "filter_sizes",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "filter_srcset_attr",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "should_ignore_image_tags",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "parse_dimensions_from_filename",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "filter_options_and_mods",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "replace_option_url",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "replace_meta",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "replace_content",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "filter_image_tags",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "parse_images_from_html",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "strip_header_from_content",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "can_replace_url",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "extract_non_replaced_urls",
				"role": null,
				"public": false,
				"private": true,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "can_lazyload_for",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "__clone",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		},
			{
				"name": "__wakeup",
				"role": null,
				"public": true,
				"private": false,
				"_type": "Hal\\Metric\\FunctionMetric"
		}
		],
		"nbMethodsIncludingGettersSetters": 30,
		"nbMethods": 30,
		"nbMethodsPrivate": 9,
		"nbMethodsPublic": 21,
		"nbMethodsGetter": 0,
		"nbMethodsSetters": 0,
		"wmc": 167,
		"ccn": 138,
		"ccnMethodMax": 20,
		"externals": [
			"self",
			"Optml_Settings",
			"Optml_Config",
			"self",
			"Optml_Image",
			"self",
			"self",
			"self",
			"self",
			"self"
		],
		"parents": [],
		"lcom": 12,
		"length": 1271,
		"vocabulary": 233,
		"volume": 9995.38,
		"difficulty": 34.59,
		"effort": 345742.99,
		"level": 0.03,
		"bugs": 3.33,
		"time": 19208,
		"intelligentContent": 288.97,
		"number_operators": 392,
		"number_operands": 879,
		"number_operators_unique": 17,
		"number_operands_unique": 216,
		"cloc": 348,
		"loc": 903,
		"lloc": 558,
		"mi": 41.01,
		"mIwoC": 0,
		"commentWeight": 41.01,
		"kanDefect": 7.55,
		"relativeStructuralComplexity": 529,
		"relativeDataComplexity": 3.25,
		"relativeSystemComplexity": 532.25,
		"totalStructuralComplexity": 15870,
		"totalDataComplexity": 97.63,
		"totalSystemComplexity": 15967.63,
		"package": "   \\Optml\\Inc\\",
		"pageRank": 0.06,
		"afferentCoupling": 1,
		"efferentCoupling": 4,
		"instability": 0.8,
		"violations": {}
	}
	]
