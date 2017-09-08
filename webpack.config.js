module.exports = [

    {
        entry: {
            /*views*/
            "emailreader-emailreader-index": "./app/views/admin/index.js",
            "emailreader-settings": "./app/views/admin/settings.js",
        },
        output: {
            filename: "./app/bundle/[name].js"
        },
        externals: {
            "lodash": "_",
            "jquery": "jQuery",
            "uikit": "UIkit",
            "vue": "Vue"
        },
        module: {
            loaders: [
                {test: /\.vue$/, loader: "vue"},
                {test: /\.html$/, loader: "vue-html"},
                {test: /\.js/, loader: 'babel', query: {presets: ['es2015']}}
            ]
        }

    }

];
