module.exports = function(grunt) {
  // Konfiguracija
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    uglify: {
      options: {
        banner: '/* <%= pkg.name %> by <%= pkg.author %> - Build date: <%= grunt.template.today("dd.mm.yyyy.") %> */\n'
      },
      build: {
        src: '<%= pkg.name %>.js',
        dest: 'min/<%= pkg.name %>.min.js'
      }
    }
  });
  // Ucitaj plugin za "uglify" task
  grunt.loadNpmTasks('grunt-contrib-uglify');
  // Podrazumevani taskovi
  grunt.registerTask('default', ['uglify']);
};