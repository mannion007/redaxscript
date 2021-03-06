module.exports = function ()
{
	'use strict';

	var config =
	{
		languages:
		{
			src:
			[
				'languages/*.json',
				'!languages/en.json'
			],
			dest: 'build/parser_language.json'
		},
		options:
		{
			type: 'key',
			report:
			{
				obsolete: 'error',
				missing: 'error'
			}
		}
	};

	return config;
};