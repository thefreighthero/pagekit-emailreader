<?php

namespace Bixie\Emailreader\Controller;

use Bixie\Emailreader\EmailreaderException;
use Pagekit\Application as App;

/**
 * Message Api Controller
 * @Access("emailreader: access mailbox")
 */
class EmailreaderApiController {


    /**
     * @Route ("/info", methods="POST")
     * @Request ({"path": "string"}, csrf=true)
     * @return array
     */
    public function infoAction ($path = null) {

        $result = [];
        try {

            $result = App::module('bixie/emailreader')->mailboxInfo($path);

        } catch (EmailreaderException $e) {
            App::abort(500, $e->getMessage());
        }
        return $result;
    }

    /**
     * @Route ("/process", methods="POST")
     * @Request (csrf=true)
     * @return array
     */
    public function processAction () {
        $result = [];
        try {

            $result = App::module('bixie/emailreader')->processMail();

        } catch (EmailreaderException $e) {
            App::abort(500, $e->getMessage());
        }
        return $result;
    }

    /**
     * @Route ("/logfiles", methods="GET")
     * @Request (csrf=true)
     * @return array
     */
    public function logfilesAction () {

        return App::module('bixie/emailreader')->getLogFiles();

    }

    /**
     * @Route ("/logdata", methods="GET")
     * @Request ({"filename": "string", "lines": "int"}, csrf=true)
     * @return array
     */
    public function logdataAction ($filename = null, $lines = null) {

        return App::module('bixie/emailreader')->loadLogData($filename, $lines);

    }


}

