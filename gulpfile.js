const { dest, parallel, series, src, watch } = require('gulp')
const sass = require('gulp-sass')
const gutil = require('gulp-util')
const nodeSaas = require('node-sass');
const cleanCSS = require('gulp-clean-css');
const webpack = require('webpack');
const WebpackDevServer = require('webpack-dev-server');
const webpackConfig = require('./webpack.config');

sass.compiler = nodeSaas;

function css () {
	return src('assets/css/src/*.scss')
		.pipe(sass().on('error', sass.logError))
		.pipe(cleanCSS())
		.pipe(dest('assets/css/dist'))
		.pipe(dest('web/wp-content/plugins/expertstatsplugin/assets/css/dist'))
}

function js () {
	const compiler = webpack(webpackConfig);
	new WebpackDevServer(compiler, webpackConfig.devServer).listen(8080, "localhost", function (err) {
		if (err) throw new gutil.PluginError("webpack-dev-server", err);
		// Server listening
		gutil.log("[webpack-dev-server]", "http://localhost:8080/webpack-dev-server/index.html");
		// keep the server alive or continue?
		// callback();
	});
}

function watchTask () {
	return watch('assets/css/src/*.scss', series(css));
}

module.exports.css = css;
module.exports.js = js;
module.exports.watchTask = watchTask;
module.exports.default = parallel(series(css, watchTask), js);
