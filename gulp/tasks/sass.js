'use strict';

var gulp = require( 'gulp' );

gulp.task( 'sass', function () {

	var autoprefixer  = require( 'autoprefixer' ),
		postcss       = require( 'gulp-postcss' ),
		sass          = require( 'gulp-sass' ),
		notify        = require( 'gulp-notify' ),
		atImport      = require( 'postcss-import' ),
		mqpacker      = require( 'css-mqpacker' ),
		config        = require( '../config' ),
		perfectionist = require( 'perfectionist' ),
		browserSync   = require( 'browser-sync' ),
		processors    = [
			atImport,
			autoprefixer( {
				cascade: false,
				remove: false
			} ),
			mqpacker( {sort: true} ),
			perfectionist( {
				cascade: true,
				format: config.output.style,
				indentSize: 4,
				maxAtRuleLength: 80,
				maxSelectorLength: 80,
				maxValueLength: 80,
				sourcemap: true
			} )
		];
	gulp.src( config.paths.sassPath )
		.pipe( sass( {outputStyle: config.output.style} ).on( 'error', sass.logError ) )
		.pipe( postcss( processors ) )
		.pipe( gulp.dest( config.output.styleDestination ) )
		.pipe( browserSync.reload( {
			stream: true
		} ) )
		.pipe( notify( {message: 'you sassed the shit out of that.', onLast: true} ) );
} );
