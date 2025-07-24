import path from 'path';
import { fileURLToPath } from 'url';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import StylelintPlugin from 'stylelint-webpack-plugin';
import CreateHashFileWebpack from 'create-hash-file-webpack';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export default (env, argv) => {
	const isProduction = argv.mode === 'production';
	const isDevelopment = !isProduction;

	return {
		mode: argv.mode || 'development',
		devtool: isProduction ? 'source-map' : 'eval-source-map',
		cache: {
			type: 'filesystem',
			buildDependencies: {
				config: [__filename],
			},
		},
		entry: {
			main: './js/index.js',
		},
		output: {
			path: path.resolve(__dirname, '../assets'),
			filename: '[name].js',
			clean: isProduction,
		},
		module: {
			rules: [
				{
					test: /\.js$/,
					exclude: /node_modules/,
					use: {
						loader: 'babel-loader',
						options: {
							presets: ['@babel/preset-env'],
						},
					},
				},
				{
					test: /\.css$/,
					use: [
						MiniCssExtractPlugin.loader,
						{
							loader: 'css-loader',
							options: {
								sourceMap: isDevelopment,
							},
						},
					],
				},
				{
					test: /\.scss$/,
					use: [
						MiniCssExtractPlugin.loader,
						{
							loader: 'css-loader',
							options: {
								sourceMap: isDevelopment,
							},
						},
						{
							loader: 'sass-loader',
							options: {
								sourceMap: isDevelopment,
							},
						},
					],
				},
			],
		},
		plugins: [
			new MiniCssExtractPlugin({
				filename: '[name].css',
			}),
			new StylelintPlugin({
				files: 'styles/**/*.{css,scss}',
				failOnError: isProduction,
				failOnWarning: isProduction,
			}),
			new CreateHashFileWebpack([
				{
					path: './../assets',
					fileName: 'assets_hash.php',
					content: '<?php static $assets_hash = "[hash]";',
				},
			]),
		],
		optimization: {
			minimize: isProduction,
		},
		devServer: {
			static: {
				directory: path.resolve(__dirname, '../assets'),
			},
			compress: true,
			port: 9000,
			hot: true,
			watchFiles: ['styles/**/*', 'js/**/*'],
		},
	};
};
