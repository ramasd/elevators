# elevators
Elevators System

## How to start

- Clone the project to a local machine:
>`git clone https://github.com/ramasd/elevators.git`

- Go to project directory:
>`cd elevators`

- Rename *.env.example* to *.env*:
>`mv src\.env.example src\.env`\
or\
>`move src\.env.example src\.env`

- run commands (need to have *Docker*):
>`docker-compose up -d server`\
>`docker-compose run composer install`\
>`docker-compose run artisan key:generate`


- type in your browser:
>`http://localhost:8000`
