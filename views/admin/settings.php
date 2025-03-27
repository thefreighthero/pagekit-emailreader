
<?php
$view->script('emailreader-settings', 'bixie/emailreader:app/bundle/emailreader-settings.js', ['bixie-pkframework'],
    ['version' => $app->module('bixie/pk-framework')->getVersionKey($app->package('bixie/emailreader')->get('version'))]);
?>
<div id="emailreader-settings">
    <div class="uk-grid pk-grid-large" data-uk-grid-margin>
        <div class="pk-width-sidebar">

            <div class="uk-panel">

                <ul class="uk-nav uk-nav-side pk-nav-large" data-uk-tab="{ connect: '#tab-content' }">
                    <li><a><i class="pk-icon-large-settings uk-margin-right"></i> {{ 'Settings' | trans }}</a></li>
                </ul>

            </div>

        </div>
        <div class="pk-width-content">

            <ul id="tab-content" class="uk-switcher uk-margin">
                <li class="uk-form uk-form-horizontal">

                    <div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap" data-uk-margin>
                        <div data-uk-margin>

                            <h2 class="uk-margin-remove">{{ 'Emailreader Settings' | trans }}</h2>

                        </div>
                        <div data-uk-margin>

                            <button class="uk-button uk-button-primary" @click="save">{{ 'Save' | trans }}</button>

                        </div>
                    </div>

                    <div class="uk-margin">
                        <bixie-fields :config="$options.fields.settings" :values.sync="config"></bixie-fields>
                    </div>


                    <div class="uk-margin">
                        <label class="uk-form-label">{{ 'E-mailafzenders mapping' | trans }}</label><br>

                        <div class="">

                            <div class="uk-form">

                                <ul class="uk-list uk-list-line uk-margin-top">
                                    <li v-for="sender in config.senders">
                                        <div class="uk-grid" data-uk-grid-margin>
                                            <div class="uk-width-medium-1-5">
                                                <input type="text" v-model="sender.name"  class="uk-width-1-1"
                                                       :placeholder="$trans('Name')"/>
                                            </div>
                                            <div class="uk-width-medium-1-5">
                                                <input type="email" v-model="sender.email"  class="uk-width-1-1"
                                                       :placeholder="$trans('Email')"/>
                                            </div>
                                            <div class="uk-width-medium-1-5">
                                                <select id="form-assigned" class="uk-width-1-1" v-model="sender.user_id" number>
                                                    <option :value="0">{{ 'Not assigned to user' | trans }}</option>
                                                    <option v-for="moderator in moderators" :value="moderator.id">{{ moderator.name }}</option>
                                                </select>
                                            </div>
                                            <div class="uk-width-medium-1-5">
                                                <a @click="config.senders.$remove(sender)"><i
                                                            class="uk-icon-trash-o uk-margin-small-right"></i></a>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <p>
                                    <button type="button" @click="addSender" class="uk-button">{{ 'Add sender' | trans }}</button>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="uk-grid uk-grid-width-medium-1-2" data-uk-grid-margin>
                        <div>

                            <button type="button" @click="mailboxInfo" class="uk-button">
                                <i v-spinner="loading" icon="refresh"></i>{{ 'Get mailbox info' | trans }}
                            </button>

                            <dl v-if="info_result.general" class="uk-description-list-horizontal">
                                <dt>{{ 'Recent emails' | trans }}</dt>
                                <dd>{{ info_result.general.Recent }}</dd>
                                <dt>{{ 'Unread emails' | trans }}</dt>
                                <dd>{{ info_result.general.Unread }}</dd>
                                <dt>{{ 'Deleted emails' | trans }}</dt>
                                <dd>{{ info_result.general.Deleted }}</dd>
                                <dt>{{ 'Total emails' | trans }}</dt>
                                <dd>{{ info_result.general.Nmsgs }}</dd>
                                <dt>{{ 'Size' | trans }}</dt>
                                <dd>{{ info_result.general.Size | fileSize }}</dd>
                            </dl>

                            <div v-if="error" class="uk-alert uk-alert-danger">{{ error | trans}}</div>

                        </div>
                        <div>

                            <div class="uk-form-row uk-form-horizontal">
                                <label for="field_from_addresses" class="uk-form-label">{{ 'Processed mail folder' | trans }}</label>

                                <div class="uk-form-controls" :class="{'uk-form-controls-text': !info_result.mailboxes}">
                                    <select v-if="info_result.mailboxes" v-model="config.mailboxes.processed" class="uk-form-width-medium">
                                        <option value="">{{ 'Select mailbox' | trans }}</option>
                                        <option v-for="mailbox in info_result.mailboxes" :value="mailbox.shortpath">{{ mailbox.shortpath }}</option>
                                    </select>
                                    <p v-else>{{ config.mailboxes.processed }}</p>
                                </div>
                            </div>
                            <div class="uk-form-row uk-form-horizontal">
                                <label for="field_from_addresses" class="uk-form-label">{{ 'Unprocessed mail folder' | trans }}</label>

                                <div class="uk-form-controls" :class="{'uk-form-controls-text': !info_result.mailboxes}">
                                    <select v-if="info_result.mailboxes" v-model="config.mailboxes.unprocessed" class="uk-form-width-medium">
                                        <option value="">{{ 'Select mailbox' | trans }}</option>
                                        <option v-for="mailbox in info_result.mailboxes" :value="mailbox.shortpath">{{ mailbox.shortpath }}</option>
                                    </select>
                                    <p v-else>{{ config.mailboxes.unprocessed }}</p>
                                </div>
                            </div>
                            <p>
                                <em>{{ 'Get mailbox info to change values' | trans }}</em>
                            </p>

                        </div>
                    </div>
                </li>
            </ul>

        </div>

    </div>
</div>