'use strict';

require('dotenv').config();

// Express HTTP server
const express = require('express');
const bodyParser = require('body-parser');

const REST_PORT = process.env.NODE_SERVER_PORT;

const app = express();
app.use(bodyParser.text({ type: 'application/json' }));

if (process.env.APP_ENV == 'production') {
    var https = require('https'),
        fs = require('fs');

    var options = {
        key: fs.readFileSync('/etc/letsencrypt/live/usha.ulkabd.com/privkey.pem'),
        cert: fs.readFileSync('/etc/letsencrypt/live/usha.ulkabd.com/fullchain.pem')
    };

    var server = https.createServer(options, app);
}
else server = require("http").Server(app);

server.listen(REST_PORT, () => {
    // Connect to database
    require('./node_app/resource/MySqlSequelize');
    
    // Expose APIs
    app.use(require('./node_app/api'));

    console.debug('Rest service ready on port ' + REST_PORT);
});

// Socket.io Server
let io = require("socket.io")(server);

// Redis server for php event queueing
let Redis = require("ioredis");
let redis = new Redis();

redis.psubscribe('*');

redis.on('pmessage', function (subscribed, channel, message) {
    message = JSON.parse(message);
    console.debug(channel, message.event + " : " + subscribed);
    io.emit(message.event, channel, message.data);
});

// Socket.io Client connect to socket server
const facebookBot = require('./node_app/app/user/agent/FacebookBot.js');
const socketClient = require('./node_app/socket-client');
socketClient.socket(facebookBot);
