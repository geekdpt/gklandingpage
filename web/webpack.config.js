const webpack = require('webpack');
const webpackMerge = require('webpack-merge');
const HtmlWebpackPlugin = require('html-webpack-plugin');

const CONFIG = {
    entry: ['babel-polyfill', 'whatwg-fetch', './app.js'],

    output: {
        path: './dist',
        filename: 'bundle.js'
    },

    plugins: [
        new HtmlWebpackPlugin({ template: './index.html' })
    ],

    module: {
        loaders: [
            { test: /\.html/, loader: 'html?minimize=false' },
            { test: /\.js/, loader: 'babel?presets[]=es2015', exclude: /node_modules/ },
            { test: /\.css/, loaders: ['style', 'css', 'resolve-url'] },
            { test: /\.scss/, loaders: ['style', 'css', 'resolve-url', 'sass'] },
            { test: /\.(jpe?g|png|gif|svg|ico)$/i, loaders: [
                'file?hash=sha512&digest=hex&name=[hash].[ext]',
                'image-webpack?bypassOnDebug&optimizationLevel=7&interlaced=false'
            ]}
        ]
    }
};

const PROD_CONFIG = {
    plugins: [
        new webpack.optimize.DedupePlugin(),
        new webpack.optimize.UglifyJsPlugin()
    ]
};

module.exports = webpackMerge(CONFIG, (process.env.WEBPACK_PROD == 'true' ? PROD_CONFIG : {}));