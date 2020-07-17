# LearnHub - Angular Laravel Chain App Example

## Get Project Running

1.  Navigate to project root folder
2.  Copy `.env.example` and rename to `.env`. See dev environment file database configuration below
3.  Run `docker-compose up --build` add ahead `sudo` for ubuntu/linux
4.  Once the container is built, open a new terminal tab/window, and run `docker-compose exec app bash` add ahead `sudo` for ubuntu/linux
5.  Run `composer update`
5.  Run `npm install`
6.  Once this is complete, run `php artisan key:generate`
7.  Once this is complete, run `php artisan jwt:secret`
8.  Open a new terminal tab/window, and run `php artisan migrate`
9.  Navigate to http://localhost:7777

## Docker's command helpers

- Build\Rebuild of containers `docker-compose up --build` or `docker-compose up -d --build` add ahead `sudo` for ubuntu/linux
- Start of containers `docker-compose up` or `docker-compose up -d` add ahead `sudo` for ubuntu/linux
- Stop of containers `docker-compose down` add ahead `sudo` for ubuntu/linux
- Checking logs `docker-compose logs --tal 25`

###

