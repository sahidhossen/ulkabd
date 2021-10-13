/**
 * Created by tmukammel on 11/2/17.
 */

'use strict';

const apiai             = require('apiai');
const async             = require('async');
const Promise           = require('bluebird');
const request           = require('request');

const Agent = require('./agent.js');
const Product = require('./product.js');
const EndUser = require('./usha_consumer/end_user/end_user.js');
const Order = require('./usha_consumer/end_user/orders.js');
const socketClient = require('./../../../socket-client');

const FACEBOOK_LOCATION = "FACEBOOK_LOCATION";
const FACEBOOK_WELCOME = "FACEBOOK_WELCOME";
const FACEBOOK_IMAGE = "FACEBOOK_IMAGE";
const FACEBOOK_VIDEO = "FACEBOOK_VIDEO";
const FACEBOOK_FILE = "FACEBOOK_FILE";

class FacebookBot {

    constructor() {
        this.APIAI_ACCESS_TOKEN     = '';
        this.FB_PAGE_ACCESS_TOKEN   = '';
        this.APIAI_LANG             = 'en';
        this.FB_TEXT_LIMIT          = 640;
        this.messagesDelay          = 10;//200
        this.fbAPIVersion           = 'v3.1';
        this.fbInboxId              = '263902037430900';
        this.fbAppID                = "120759628496384";
    }

    doDataResponse(sender, facebookResponseData, platform = undefined) {
        return new Promise((resolve, reject) => {
            if (!Array.isArray(facebookResponseData)) {
                console.debug('Response as non-array formatted message');
                if(platform && platform === "web") {
                    socketClient.sendMessage({
                        senderId: sender, 
                        replies: [facebookResponseData]
                    });
                }
                else {
                    this.sendFBMessage(sender, facebookResponseData, this)
                    .then(() => {
                        facebookResponseData = null;
                        resolve();
                    })
                    .catch(err => {
                        console.error(err);
                        facebookResponseData = null;
                        reject(err);
                    });
                }
                
            } else {
                async.eachSeries(facebookResponseData, (facebookMessage, callback) => {
                    if (facebookMessage.sender_action) {
                        console.debug('Response as sender action');
                        this.sendFBSenderAction(sender, facebookMessage.sender_action, this)
                            .then(() => callback())
                            .catch(err => callback(err));
                    }
                    else {
                        console.debug('Response as array formatted message');
                        if(platform && platform === "web") {
                            socketClient.sendMessage({
                                senderId: sender, 
                                replies: facebookMessage
                            });
                        }
                        else {
                            this.sendFBMessage(sender, facebookMessage, this)
                                .then(() => callback())
                                .catch(err => callback(err));
                        }
                    }
                }, (err) => {
                    if (err) {
                        console.error(err);
                        facebookResponseData = null;
                        reject(err);
                    } else {
                        facebookResponseData = null;
                        resolve();
                    }
                });
            }
        });
    }

