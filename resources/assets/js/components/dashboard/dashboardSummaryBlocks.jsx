import React from "react";
import ReactTable from 'react-table'
import i18n from "./../../plugins/i18n.js"

class DashboardSummaryBlocks extends React.Component {
    constructor(props) {
        super(props);

    }

    render(){
        return (
            <div className="row margin-0">
                <div className="col-lg-3 col-md-6">
                    <div className="dashboard-widget stat fb-stat">
                        <div className="widget-icon pull-left">
                            <div className="outer-border">
                                <div className="inner-icon"><i className="fa fa-facebook fa-2x" aria-hidden="true"></i></div>
                            </div>
                        </div>
                        <div className="widget-info pull-right text-right">
                            <p> {i18n.t('pages.dashboard.widgets.titles.users')}  </p>
                        </div>
                        <div className="count-total">
                            <p className="quantity"> { this.props.facebookOptins } </p>
                        </div>
                    </div>
                </div>
                <div className="col-lg-3 col-md-6">
                    <div className="dashboard-widget stat total-products">
                        <div className="widget-icon pull-left">
                            <div className="outer-border">
                                <div className="inner-icon"><i className="fa fa-briefcase fa-2x" aria-hidden="true"></i></div>
                            </div>
                        </div>
                        <div className="widget-info pull-right text-right">
                            <p>{i18n.t('pages.dashboard.widgets.titles.entities')}</p>
                        </div>
                        <div className="count-total">
                            <p className="quantity"> { this.props.productCount } </p>
                        </div>
                    </div>
                </div>
                <div className="col-lg-3 col-md-6">
                    <div className="dashboard-widget stat upcoming-broadcast">

                        <div className="widget-icon pull-left">
                            <div className="outer-border">
                                <div className="inner-icon"><i className="fa fa-calendar fa-2x" aria-hidden="true"></i></div>
                            </div>
                        </div>
                        <div className="widget-info pull-right text-right">
                            <p> {i18n.t('pages.dashboard.widgets.titles.feedback')} </p>
                        </div>
                        <div className="count-total">
                            <div className="coming-soon extra-small">
                                <span> Coming Soon... </span>
                            </div>
                            <div className="quantity hide">
                                <p className="rating">
                                    <span className="fa fa-star checked"></span>
                                    <span className="fa fa-star checked"></span>
                                    <span className="fa fa-star checked"></span>
                                    <span className="fa fa-star"></span>
                                    <span className="fa fa-star"></span>
                                </p>
                                <p className="points"> 500 </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="col-lg-3 col-md-6">
                    <div className="dashboard-widget stat new-order">
                        <div className="widget-icon pull-left">
                            <div className="outer-border">
                                <div className="inner-icon"><i className="fa fa-shopping-bag fa-2x" aria-hidden="true"></i></div>
                            </div>
                        </div>
                        <div className="widget-info pull-right text-right">
                            <p>{i18n.t('pages.dashboard.widgets.titles.order')}</p>
                        </div>
                        <div className="count-total">
                            <p className="quantity"> { this.props.orderCount } </p>
                        </div>
                    </div>
                </div>
            </div>
        )
    }

}

export default DashboardSummaryBlocks;
