<?php

namespace Bixie\Emailreader\Controller;

use Pagekit\Application as App;
use Pagekit\Kernel\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Emailreader Admin Controller
 * @Access (admin=true)
 */
class EmailreaderController {

    /**
     * @Route ("/", methods="GET", name="index")
     * @return array
     */
    public function indexAction () {

        return [
            '$view' => [
                'title' => 'Emailreader index',
                'name' => 'bixie/emailreader/admin/index.php'
            ],
            '$data' => [
                'config' => App::module('bixie/emailreader')->config()
            ]
        ];
    }

    /**
     * @Route ("/downloadlog", methods="GET", name="downloadlog")
     * @Access("emailreader: access mailbox")
     * @Request ({"filename": "string"})
     * @return BinaryFileResponse
     */
    public function downloadlogAction ($filename) {

        $path = App::module('bixie/emailreader')->getLogFilePath($filename);

        if (!file_exists($path)) {
            throw new NotFoundException(__('File not found.'));
        }

        // Generate response
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($path),
            mb_convert_encoding(basename($path), 'ASCII')
        ));

        return $response;
    }

    /**
     * @Access ("system: access settings")
     * @return array
     */
    public function settingsAction () {
        return [
            '$view' => [
                'title' => 'Emailreader settings',
                'name' => 'bixie/emailreader/admin/settings.php'
            ],
            '$data' => [
                'config' => App::module('bixie/emailreader')->config()
            ]
        ];
    }

    /**
     * @Access ("system: access settings")
     * @Request ({"config": "array"}, csrf=true)
     * @param array $config
     * @return array
     */
    public function configAction ($config = []) {
        App::config('bixie/emailreader')->merge($config, true);

        return ['message' => 'success'];
    }


}