    doRichContentResponse(sender, messages, platform = undefined) {
        console.debug('Response as Rich Content');
        let facebookMessages = []; // array with result messages

        for (let messageIndex = 0; messageIndex < messages.length; messageIndex++) {
            let message = messages[messageIndex];

            switch (message.type) {
                //message.type 0 means text message
                case 0:
                    // speech: ["hi"]
                    // we have to get value from fulfillment.speech, because of here is raw speech
                    if (message.speech) {

                        let splittedText = this.splitResponse(message.speech);

                        splittedText.forEach(s => {
                            facebookMessages.push({text: s});
                        });
                    }

                    break;
                //message.type 1 means card message
                case 1: {
                    let carousel = [message];

                    for (messageIndex++; messageIndex < messages.length; messageIndex++) {
                        if (messages[messageIndex].type == 1) {
                            carousel.push(messages[messageIndex]);
                        } else {
                            messageIndex--;
                            break;
                        }
                    }

                    let facebookMessage = {};
                    carousel.forEach((c) => {
                        // buttons: [ {text: "hi", postback: "postback"} ], imageUrl: "", title: "", subtitle: ""

                        let card = {};

                        card.title = c.title;
                        card.image_url = c.imageUrl;
                        if (this.isDefined(c.subtitle)) {
                            card.subtitle = c.subtitle;
                        }
                        //If button is involved in.
                        if ('buttons' in c && c.buttons.length > 0) {
                            let buttons = [];
                            for (let buttonIndex = 0; buttonIndex < c.buttons.length; buttonIndex++) {
                                let button = c.buttons[buttonIndex];

                                if (button.text) {
                                    let postback = button.postback;
                                    if (!postback) {
                                        postback = button.text;
                                    }

                                    let buttonDescription = {
                                        title: button.text
                                    };

                                    if (postback.startsWith("http")) {
                                        buttonDescription.type = "web_url";
                                        buttonDescription.url = postback;
                                    } else {
                                        buttonDescription.type = "postback";
                                        buttonDescription.payload = postback;
                                    }

                                    buttons.push(buttonDescription);
                                }
                            }

                            if (buttons.length > 0) {
                                card.buttons = buttons;
                            }
                        }

                        if (!facebookMessage.attachment) {
                            facebookMessage.attachment = {type: "template"};
                        }

                        if (!facebookMessage.attachment.payload) {
                            facebookMessage.attachment.payload = {template_type: "generic", elements: []};
                        }

                        facebookMessage.attachment.payload.elements.push(card);
                    });

                    facebookMessages.push(facebookMessage);
                }

                    break;
                //message.type 2 means quick replies message
                case 2: {
                    if (message.replies && message.replies.length > 0) {
                        let facebookMessage = {};

                        facebookMessage.text = message.title ? message.title : 'You can choose:';
                        facebookMessage.quick_replies = [];

                        message.replies.forEach((r) => {
                            facebookMessage.quick_replies.push({
                                content_type: "text",
                                title: r,
                                payload: r
                            });
                        });

                        facebookMessages.push(facebookMessage);
                    }
                }

                    break;
                //message.type 3 means image message
                case 3:

                    if (message.imageUrl) {
                        let facebookMessage = {};

                        // "imageUrl": "http://example.com/image.jpg"
                        facebookMessage.attachment = {type: "image"};
                        facebookMessage.attachment.payload = {url: message.imageUrl};

                        facebookMessages.push(facebookMessage);
                    }

                    break;
                //message.type 4 means custom payload message
                case 4:
                    if (message.payload && message.payload.facebook) {
                        facebookMessages.push(message.payload.facebook);
                    }
                    break;

                default:
                    break;
            }
        }

        return new Promise((resolve, reject) => {
            if(platform && platform === "web") {
                resolve();

                socketClient.sendMessage({
                    senderId: sender, 
                    replies: facebookMessages
                });
            }else {
                async.eachSeries(facebookMessages, (msg, callback) => {
                        this.sendFBSenderAction(sender, "typing_on", this)
                            .then(() => this.sleep(this.messagesDelay))
                            .then(() => this.sendFBMessage(sender, msg, this))
                            .then(() => callback())
                            .catch(callback);
                    },
                    (err) => {
                        if (err) {
                            console.error(err);
                            facebookMessages = null;
                            messages = null;
                            reject(err);
                        } else {
                            facebookMessages = null;
                            messages = null;
                            resolve();
                        }
                    });
            }
        });

    }

    doTextResponse(sender, responseText, platform = undefined) {
        console.debug('Response as text message');
        console.debug({ platform });
        
        // facebook API limit for text length is 640,
        // so we must split message if needed
        let splittedText = this.splitResponse(responseText);

        return new Promise((resolve, reject) => {

            if(platform && platform === "web") {
                socketClient.sendMessage(splittedText);
            } else {
                async.eachSeries(splittedText, (textPart, callback) => {
                        this.sendFBMessage(sender, {text: textPart}, this)
                            .then(() => callback())
                            .catch(err => callback(err));
                    },
                    (err) => {
                        if (err) {
                            console.error(err);
                            splittedText = null;
                            reject(err);
                        } else {
                            splittedText = null;
                            resolve();
                        }
                    });
            }
        });
    }

    static getEventText(event) {
        if (event.message) {
            if (event.message.quick_reply && event.message.quick_reply.payload) {
                return event.message.quick_reply.payload;
            }

            if (event.message.text) {
                return event.message.text;
            }
        }

        if (event.postback && event.postback.payload) {
            return event.postback.payload;
        }

        return null;

    }

