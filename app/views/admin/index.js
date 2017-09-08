
module.exports = {

    name: 'emailreader-index',

    el: '#emailreader-index',

    data() {
        return _.merge({
            loading: false,
            loading_log: false,
            processing: false,
            error: false,
            process_result: false,
            info_result: {
                main: {},
                processed: {},
                unprocessed: {},
            },
            logfile: '',
            records_limit: 25,
            logfiles: [],
            logdata: [],
            config: {},
        }, window.$data);
    },

    created() {
        this.Emailreader = this.$resource('api/emailreader', {}, {
            'info': { method: 'post', url: 'api/emailreader/info{/path}'},
            'process': { method: 'post', url: 'api/emailreader/process'},
            'logfiles': { method: 'get', url: 'api/emailreader/logfiles'},
            'logdata': { method: 'get', url: 'api/emailreader/logdata'},
        });
        this.mailboxInfo();
        this.getLogFiles();
        this.getLogData();
    },

    computed: {
        downloadLink() {
            return this.$url('admin/emailreader/downloadlog', {filename: this.logfile});
        },
    },

    methods: {
        mailboxInfo() {
            this.loading = true;
            Vue.Promise.all([
                this.Emailreader.info(),
                this.Emailreader.info({path: this.config.mailboxes.processed}),
                this.Emailreader.info({path: this.config.mailboxes.unprocessed})
            ])
                .then(
                    res => {
                        this.info_result.main = res[0].data;
                        this.info_result.processed = res[1].data;
                        this.info_result.unprocessed = res[2].data;
                    },
                    res => this.error = (res.data.message || res.data)
                )
                .then(res => this.loading = false);
        },
        processMail() {
            this.error = '';
            this.processing = true;
            this.Emailreader.process()
                .then(
                    res => this.process_result = res.data,
                    res => this.error = (res.data.message || res.data)
                )
                .then(res => this.processing = false)
                .then(this.mailboxInfo)
                .then(this.getLogData);
        },
        getLogFiles() {
            this.Emailreader.logfiles()
                .then(
                    res => this.logfiles = res.data,
                    res => this.$notify((res.data.message || res.data), 'danger')
                );
        },
        getLogData() {
            this.loading_log = true;
            this.logdata = [];
                this.Emailreader.logdata({filename: this.logfile, lines: this.records_limit})
                .then(
                    res => this.logdata = res.data,
                    res => this.$notify((res.data.message || res.data), 'danger')
                )
                .then(res => this.loading_log = false);
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

    partials: {
        'log-entry': require('../../templates/log-entry.html'),
    }


};

Vue.ready(module.exports);
