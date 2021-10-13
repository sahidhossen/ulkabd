import React from "react";
import ReactTable from 'react-table'
import request from 'superagent';
import DashboardSummaryBlocks from "../../components/dashboard/dashboardSummaryBlocks";
import DashboardOrders from "../../components/dashboard/dashboardOrder";
import Config from "../../components/config";

let config = new Config();
class Dashboard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            productCount:0,
            orderCount:0,
            facebookOptins:0
        }
        this.addBroadCastOrders = this.addBroadCastOrders.bind(this);
    }

    componentDidMount(){
        this.getIntialInformation();
    }

    getIntialInformation(){
        let orders = request
            .post('/api/dashboard_initial_information')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json');
        orders.end((err, response) => {
            if( response.body.error == false ) {
                let current_agent = response.body.current_agent;
                this.setState({ productCount: response.body.products, orderCount:response.body.numberOfOrders, facebookOptins: current_agent.fb_opt_in_count  })
            }
        });
    }

    addBroadCastOrders(){
        this.setState({ orderCount : this.state.orderCount + 1})
    }


    dashboard(){

        return (
            <div className="dashboard-container">
                <DashboardSummaryBlocks
                    productCount={this.state.productCount}
                    orderCount={this.state.orderCount}
                    facebookOptins={this.state.facebookOptins}
                />
                <DashboardOrders incrementOrderCounter={ this.addBroadCastOrders }/>
            </div>
        )
    }

    render(){
        return this.dashboard();
    }
}


export default Dashboard;