    processMessageEvent(event, platform = undefined) {
        console.debug("\nMessageEvent:\n-----------------\n", event, '\n');

        let sender = event.sender.id.toString();
        let senderName = (event.sender.name !== undefined) ? event.sender.name.toString() : undefined;
        let recipient = event.recipient.id.toString();
        let text = FacebookBot.getEventText(event);

        if (text) {
            console.debug("User Text:", text);

            Promise.coroutine(function* (fb_obj, recipient, sender, senderName = undefined, platform) {
                try {
                    let recipientData = yield Agent.getRecipientAgent(recipient, platform);
                    // console.debug("\nRECEPIENT:\n-----------------\n", recipientData, '\n');

                    let senderData = yield EndUser.getSenderEndUser(sender, recipientData.id, senderName, platform);
                    // console.debug("\nSENDER USER:\n-----------------\n ", senderData, '\n');

                    let session_id = senderData.session_id;
                    fb_obj.APIAI_ACCESS_TOKEN = recipientData.apiai_client_access_token;
                    fb_obj.FB_PAGE_ACCESS_TOKEN = recipientData.fb_access_token;

                    if (text.startsWith('PLOAD_ED-')) {
                        let product_id = text.replace('PLOAD_ED-', '');
                        console.debug("Product id for more detail: " + product_id);

                        fb_obj.sendFBSenderAction(sender, "typing_on", fb_obj)
                            .then(() => fb_obj.sleep(fb_obj.messagesDelay))
                            .then(() => Product.getProductDetail(product_id))
                            .then((message) => fb_obj.doDataResponse(sender, message))
                            .then(() => {
                                event = null;
                                text = null;
                                recipientData = null;
                                senderData = null;
                            })
                            .catch((error) => {
                                fb_obj.doTextResponse(sender, "Apologies! But I could not deliver more detail right now due to:" + error + ". Could you please try again in a moment.")
                                    .then(() => {
                                        event = null;
                                        text = null;
                                        recipientData = null;
                                        senderData = null;
                                    });
                            });
                        return;
                    }
                    else if (text.toLowerCase().startsWith('#order')) {
                        let orderCode = text.toLowerCase().replace('#order', '').replace(/\s/g,'').toUpperCase();
                        console.debug("Order code: " + orderCode);

                        fb_obj.doTextResponse(sender, "Searching your order...")
                            .then(() => fb_obj.sendFBSenderAction(sender, "typing_on", fb_obj))
                            .then(() => fb_obj.sleep(fb_obj.messagesDelay))
                            .then(() => Order.getOrderStatusFBFormattedMessageByCode(orderCode, senderData.id))
                            .then(async (messages) => {
                                for (let index = 0; index < messages.length; index++) {
                                    const message = messages[index];
                                    
                                    if (typeof message === 'string') await fb_obj.doTextResponse(sender, message)
                                    else await fb_obj.doDataResponse(sender, message)
                                    
                                    if (index < (messages.length - 1)) {
                                        await fb_obj.sendFBSenderAction(sender, "typing_on", fb_obj)
                                        await fb_obj.sleep(fb_obj.messagesDelay)
                                    }
                                }

                                return Promise.resolve();
                            })
                            .then(() => {
                                event = null;
                                text = null;
                                recipientData = null;
                                senderData = null;
                            })
                            .catch((error) => {
                                fb_obj.doTextResponse(sender, "I could not find an order with Order code: " + orderCode + ". Please recheck!")
                                    .then(() => {
                                        event = null;
                                        text = null;
                                        recipientData = null;
                                        senderData = null;
                                    });
                            });
                        return;
                    }

                    switch (text) {
                        case 'PAYLOAD_HA_ENABLE': {

                            if (recipientData.fb_receiver_role == 1) {
                                fb_obj.sendFBSenderAction(sender, "typing_on", fb_obj)
                                    .then(() => fb_obj.passThreadControl(
                                        sender,
                                        senderData.first_name + " "+ senderData.last_name + " wants to talk to a human assistant.",
                                        fb_obj
                                    ))
                                    .then(() => {
                                        let message = "I am now delivering your messages to "
                                            + recipientData.fb_page_name
                                            +  " Inbox. Please leave your message. Someone should respond to your queries very soon!" + require('os').EOL + "You can reactivate me from 'Menu > Talk to Usha AI'";
                                        fb_obj.doTextResponse(sender, message);
                                    })
                                    .then(() => {
                                        event = null;
                                        text = null;
                                        recipientData = null;
                                        senderData = null;
                                    })
                                    .catch((error) => {
                                        fb_obj.doTextResponse(sender, "It is beyond my abilities now, someone did not gave me the control!")
                                            .then(() => {
                                                event = null;
                                                text = null;
                                                recipientData = null;
                                                senderData = null;
                                            });
                                    });
                            }
                            else {
                                let message = recipientData.fb_page_name
                                    +  " did not gave me the permission to deliver messages to Inbox! Apologies for the inconvenience.";
                                fb_obj.doTextResponse(sender, message)
                                    .then(() => {
                                        event = null;
                                        text = null;
                                        recipientData = null;
                                        senderData = null;
                                    });
                            }


                        }
                            break;

                        case 'PAYLOAD_HA_DISABLE': {
                            if (recipientData.fb_receiver_role == 1) {
                                fb_obj.sendFBSenderAction(sender, "typing_on", fb_obj)
                                    .then(() => fb_obj.takeThreadControl(
                                        sender,
                                        senderData.first_name + " "+ senderData.last_name + " wants to talk to Usha AI.",
                                        fb_obj
                                    ))
                                    .then(() => {
                                        let message = "Dear " + senderData.first_name + " "+ senderData.last_name + "! I am here with you :)";
                                        fb_obj.doTextResponse(sender, message);
                                    })
                                    .then(() => {
                                        senderData.fallback_response_count = 0;
                                        EndUser.updateSenderEndUserInRedisCache(senderData.agent_scoped_id, senderData);

                                        event = null;
                                        text = null;
                                        recipientData = null;
                                        senderData = null;
                                    })
                                    .catch((error) => {
                                        fb_obj.doTextResponse(sender, "Could not process your request!")
                                            .then(() => {
                                                event = null;
                                                text = null;
                                                recipientData = null;
                                                senderData = null;
                                            });
                                    });
                            }
                            else {
                                let message = recipientData.fb_page_name
                                    +  " did not gave me the permission to take control of our conversations!";
                                fb_obj.doTextResponse(sender, message)
                                    .then(() => {
                                        event = null;
                                        text = null;
                                        recipientData = null;
                                        senderData = null;
                                    });
                            }

                        }
                            break;

                        default: {
                            let apiaiService = apiai(fb_obj.APIAI_ACCESS_TOKEN, {language: fb_obj.APIAI_LANG, requestSource: "fb"});
                            //send user's text to api.ai service
                            let apiaiRequest = apiaiService.textRequest(
                                text,
                                {
                                    sessionId: session_id,
                                    originalRequest: {
                                        data: event,
                                        source: platform
                                    }
                                }
                            );

                            
                            new Promise((resolve, reject) => {
                                if(platform === undefined ) {
                                    fb_obj.sendFBSenderAction(sender, "typing_on", fb_obj)
                                }
                                resolve();
                            })
                            .then(() => fb_obj.doApiAiRequest(apiaiRequest, recipientData, senderData, platform))
                            .then(() => {
                                event = null;
                                text = null;
                                recipientData = null;
                                senderData = null;
                            });
                            
                        }
                            break;
                    }
                } catch (e) {
                    console.debug(e);
                }
            })(this, recipient, sender, senderName, platform);
        }
    }

