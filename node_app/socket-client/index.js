const ioclient = require("socket.io-client");
const { constants } = require('./constants');

const socketClient = ioclient(process.env.SOCKET_IO_SERVER, {
    path: '/web-messenger',
    query: {
        SECRET_KEY: process.env.AUTHORIZED_SOCKET_CLIENT_SECRET_KEY,
        CLIENT_TYPE: "API"
    }
});

module.exports = {
    socket: function (bot) {
        socketClient.on("connect", function () {
            console.debug('socket connected');
        });

        socketClient.on(constants.CHAT_MESSAGE_NODE, payload => {
            console.debug(`socket message received`);
            bot.processMessageEvent(payload.entry[0].messaging[0], constants.CLIENT_TYPES.WEB);
        });

        socketClient.on('disconnect', function () {
            console.debug('socket disconnected');
        });
    },

    sendMessage: function (payload) {
        console.debug(`Sending reply to user`);
        console.debug(JSON.stringify(payload, null, 4));
        socketClient.emit(constants.CHAT_MESSAGE_SERVER, payload);
    }
}
