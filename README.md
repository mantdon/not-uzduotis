## Project setup.

* Clone the repository via\
`git clone https://github.com/mantdon/not-uzduotis` 
* Install the dependencies\
`composer install`\
`yarn install`
#### Database setup
* Copy the contents of *.evn.dist* file to *.env* file
* In *.env* file configure the database connection\
`DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name`
* Create the database with\
`php bin/console doctrine:database:create`\
`php bin/console doctrine:migrations:migrate`
* Populate the database\
`php bin/console app:csv:import`

## Running tests
* Copy the contents of *phpunit.xml.dist* to *phpunit.xml*
* In *phpunit.xml* file configure the database connection\
`<env name="DATABASE_URL" value="mysql://db_user:db_password@127.0.0.1:3306/db_name"/>`
* Run all tests using\
`php bin/phpunit`

## Executing the program
After the project has been setup, the program can be executed using the `php bin/console app:run` command.
#### Options
Options are provided in the following format: `[--fullname, -shortname]` - effect - `(default_value)`  | `[valid,values]`(if a value is accepted)
* `[--lat, -l]` - the latitude coordinate (in degrees) of the starting location - `(51.355468)` | `[0 <= val <= 90]`
* `[--lon, -L]` - the longitude coordinate (in degrees) of the starting location - `(11.10079)` | `[0 <= val <= 180]`
* `[--maxdist, -D]` - the distance (in kilometers) limit for the route - `(2000)` | `[val > 0]`
* `[--mode, -M]` - the next location selection mode (more details below) - `(clst)` | `[val = clst || val=mstb]`
* `[--distdelta, -d]` -  The maximum distance (in kilometers) away from the closest location, to consider
 picking another location if it has more uncollected beer types. - `(90)` | `[val > 0]`
* `[--srchrad, -r]` - The radius (in degrees) to look for potential next location in. - `(5)` | `[0 <= val <= 90]`
* `[--printbeers, -p]` - prints the names of all beers collected during the trip.
* `[--printstyles, -P]` - prints the styles of all beers collected during the trip.\

If any of the options is not specified default value will be used for it.
#### Next location selection modes
The program has 2 modes of next location selection:
* `--mode clst` - Closest Brewery
* `--mode mstb` - Brewery with most beer types(names)\

Given that the program is at location *x* and there are 7 locations that were found withing the `--srchrad` 
and distances to these locations from *x* are stored in an array *distances[x][]*, where *distances[x][]* 
is an array mapped by *[next_location_id => distance_to_it_from_x]* then...
##### Closest brewery mode 
the next location will always be *min_value(distances[x])*.
##### Brewery with most beer mode
the beer types will be checked in locations up to `--distdelta` away from the closest location *min_value(distances[x])*,
location with most (uncollected) beer types will then be chosen as the next location.
### Execution examples
##### Closest brewery mode
![Alt text](/execution_examples/mode-clst.PNG?raw=true "Closest brewery")
##### Brewery with most beer mode
![Alt text](/execution_examples/mode-mstb.PNG?raw=true "Most beer")