    static getFacebookEvent(event) {
        if (event.postback && event.postback.payload) {

            let payload = event.postback.payload;

            //console.debug("Event event.postback:", event.postback);

            switch (payload) {
                case FACEBOOK_WELCOME:
                    return {name: FACEBOOK_WELCOME};

                case FACEBOOK_LOCATION:
                    return {name: FACEBOOK_LOCATION};

                case FACEBOOK_IMAGE:
                    return {name: FACEBOOK_IMAGE};

                case FACEBOOK_VIDEO:
                    return {name: FACEBOOK_VIDEO};

                case FACEBOOK_FILE:
                    return {name: FACEBOOK_FILE};//, data: event.postback.data
            }

            return null;
        }

        return null;
    }

    processFacebookEvent(event) {
        //console.debug("\nFacebookEvent:\n-----------------\n", event, '\n');

        let sender = event.sender.id.toString();
        let recipient = event.recipient.id.toString();
        let eventObject = FacebookBot.getFacebookEvent(event);

        if (eventObject) {
            console.debug("User eventObject:", eventObject);

            Promise.coroutine(function* (fb_obj, recipient, sender) {
                try {
                    let recipientData = yield Agent.getRecipientAgent(recipient);
                    //console.debug("\nRECEPIENT:\n-----------------\n", recipientData, '\n');

                    let senderData = yield EndUser.getSenderEndUser(sender, recipientData.id);
                    //console.debug("\nSENDER USER:\n-----------------\n ", senderData, '\n');

                    let session_id = senderData.session_id;
                    fb_obj.APIAI_ACCESS_TOKEN = recipientData.apiai_client_access_token;
                    fb_obj.FB_PAGE_ACCESS_TOKEN = recipientData.fb_access_token;

                    switch (eventObject.name) {
                        case FACEBOOK_LOCATION: {

                            fb_obj.sendFBSenderAction(sender, "typing_on", fb_obj)
                                .then(() => {
                                    let message = "Thanks for sharing your location!";
                                    fb_obj.doTextResponse(sender, message);
                                })
                                .then(() => {
                                    event = null;
                                    eventObject = null;
                                    recipientData = null;
                                    senderData = null;
                                })
                                .catch((error) => {
                                    fb_obj.doTextResponse(sender, "Apologies, something went wrong with the network!")
                                        .then(() => {
                                            event = null;
                                            eventObject = null;
                                            recipientData = null;
                                            senderData = null;
                                        });
                                });
                        }
                            break;

                        case FACEBOOK_IMAGE: {
                            if (recipientData.fb_receiver_role == 1) {
                                fb_obj.sendFBSenderAction(sender, "typing_on", fb_obj)
                                    //.then(() => fb_obj.passThreadControl(
                                    //    sender,
                                    //    senderData.first_name + " "+ senderData.last_name + " sent and image.",
                                    //    fb_obj
                                    //))
                                    .then(() => {

                                        let message = {
                                            attachment: {
                                                type: "template",
                                                payload: {
                                                    template_type: "button",
                                                    text: "Please submit the CODE!" + require('os').EOL + "I see you have sent an image attachment, if you are looking for a particular entity (product/service) please send me the entity code. Or,",
                                                    buttons: [
                                                        {
                                                            type: "postback",
                                                            title: "Talk to Human",
                                                            payload: "PAYLOAD_HA_ENABLE"
                                                        }
                                                    ]
                                                }
                                            }
                                        };

                                        fb_obj.doDataResponse(sender, message)
                                            .then(() => {
                                                console.debug('DataResponse Messages sent for image attachment');
                                                message = null;
                                            });

                                    })
                                    .then(() => {
                                        event = null;
                                        eventObject = null;
                                        recipientData = null;
                                        senderData = null;
                                    })
                                    .catch((error) => {
                                        fb_obj.doTextResponse(sender, "Hmmm... I am still learning to process images!")
                                            .then(() => {
                                                event = null;
                                                eventObject = null;
                                                recipientData = null;
                                                senderData = null;
                                            });
                                    });
                            }
                            else {
                                fb_obj.doTextResponse(sender, "Hmmm... I am still learning to process images!")
                                    .then(() => {
                                        event = null;
                                        eventObject = null;
                                        recipientData = null;
                                        senderData = null;
                                    });
                            }
                        }
                            break;

                        case FACEBOOK_VIDEO: {
                            if (recipientData.fb_receiver_role == 1) {
                                fb_obj.sendFBSenderAction(sender, "typing_on", fb_obj)
                                    .then(() => fb_obj.passThreadControl(
                                        sender,
                                        senderData.first_name + " "+ senderData.last_name + " wants to talk to a human assistant.",
                                        fb_obj
                                    ))
                                    .then(() => {
                                        let message = "While I am learning to understand videos, I have delivered your attachment(s) to "
                                            + recipientData.fb_page_name
                                            +  " Inbox. Someone should respond to your queries very soon!" + require('os').EOL + "You can reactivate me from 'Menu > Talk to Usha AI'";
                                        fb_obj.doTextResponse(sender, message);
                                    })
                                    .then(() => {
                                        event = null;
                                        eventObject = null;
                                        recipientData = null;
                                        senderData = null;
                                    })
                                    .catch((error) => {
                                        fb_obj.doTextResponse(sender, "Hmmm... I am still learning to process videos!")
                                            .then(() => {
                                                event = null;
                                                eventObject = null;
                                                recipientData = null;
                                                senderData = null;
                                            });
                                    });
                            }
                            else {
                                fb_obj.doTextResponse(sender, "Hmmm... I am still learning to process videos!")
                                    .then(() => {
                                        event = null;
                                        eventObject = null;
                                        recipientData = null;
                                        senderData = null;
                                    });
                            }
                        }
                            break;

                        case FACEBOOK_FILE: {
                            if (recipientData.fb_receiver_role == 1) {
                                fb_obj.sendFBSenderAction(sender, "typing_on", fb_obj)
                                    .then(() => fb_obj.passThreadControl(
                                        sender,
                                        senderData.first_name + " "+ senderData.last_name + " wants to talk to a human assistant.",
                                        fb_obj
                                    ))
                                    .then(() => {
                                        let message = "While I am learning to understand file attachments, I have delivered your attachment(s) to "
                                            + recipientData.fb_page_name
                                            +  " Inbox. Someone should respond to your queries very soon!" + require('os').EOL + "You can reactivate me from 'Menu > Talk to Usha AI'";
                                        fb_obj.doTextResponse(sender, message);
                                    })
                                    .then(() => {
                                        event = null;
                                        eventObject = null;
                                        recipientData = null;
                                        senderData = null;
                                    })
                                    .catch((error) => {
                                        fb_obj.doTextResponse(sender, "Hmmm... I am still learning to process file attachments!")
                                            .then(() => {
                                                event = null;
                                                eventObject = null;
                                                recipientData = null;
                                                senderData = null;
                                            });
                                    });
                            }
                            else {
                                fb_obj.doTextResponse(sender, "Hmmm... I am still learning to process file attachments!")
                                    .then(() => {
                                        event = null;
                                        eventObject = null;
                                        recipientData = null;
                                        senderData = null;
                                    });
                            }
                        }
                            break;

                        default: {
                            let apiaiService = apiai(fb_obj.APIAI_ACCESS_TOKEN, {language: fb_obj.APIAI_LANG, requestSource: "fb"});

                            let apiaiRequest = apiaiService.eventRequest(
                                eventObject,
                                {
                                    sessionId: session_id,
                                    originalRequest: {
                                        data: event,
                                        source: "facebook"
                                    }
                                }
                            );

                            fb_obj.sendFBSenderAction(sender, "typing_on", fb_obj)
                                .then(() => fb_obj.doApiAiRequest(apiaiRequest, recipientData, senderData))
                                .then(() => {
                                    event = null;
                                    eventObject = null;
                                    recipientData = null;
                                    senderData = null;
                                });
                        }
                            break;
                    }
                } catch (e) {
                    console.debug(e);
                }
            })(this, recipient, sender);
        }
    }

