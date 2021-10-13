# USHA | Unified Services for Human Assistance

> ## ➤ About
    Usha (Unified Services for Human Assistance) is a Chatbot Platform with unique Hierarchical Intent Flow chatbot design.

> ## ➤ Setting Up Project

> ### Installation of Prerequisites
* git
    + Mac:  
    `$ brew install git`  
    *(Install `Homebrew` if needed from [here](https://brew.sh))*  
    + Linux:  
    `$ sudo apt update`  
    `$ sudo apt install git`
* mysql
    + Mac:  
    `$ brew install mysql`; and follow instructions  
    You can use [Sequel Pro](https://sequelpro.com) as mysql client
    + Linux:  
    `$ sudo apt-get install mysql-server`; and follow instructions  
    *Resource: [Install MySQL Server on the Ubuntu operating system](https://support.rackspace.com/how-to/install-mysql-server-on-the-ubuntu-operating-system/)*  
    You can use [DBeaver](https://dbeaver.io) as mysql client  
    + Log into mysql console  
    `$ mysql -u root`  
    using password -  
    `$ mysql -u root -p`  
    + Troubleshoot connecting to db  
    If you are facing problem connecting to mysql db through artisan or db client reset root user password -  
    `mysql> ALTER USER root@localhost IDENTIFIED WITH mysql_native_password BY <password>;`  
    `mysql> FLUSH PRIVILEGES;`  
* node
    + Mac:  
    `$ brew install node`
    + Linux:  
    `$ sudo apt install nodejs`  
    *Resource: [How To Install Node.js on Ubuntu](https://www.digitalocean.com/community/tutorials/how-to-install-node-js-on-ubuntu-18-04)*
* redis
    + Mac:  
    `$ brew install redis`
    + Linux:  
    `$ sudo apt install redis-server`  
    *Resource: [Redis on Ubuntu](https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-redis-on-ubuntu-18-04)*
* composer
    + Install composer from [getcomposer.org](https://getcomposer.org/download/)  
    `$ sudo mv composer.phar /usr/local/bin/composer`
* Other linux dependencies
    + `sudo apt-get install libpng-dev`
    + `sudo apt-get install build-essential`
    + `sudo apt-get install php7.x-mbstring`
    + `sudo apt-get install curl`
    + `sudo apt-get install php7.x-xml`
    + `sudo apt install php-mysql`

> ### Clone repository
* `$ git clone <repo>`
* `$ git checkout development`

> ### Environment configuration
* .env
    + Create `.env` file  
    `$ cp .env.example ./.env`  
    + Generate configuration key in `.env` file  
    `$ php artisan key:generate`
    + App configuration  
        ```bash
        APP_ENV=local | staging | production
        APP_DEBUG=true | false
        APP_LOG_LEVEL=debug | info | notice | warning | error | critical | alert | emergency
        APP_URL=http://localhost
        ```
    + DB Connection configuration  
        ```
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=
        DB_DATABASE=
        DB_USERNAME=
        DB_PASSWORD=
        ```
    + Broadcast dirver configuration  
        ```
        BROADCAST_DRIVER=redis
        CACHE_DRIVER=array
        SESSION_DRIVER=file
        QUEUE_DRIVER=redis
        ```
    + Cache server configuration  
        ```
        REDIS_HOST=127.0.0.1
        REDIS_PASSWORD=null
        REDIS_PORT=6379
        ```

> ### Installation of Dependencies
* php
    + `$ composer update`
* node
    + `$ yarn install`

> ### Database configuration and migration
* Run mysql server
    * Mac:  
    `$ mysql.server start`
    * Linux:  
    `$ sudo systemctl start mysql` or  
    `$ sudo service mysql start`
* Create Database  
After logging in to `mysql` [console](#installation-of-prerequisites)  
`mysql> create database <db-name>`
* Migration  
`$ php artisan migrate:refresh --seed`  
`$ php artisan passport:install`  
`$ sudo chmod 600 storage/oauth-public.key`

> ## ➤ Running Project Locally

> ### Starting servers
* mysql server
    + Mac:  
    `$ mysql.server start`
    + Linux:  
    `$ sudo systemctl start mysql` or  
    `$ sudo service mysql start`
* redis server  
    `$ redis-server`  
* Redis command line interface (cli)
    + Installation  
    `$ npm install -g redis-cli`  
    *npm: [redis-cli](https://www.npmjs.com/package/redis-cli)*
    + Run redis-cli  
    `$ redis-cli`  
    *Resource: [The Redis command line interface](https://redis.io/topics/rediscli)*
* php artisan queue worker
    + start  
    `$ php artisan queue:work --queue=MessengerUpdater,CSVReader,APIAIAgentUpdater,TransferProducts,default --tries=2`
    + restart signal  
    `$ php artisan queue:restart`  
    *Note: you have to run `queue:work` (start) command again*
* node server
    `$ yarn run node`
* react (webpack) build and watch
    `$ yarn watch`
* PHP server
    `$ php artisan serve` or  
    `$ php -S localhost:<port>`  

> ### Platform Homepage
* if PHP server is running on port `8000` Go to [`http://localhost:8000`](http://localhost:8000)

> ### Connecting to [dialogflow](https://dialogflow.cloud.google.com) agent
1. Login to your dialogflow console using your google account
2. From the left menu [Create new agent](https://dialogflow.cloud.google.com/#/newAgent)
    - Provide agent name without space
3. Get the API keys
    + Click the gear icon on the left menu next to the agent name and copy
        - Client access token
        - Developer access token
4. Put the API keys in the only seeded agent (Ulka Bot) in the db `agents` table
    + `apiai_client_access_token`
    + `apiai_dev_access_token`
5. Set dialogflow webhook
    + Expose your locale PHP server (+ other servers if you are using ngrok) ([see Appendix](#Expose-multiple-ports-with-ngrok))  
    + set the webhook url  
    `https://<str>.ngrok.io/apiaiwebhook`  
    *Note: the url used is http(s)*

> ### Installing facebook app

*To be written...*

> ### Installing facebook page

*To be written...*

> ## ➤ Appendix

> ### Expose multiple ports with `ngrok`

* Default ngrok config file location  

    |   OS  |               Location              |
    |:-------|:-----------------------------------|
    |OS X    |`/Users/example/.ngrok2/ngrok.yml`  |
    |Linux   |`/home/example/.ngrok2/ngrok.yml`   |
    |Windows |`C:\Users\example\.ngrok2\ngrok.yml`|

* Example config file  
    ```bash  
    authtoken: <token>  
    tunnels:  
    usha-php:  
    proto: https  
    addr: 8000  
    usha-node:  
    proto: https  
    addr: 6001  
    ```  
    *Note: You can get your authentication token from your ngrok account dashboard*
* Expose servers
    + Start all  
    `$ ngrok start --all`
    + Start selected  
    `$ ngrok start <tunnel-name>`
