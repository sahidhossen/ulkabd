/**
 * Created by sahidhossen on 3/27/17.
 */

import React from "react";
import ReactDOM from "react-dom";
import Config from "./components/config";

let config = new Config();

import MainBot from './components/MainBot';
if (document.getElementById('mainBot')) {
    ReactDOM.render(<MainBot user_id={config.userID} />, document.getElementById('mainBot'));
}

import Dashboard from './components/dashboard/dashboard';
if (document.getElementById('mainDashboard')) {
    ReactDOM.render(<Dashboard user_id={config.userID} />, document.getElementById('mainDashboard'));
}

import ProductMain from './components/products/ProductMain';
if (document.getElementById('product_body')) {
    ReactDOM.render(<ProductMain user_id={config.userID} />, document.getElementById('product_body'));
}

import Category from './components/categories/category_main';
if (document.getElementById('category_body')) {
    ReactDOM.render(<Category user_id={config.userID} />, document.getElementById('category_body'));
}
import OrderMain from './components/orders/mainOrders';
if (document.getElementById('ProductOrderList')) {
    ReactDOM.render(<OrderMain user_id={config.userID} />, document.getElementById('ProductOrderList'));
}

import TrainAgent from './components/agentTrain';
if(document.getElementById("trainAgentAction")){
    ReactDOM.render(<TrainAgent/>, document.getElementById('trainAgentAction'));
}


import Configure from './components/configure';
if(document.getElementById("configDashboard")){
    ReactDOM.render(<Configure/>, document.getElementById('configDashboard'));
}

import Campaign from './components/campaign/campaign';
if(document.getElementById("mainCampaign")){
    ReactDOM.render(<Campaign/>, document.getElementById('mainCampaign'));
}

import ChatInbox from './components/ChatInbox';
if(document.getElementById("chat_inbox")){
    ReactDOM.render(<ChatInbox/>, document.getElementById('chat_inbox'));
}

import ChangePlan from './components/billing/ChangePlan';
if(document.getElementById("change_plan")){
    ReactDOM.render(<ChangePlan/>, document.getElementById('change_plan'));
}
