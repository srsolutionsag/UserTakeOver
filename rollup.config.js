export default {
	external: ['@varvet/tiny-autocomplete'],
	input:    './js/src/main.js',
	output:   [
		{
			file:      './js/dist/main.js',
			format:    'es',
			sourcemap: 'inline'             // optional: creates inline source map
		}
	]
};
