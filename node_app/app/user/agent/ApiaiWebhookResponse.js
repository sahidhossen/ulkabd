/**
 * Created by tmukammel on 11/2/17.
 */

'use strict';

const JSONbig = require('json-bigint');

const redisClient = require('../../../resource/RedisClient.js');

class ApiaiWebhookResponse {
    constructor() {
        
    }

    responseWithRequest(req, res) {

        try {
            const data = JSONbig.parse(req.body);
            console.debug("Apiai request: ", data);

            let timeout = 300;

            let defaultSpeech = data.result.fulfillment.speech ?
                // Response for 'Cancel' text
                data.result.fulfillment.speech : 'Ok cancelled, but I can help you find what you need, just ask.';

            var defaultResponse = {
                speech: '',
                displayText: '',
                source: 'https://usha.ulkabd.com',
                data: {
                    facebook: {
                        text: defaultSpeech
                    }
                }
            };

            let senderId = data.originalRequest.data.sender.id;
            let recipientId = data.originalRequest.data.recipient.id;





            return res.status(200).json(defaultResponse);

        } catch (err) {
            return res.status(400).json({
                status: "error",
                error: err
            });
        }
    }
}

module.exports = new ApiaiWebhookResponse();
