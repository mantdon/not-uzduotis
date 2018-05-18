<?php

namespace App\Util;

use App\Entity\Beer;
use App\Entity\Brewery;
use App\Entity\Category;
use App\Entity\Geocode;
use App\Entity\Style;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception;
use League\Csv\Reader;

class CSVLoader
{
    private $em;
    private $paths = [
        'App:Beer'     => '%kernel.root_dir%/../assets/csv/beers.csv',
        'App:Brewery'  => '%kernel.root_dir%/../assets/csv/breweries.csv',
        'App:Category' => '%kernel.root_dir%/../assets/csv/categories.csv',
        'App:Geocode'  => '%kernel.root_dir%/../assets/csv/geocodes.csv',
        'App:Style'    => '%kernel.root_dir%/../assets/csv/styles.csv',
    ];

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Returns the CSV records as an Iterator object.<br>
     * Each CSV record is represented as an array mapped
     * by the header record.
     * @param $className - class, the .csv file of which to read.
     * @return \Iterator
     * @throws Exception
     */
    private function readCsv($className): \Iterator
    {
        $reader = Reader::createFromPath($this->paths[$className])
                        ->setHeaderOffset(0);
        return $reader->getRecords($reader->getHeader());
    }

    /**
     * @return CSVLoader
     * @throws Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function loadCategories(): self
    {
        $results = $this->readCsv('App:Category');
        foreach ($results as $result) {
            if (!$this->existsInDatabase('App:Category', $result['id'])) {
                $category = new Category();
                $category->setId($result['id'])
                         ->setName($result['cat_name'])
                         ->setLastModification(new \DateTime($result['last_mod']));
                $this->em->persist($category);
            }
        }
        $this->em->flush();
        return $this;
    }

    /**
     * @return CSVLoader
     * @throws Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function loadStyles(): self
    {
        $results = $this->readCsv('App:Style');
        foreach ($results as $result) {
            if (!$this->existsInDatabase('App:Style', $result['id'])) {
                $category = $this->em->getRepository('App:Category')
                                     ->find($result['cat_id']);
                $style = new Style();
                $style->setId($result['id'])
                      ->setName($result['style_name'])
                      ->setLastModification(new \DateTime($result['last_mod']));
                $category->addStyle($style);
                $this->em->persist($style);
            }
        }
        $this->em->flush();
        return $this;
    }

    /**
     * @return CSVLoader
     * @throws Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function loadBreweries(): self
    {
        $results = $this->readCsv('App:Brewery');
        foreach ($results as $result) {
            if (!$this->existsInDatabase('App:Brewery', $result['id'])) {
                $brewery = new Brewery();
                $brewery->setId($result['id'])
                        ->setName($result['name'])
                        ->setAddress1($result['address1'])
                        ->setAddress2($result['address2'])
                        ->setCity($result['city'])
                        ->setState($result['state'])
                        ->setCode($result['code'])
                        ->setCountry($result['country'])
                        ->setPhone($result['phone'])
                        ->setWebsite($result['website'])
                        ->setDescription($result['descript'])
                        ->setLastModification(new \DateTime($result['last_mod']));
                $this->em->persist($brewery);
            }
        }
        $this->em->flush();
        return $this;
    }

    /**
     * @return CSVLoader
     * @throws Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function loadGeocodes(): self
    {
        $results = $this->readCsv('App:Geocode');
        foreach ($results as $result) {
            if (!$this->existsInDatabase('App:Geocode', $result['id'])) {
                $geocode = new Geocode();
                $geocode->setId($result['id'])
                        ->setLatitude($result['latitude'])
                        ->setLongitude($result['longitude'])
                        ->setAccuracy($result['accuracy']);
                $brewery = $this->em->getRepository('App:Brewery')
                                    ->find($result['brewery_id']);
                if($brewery !== NULL) {
                    $brewery->addGeocode($geocode);
                }
                $this->em->persist($geocode);
            }
        }
        $this->em->flush();
        return $this;
    }

    /**
     * @return CSVLoader
     * @throws Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function loadBeers(): self
    {
        $results = $this->readCsv('App:Beer');
        foreach ($results as $result) {
            if (!$this->existsInDatabase('App:Beer', $result['id'])) {
                $beer = new Beer();
                $beer->setId($result['id'])
                     ->setName($result['name'])
                     ->setABV($result['abv'])
                     ->setIBU($result['ibu'])
                     ->setSRM($result['srm'])
                     ->setUPC($result['upc'])
                     ->setDescription($result['descript'])
                     ->setLastModification(new \DateTime($result['last_mod']));
                $category = $this->em->getRepository('App:Category')
                                     ->find($result['cat_id']);
                $brewery = $this->em->getRepository('App:Brewery')
                                    ->find($result['brewery_id']);
                $style = $this->em->getRepository('App:Style')
                                  ->find($result['style_id']);
                if($category !== NULL) {
                    $category->addBeer($beer);
                }
                if($brewery !== NULL) {
                    $brewery->addBeer($beer);
                }
                if($style !== NULL) {
                    $style->addBeer($beer);
                }
                $this->em->persist($beer);
            }
        }
        $this->em->flush();
        return $this;
    }

    private function existsInDatabase(string $className, int $id)
    {
        return $this->em->getRepository($className)
                        ->find($id);
    }
}