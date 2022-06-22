# e-commerce

## Setup

- Install dependencies

```shell
$ composer install
```

- Copy `.env.example` into a `.env` file and modify the database connection

```txt
DATABASE_URL="mysql://username:password@127.0.0.1:3306/db_name"
```

- Generate a base64 key and paste in the `.env` file

```shell
$ openssl rand -base64 32
PzpTZodk1E8GiIEvn/LDLot3hfdF3CkZX75kEswmarI=
```

```txt
JWT_ENCODER=PzpTZodk1E8GiIEvn/LDLot3hfdF3CkZX75kEswmarI=
```

- Generate a random string (at least 32 characters) and paste in the `.env` file

```txt
JWT_SECRET=HXzEA&andcPJY2=qn@Nn%n3y3xMZgYRA
```

- Generate the database and run migrations

```shell
$ php bin/console doctrine:database:create
$ php bin/console make:migration
$ php bin/console doctrine:migrations:migrate
```

- If you wish to have an already populated database, you can run

```shell
$ php bin/console doctrine:fixtures:load
```