<?php

namespace App\Tests;

use App\Program;
use App\Util\BeerCollector;
use App\Util\Messages;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProgramTest extends KernelTestCase
{
    private $entityManager;
    /** @var Program */
    private $program;

    public function testProgramTerminatesWhenNoSolutionExists(): void
    {
        $this->constructProgram();
        $this->program->setStartLatitude(84.234573)
                      ->setStartLongitude(45.081805)
                      ->Initialize();

        $this->assertEquals(false, $this->program->hasSolution());

    }

    private function constructProgram(): void
    {
        $this->program = new Program($this->entityManager, new BeerCollector(), new Messages());
    }

    public function testCanReachNextLocationWithUnreachableLocation(): void
    {
        $this->constructProgram();
        $this->program->setStartLatitude(84.234573)
                      ->setStartLongitude(45.081805)
                      ->Initialize();

        $this->assertEquals(false, $this->program->canReachNextLocation());
    }

    public function testCanReachNextLocationWithReachableLocation(): void
    {
        $this->constructProgram();
        $this->program->setStartLatitude(51.355468)
                      ->setStartLongitude(11.100790)
                      ->setSearchRadius(50)
                      ->Initialize();

        $this->assertEquals(true, $this->program->canReachNextLocation());
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

    }
}