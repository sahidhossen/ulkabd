'use strict'

const express = require('express');
const router = express.Router();
const JSONbig = require('json-bigint');
const facebookBot = require('../app/user/agent/FacebookBot');

const FB_VERIFY_TOKEN = 'WeAreUlka#0101';
const FACEBOOK_LOCATION = "FACEBOOK_LOCATION";
const FACEBOOK_WELCOME = "FACEBOOK_WELCOME";
const FACEBOOK_IMAGE = "FACEBOOK_IMAGE";
const FACEBOOK_VIDEO = "FACEBOOK_VIDEO";
const FACEBOOK_FILE = "FACEBOOK_FILE";

router.get('/webhook/', (req, res) => {
    if (req.query['hub.verify_token'] === FB_VERIFY_TOKEN) {
        res.send(req.query['hub.challenge']);

        //setTimeout(() => {
        //    facebookBot.doSubscribeRequest();
        //}, 3000);
    } else {
        res.send('Error, wrong validation token');
    }
});

router.post('/webhook/', (req, res) => {
    try {
        const data = JSONbig.parse(req.body);

        //console.debug('Webhook Request: ', data);

        if (data.entry) {
            let entries = data.entry;
            entries.forEach((entry) => {
                let messaging_events = entry.messaging;
                if (messaging_events) {
                    messaging_events.forEach((event) => {

                        //console.debug("\nEvent:\n-----------------\n", event, '\n');

                        if (event.message && !event.message.is_echo) {

                            if (event.message.attachments) {
                                //console.debug("\nAttachments:\n-----------------\n", event.message.attachments, '\n');

                                let locations = event.message.attachments.filter(a => a.type === "location");
                                event.message.attachments = event.message.attachments.filter(a => a.type !== "location");

                                if (locations.length > 0) {
                                    locations.forEach(l => {
                                        let locationEvent = {
                                            sender: event.sender,
                                            recipient: event.recipient,
                                            postback: {
                                                payload: FACEBOOK_LOCATION,
                                                data: l.payload.coordinates
                                            }
                                        };

                                        facebookBot.processFacebookEvent(locationEvent);
                                    });
                                }

                                let images = event.message.attachments.filter(a => a.type === "image");
                                event.message.attachments = event.message.attachments.filter(a => a.type !== "image");
                                if (images.length > 0) {
                                    images.forEach(i => {
                                        let imageEvent = {
                                            sender: event.sender,
                                            recipient: event.recipient,
                                            postback: {
                                                payload: FACEBOOK_IMAGE,
                                                data: i.payload
                                            }
                                        };

                                        facebookBot.processFacebookEvent(imageEvent);
                                    });
                                }

                                let videos = event.message.attachments.filter(a => a.type === "video");
                                event.message.attachments = event.message.attachments.filter(a => a.type !== "video");
                                if (videos.length > 0) {
                                    videos.forEach(i => {
                                        let videoEvent = {
                                            sender: event.sender,
                                            recipient: event.recipient,
                                            postback: {
                                                payload: FACEBOOK_VIDEO,
                                                data: i.payload
                                            }
                                        };
                                        facebookBot.processFacebookEvent(videoEvent);
                                    });
                                }

                                let file = event.message.attachments.filter(a => a.type === "file");
                                event.message.attachments = event.message.attachments.filter(a => a.type !== "file");
                                if (file.length > 0) {
                                    file.forEach(i => {
                                        let fileEvent = {
                                            sender: event.sender,
                                            recipient: event.recipient,
                                            postback: {
                                                payload: FACEBOOK_FILE,
                                                data: i.payload
                                            }
                                        };
                                        facebookBot.processFacebookEvent(fileEvent);
                                    });
                                }
                            }

                            facebookBot.processMessageEvent(event);
                        }
                        else if (event.postback && event.postback.payload) {
                            if (event.postback.payload === FACEBOOK_WELCOME) {
                                facebookBot.processFacebookEvent(event);
                            } else {
                                facebookBot.processMessageEvent(event);
                            }
                        }
                        else if (event.app_roles) {
                            facebookBot.processFBWebhookEvent(event);
                        }
                    });
                }
            });
        }

        return res.status(200).json({
            status: "ok"
        });
    } catch (err) {
        return res.status(400).json({
            status: "error",
            error: err
        });
    }

});

module.exports = router;

// TODO: Implement node based 'apiaiWebhookResponse' later
//const apiaiWebhookResponse  = require('./node_app/app/user/agent/ApiaiWebhookResponse.js');
//router.post('/apiaiwebhook/', (req, res) => {
//    return apiaiWebhookResponse.responseWithRequest(req, res);
//});