/**
 * Created by tmukammel on 11/5/17.
 * Create Model: sequelize-auto -o "./node_app/app/user/" -d ulkabot -h localhost -u root -p 3306 -e mysql -t end_users
 */


const Sequelize = require('sequelize');
const moment    = require('moment');

const dbConfig = {
    host: process.env.DB_HOST,
    dialect: process.env.DB_CONNECTION,
    database: process.env.DB_DATABASE,
    user: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
    port: process.env.DB_PORT
}

const MySqlSequelize = (function() {
    this.sequelize = new Sequelize(dbConfig.database, dbConfig.user, dbConfig.password, {
        host: dbConfig.host,
        dialect: dbConfig.dialect,
        port: dbConfig.port,
        pool: {
            max: 100,
            min: 0,
            acquire: 30000,
            idle: 10000
        },
        define: {
            timestamps: false // true by default
        },
        logging: false
    });

    this.sequelize
        .authenticate()
        .then(() => {
            console.debug('Database connection has been established successfully.');
        })
        .catch(err => {
            console.error('Unable to connect to the database:', err);
        });

    this.sequelize.timestamp = function() {
        return moment(Date.now()).format('YYYY-MM-DD HH:mm:ss');
    };

    return this.sequelize;
})();

module.exports = MySqlSequelize;
