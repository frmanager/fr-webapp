<?php


namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Utils\DonationHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;


class RefreshDonationDatabaseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
      $this
        // the name of the command (the part after "bin/console")
        ->setName('app:refresh-donation-db')

        // the short description shown while running "php bin/console list"
        ->setDescription('Refreshes the donation database table')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command manually refreshes the donation database table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
        'Executing Refresh of Donation Database Table',
        '============',
        '',
        ]);

        $logger = $this->getContainer()->get('logger');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $donationHelper = new DonationHelper($em, $logger);
        $donationHelper->reloadDonationDatabase(array());

        $output->writeln([
        '============',
        'Done',
        '',
        ]);


    }
}