    processFBWebhookEvent(event) {
        //console.debug("\nFBWebhookEvent:\n-----------------\n", event, '\n');

        if (event.app_roles && event.app_roles[this.fbAppID]) {
            const recipient = event.recipient.id.toString();

            Agent.assignReceiverRole(
                recipient,
                event.app_roles[this.fbAppID].includes('primary_receiver') ? 1 : 0
            ).then((agent) => {
                //console.debug("\nAgent:\n-----------------\n", agent, '\n');
            }).catch((error) => console.debug(error));
        }
    }

    passThreadControlOnUnrecognizedQuery(response, agent, user) {
        return new Promise((resolve, reject) => {
            let action = response.result.action;

            if (action != null && action == 'input.unknown' && agent.configuration != null) {
                let config = JSON.parse(agent.configuration);

                //console.debug("Configuration: ", config);

                if (config.ai_response_tolerance && config.ai_response_tolerance >= 0) {
                    if (user.fallback_response_count != null && user.fallback_response_count >= config.ai_response_tolerance) {
                        this.sendFBSenderAction(user.agent_scoped_id, "typing_on", this)
                            .then(() => this.passThreadControl(
                                user.agent_scoped_id,
                                user.first_name + " "+ user.last_name + " is directed to inbox for unrecognized queries.",
                                this
                            ))
                            .then(() => {
                                let message = "I am sorry, as I did not understand you. I am delivering your messages to inbox. Someone should respond to you shortly." + require('os').EOL + "You can reactivate me from 'Menu > Talk to Usha AI'";
                                this.doTextResponse(user.agent_scoped_id, message);
                            })
                            .then(() => {
                                response = null;
                                agent = null;
                                user = null;
                                resolve(true);
                            })
                            .catch((error) => {
                                response = null;
                                agent = null;
                                user = null;
                                console.error(error);
                                reject(error);
                            });
                    }
                    else {
                        user.fallback_response_count += 1;
                        EndUser.updateSenderEndUserInRedisCache(user.agent_scoped_id, user);

                        response = null;
                        agent = null;
                        user = null;
                        resolve(false);
                    }
                }
                else {
                    response = null;
                    agent = null;
                    user = null;
                    resolve(false);
                }
            }
            else {
                user.fallback_response_count = 0;
                EndUser.updateSenderEndUserInRedisCache(user.agent_scoped_id, user);

                response = null;
                agent = null;
                user = null;
                resolve(false);
            }
        });
    }

