/**
 * Created by Ulka on 4/15/18.
 */

const Sequelize = require('sequelize');
const Promise = require('bluebird');
const MySqlSequelize = require('../../../resource/MySqlSequelize.js');
const RedisClient = require('../../../resource/RedisClient.js');
const Category = require('./category.js');

var Product = MySqlSequelize.define('products', {
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
    category_id: {
        type: Sequelize.INTEGER(10).UNSIGNED,
        allowNull: false,
        references: {
            model: 'categories',
            key: 'id'
        }
    },
    name: {
        type: Sequelize.STRING(191),
        allowNull: false
    },
    code: {
        type: Sequelize.STRING(191),
        allowNull: false
    },
    product_attributes: {
        type: Sequelize.TEXT,
        allowNull: true
    },
    price: {
        type: Sequelize.DECIMAL,
        allowNull: true
    },
    offer_price: {
        type: Sequelize.DECIMAL,
        allowNull: true
    },
    priority: {
        type: Sequelize.INTEGER(10).UNSIGNED,
        allowNull: true
    },
    detail: {
        type: Sequelize.TEXT,
        allowNull: true
    },
    is_image: {
        type: Sequelize.TEXT,
        allowNull: true
    },
    unit: {
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
    image_link: {
        type: Sequelize.TEXT,
        allowNull: true
    },
    stock: {
        type: Sequelize.INTEGER(11),
        allowNull: false
    },
    external_link: {
        type: Sequelize.TEXT,
        allowNull: true
    }
}, {
    tableName: 'products'
});

Product._splitResponse = function (str) {
    let FB_TEXT_LIMIT = 640;

    if (str.length <= FB_TEXT_LIMIT) {
        return [str];
    }

    return Product._chunkString(str, FB_TEXT_LIMIT);
};

Product._chunkString = function (s, len) {
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
};

Product.getProductDetail = function (product_id) {
    try {
        return new Promise(function(resolve, reject) {
            Product.findById(product_id)
                .then((product) => {
                    //console.debug("Product:" + product);

                    Category.findById(product.category_id)
                        .then((category) => {

                            //console.debug("Category:" + category);

                            let productDetail = [];

                            let img_url = null;
                            if (product.is_image)
                                img_url = 'https://usha.ulkabd.com' + '/uploads/' + product.is_image;
                            else if (product.image_link)
                                img_url = product.image_link;

                            if(img_url) {
                                productDetail.push({
                                    attachment: {
                                        type: 'image',
                                        payload: {
                                            url: img_url,
                                            is_reusable: false
                                        }
                                    }
                                });
                            }

                            let detail = product.name + require('os').EOL + product.detail;
                            let detailArray = Product._splitResponse(detail);

                            for (var i = 0; i < detailArray.length; ++i) {
                                let text = detailArray[i];

                                if (i < detailArray.length - 1) {
                                    productDetail.push({
                                        text: text
                                    });
                                }
                                else {
                                    productDetail.push(
                                        {
                                            attachment: {
                                                type: "template",
                                                payload: {
                                                    template_type: "button",
                                                    text: text,
                                                    buttons: [
                                                        {
                                                            type: "postback",
                                                            title: decodeURIComponent(escape("\xF0\x9F\x9B\x92")) + ' (' + product.offer_price + ' TK)',
                                                            payload: category.apiai_entity_name + '-' + product.code
                                                        }
                                                    ]
                                                }
                                            }
                                        }
                                    );
                                }
                            }

                            resolve(productDetail);
                        })
                        .catch((error) => reject(error))
                })
                .catch((error) => reject(error))
        });
    } catch (e) {
        throw e;
    }
};

module.exports = Product;
