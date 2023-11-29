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

        /*$stripe->customers->create([
            'description' => 'Moniteur',
            'name' => 'messi',
            'phone' => '0 6 00 00 00 00'

        ]);*/

        $io->success(self::$defaultDescription);

        return Command::SUCCESS;
    }
}
