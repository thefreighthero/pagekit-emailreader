<?php

namespace Bixie\Emailreader\Console\Commands;

use Bixie\Emailreader\EmailreaderException;
use Bixie\Emailreader\EmailreaderModule;
use Pagekit\Application\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCommand extends Command {
    /**
     * {@inheritdoc}
     */
    protected $name = 'emailreader:process';

    /**
     * {@inheritdoc}
     */
    protected $extension = 'emailreader';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Processes email from connected emailbox';

    /**
     * {@inheritdoc}
     */
    protected function execute (InputInterface $input, OutputInterface $output) {
        //initialize entity manager
        $this->container->get('db.em');
        try {
            /** @var EmailreaderModule $emailreader */
            $emailreader = $this->container->module('bixie/emailreader');

            $result = $emailreader->processMail();

        } catch (EmailreaderException $e) {
            $this->abort(sprintf('Error in Emailreader: %s', $e->getMessage()));
        }

        $this->line(sprintf('%d new mails in mailbox.', $result['count_new']));
        $this->line(sprintf('%d mails processed.', $result['count_processed']));
        $this->line(sprintf('%d mails unprocessed .', $result['count_unprocessed']));

    }

}