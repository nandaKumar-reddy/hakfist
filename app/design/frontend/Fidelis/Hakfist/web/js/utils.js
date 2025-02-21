
/*!--
Author: Sudhakara Rao Kilaparthi
email: sudhakar@kavayahsolutions.com
URL: http://www.kavayahsolutions.com
--*/

define(['jquery'], function ($) {

	var Utility = {};

	try {

		Utility = {
			parseURL: function (url) {

				url = (url) ? url : window.location.href;

				var parser = document.createElement('a'),
					searchObject = {},
					queries, split, i;
				// Let the browser do the work
				parser.href = url;
				// Convert query string to object
				queries = parser.search.replace(/^\?/, '').split('&');
				for (i = 0; i < queries.length; i++) {
					split = queries[i].split('=');

					// console.info('Param Key: ', split[0]);

					split[1] = decodeURIComponent((split[1] + '').replace(/\+/g, '%20'));

					if (split[0] != '') {
						searchObject[split[0]] = split[1];
					}
				}

				// console.info('searchObject: ', searchObject);

				return {
					protocol: parser.protocol,
					host: parser.host,
					hostname: parser.hostname,
					port: parser.port,
					pathname: parser.pathname,
					search: decodeURIComponent((parser.search + '').replace(/\+/g, '%20')),
					searchObject: searchObject,
					hash: parser.hash
				};


			}
		}

		return Utility;
	} catch (err) {
		console.log(err.message);
	}
});