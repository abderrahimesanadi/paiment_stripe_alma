<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StripetransfertCommand extends Command
{
    protected static $defaultName = 'app:stripetransfert';
    protected static $defaultDescription = ' cette commande permet de transferer de l\'argent du compte stripe Lba vers les comptes stripe des moniteurs';

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        // il faut prévoir de faire un boucle sur les moniteurs qu'ils ont effectué une séance de conduite 
        $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);

        $stripe->accounts->create([
            'country' => 'FR',
            'type' => 'custom',
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
        ]);
        /*$stripe->transfers->create([
            'amount' => 4000,
            'currency' => 'eur',
            'destination' => 'acct_1OFw2gGbIb1iUpyI',
            'transfer_group' => 'ORDER10', //  compte stripe du moniteur must be connected to our compte
        ]);
        */
        /*$stripe->customers->create([
            'description' => 'Moniteur',
            'name' => 'messi',
            'phone' => '0 6 00 00 00 00'

        ]);*/
        // si le transerfe s'est bien passé il faire faire une mise à jour de la base de donné

        $io->success(self::$defaultDescription);

        return Command::SUCCESS;
    }
}
