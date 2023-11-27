<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckAlmaPaymentCommand extends Command
{
    protected static $defaultName = 'app:check-alma-payment';
    protected static $defaultDescription = 'Retrieve alma payment information and display status ';

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // retrieve alma payment information and display status
        $alma = new \Alma\API\Client($_ENV['ALMA_SECRET_KEY'], ['mode' => \Alma\API\Client::TEST_MODE]);
        //$paymentId  = "payment_11xt89J6rTJCIlMbxcHPbMjn0NlJc5hltK";
        $paymentId  = "payment_11xt8SE3G1JyskBnJXCIRWu2DQ2c9m3aqs";
        $payment = $alma->payments->fetch($paymentId);
        switch ($payment->state) {
            case \Alma\API\Entities\Payment::STATE_IN_PROGRESS:
                break;
            case \Alma\API\Entities\Payment::STATE_PAID:
                break;
        }

        $io->success(self::$defaultDescription . $payment->state);

        return Command::SUCCESS;
    }
}