    sendFacebookResponse(response, user, platform = undefined) {
        return new Promise((resolve, reject) => {
            let responseText = response.result.fulfillment.speech;
            let responseData = response.result.fulfillment.data;
            let responseMessages = response.result.fulfillment.messages;

            if (this.isDefined(responseData) && this.isDefined(responseData.facebook)) {
                let facebookResponseData = responseData.facebook;
                this.doDataResponse(user.agent_scoped_id, facebookResponseData, platform)
                    .then(() => {
                        console.debug('DataResponse Messages sent');
                        response = null;
                        responseText = null;
                        responseData = null;
                        responseMessages = null;
                        resolve();
                    });
            } else if (this.isDefined(responseMessages) && responseMessages.length > 0) {
                this.doRichContentResponse(user.agent_scoped_id, responseMessages, platform)
                    .then(() => {
                        console.debug('RichContent Messages sent');
                        response = null;
                        responseText = null;
                        responseData = null;
                        responseMessages = null;
                        resolve();
                    });
            }
            else if (this.isDefined(responseText)) {
                this.doTextResponse(user.agent_scoped_id, responseText, platform)
                    .then(() => {
                        console.debug('TextResponse Messages sent');
                        response = null;
                        responseText = null;
                        responseData = null;
                        responseMessages = null;
                        resolve();
                    });
            }
        });
    }

