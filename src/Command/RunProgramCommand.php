<?php

namespace App\Command;

use App\Util\BeerCollector;
use App\Util\LocationSelectionMode;
use App\Util\Messages;
use App\Util\MessageType;
use App\Program;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunProgramCommand extends ContainerAwareCommand
{
    /** @var Program */
    private $program;

    private $lat;
    private $lon;
    private $maxdist;
    private $srchrad;
    private $mode;
    private $distdelta;
    private $printbeers;
    private $printstyles;

    public function __construct($name = 'app:run')
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription('Executes the program');
        $this->addOption('lat',
                         'l',
                         InputOption::VALUE_REQUIRED,
                         'The latitude coordinate (in degrees) of the starting location.',
                         program::defaultStartLatitude);
        $this->addOption('lon',
                         'L',
                         InputOption::VALUE_REQUIRED,
                         'The longitude coordinate (in degrees) of the starting location.',
                         program::defaultStartLongitude);
        $this->addOption('maxdist',
                         'D',
                         InputOption::VALUE_REQUIRED,
                         'The distance (in kilometers) limit for the route.',
                         program::defaultMaxDistance);
        $this->addOption('mode',
                         'M',
                         InputOption::VALUE_REQUIRED,
                         "Next location selection method. \"clst\" to always travel to closest \n".
                         "location. \"mstb\" to look at other locations up to \"--distdelta\" km away from \n".
                         'the closest location and pick the one with most uncollected beer types.',
                         'clst');
        $this->addOption('distdelta',
                         'd',
                         InputOption::VALUE_REQUIRED,
                         "Will only take effect when running in \"mstb\" mode\n"
                         ."The maximum distance (in kilometers) away from the closest location, to consider \n".
                         'picking another location if it has more uncollected beer types.',
                         90);
        $this->addOption('srchrad',
                         's',
                         InputOption::VALUE_REQUIRED,
                         "The radius (in degrees) to look for potential next location in. \n".
                         "If no locations are found within it, it will be temporarily expanded until \n".
                         'a location is found',
                         5);
        $this->addOption('printbeers',
                         'p',
                         InputOption::VALUE_NONE,
                         'Prints the names of all collected beers at the end.');
        $this->addOption('printstyles',
                         'P',
                         InputOption::VALUE_NONE,
                         'Prints the names of all the collected beer styles at the end');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);

        $io = new SymfonyStyle($input, $output);
        $this->getOptions($input);
        $error = $this->validateOptions();

        if(empty($error)) {
            $io->newLine();
            $io->title('Beer Collector 9000');

            $this->initializeProgram();
            $io->writeln('Brewery visit route');
            $io->writeln($this->program->getMessage());

            if ($this->program->hasSolution()) {
                while ($this->program->canReachNextLocation()) {
                    $this->program->nextLocation();
                    $io->writeln($this->program->getMessage());
                }

                $this->program->returnHome();
                $io->writeln($this->program->getMessage());

                if ($this->program->wasAborted()) {
                    $io->warning($this->program->getMessage(MessageType::Warning));
                }

                $io->newLine();
                $io->writeln(sprintf('Breweries visited: %d',$this->program->getNumberOfBreweriesVisited()));
                $io->writeln(sprintf('Total distance travelled: %.2f km', $this->program->getDistanceTravelled()));
                $io->newLine();
                $beers = $this->program->getCollectedBeerNames(true);
                $styles = $this->program->getCollectedStyleNames(true);
                $io->writeln(sprintf('Collected %d beer styles', \count($styles)));

                if($this->printstyles) {
                    $this->printNames($styles, $output);
                }

                $io->newLine();
                $io->writeln(sprintf('Collected %d beer types', \count($beers)));

                if($this->printbeers) {
                    $this->printNames($beers, $output);
                }
            }
            else {
                $io->error($this->program->getMessage(MessageType::Error));
            }

            $io->newLine();
            $io->writeln(sprintf('Program finished in %.2f s', microtime(true) - $start));

            if ($this->program->getMessage(MessageType::Error) === null &&
                $this->program->getMessage(MessageType::Warning) === null) {
                $io->success('Exited successfully.');
            }
        } else {
            foreach($error as $err) {
                $io->error($err);
            }
        }
    }

    private function printNames(array $names, OutputInterface $output): void
    {
        foreach ($names as $name) {
            $output->writeln(sprintf('    -> %s', $name));
        }
    }

    private function initializeProgram(): void
    {
        // Injecting in the constructor results in errors when executing any command
        // if the database is not created. Program constructor has to be called when this
        // command is executed.
        $em = $this->getContainer()->get('doctrine')->getManager();
        $this->program  = new Program($em, new BeerCollector(), new Messages());

        $this->program->setStartLatitude($this->lat)
                      ->setStartLongitude($this->lon)
                      ->setMaxDistance($this->maxdist)
                      ->setSearchRadius($this->srchrad)
                      ->setNextLocationSelectionMode($this->mode)
                      ->setLocationSelectionDistanceDelta($this->distdelta)
                      ->Initialize();
    }

    private function getOptions(InputInterface $input): void
    {
        $this->lat = $input->getOption('lat');
        $this->lon = $input->getOption('lon');
        $this->maxdist = $input->getOption('maxdist');
        $this->srchrad = $input->getOption('srchrad');
        $this->mode = $input->getOption('mode');
        $this->distdelta = $input->getOption('distdelta');
        $this->printbeers = $input->getOption('printbeers');
        $this->printstyles = $input->getOption('printstyles');
    }

    private function validateOptions(): array
    {
        $errorMessage = [];

        if ($this->lat > 90 || $this->lat < -90) {
            $errorMessage[] = sprintf('- Given latitude of %f does not exist.', $this->lat);
        }
        if ($this->lon > 180 || $this->lon < -180) {
            $errorMessage[] = sprintf('- Given longitude of %f does not exist.', $this->lon);
        }
        if($this->maxdist <= 0) {
            $errorMessage[] = sprintf('- Max distance must be positive: %f given.', $this->maxdist);
        }
        if ($this->srchrad < 0 || $this->srchrad > 90) {
            $errorMessage[] = sprintf('- Search radius must be in the range [0 - 90]. %f given.', $this->srchrad);
        }
        if(!LocationSelectionMode::modeIsValid($this->mode)) {
            $errorMessage[] = sprintf('- Given next location selection mode of \'%s\' does not exist.', $this->mode);
        }
        if ($this->distdelta <= 0) {
            $errorMessage[] = sprintf('- Distance delta must be positive: %f given.', $this->distdelta);
        }
        return $errorMessage;
    }
}