/**
 * Created by Ulka on 4/15/18.
 */

const Sequelize = require('sequelize');
const Promise = require('bluebird');
const MySqlSequelize = require('../../../resource/MySqlSequelize.js');
const RedisClient = require('../../../resource/RedisClient.js');

var Category = MySqlSequelize.define('categories', {
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
    name: {
        type: Sequelize.STRING(191),
        allowNull: false
    },
    description: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    required_attributes: {
        type: Sequelize.TEXT,
        allowNull: true
    },
    image: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    next: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    prev: {
        type: Sequelize.INTEGER(10).UNSIGNED,
        allowNull: true,
        references: {
            model: 'categories',
            key: 'id'
        }
    },
    apiai_intent_id: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    apiai_intent_name: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    apiai_entity_id: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    apiai_entity_name: {
        type: Sequelize.STRING(191),
        allowNull: true
    },
    flag: {
        type: Sequelize.INTEGER(11),
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
    external_link: {
        type: Sequelize.TEXT,
        allowNull: true
    }
}, {
    tableName: 'categories'
});

module.exports = Category;
