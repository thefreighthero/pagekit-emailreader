<?php

namespace Bixie\Emailreader;

use Bixie\Emailreader\Event\EmailreaderEvent;
use Pagekit\Application as App;
use Pagekit\Module\Module;
use PhpImap\IncomingMail;
use PhpImap\Mailbox;
use PhpImap\Exception as ImapException;

/**
 * Emailreader Main Module
 */
class EmailreaderModule extends Module {
    /**
     * @var Mailbox[]
     */
    protected $mailboxes = [];

    /**
     * @param App $app
     * @return void
     */
    public function main (App $app) {

        //subscribe listeners before boot, boot is not run in console
     //   $app->subscribe(new FreightheroEmailreaderListener());

        //initialize extra properties on container instance in console
        $app->on('emailreader.console.init', function ($event, $app) {
            //initialize entity manager
            $app['db.em'];

        }, 50);

    }

    /**
     * @throws EmailreaderException
     * @return array
     */
    public function processMail () {

        $processed_mailbox = $this->config('mailboxes.processed');
        $unprocessed_mailbox = $this->config('mailboxes.unprocessed');

        try {
            $mailbox = $this->getMailBox();

            $mailIds = $mailbox->searchMailbox('UNDELETED');
            $count_new = count($mailIds);
            $count_processed = 0;
            $count_unprocessed = 0;
            $log_entries = [];

            foreach ($mailIds as $mailId) {

                $incomingMail = $mailbox->getMail($mailId);
                $event = new EmailreaderEvent('emailreader.mail.incoming', $incomingMail, ['config' => $this->config()]);
                try {
                    App::trigger($event);
                } catch (\Exception $e) {
                    $event->setError($e->getMessage());
                }

                $log_entries[] = $this->getIncomingMailLogData($incomingMail, $event);
                if ($event->isProcessed()) {
                    $mailbox->moveMail($mailId, $processed_mailbox);
                    $count_processed++;
                } else {
                    $mailbox->moveMail($mailId, $unprocessed_mailbox);
                    $count_unprocessed++;
                }
            }

            //clear downloaded attachments
            $this->clearAttachmentPath();
            //write actions to logfile
            $this->writeLog($log_entries);

            return compact('count_new', 'count_processed', 'count_unprocessed', 'log_entries');

        } catch (ImapException $e) {
            throw new EmailreaderException('IMAP error: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param null $path
     * @return Object
     */
    public function mailboxInfo ($path = null) {
        try {
            $mailbox = $this->getMailBox($path);

            $general = $mailbox->getMailboxInfo();
            $mailboxes = $mailbox->getMailboxes();

            return compact('general', 'mailboxes');
        } catch (ImapException $e) {
            throw new EmailreaderException('IMAP error: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param null $path
     * @throws ImapException
     * @return Mailbox
     */
    protected function getMailBox ($path = null) {
        $path = $path ?: 'INBOX';
        if (!isset($this->mailboxes[$path])) {
            $config = $this->config('server');
            $this->mailboxes[$path] = new Mailbox(
                sprintf('{%s:993/novalidate-cert/imap/ssl}%s', $config['host'], $path),
                $config['email'],
                $config['password'],
                $this->getAttachmentPath()
            );
        }
        return $this->mailboxes[$path];
    }

    /**
     * @param IncomingMail $incomingMail
     * @param EmailreaderEvent $event
     * @return array
     */
    public function getIncomingMailLogData (IncomingMail $incomingMail, EmailreaderEvent $event) {
        $data = [
            'mail_id' => $incomingMail->id,
            'date' => (new \DateTime($incomingMail->date))->format(DATE_ATOM),
            'subject' => $incomingMail->subject,
            'to' => implode(', ', array_keys($incomingMail->to)),
            'cc' => implode(', ', array_keys($incomingMail->cc)),
            'bcc' => implode(', ', array_keys($incomingMail->bcc)),
            'message_id' => $incomingMail->messageId,
            'processed_by' => implode(', ', $event->getProcessedBy()),
            'error' => $event->getError(),
        ];
        return $data;
    }

    /**
     * @param $log_entries
     */
    public function writeLog ($log_entries) {
        if (count($log_entries)) {
            $filename = $this->getLogFilePath();
            $lines = array_map(function ($data) {
                return implode(';', $data) . "\n";
            }, $log_entries);
            //add header to new files
            if (!file_exists($filename)) {
                array_unshift($lines, implode(';', array_keys($log_entries[0])) . "\n");
            }
            @file_put_contents($filename, implode('', $lines), FILE_APPEND);
        }
    }

    /**
     * folder to temporarily store attachments
     * @return string
     */
    public function getAttachmentPath () {
        $root = strtr(App::path(), '\\', '/');
        $path = $this->normalizePath($root . '/' . $this->config['attachment_path']);
        if (!is_dir($path)) {
            App::file()->makeDir($path);
        }
        return $path;
    }

    /**
     * Remove temp attachments
     */
    public function clearAttachmentPath () {
        App::file()->delete($this->getAttachmentPath());
    }

    /**
     * @return array
     */
    public function getLogFiles () {
        $root = strtr(App::path(), '\\', '/');
        $path = $this->normalizePath($root . '/' . $this->config['log_path']);

        $files = array_map(function ($path) {
            return basename($path);
        }, glob($path . '/logs_*.csv'));

        return $files;

    }

    /**
     * @param null $filename
     * @return string
     */
    public function getLogFilePath ($filename = null) {
        $root = strtr(App::path(), '\\', '/');
        $path = $this->normalizePath($root . '/' . $this->config['log_path']);
        if (!is_dir($path)) {
            App::file()->makeDir($path);
        }
        $filename = $filename ?: sprintf('logs_%d.csv', (new \DateTime())->format('Ym'));
        return "$path/$filename";
    }

    /**
     * @param null $filename
     * @param null $lines
     * @return array
     */
    public function loadLogData ($filename = null, $lines = null) {
        $filepath = $this->getLogFilePath($filename);
        if (!file_exists($filepath)) {
            return [];
        }
        $data = [];
        $contents = file_get_contents($filepath);
        $rows = explode("\n", $contents);
        $labels = explode(';', trim(array_shift($rows)));
        //most recent first
        $rows = array_reverse($rows);
        foreach ($rows as $row) {
            if (empty($row[0])) continue;
            $row_data = [];
            $i = 0;
            foreach (explode(';', trim($row)) as $value) {
                if (!isset($labels[$i])) break;
                $row_data[$labels[$i]] = trim(utf8_encode($value));
                $i++;
            }
            $data[] = $row_data;
            if ($lines && count($data) == $lines) {
                break;
            }
        }

        return $data;
    }

    /**
     * Normalizes the given path
     * @param  string $path
     * @return string
     */
    protected function normalizePath ($path) {
        $path = str_replace(['\\', '//'], '/', $path);
        $prefix = preg_match('|^(?P<prefix>([a-zA-Z]+:)?//?)|', $path, $matches) ? $matches['prefix'] : '';
        $path = substr($path, strlen($prefix));
        $parts = array_filter(explode('/', $path), 'strlen');
        $tokens = [];

        foreach ($parts as $part) {
            if ('..' === $part) {
                array_pop($tokens);
            } elseif ('.' !== $part) {
                array_push($tokens, $part);
            }
        }

        return $prefix . implode('/', $tokens);
    }

    /**
     * Whitelist of publicly accessable config keys
     * @return array
     */
    public function publicConfig () {
        return array_intersect_key(static::config(), array_flip([]));
    }


}

