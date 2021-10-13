const { mix } = require('laravel-mix');

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

mix.react('resources/assets/js/webview.jsx', 'public/js');
mix.react('resources/assets/js/bots.jsx', 'public/js');
mix.js('resources/assets/js/app.js', 'public/js');

// mix.copy('node_modules/bootstrap-slider/dist/bootstrap-slider.min.js', 'public/js/bootstrap-slider.min.js');
// mix.copy('node_modules/bootstrap-slider/dist/css/bootstrap-slider.min.css', 'public/css/bootstrap-slider.min.css');

// mix.copy('node_modules/cropperjs/dist/cropper.min.js', 'public/js/cropper/cropper.min.js');
// mix.copy('node_modules/cropperjs/dist/cropper.min.css', 'public/css/cropper/cropper.min.css');
//
// mix.copy('node_modules/react-tagsinput/react-tagsinput.css', 'public/css/react-tagsinput/react-tagsinput.css');
//mix.copy('node_modules/socket.io-client/socket.io.min.js','public/js');

mix.sass('resources/assets/sass/app.scss', 'public/css');
mix.sass('resources/assets/sass/webview.scss', 'public/css');
mix.sass('resources/assets/sass/sa_app.scss', 'public/css');

//
// mix.styles([
//     'node_modules/react-cropper/node_modules/cropperjs/dist/cropper.css',
// ], 'public/css/node_modules.css');

// mix.copy('node_modules/bootstrap-slider/dist/bootstrap-slider.min.js', 'public/js/bootstrap-slider.min.js');
// mix.copy('node_modules/bootstrap-slider/dist/css/bootstrap-slider.min.css', 'public/css/bootstrap-slider.min.css');

// mix.sass('resources/assets/sass/ulka_modal.scss').styles(['public/css/ulka_modal.css'])
