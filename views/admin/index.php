
<?php
$view->script('emailreader-emailreader-index', 'bixie/emailreader:app/bundle/emailreader-emailreader-index.js', ['bixie-pkframework'],
    ['version' => $app->module('bixie/pk-framework')->getVersionKey($app->package('bixie/emailreader')->get('version'))]);
?>
<div id="emailreader-index">

    <div class="uk-grid uk-grid-width-medium-1-2" data-uk-grid-margin>
        <div>

            <div class="uk-panel uk-panel-box">
                <div class="uk-flex uk-flex-space-between">
                    <h3 class="uk-panel-title">{{ 'Process Mailbox' | trans }}</h3>

                    <button type="button" @click="processMail" class="uk-button uk-button-primary">
                        <i v-spinner="processing" icon="refresh"></i>{{ 'Process email' | trans }}
                    </button>

                </div>
                <div class="uk-grid uk-grid-small uk-grid-width-medium-1-3 uk-margin" data-uk-grid-margin>
                    <div>
                        <h4 class="uk-margin-small-bottom">{{ 'Main mailbox' | trans }}</h4>
                        <ul v-if="info_result.main.general" class="uk-list uk-margin-small-top">
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Recent emails' | trans }}</strong>
                                <span>{{ info_result.main.general.Recent }}</span>
                            </li>
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Unread emails' | trans }}</strong>
                                <span>{{ info_result.main.general.Unread }}</span>
                            </li>
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Deleted emails' | trans }}</strong>
                                <span>{{ info_result.main.general.Deleted }}</span>
                            </li>
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Total emails' | trans }}</strong>
                                <span>{{ info_result.main.general.Nmsgs }}</span>
                            </li>
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Size' | trans }}</strong>
                                <span>{{ info_result.main.general.Size | fileSize }}</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="uk-margin-small-bottom">{{ 'Processed mailbox' | trans }}</h4>
                        <ul v-if="info_result.main.general" class="uk-list uk-margin-small-top">
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Recent emails' | trans }}</strong>
                                <span>{{ info_result.processed.general.Recent }}</span>
                            </li>
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Unread emails' | trans }}</strong>
                                <span>{{ info_result.processed.general.Unread }}</span>
                            </li>
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Deleted emails' | trans }}</strong>
                                <span>{{ info_result.processed.general.Deleted }}</span>
                            </li>
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Total emails' | trans }}</strong>
                                <span>{{ info_result.processed.general.Nmsgs }}</span>
                            </li>
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Size' | trans }}</strong>
                                <span>{{ info_result.processed.general.Size | fileSize }}</span>
                            </li>
                        </ul>
                        <p v-else class="uk-text-center"><i class="uk-icon-circle-o-notch uk-icon-spin"></i></p>
                    </div>
                    <div>
                        <h4 class="uk-margin-small-bottom">{{ 'Unprocessed mailbox' | trans }}</h4>
                        <ul v-if="info_result.main.general" class="uk-list uk-margin-small-top">
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Recent emails' | trans }}</strong>
                                <span>{{ info_result.unprocessed.general.Recent }}</span>
                            </li>
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Unread emails' | trans }}</strong>
                                <span>{{ info_result.unprocessed.general.Unread }}</span>
                            </li>
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Deleted emails' | trans }}</strong>
                                <span>{{ info_result.unprocessed.general.Deleted }}</span>
                            </li>
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Total emails' | trans }}</strong>
                                <span>{{ info_result.unprocessed.general.Nmsgs }}</span>
                            </li>
                            <li class="uk-flex uk-flex-middle">
                                <strong class="uk-flex-item-1">{{ 'Size' | trans }}</strong>
                                <span>{{ info_result.unprocessed.general.Size | fileSize }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <dl v-if="process_result" class="uk-description-list-horizontal">
                    <dt>{{ 'New emails' | trans }}</dt>
                    <dd>{{ process_result.count_new }}</dd>
                    <dt>{{ 'Processed emails' | trans }}</dt>
                    <dd>{{ process_result.count_processed }}</dd>
                    <dt>{{ 'Unprocessed emails' | trans }}</dt>
                    <dd>{{ process_result.count_unprocessed }}</dd>
                </dl>

                <ul v-if="process_result.log_entries" class="uk-list uk-list-line">
                    <li v-for="log in process_result.log_entries">
                        <partial name="log-entry"></partial>
                    </li>
                </ul>

                <div v-if="error" class="uk-alert uk-alert-danger">{{ error | trans}}</div>

            </div>

        </div>
        <div>
            <h3>{{ 'Last %count% processed emails' | trans {count: records_limit} }}</h3>

            <div class="uk-form uk-grid">
                <div class="uk-width-2-3">
                    <select v-model="logfile" class="uk-width-1-1" @change="getLogData">
                        <option value="">{{ 'Most recent' | trans }}</option>
                        <option v-for="file in logfiles" :value="file">{{ file }}</option>
                    </select>
                    <small><a :href="downloadLink"><i class="uk-icon-download uk-margin-small-right"></i>{{ 'Download full log' | trans }}</a></small>
                </div>
                <div class="uk-width-1-3">
                    <select v-model="records_limit" class="uk-width-1-1" @change="getLogData">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <ul v-if="logdata.length" class="uk-list uk-list-line">
                <li v-for="log in logdata">
                    <partial name="log-entry"></partial>
                </li>
            </ul>
            <p v-if="loading_log" class="uk-text-center"><i class="uk-icon-circle-o-notch uk-icon-spin"></i></p>

        </div>
    </div>


</div>