    doApiAiRequest(apiaiRequest, agent, user, platform) {
        return new Promise((resolve, reject) => {
            apiaiRequest.on('response', (response) => {
                //console.debug("api.ai response result: ", response.result);

                if (this.isDefined(response.result) && this.isDefined(response.result.fulfillment)) {

                    this.passThreadControlOnUnrecognizedQuery(response, agent, user)
                        .then((handled) => {
                            //console.debug("User: ", user);

                            if (handled == false) {
                                this.sendFacebookResponse(response, user, platform)
                                    .then(() => {
                                        response = null;
                                        agent = null;
                                        user = null;
                                        resolve();
                                    })
                            }
                            else {
                                resolve();
                            }
                        })
                        .catch((error) => {
                            //console.debug("User: ", user);

                            this.sendFacebookResponse(response, user, platform)
                                .then(() => {
                                    response = null;
                                    agent = null;
                                    user = null;
                                    resolve();
                                })
                        });
                }
            });

            apiaiRequest.on('error', (error) => {
                console.error(error);
                reject(error);
            });

            apiaiRequest.end();
        });
    }

    splitResponse(str) {
        if (str.length <= this.FB_TEXT_LIMIT) {
            return [str];
        }

        return this.chunkString(str, this.FB_TEXT_LIMIT);
    }

    chunkString(s, len) {
        let curr = len, prev = 0;

        let output = [];

        while (s[curr]) {
            if (s[curr++] == ' ') {
                output.push(s.substring(prev, curr));
                prev = curr;
                curr += len;
            }
            else {
                let currReverse = curr;
                do {
                    if (s.substring(currReverse - 1, currReverse) == ' ') {
                        output.push(s.substring(prev, currReverse));
                        prev = currReverse;
                        curr = currReverse + len;
                        break;
                    }
                    currReverse--;
                } while (currReverse > prev)
            }
        }
        output.push(s.substr(prev));
        return output;
    }

