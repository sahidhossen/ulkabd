/**
 * Created by tmukammel on 11/5/17.
 */

const Sequelize = require('sequelize');
const Promise = require('bluebird');
const MySqlSequelize = require('../../../resource/MySqlSequelize.js');
const RedisClient = require('../../../resource/RedisClient.js');
const { constants } = require('../../../socket-client/constants');

var Agent = MySqlSequelize.define('agents', {
    id: { type: Sequelize.INTEGER.UNSIGNED, primaryKey: true },
    user_id: { type: Sequelize.INTEGER.UNSIGNED },//, references: { model: User, key: 'id' }
    agent_code: { type: Sequelize.STRING, unique: true },
    agent_name: Sequelize.STRING,
    fb_page_id: Sequelize.STRING,
    fb_page_name: Sequelize.STRING,
    image_path: Sequelize.STRING,
    apiai_dev_access_token: Sequelize.TEXT,
    apiai_client_access_token: Sequelize.TEXT,
    is_default_intents_fetched: { type: Sequelize.BOOLEAN, allowNull: false, defaultValue: false },
    fb_access_token: Sequelize.TEXT,
    fb_verify_token: Sequelize.STRING,
    fb_likes_count: { type: Sequelize.INTEGER.UNSIGNED, defaultValue: 0 },
    fb_opt_in_count: { type: Sequelize.INTEGER.UNSIGNED, defaultValue: 0 },
    is_fb_webhook: { type: Sequelize.BOOLEAN, defaultValue: false },
    page_subscription: { type: Sequelize.BOOLEAN, defaultValue: false },
    messenger_profile: { type: Sequelize.BOOLEAN, defaultValue: false },
    is_apiai_fb_integration: { type: Sequelize.BOOLEAN, defaultValue: false },
    training_status: { type: Sequelize.INTEGER, defaultValue: 2 },
    is_payment_due: { type: Sequelize.BOOLEAN, defaultValue: false },
    fb_receiver_role: { type: Sequelize.INTEGER, allowNull: true, defaultValue: 0 },
    created_at: Sequelize.DATE,
    updated_at: Sequelize.DATE,
    configuration: Sequelize.TEXT
});

/**
 * @param {Object} object can be {fb_page_id: id} or {agent_code: code}
 */
Agent._getAgentFromDB = function (object) {
    console.debug('Retrieving Agent from database!');

    return Agent.findOne({
        where: object
    });
};

Agent._getAgentFromRedisCache = function (code) {
    return new Promise((resolve, reject) => {
        RedisClient.exists("a:" + code, function (err, exists) {
            if (err) reject(err);
            if (exists) {
                console.debug('Retrieving Agent from redis cache!');

                Agent._updateRedisCacheExpiration(code);
                RedisClient.get("a:" + code, function (error, response) {
                    if (error) reject(error);

                    let agent = JSON.parse(response);
                    resolve(agent);
                })
            }
            else {
                reject(null);
            }
        })
    })
};

Agent._updateRedisCacheExpiration = function (code) {
    RedisClient.expire("a:" + code, 60 * 5);
};

Agent._setAgentInRedisCache = function (code, agent) {
    RedisClient.set("a:" + code, JSON.stringify(agent));
};

Agent.incrementOptinUserCount = function (agent_id) {
    console.debug('Incrementing opt-in user count!');

    return Agent.increment('fb_opt_in_count', {
        by: 1,
        where: {
            id: agent_id
        }
    })
};

/**
 * Fast retrive agent from cache. Or db if not found
 * 
 * @param {string} code agent fb_page_id or agent_code
 * @param {string} client_platform 'web' | 'facebook' | undefined, if 'web' code is assumed agent_code and vice-versa
 */
Agent.getRecipientAgent = function (code, client_platform = undefined) {
    // console.debug(`Platform: ${client_platform}`);
    return new Promise((resolve, reject) => {
        Agent._getAgentFromRedisCache(code)
            .then((agent) => {
                resolve(agent)
            })
            .catch(() => {
                const idObject = client_platform == constants.CLIENT_TYPES.WEB
                    ? { agent_code: code } : { fb_page_id: code };
                Agent._getAgentFromDB(idObject)
                    .then((data) => {
                        let agent = data.dataValues;
                        Agent._setAgentInRedisCache(code, agent);
                        Agent._updateRedisCacheExpiration(code);

                        resolve(agent)
                    })
                    .catch((error) => reject(error))
            })
    });

};

Agent.assignReceiverRole = function (fb_page_id, role) {
    return new Promise((resolve, reject) => {
        Agent._getAgentFromDB({fb_page_id})
            .then((agent) => {
                agent.fb_receiver_role = role;
                return agent.save()
            })
            .then((data) => {
                let agent = data.dataValues;
                Agent._setAgentInRedisCache(fb_page_id, agent);
                Agent._updateRedisCacheExpiration(fb_page_id);

                resolve(agent)
            })
            .catch((error) => reject(error));
    });
};

module.exports = Agent;
