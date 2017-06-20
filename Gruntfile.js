module.exports = function( grunt ) {

	var bannerTemplate = '/**\n' +
		' * <%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>\n' +
		' * <%= pkg.homepage %>\n' +
		' *\n' +
		' * Copyright (c) <%= grunt.template.today("yyyy") %>;\n' +
		' * Licensed GPLv2+\n' +
		' */\n';

	var compactBannerTemplate = '/**\n' +
		' * <%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %> | <%= pkg.homepage %> | Copyright (c) <%= grunt.template.today("yyyy") %>; | Licensed GPLv2+\n' +
		' */\n';

	// Project configuration
	grunt.initConfig( {

		pkg:    grunt.file.readJSON( 'package.json' ),

		concat: {
			options: {
				stripBanners: true,
				banner: bannerTemplate
			},
			rss_post_aggregator: {
				src: [
					'assets/js/src/rss_post_aggregator.js'
				],
				dest: 'assets/js/rss_post_aggregator.js'
			}
		},

		jshint: {
			all: [
				'Gruntfile.js',
				'assets/js/src/**/*.js',
				'assets/js/test/**/*.js'
			],
			options: {
				curly   : true,
				eqeqeq  : true,
				immed   : true,
				latedef : true,
				newcap  : true,
				noarg   : true,
				sub     : true,
				unused  : true,
				undef   : true,
				boss    : true,
				eqnull  : true,
				globals : {
					exports : true,
					module  : false
				},
				predef  :['document','window','ajaxurl','wp','_']
			}
		},

		uglify: {
			all: {
				files: {
					'assets/js/rss_post_aggregator.min.js': ['assets/js/rss_post_aggregator.js']
				},
				options: {
					banner: compactBannerTemplate,
					mangle: {
						except: ['jQuery']
					}
				}
			}
		},

		test:   {
			files: ['assets/js/test/**/*.js']
		},


		sass:   {
			all: {
				files: {
					'assets/css/rss_post_aggregator.css': 'assets/css/sass/rss_post_aggregator.scss'
				}
			}
		},


		cssmin: {
			options: {
				banner: bannerTemplate
			},
			minify: {
				expand: true,

				cwd: 'assets/css/',
				src: ['rss_post_aggregator.css'],

				dest: 'assets/css/',
				ext: '.min.css'
			}
		},

		watch:  {

			sass: {
				files: ['assets/css/sass/*.scss'],
				tasks: ['sass', 'cssmin'],
				options: {
					debounceDelay: 500
				}
			},

			scripts: {
				files: ['assets/js/src/**/*.js', 'assets/js/vendor/**/*.js'],
				tasks: ['jshint', 'concat', 'uglify'],
				options: {
					debounceDelay: 500
				}
			}
		}

	} );

	// Load other tasks
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');

	grunt.loadNpmTasks('grunt-contrib-sass');

	grunt.loadNpmTasks('grunt-contrib-watch');

	// Default task.

	grunt.registerTask( 'default', ['jshint', 'concat', 'uglify', 'sass', 'cssmin'] );


	grunt.util.linefeed = '\n';
};
