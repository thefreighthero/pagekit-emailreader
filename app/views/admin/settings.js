/*global _, Vue*/
import fields from '../../settings/fields';

// @vue/component
const vm = {

    el: '#emailreader-settings',

    name: 'EmailreaderSettings',

    fields,

    filters: {
        fileSize(size) {
            if (!size) {
                return size;
            }
            let i = Math.floor( Math.log(size) / Math.log(1024) );
            return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB',][i];
        },
    },

    data: () => _.merge({
        loading: false,
        error: false,
        info_result: {},
        config: {
            senders: [],
        },
        form: {},
    }, window.$data),

    created() {
        this.Emailreader = this.$resource('api/emailreader', {}, {
            'info': { method: 'post', url: 'api/emailreader/info', },
        });
    },

    methods: {
        save() {
            this.$http.post('admin/emailreader/config', {config: this.config,}).then(() => {
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
                .then(() => this.loading = false);
        },
        addSender() {
            console.log(this.config)
            this.config.senders.push({name: '', email: '',});
        },
    },

};

Vue.ready(vm);
