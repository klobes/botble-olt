const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// Set public path
//mix.setPublicPath('public');
const path = require('path');
let directory = path.basename(path.resolve(__dirname));

const source = 'platform/plugins/' + directory;
const dist = 'public/vendor/core/plugins/' + directory;


// JavaScript files
mix.js(source + 'resources/assets/js/dashboard.js', dist + '/js')
   .js(source +'resources/assets/js/datatables-init.js',dist +'/js')
   .js(source +'resources/assets/js/olt-management.js',dist + '/js')
   .js(source +'resources/assets/js/onu-management.js',dist + '/js')
   .js(source +'resources/assets/js/bandwidth-profiles.js',dist + '/js')
   .js(source +'resources/assets/js/settings.js',dist + '/js')
   .js(source +'resources/assets/js/topology.js', dist +'/js');

// CSS files
mix.sass(source +'resources/assets/css/plugin.scss',dist + '/css')
   .sass(source +'resources/assets/css/dashboard.scss',dist + '/css')
   .sass(source +'resources/assets/css/olt-management.scss',dist + '/css')
   .sass(source +'resources/assets/css/onu-management.scss',dist + '/css')
   .css(source +'resources/assets/css/topology.css', dist +'/css');

// Options
mix.options({
    processCssUrls: false,
    postCss: [
        require('autoprefixer')
    ]
});

// Versioning for cache busting in production
if (mix.inProduction()) {
    mix.version();
} else {
    mix.sourceMaps();
}

// BrowserSync for live reload during development
mix.browserSync({
    proxy: 'localhost',
    files: [
        'resources/views/**/*.blade.php',
        'resources/assets/js/**/*.js',
        'resources/assets/css/**/*.css'
    ]
});