const mix = require('laravel-mix');
const webpack = require('webpack');
const path = require('path');

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

mix.options({
    terser: {
        terserOptions: {
            compress: {
                drop_console: true,
            },
        },
    },
})
    .setPublicPath('public')
    .ts('resources/ts/app.ts', 'public')
    .vue()
    //.sass('resources/sass/app.scss', 'public')
    //.sass('resources/sass/app-dark.scss', 'public')
    .version()
    .copy('resources/images', 'public/images')
    .webpackConfig({
        resolve: {
            symlinks: false,
            alias: {
                '@': path.resolve('resources/ts'),
            },
            // modules: [
            //     path.resolve(__dirname, 'resources/js')
            // ]
        },
        plugins: [
            new webpack.IgnorePlugin({
                resourceRegExp: /^\.\/locale$/,
                contextRegExp: /moment$/,
            }),
        ],
    });
