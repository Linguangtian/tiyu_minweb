require.config({
	baseUrl: 'resource/js/app',
	paths: {
		'jquery': '//cdn.w7.cc/web/resource/js/lib/jquery-1.11.1.min',
		'jquery.ui': '//cdn.w7.cc/web/resource/js/lib/jquery-ui-1.10.3.min',
		'jquery.lazyloading': '//cdn.w7.cc/web/resource/js/lib/jquery.lazyloading',
		'jquery.caret': '//cdn.w7.cc/web/resource/js/lib/jquery.caret',
		'jquery.jplayer': '//cdn.w7.cc/web/resource/components/jplayer/jquery.jplayer.min',
		'jquery.zclip': '//cdn.w7.cc/web/resource/components/zclip/jquery.zclip.min',
		'bootstrap': '//cdn.w7.cc/web/resource/js/lib/bootstrap.min',
		'bootstrap.switch': '//cdn.w7.cc/web/resource/components/switch/bootstrap-switch.min',
		'angular': '//cdn.w7.cc/web/resource/js/lib/angular.min',
		'angular.sanitize': '//cdn.w7.cc/web/resource/js/lib/angular-sanitize.min',
		'angular.messages': '//cdn.w7.cc/web/resource/js/lib/angular-messages',
		'underscore': '//cdn.w7.cc/web/resource/js/lib/underscore-min',
		'chart': '//cdn.w7.cc/web/resource/js/lib/chart.min',
		'moment': '//cdn.w7.cc/web/resource/js/lib/moment',
		'filestyle': '//cdn.w7.cc/web/resource/js/lib/bootstrap-filestyle.min',
		'datetimepicker': '//cdn.w7.cc/web/resource/components/datetimepicker/bootstrap-datetimepicker.min',
		'new_datetimepicker': '//cdn.w7.cc/web/resource/components/new-datetimepicker/jquery-ui-timepicker-addon',
		'daterangepicker': '//cdn.w7.cc/web/resource/components/daterangepicker/daterangepicker',
		'colorpicker': '//cdn.w7.cc/web/resource/components/colorpicker/spectrum',
		'map': '//api.map.baidu.com/getscript?v=2.0&ak=F51571495f717ff1194de02366bb8da9&services=&t=20140530104353',
		'css': '//cdn.w7.cc/web/resource/js/lib/css.min',
		'webuploader' : '//cdn.w7.cc/web/resource/components/webuploader/webuploader.min',
		'star-rating' : '//cdn.w7.cc/web/resource/components/star-rating/star-rating',
		'switchery' : '//cdn.w7.cc/web/resource/components/switchery/switchery.min',
		'iosOverlay' : '//cdn.w7.cc/web/resource/components/iosOverlay/iosOverlay',
		'ion.rangeSlider': '//cdn.w7.cc/web/resource/components/rangeSlider/ion.rangeSlider',
		'raty' : '//cdn.w7.cc/web/resource/js/lib/raty',
		'layer': '//cdn.w7.cc/web/resource/components/layer/layer',
		'dependencies': '//cdn.w7.cc/web/resource/js/lib/vendor/dependencies',
		'pizza' : '//cdn.w7.cc/web/resource/js/lib/pizza',
		'vue' : '//cdn.w7.cc/web/resource/js/lib/vue.min',
		'vue-resource' : '//cdn.w7.cc/web/resource/js/lib/vue-resource.min',
		'echarts' : '//cdn.w7.cc/web/resource/js/lib/echarts.min',
		'clipboard' : '//cdn.w7.cc/web/resource/js/lib/clipboard.min',

		'marked' : '../lib/editormd/lib/marked.min',
		'prettify' : '../lib/editormd/lib/prettify.min',
		'raphael' : '../lib/editormd/lib/raphael.min',
		'underscore_' : '../lib/editormd/lib/underscore.min',
		'flowchart' : '../lib/editormd/lib/flowchart.min', 
		'jqueryflowchart' : '../lib/editormd/lib/jquery.flowchart.min', 
		'sequenceDiagram' : '../lib/editormd/lib/sequence-diagram.min',
		'katex' : '//cdn.bootcss.com/KaTeX/0.3.0/katex.min',
		'editormd' : '../lib/editormd.amd',
	},
	shim:{
		'jquery.ui': {
			exports: "$",
			deps: ['jquery']
		},
		'jquery.lazyloading': {
			exports: "$",
			deps: ['jquery']
		},
		'layer': {
			exports: "$",
			deps: ['css!//cdn.w7.cc/web/resource/components/layer/skin/layer.css']
		},
		'dependencies': {
			exports: "$",
			deps: ['jquery']
		},
		'pizza': {
			exports: "$",
			deps: ['jquery']
		},
		'jquery.caret': {
			exports: "$",
			deps: ['jquery']
		},
		'jquery.jplayer': {
			exports: "$",
			deps: ['jquery']
		},
		'bootstrap': {
			exports: "$",
			deps: ['jquery']
		},
		'bootstrap.switch': {
			exports: "$",
			deps: ['bootstrap', 'css!//cdn.w7.cc/web/resource/components/switch/bootstrap-switch.min.css']
		},
		'angular': {
			exports: 'angular',
			deps: ['jquery']
		},
		'angular.sanitize': {
			exports: 'angular',
			deps: ['angular']
		},
		'angular.messages': {
			exports: 'angular',
			deps: ['angular']
		},
		'emotion': {
			deps: ['jquery']
		},
		'chart': {
			exports: 'Chart'
		},
		'filestyle': {
			exports: '$',
			deps: ['bootstrap']
		},
		'datetimepicker': {
			exports: '$',
			deps: ['bootstrap', 'css!//cdn.w7.cc/web/resource/components/datetimepicker/bootstrap-datetimepicker.min.css']
		},
		'new_datetimepicker': {
			exports: '$',
			deps: [
				'jquery',
				'css!//cdn.w7.cc/web/resource/components/new-datetimepicker/jquery-ui.css',
				'css!//cdn.w7.cc/web/resource/components/new-datetimepicker/datetimepicker.css',
				'//cdn.w7.cc/web/resource/components/new-datetimepicker/jquery-ui-1.10.4.custom.min.js'
			]
		},
		'daterangepicker': {
			exports: '$',
			deps: ['bootstrap', 'moment', 'css!//cdn.w7.cc/web/resource/components/daterangepicker/daterangepicker.css']
		},
		'colorpicker': {
			exports: '$',
			deps: ['css!//cdn.w7.cc/web/resource/components/colorpicker/spectrum.css']
		},
		'map': {
			exports: 'BMap'
		},
		'webuploader': {
			deps: ['css!//cdn.w7.cc/web/resource/components/webuploader/webuploader.css', 'css!//cdn.w7.cc/web/resource/components/webuploader/style.css']
		},
		'star-rating': {
			deps: ['css!//cdn.w7.cc/web/resource/components/star-rating/star-rating.min.css']
		},
		'switchery': {
			deps: ['css!//cdn.w7.cc/web/resource/components/switchery/switchery.min.css']
		},
		'iosOverlay': {
			deps: ['css!//cdn.w7.cc/web/resource/components/iosOverlay/iosOverlay.css']
		},
		'ion.rangeSlider': {
			exports: "$",
			deps: ['css!//cdn.w7.cc/web/resource/components/rangeSlider/ion.rangeSlider.css', 'css!//cdn.w7.cc/web/resource/components/rangeSlider/ion.rangeSlider.skinFlat.css']
		},

		'editormd': {
			deps: ['css!../../css/editormd.min.css']
		},
	}
});