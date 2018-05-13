## Project setup.

* Clone the repository via\
`git clone https://github.com/mantdon/not-uzduotis` 
* Install the dependencies\
`composer install`\
`yarn install`
#### Database setup
* In .env file configure the database connection\
`DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name`
* Create the database with\
`php bin/console doctrine:database:create`\
`php bin/console doctrine:migrations:migrate`
* Populate the database\
`php bin/console csv:import`