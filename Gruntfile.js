module.exports = function(grunt) {

  require('load-grunt-tasks')(grunt);
	
  // Project configuration.
  grunt.initConfig({
	pkg: grunt.file.readJSON('package.json'),

	// compile 
	sass: {                              // Task
		dist: {                            // Target
			options: {                       // Target options
				style: 'compact',
				sourcemap: 'none'
			},
			files: {                         // Dictionary of files
				'assets/css/mnm-edit-order.css': 'assets/css/mnm-edit-order.scss',       // 'destination': 'source'
				'assets/css/mnm-frontend.css': 'assets/css/mnm-frontend.scss'
			}
		}
	},

	uglify: {
		options: {
			compress: {
				global_defs: {
					"EO_SCRIPT_DEBUG": false
				},
				dead_code: true
				},
			banner: '/*! <%= pkg.title %> <%= pkg.version %> <%= grunt.template.today("yyyy-mm-dd HH:MM") %> */\n'
		},
		build: {
			files: [{
				expand: true,	// Enable dynamic expansion.
				src: ['assets/js/*.js', 'includes/admin/js/*.js', '!assets/js/*.min.js', '!includes/admin/js/*.min.js'], // Actual pattern(s) to match.
				ext: '.min.js',   // Dest filepaths will have this extension.
			}]
		}
	},
	jshint: {
		options: {
			reporter: require('jshint-stylish'),
			globals: {
				"EO_SCRIPT_DEBUG": false,
			},
			 '-W099': true, //Mixed spaces and tabs
			 '-W083': true,//TODO Fix functions within loop
			 '-W082': true, //Todo Function declarations should not be placed in blocks
			 '-W020': true, //Read only - error when assigning EO_SCRIPT_DEBUG a value.
		},
		all: [ 'js/*.js', '!js/*.min.js' ]
  	},

	watch: {
		scripts: {
			files: 'assets/js/*.js',
			tasks: ['jshint', 'uglify'],
			options: {
				debounceDelay: 250,
			},
		},
		css: {
			files: 'assets/css/*.scss',
			tasks: ['sass'],
		},
	},

	// # Internationalization 

	// Add text domain
	addtextdomain: {
		options: {
            textdomain: '<%= pkg.name %>',    // Project text domain.
            updateDomains: [ 'woocommerce-mix-and-match', 'woocommerce-mix-and-match-products' ]  // List of text domains to replace.
        },
		target: {
			files: {
				src: ['*.php', '**/*.php', '**/**/*.php', '!node_modules/**', '!deploy/**']
			}
		}
	},

	// Generate .pot file
	makepot: {
		target: {
			options: {
				domainPath: '/languages', // Where to save the POT file.
				exclude: ['deploy'], // List of files or directories to ignore.
				mainFile: 'woocommerce-mix-and-match-products.php', // Main project file.
				potFilename: 'woocommerce-mix-and-match-products.pot', // Name of the POT file.
				type: 'wp-plugin' // Type of project (wp-plugin or wp-theme).
			}
		}
	},


	// bump version numbers (replace with version in package.json)
	replace: {
		Version: {
			src: [
				'readme.txt',
				'<%= pkg.name %>.php'
			],
			overwrite: true,
			replacements: [
				{
					from: /Stable tag:.*$/m,
					to: "Stable tag: <%= pkg.version %>"
				},
				{ 
					from: /Version:.*$/m,
					to: "Version: <%= pkg.version %>"
				},
				{ 
					from: /public \$version = \'.*.'/m,
					to: "public $version = '<%= pkg.version %>'"
				}
			]
		}
	}

});

grunt.registerTask( 'test', [ 'jshint', 'newer:uglify' ] );
grunt.registerTask( 'build', [ 'replace', 'newer:sass', 'newer:uglify', 'addtextdomain', 'makepot' ] );

};
