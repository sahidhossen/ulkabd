/**
 * Created by tmukammel on 11/5/17.
 */

'use strict';

const redisKeyStore = require("redis");

const RedisClient = (function () {

    const client = redisKeyStore.createClient();

    client.on("error", function (err) {
        console.debug("Redis client error: " + err);
    });

    client.on("connect", function (err) {
        console.debug("Redis client created successfully");
    });

    return client;
})();

module.exports = RedisClient;
