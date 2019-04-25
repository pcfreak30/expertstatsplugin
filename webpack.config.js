const webpack = require('webpack');
const join = require('path').join;

let mode = 'development';

let development = mode === 'development';

module.exports = {
	mode: mode,
	devtool: 'inline-source-map',
	entry: __dirname + '/assets/js/src/settings/index.jsx',
	module: {
		rules: [
			{
				test: /\.(js|jsx)$/,
				exclude: /node_modules/,
				use: [ 'babel-loader' ]
			},{
				test: /\.s?css$/,
				use: [ 'sass-loader' ],
			},
		]
	},
	resolve: {
		extensions: [ '*', '.js', '.jsx' ]
	},
	output: {
		path: development ? __dirname + '/web/wp-content/plugins/expertstatsplugin/assets/js/dist' : __dirname + '/assets/js/dist',
		publicPath: "http://localhost:8080/wp-content/plugins/expertstatsplugin/assets/js/dist/",
		filename: 'settings.js'
	},
	devServer: {
		contentBase: join(__dirname, 'web'),
		publicPath: "http://localhost:8080/wp-content/plugins/expertstatsplugin/assets/js/dist/",
		host: '0.0.0.0',
		port: 8080,
		hot: true,
		stats: {
			colors: true
		},
		proxy: [
			{
				path: '/',
				target: "http://localhost"
			}
		]
	},
	externals: Object.assign(development ? {} : {
		'react': 'React',
		'react-dom': 'ReactDOM',
	}, {
		'@wordpress/element': 'wp.element',
		'@wordpress/dom-ready': 'wp.domReady',
		'@wordpress/api-fetch': 'wp.apiFetch',
	}),
	plugins: [
		new (require('flow-babel-webpack-plugin'))()
	]
};
