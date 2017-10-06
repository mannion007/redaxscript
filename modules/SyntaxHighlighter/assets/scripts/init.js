/**
 * @tableofcontents
 *
 * 1. syntax highlighter
 */

/** @section 1. syntax highlighter */

rs.modules.syntaxHighlighter =
{
	init: true,
	dependency: typeof hljs === 'object',
	selector: 'pre.rs-admin-js-code, pre.rs-js-code',
	options:
	{
		languages:
		[
			'bash',
			'css',
			'html',
			'js',
			'php'
		]
	}
};
