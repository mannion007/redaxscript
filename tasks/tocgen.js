module.exports = function ()
{
	'use strict';

	var config =
	{
		base:
		{
			src:
			[
				'assets/**/*.{css,js}'
			]
		},
		templates:
		{
			src:
			[
				'templates/**/assets/**/*.{css,js}'
			]
		},
		modules:
		{
			src:
			[
				'modules/**/assets/**/*.{css,js}'
			]
		}
	};

	return config;
};