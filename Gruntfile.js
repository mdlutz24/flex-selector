var timer = require("grunt-timer");

module.exports = function(grunt) {
  'use strict';

  timer.init(grunt, {
    friendlyTime: true
  });

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    env: grunt.option('env') || process.env.GRUNT_ENV || 'development'
  });

  grunt.config('sourcemap', grunt.config('env') === 'development'?'css/style.map':false);
  grunt.config('outputStyle', grunt.config('env') === 'development'?'expanded':'compressed');

  grunt.config('sass', {
    dist: {
      options: {
        sourceMap: grunt.config('sourcemap'),
        outputStyle: grunt.config('outputStyle'),
        sourceComments: grunt.config('env') === 'development'
      },
      files: [{
        'css/style.css': 'scss/style.scss',
      }]
    }
  });


  grunt.config('postcss',{
    options: {
      diff: false,
      map: false,
      processors: [
        require('autoprefixer')({
          browsers: ['last 2 versions', '> 5% in US']
        })
      ]
    },
    dist: {
      src: 'css/*.css'
    }
  });

  grunt.config('watch',{
    src: {
      files: ['scss/*.scss', 'scss/**/*.scss', 'js/*.js'],
      tasks: ['default']
    }
  });

  grunt.loadNpmTasks('grunt-postcss');
  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.registerTask('compile', ['sass', 'postcss:dist']);

  grunt.registerTask('default', ['sass', 'postcss:dist', 'watch']);
};
