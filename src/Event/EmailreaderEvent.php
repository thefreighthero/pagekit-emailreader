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
     * @var String
     */
    protected $error;

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
     * @return String
     */
    public function getError () {
        return $this->error;
    }

    /**
     * @param String $error
     * @return EmailreaderEvent
     */
    public function setError ($error) {
        $this->error = $error;
        return $this;
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

        // Use a regular expression to find the Envelope-to header
        preg_match('/^Envelope-to:\s*(.*)$/mi', $this->incomingMail->headersRaw, $matches);

        // Check if the Envelope-to header was found
        if (isset($matches[1])) {
            $receivers[] = trim(str_replace('\n', '', (str_replace('\r', '', $matches[1]))));
        }

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
//        $body = $this->incomingMail->textPlain ?:
//            preg_replace('/=["\'](ci?d:([\w\.%*@-]+))["\']/i', '', $this->incomingMail->textHtml);

        $body = preg_replace('/=["\'](ci?d:([\w\.%*@-]+))["\']/i', '', $this->incomingMail->textHtml);
        $body = preg_replace('/([\r\n]){2,}/', '$1', strip_tags($body, '<p><br>'));
        return utf8_encode($body);
    }

    /**
     * @return IncomingMailAttachment[]
     */
    public function getNonImageAttachments () {
        return array_filter($this->incomingMail->getAttachments(), function ($attachment) {
            /** @var IncomingMailAttachment $attachment */
            return preg_match('/\.\w{2,4}$/i', $attachment->name) //has a regular extension (3-4 letters)
                        && !preg_match('/(jpg|png|gif|jpeg|tif|tiff|html)$/i', $attachment->name); //which is not an image/html
        });
    }

}
