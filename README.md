INSTRUCTIONS

----------------------------------------

import the sql file to a db named integ

edit .env in backend folder depending on your database

----------------------------------------

open terminal

cd integ-main

cd frontend

cd TheraPath

npm install

npm run dev

----------------------------------------

open 2nd terminal

cd integ-main

cd backend

composer install

php artisan storage:link

php artisan queue:work (if pause go new terminal and skip this part)

php artisan serve


----------------------------------------

user account

email: test@gmail.com

password: password

therapist account

email: willie.revillame@example.com

password: password
