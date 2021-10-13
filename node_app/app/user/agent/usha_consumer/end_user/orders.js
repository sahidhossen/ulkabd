/**
 * Created by tmukammel on 03/22/18.
 */

const Sequelize = require('sequelize');
const Promise = require('bluebird');
const uuid = require('uuid');
const Product = require('../../product.js');

const MySqlSequelize = require('../../../../../resource/MySqlSequelize.js');
const RedisClient = require('../../../../../resource/RedisClient.js');
const Agent = require('../../agent.js');

var Order = MySqlSequelize.define('orders', {
    id: {
        type: Sequelize.INTEGER(10).UNSIGNED,
        allowNull: false,
        primaryKey: true,
        autoIncrement: true
    },
    agent_id: {
        type: Sequelize.INTEGER(10).UNSIGNED,
        allowNull: false,
        references: {
            model: 'agents',
            key: 'id'
        }
    },
    end_user_id: {
        type: Sequelize.INTEGER(10).UNSIGNED,
        allowNull: false,
        references: {
            model: 'end_users',
            key: 'id'
        }
    },
    delivery_charge: {
        type: Sequelize.INTEGER(11),
        allowNull: false,
        defaultValue: '0'
    },
    status: {
        type: Sequelize.INTEGER(6),
        allowNull: false,
        defaultValue: '0'
    },
    order_code: {
        type: Sequelize.STRING(191),
        allowNull: false,
        unique: true
    },
    status_detail: {
        type: Sequelize.TEXT,
        allowNull: true
    },
    payment_status: {
        type: Sequelize.INTEGER(4),
        allowNull: false,
        defaultValue: '0'
    },
    created_at: {
        type: Sequelize.DATE,
        allowNull: true
    },
    updated_at: {
        type: Sequelize.DATE,
        allowNull: true
    },
    entities: {
        type: Sequelize.TEXT,
        allowNull: false
    }
}, {
    tableName: 'orders'
});

Order.getOrderByCode = function (order_code, end_user_id) {
    return new Promise(function(resolve, reject) {
        console.debug('Retrieving Order from database!');

        Order.findOne({
            where: {
                order_code: order_code,
                end_user_id: end_user_id
            }
        })
        .then((data) => {
            if (data) resolve(data.dataValues);
            else reject(new Error("Could not find an order with given code!"));
        });
    });
};

Order.getOrderStatusFBFormattedMessageByCode = function (order_code, end_user_id) {
    return new Promise(function(resolve, reject) {

        Order.getOrderByCode(order_code, end_user_id)
            .then(async (order) => {

                var messages = [];
                var totalPrice = 0.0;

                let entities = JSON.parse(order.entities);

                for (let index = 0; index < entities.length; index++) {
                    const entity = entities[index];
                    // console.debug(`Entity: ${JSON.stringify(entity, null, 4)}`)
                    var product = await Product.findById(entity.id)
                    // console.debug(`Product: ${product}`)
                    if (product == null) {
                        messages = [];
                        break;
                    }

                    let attributeArray =  entity.attributes.map( (attribute) => {
                        return(
                            `${attribute.name.charAt(0).toUpperCase() + attribute.name.slice(1)}: ${attribute.value}`
                        )
                    });
                    let attributes = attributeArray.join("; ");
                    let entityTotalPrice = entity.quantity * product.offer_price;
                    totalPrice += entityTotalPrice;

                    messages.push(`${entity.quantity} ${product.name} @${product.offer_price} BDT${attributes !== '' ? '\n' + attributes + '.' : ''}\n${entityTotalPrice} BDT`);
                }

                if (messages.length > 0) messages.push(`TOTAL: ${totalPrice} BDT`);

                let title = "Order is New";
                let subtitle = "Your order with code: " + order_code + " is being processed.";

                switch (order.status) {
                    case 1:
                        title = "Order Delivered!";
                        subtitle = "Your order with code: " + order_code + " should be at your hands!";
                        break;

                    case 2:
                        title = "Order Sent!";
                        subtitle = "Your order with code: " + order_code + " on it's way to you!";
                        break;

                    case 3:
                        title = "Order Cancelled!";
                        subtitle = "Order with code: " + order_code + " is Cancelled! Contact a human agent for detail.";
                        break;

                    case 4:
                        title = "Order Confirmed!";
                        subtitle = "Order with code: " + order_code + " is Confirmed!";
                        break;
                }

                let message = {
                    attachment: {
                        type: "template",
                        payload: {
                            template_type: "generic",
                            elements: [
                                {
                                    title: title,
                                    image_url: null,
                                    subtitle: subtitle,
                                    buttons: [
                                        {
                                            type: "postback",
                                            payload: "Start Browsing",
                                            title: "Continue"
                                        },
                                        {
                                            type: "web_url",
                                            url: "http://m.me/UshaAI",
                                            title: "Powered by Usha"
                                        }
                                    ]
                                }
                            ]
                        }
                    }
                };

                messages.unshift(message);

                resolve(messages);
            })
            .catch((error) => {
                reject(error);
            });
    });
};

module.exports = Order;
