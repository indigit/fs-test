import globals from 'globals';
import pluginJs from '@eslint/js';
import path from 'node:path';
import { FlatCompat } from '@eslint/eslintrc';
import { fileURLToPath } from 'node:url';
import wordpressPlugin from '@wordpress/eslint-plugin';

const __filename = fileURLToPath( import.meta.url );
const __dirname = path.dirname( __filename );
const compat = new FlatCompat( {
	baseDirectory: __dirname,
	recommendedConfig: pluginJs.configs.recommended,
	allConfig: pluginJs.configs.all,
} );

export default [
	{
		files: [ '**/*.js' ],
		ignores: [ '**/webpack.config.js', '**/node_modules/' ],
	},
	pluginJs.configs.recommended,
	...compat.extends( 'plugin:@wordpress/eslint-plugin/esnext' ),
	{
		plugins: {
			'@wordpress': wordpressPlugin,
		},
		languageOptions: {
			globals: {
				...globals.browser,
				...globals.node,
				wp: 'readonly',
				jQuery: 'readonly',
				$: 'readonly',
				ajaxurl: 'readonly',
				lodash: 'readonly',
				_: 'readonly',
			},
			ecmaVersion: 2023,
			sourceType: 'module',
			parserOptions: {
				ecmaFeatures: {
					experimentalObjectRestSpread: true,
				},
			},
		},
		rules: {
			'no-console': 'warn',
			'no-unused-vars': 'error',
			'no-undef': 'error',
			'@wordpress/i18n-text-domain': [
				'error',
				{
					allowedTextDomain: 'fs-likes',
				},
			],
		},
	},
];
