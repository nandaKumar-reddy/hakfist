var config = {
    paths: {
        'custom-script': 'Hakfist_CreateCustomer/js/custom-script' // Path to your JS file relative to baseUrl
    },
    shim: {
        'custom-script': {
            deps: ['jquery'] // If your script depends on jQuery
        }
    }
};
