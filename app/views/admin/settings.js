
module.exports = {

    name: 'emailreader-settings',

    el: '#emailreader-settings',

    fields: require('../../settings/fields'),

    data() {
        return _.merge({
            loading: false,
            error: false,
            info_result: {},
            config: {},
            form: {},
        }, window.$data);
    },

    created() {
        this.Emailreader = this.$resource('api/emailreader', {}, {
            'info': { method: 'post', url: 'api/emailreader/info' },
        });
    },

    methods: {
        save() {
            this.$http.post('admin/emailreader/config', {config: this.config}).then(() => {
                this.$notify('Settings saved.');
                this.mailboxInfo();
            }, res => this.$notify((res.data.message || res.data), 'danger'));
        },
        mailboxInfo() {
            this.error = '';
            this.loading = true;
            this.Emailreader.info()
                .then(
                    res => this.info_result = res.data,
                    res => this.error = (res.data.message || res.data)
                )
                .then(res => this.loading = false);
        },
    },

    filters: {
        fileSize: function (size) {
            if (!size) {
                return size;
            }
            var i = Math.floor( Math.log(size) / Math.log(1024) );
            return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
        }
    },


};

Vue.ready(module.exports);
