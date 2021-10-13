import React, { Component } from "react";
import i18n from "./../../plugins/i18n.js";
export default class ChangePlan extends Component {

  constructor(){
    super()

    this.state = {
      currancy : 'BDT',
      currancySign : '৳'
    }
  }

  componentDidMount(){

    const domain = window.location.hostname;
    const japan = '.co.jp';

    if(domain.includes(japan)){
      this.setState({currancy : '円', currancySign : '￥'})
    }
    else {
      this.setState({currancy : 'BDT', currancySign : '৳'})
    }

  }

  render() {
    return (
      <div className="row text-center __pricing-dashboard">
        <div className="__pricingPlanContainer">
          {/* Free Plan block  */}

          <div className="__pricingPlans __current-plan">
            <p className="__changeNotification">{i18n.t('pages.changePlan.nextPlan.changePlanNotification')}</p>
            <h3 className="__pricing-planName ">{i18n.t('pages.changePlan.freePlan.planName')}</h3>
            <p className="__dateNotification">
              {i18n.t('pages.changePlan.nextPlan.date.from')} 31st July 2020 <br/>({i18n.t('pages.changePlan.nextPlan.date.in')} <span className="__date">N</span> {i18n.t('pages.changePlan.nextPlan.date.days')})
            </p>
            <p className="__freePlan"></p>
            <div className="__pricing-button-container ">
              <button target="_blank" className="btn btn-admin btn-admin-white disabled">{i18n.t('pages.changePlan.buttons.currentPlan')}</button>
              <button target="_blank" className="btn btn-admin btn-admin-blue">{i18n.t('pages.changePlan.buttons.selectPlan')}</button>
              <button target="_blank" className="btn btn-admin btn-admin-red">{i18n.t('pages.changePlan.buttons.cancel')}</button>
            </div>
            <p className="__pricing-details">{i18n.t('pages.changePlan.freePlan.planDetails.dashboard')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.freePlan.planDetails.member')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.freePlan.planDetails.chatbot')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.freePlan.planDetails.userDays')}</p>
          </div>

          {/* Minimal Plan block  */}

          <div className="__pricingPlans ">
            <p className="__changeNotification">{i18n.t('pages.changePlan.nextPlan.changePlanNotification')}</p>
            <h3 className="__pricing-planName">{i18n.t('pages.changePlan.minimalPlan.planName')}</h3>
            <p className="__dateNotification">
              {i18n.t('pages.changePlan.nextPlan.date.from')} 31st July 2020 <br/>({i18n.t('pages.changePlan.nextPlan.date.in')} <span className="__date">N</span> {i18n.t('pages.changePlan.nextPlan.date.days')})
            </p>
            <h4 className="__pricing-price">{this.state.currancySign}3,871 {this.state.currancy}<sub>/{i18n.t('pages.changePlan.price.month')}</sub></h4>
            <div className="__pricing-button-container">
              <button target="_blank" className="btn btn-admin btn-admin-white disabled">{i18n.t('pages.changePlan.buttons.currentPlan')}</button>
              <button target="_blank" className="btn btn-admin btn-admin-blue">{i18n.t('pages.changePlan.buttons.selectPlan')}</button>
              <button target="_blank" className="btn btn-admin btn-admin-red">{i18n.t('pages.changePlan.buttons.cancel')}</button>
            </div>
            <p className="__pricing-details">{i18n.t('pages.changePlan.minimalPlan.planDetails.dashboard')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.minimalPlan.planDetails.member')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.minimalPlan.planDetails.chatbot')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.minimalPlan.planDetails.userDays')}</p>
          </div>

          {/* Normal Plan block  */}

          <div className="__pricingPlans __nextPlan">
            <p className="__changeNotification">{i18n.t('pages.changePlan.nextPlan.changePlanNotification')}</p>
            <h3 className="__pricing-planName">{i18n.t('pages.changePlan.normalPlan.planName')}</h3>
            <p className="__dateNotification">
              {i18n.t('pages.changePlan.nextPlan.date.from')} 31st July 2020 <br/>({i18n.t('pages.changePlan.nextPlan.date.in')} <span className="__date">N</span> {i18n.t('pages.changePlan.nextPlan.date.days')})
            </p>
            <h4 className="__pricing-price">{this.state.currancySign}7,757 {this.state.currancy}<sub>/{i18n.t('pages.changePlan.price.month')}</sub></h4>
            <div className="__pricing-button-container">
              <button target="_blank" className="btn btn-admin btn-admin-white disabled">{i18n.t('pages.changePlan.buttons.currentPlan')}</button>
              <button target="_blank" className="btn btn-admin btn-admin-blue">{i18n.t('pages.changePlan.buttons.selectPlan')}</button>
              <button target="_blank" className="btn btn-admin btn-admin-red">{i18n.t('pages.changePlan.buttons.cancel')}</button>
            </div>
            <p className="__pricing-details">{i18n.t('pages.changePlan.normalPlan.planDetails.dashboard')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.normalPlan.planDetails.member')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.normalPlan.planDetails.chatbot')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.normalPlan.planDetails.userDays')}</p>
          </div>

          {/* Large Plan block  */}

          <div className="__pricingPlans ">
            <p className="__changeNotification">{i18n.t('pages.changePlan.nextPlan.changePlanNotification')}</p>
            <h3 className="__pricing-planName">{i18n.t('pages.changePlan.largePlan.planName')}</h3>
            <p className="__dateNotification">
              {i18n.t('pages.changePlan.nextPlan.date.from')} 31st July 2020 <br/>({i18n.t('pages.changePlan.nextPlan.date.in')} <span className="__date">N</span> {i18n.t('pages.changePlan.nextPlan.date.days')})
            </p>
            <h4 className="__pricing-price">{this.state.currancySign}15,530 {this.state.currancy}<sub>/{i18n.t('pages.changePlan.price.month')}</sub></h4>
            <div className="__pricing-button-container">
              <button target="_blank" className="btn btn-admin btn-admin-white disabled">{i18n.t('pages.changePlan.buttons.currentPlan')}</button>
              <button target="_blank" className="btn btn-admin btn-admin-blue">{i18n.t('pages.changePlan.buttons.selectPlan')}</button>
              <button target="_blank" className="btn btn-admin btn-admin-red">{i18n.t('pages.changePlan.buttons.cancel')}</button>
            </div>
            <p className="__pricing-details">{i18n.t('pages.changePlan.largePlan.planDetails.dashboard')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.largePlan.planDetails.member')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.largePlan.planDetails.chatbot')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.largePlan.planDetails.userDays')}</p>
          </div>

          {/* Enterprise Plan block  */}

          <div className="__pricingPlans">
            <p className="__changeNotification">{i18n.t('pages.changePlan.nextPlan.changePlanNotification')}</p>
            <h3 className="__pricing-planName">{i18n.t('pages.changePlan.enterprisePlan.planName')}</h3>
            <p className="__dateNotification">
              {i18n.t('pages.changePlan.nextPlan.date.from')} 31st July 2020 <br/>({i18n.t('pages.changePlan.nextPlan.date.in')} <span className="__date">N</span> {i18n.t('pages.changePlan.nextPlan.date.days')})
            </p>
            <h4 className="__pricing-price">{this.state.currancySign}15,530 {this.state.currancy}<sub>/{i18n.t('pages.changePlan.price.month')}</sub></h4>
            <div className="__pricing-button-container">
              <button target="_blank" className="btn btn-admin btn-admin-white disabled">{i18n.t('pages.changePlan.buttons.currentPlan')}</button>
              <button target="_blank" className="btn btn-admin btn-admin-blue">{i18n.t('pages.changePlan.buttons.selectPlan')}</button>
              <button target="_blank" className="btn btn-admin btn-admin-red">{i18n.t('pages.changePlan.buttons.cancel')}</button>
            </div>
            <p className="__pricing-details">{i18n.t('pages.changePlan.enterprisePlan.planDetails.dashboard')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.enterprisePlan.planDetails.member')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.enterprisePlan.planDetails.chatbot')}</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.enterprisePlan.planDetails.userDays')}</p>
            <p className="__pricing-details">+</p>
            <p className="__pricing-details">{i18n.t('pages.changePlan.enterprisePlan.planDetails.extra')}</p>
            <p className="__pricing-details-bottom">/ {this.state.currancySign}3,871 {this.state.currancy}<sub>/{i18n.t('pages.changePlan.price.month')}</sub></p>
          </div>

        </div>
      </div>
    );
  }
}
