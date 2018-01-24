<?php


namespace Bixie\Emailreader\Event;


use Pagekit\Event\Event;
use Pagekit\Event\EventInterface;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;

class EmailreaderEvent extends Event implements EventInterface {
    /**
     * @var array
     */
    protected $processed_by = [];
    /**
     * @var IncomingMail
     */
    protected $incomingMail;

    /**
     * Constructor.
     * @param string       $name
     * @param IncomingMail $incomingMail
     * @param array        $parameters
     */
    public function __construct ($name, IncomingMail $incomingMail, array $parameters = []) {
        parent::__construct($name, $parameters);

        $this->incomingMail = $incomingMail;
    }

    /**
     * @return bool
     */
    public function isProcessed () {
        return count($this->processed_by) > 0;
    }

    /**
     * @return array
     */
    public function getProcessedBy () {
        return $this->processed_by;
    }

    /**
     * @param string $processed_by
     * @param string $message
     * @return EmailreaderEvent
     */
    public function addProcessedBy ($processed_by, $message) {
        $this->processed_by[$processed_by] = $message;
        return $this;
    }

    /**
     * @return IncomingMail
     */
    public function getIncomingMail () {
        return $this->incomingMail;
    }

    /**
     * @return array
     */
    public function getAllReceivers () {
        $receivers = array_merge(
            array_keys($this->incomingMail->to),
            array_keys($this->incomingMail->cc),
            array_keys($this->incomingMail->bcc)
        );
        return array_unique($receivers);
    }

    /**
     * @return array email_address prefixes of this receiver
     */
    public function getOwnReceivers () {
        $own_receivers = [];
        $own_domain = explode('@', $this['config']['server']['email'], 2)[1];
        foreach ($this->getAllReceivers() as $receiver) {
            if (stripos($receiver, $own_domain)) {
                $own_receivers[] = str_replace('@' . $own_domain, '', $receiver);
            }
        }
        return $own_receivers;
    }

    /**
     * @return string stripped body
     */
    public function getCleanedBody () {
        $body = $this->incomingMail->textPlain ?: preg_replace('/=["\'](ci?d:([\w\.%*@-]+))["\']/i', '', $this->incomingMail->textHtml);
        $body = preg_replace('/([\r\n]){2,}/', '$1', strip_tags($body, '<p><br>'));
        return $body;
    }

    /**
     * @return IncomingMailAttachment[]
     */
    public function getNonImageAttachments () {
        return array_filter($this->incomingMail->getAttachments(), function ($attachment) {
            /** @var IncomingMailAttachment $attachment */
            return stripos(basename($attachment->name), '.') !== false || !preg_match('/(jpg|png|gif|jpeg|tif|tiff|html)$/i', $attachment->name);
        });
    }

}