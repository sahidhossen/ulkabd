/**
 * Created by tmukammel on 11/6/17.
 */

const Sequelize = require('sequelize');
const Promise = require('bluebird');
const uuid = require('uuid');

const MySqlSequelize = require('../../../../../resource/MySqlSequelize.js');
const RedisClient = require('../../../../../resource/RedisClient.js');
const Agent = require('../../agent.js');

var EndUser = MySqlSequelize.define('end_users', {
    id: {
        type: Sequelize.INTEGER(10).UNSIGNED,
        allowNull: false,
        primaryKey: true,
        autoIncrement: true
    },
    agent_id: {
        type: Sequelize.INTEGER(10).UNSIGNED,
        allowNull: true,
        references: {
            model: 'agents',
            key: 'id'
        }
    },
    agent_scoped_id: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    session_id: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    platform: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    first_name: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    last_name: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    gender: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    local: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    profile_pic: {
        type: Sequelize.TEXT,
        allowNull: true
    },
    created_at: {
        type: Sequelize.DATE,
        allowNull: true
    },
    updated_at: {
        type: Sequelize.DATE,
        allowNull: true
    },
    address: {
        type: Sequelize.TEXT,
        allowNull: true
    },
    city: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    country_code: {
        type: Sequelize.STRING(3),
        allowNull: true
    },
    mobile_no: {
        type: Sequelize.STRING(15),
        allowNull: true
    },
    country: {
        type: Sequelize.STRING(25),
        allowNull: true
    },
    zip: {
        type: Sequelize.STRING(6),
        allowNull: true
    }
}, {
    tableName: 'end_users'
});

EndUser._getEndUserFromDB = function (agent_scoped_id) {
    console.debug('Retrieving User from database!');

    return EndUser.findOne({
        where: {
            agent_scoped_id: agent_scoped_id
        }
    });
};

EndUser._getEndUserFromRedisCache = function (agent_scoped_id) {
    return new Promise((resolve, reject) => {
        RedisClient.exists("u:" + agent_scoped_id, function(err, exists) {
            if (err) reject(err);
            if (exists) {
                console.debug('Retrieving User from redis cache!');

                EndUser._updateRedisCacheExpiration(agent_scoped_id);
                RedisClient.get("u:" + agent_scoped_id, function(error, response) {
                    if (error) reject(error);

                    let user = JSON.parse(response);
                    resolve(user);
                })
            }
            else {
                reject(null);
            }
        })
    })
};

EndUser._updateRedisCacheExpiration = function (agent_scoped_id) {
    RedisClient.expire("u:" + agent_scoped_id, 60 * 5);
};

EndUser._setEndUserInRedisCache = function (agent_scoped_id, user) {
    RedisClient.set("u:" + agent_scoped_id, JSON.stringify(user));
};

EndUser._insertEndUser = function (data) {
    console.debug('Inserting User into database!');
    return EndUser.upsert(data, {})
};

EndUser.updateSenderEndUserInRedisCache = function(agent_scoped_id, user) {
    EndUser._setEndUserInRedisCache(agent_scoped_id, user);
    EndUser._updateRedisCacheExpiration(agent_scoped_id);
};

EndUser.getSenderEndUser = function(agent_scoped_id, agent_id, senderName = undefined, client_platform = undefined) {
    return new Promise(function(resolve, reject) {
        EndUser._getEndUserFromRedisCache(agent_scoped_id)
            .then((user) => {
                resolve(user)
            })
            .catch(() => {
                EndUser._getEndUserFromDB(agent_scoped_id)
                    .then((data) => {
                        if (data) {
                            let user = data.dataValues;
                            user.fallback_response_count = 0;

                            EndUser._setEndUserInRedisCache(agent_scoped_id, user);
                            EndUser._updateRedisCacheExpiration(agent_scoped_id);

                            resolve(user);
                        }
                        else {
                            let insertData = {
                                agent_id: agent_id,
                                agent_scoped_id: agent_scoped_id,
                                session_id: uuid.v4(),
                                platform: client_platform ? client_platform : 'facebook',
                                created_at: MySqlSequelize.timestamp(),
                                updated_at: MySqlSequelize.timestamp()
                            };
                            if (senderName) {
                                let names = senderName.split(' ');
                                insertData.first_name = names[0];
                                if (names.length > 1) insertData.last_name = names.splice(1, 2).join(' ');
                            }

                            EndUser._insertEndUser(insertData)
                                .then((success) => {
                                    if (success == true) {
                                        insertData.fallback_response_count = 0;

                                        EndUser._setEndUserInRedisCache(agent_scoped_id, insertData);
                                        EndUser._updateRedisCacheExpiration(agent_scoped_id);

                                        Agent.incrementOptinUserCount(agent_id)
                                            .then(() => {
                                                resolve(insertData)
                                            })
                                            .catch((error) => {
                                                reject(error)
                                            })
                                    }
                                    else reject(null)
                                })
                                .catch((error) => reject(error))
                        }
                    })
            })
    });
};

module.exports = EndUser;
