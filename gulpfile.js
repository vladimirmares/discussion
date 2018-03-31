// Include gulp
var gulp = require('gulp');
// Include plugins
var concat = require('gulp-concat');
// Concatenate JS Files
gulp.task('scripts', function() {
  return gulp.src([
    'bower_components/jquery/dist/jquery.js',
    'bower_components/nette.ajax.js/nette.ajax.js',
    'bower_components/nette-forms/src/assets/netteForms.min.js'
    ])
    .pipe(concat('app.js'))
    .pipe(gulp.dest('www/js'));
});
// Default Task
gulp.task('default', ['scripts']);