    sendFBMessage(sender, messageData, fb_obj, messaging_type = 'RESPONSE') {
        return new Promise((resolve, reject) => {
            request({
                url: "https://graph.facebook.com/" + fb_obj.fbAPIVersion + "/me/messages",
                qs: {access_token: fb_obj.FB_PAGE_ACCESS_TOKEN},
                method: 'POST',
                json: {
                    recipient: {id: sender},
                    message: messageData,
                    messaging_type: messaging_type
                }
            }, (error, response) => {
                fb_obj = null;
                if (error) {
                    console.debug('Error sending message: ', error);
                    reject(error);
                } else if (response.body.error) {
                    console.debug('Error: ', response.body.error);
                    reject(new Error(response.body.error.message));
                }

                messageData = null;

                resolve();
            });
        });
    }

    sendFBSenderAction(sender, action, fb_obj) {
        return new Promise((resolve, reject) => {
            request({
                url: "https://graph.facebook.com/" + fb_obj.fbAPIVersion + "/me/messages",
                qs: {access_token: fb_obj.FB_PAGE_ACCESS_TOKEN},
                method: 'POST',
                json: {
                    recipient: {id: sender},
                    sender_action: action
                }
            }, (error, response) => {
                fb_obj = null;
                if (error) {
                    console.error('Error sending action: ', error);
                    reject(error);
                } else if (response.body.error) {
                    console.error('Error: ', response.body.error);
                    reject(new Error(response.body.error));
                }

                resolve();
            });
        });
    }

    passThreadControl(sender, metadata, fb_obj) {
        return new Promise((resolve, reject) => {
            request({
                url: "https://graph.facebook.com/" + fb_obj.fbAPIVersion + "/me/pass_thread_control",
                qs: {access_token: fb_obj.FB_PAGE_ACCESS_TOKEN},
                method: 'POST',
                json: {
                    recipient: {id: sender},
                    target_app_id: fb_obj.fbInboxId,
                    metadata: metadata
                }
            }, (error, response) => {
                fb_obj = null;

                if (error) {
                    console.error('Error sending action: ', error);
                    reject(error);
                } else if (response.body.error) {
                    console.error('Error: ', response.body.error);
                    reject(new Error(response.body.error));
                }

                resolve();
            });
        });
    }

    takeThreadControl(sender, metadata, fb_obj) {
        return new Promise((resolve, reject) => {
            request({
                url: "https://graph.facebook.com/" + fb_obj.fbAPIVersion + "/me/take_thread_control",
                qs: {access_token: fb_obj.FB_PAGE_ACCESS_TOKEN},
                method: 'POST',
                json: {
                    recipient: {id: sender},
                    metadata: metadata
                }
            }, (error, response) => {
                fb_obj = null;
                if (error) {
                    console.error('Error sending action: ', error);
                    reject(error);
                } else if (response.body.error) {
                    console.error('Error: ', response.body.error);
                    reject(new Error(response.body.error));
                }

                resolve();
            });
        });
    }

    doSubscribeRequest() {
        request({
                method: 'POST',
                uri: "https://graph.facebook.com/" + fb_obj.fbAPIVersion + "/me/subscribed_apps?access_token=${FB_PAGE_ACCESS_TOKEN}"
            },
            (error, response, body) => {
                if (error) {
                    console.error('Error while subscription: ', error);
                } else {
                    console.debug('Subscription result: ', response.body);
                }
            });
    }

    configureGetStartedEvent() {
        request({
                method: 'POST',
                uri: "https://graph.facebook.com/" + fb_obj.fbAPIVersion + "/me/thread_settings?access_token=${FB_PAGE_ACCESS_TOKEN}",
                json: {
                    setting_type: "call_to_actions",
                    thread_state: "new_thread",
                    call_to_actions: [
                        {
                            payload: FACEBOOK_WELCOME
                        }
                    ]
                }
            },
            (error, response, body) => {
                if (error) {
                    console.error('Error while subscription', error);
                } else {
                    console.debug('Subscription result', response.body);
                }
            });
    }

    isDefined(obj) {
        if (typeof obj == 'undefined') {
            return false;
        }

        if (!obj) {
            return false;
        }

        return obj != null;
    }

    sleep(delay) {
        return new Promise((resolve, reject) => {
            setTimeout(() => resolve(), delay);
        });
    }

}

module.exports = new FacebookBot();
