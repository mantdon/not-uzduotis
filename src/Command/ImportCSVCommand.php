<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Util\CSVLoader;

class ImportCSVCommand extends Command
{
    /**
     * @var CSVLoader
     */
    private $csvLoader;

    /**
     * ImportCSVCommand constructor.
     * @param CSVLoader $csvLoader
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(CSVLoader $csvLoader)
    {
        parent::__construct();
        $this->csvLoader = $csvLoader;
    }

    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setName('csv:import')
             ->setDescription('Imports beers, breweries, categories, geocodes and styles .csv into the database');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\DBAL\DBALException
     * @throws \League\Csv\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Loading assets/csv/*.csv files into database');

        ProgressBar::setFormatDefinition(
            'custom',
            ' %current%/%max% [%bar%] %percent:3s%% - %elapsed:6s% | %message% |'
        );
        $progressBar = new ProgressBar($output, 5);
        $progressBar->setFormat('custom');
        $progressBar->setBarWidth(50);
        $progressBar->start();
        $progressBar->setMessage('Loading categories...');
        $this->csvLoader->loadCategories();
        $progressBar->advance();
        $progressBar->setMessage('Loading styles...');
        $this->csvLoader->loadStyles();
        $progressBar->advance();
        $progressBar->setMessage('Loading breweries...');
        $this->csvLoader->loadBreweries();
        $progressBar->advance();
        $progressBar->setMessage('Loading geocodes...');
        $this->csvLoader->loadGeocodes();
        $progressBar->setMessage('Loading beers...');
        $this->csvLoader->loadBeers();
        $progressBar->setMessage('Done');
        $progressBar->finish();
        $io->newLine(2);
        $io->success('All .csv files successfully loaded into database.');
    }
}