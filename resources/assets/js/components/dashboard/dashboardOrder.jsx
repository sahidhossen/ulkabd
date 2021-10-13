import React from "react";
import ReactTable from 'react-table'
import request from 'superagent';
import Config from "../../components/config";
import Echo from "laravel-echo";
import OrderPopup from '../orders/OrderPopup';
import moment from 'moment';
import i18n from "./../../plugins/i18n.js"

let config = new Config();
class DashboardOrders extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            orders: [],
            orders_filter: [],
            current_order_state: null,
            actionListTriggerIndex: null
        };

        this.onActionOn = this.onActionOn.bind(this);
        this.documentClickEventHandler = this.documentClickEventHandler.bind(this);
        this.OnFilter = this.OnFilter.bind(this);
        this.onSelectAction = this.onSelectAction.bind(this);
        this.closeOrderDetailsPopup = this.closeOrderDetailsPopup.bind(this);
        this.action_list = [
            { name: i18n.t('pages.order.orderTable.columns.action.actionList.new'), value: '0' },
            { name: i18n.t('pages.order.orderTable.columns.action.actionList.confirmed'), value: '4' },
            { name: i18n.t('pages.order.orderTable.columns.action.actionList.sent'), value: '2' },
            { name: i18n.t('pages.order.orderTable.columns.action.actionList.delivered'), value: '1' },
            { name: i18n.t('pages.order.orderTable.columns.action.actionList.cancelled'), value: '3' }
        ]
    }

    componentDidMount() {
        this.getAllOrder();
        document.addEventListener("click", this.documentClickEventHandler);
        this.listenBroadCast();
        //if (this.props.broadcastOrder != null) {
        //    this.state.orders_filter.unshift( this.props.broadcastOrder );
        //    this.props.broadcastOrder = null;
        //}

    }

    listenBroadCast() {
        if (typeof io !== 'undefined') {
            window.Echo = new Echo({
                broadcaster: 'socket.io',
                host: config.root + ':6001'
            });
            window.Echo.private('channel-new-order_' + config.userID)
                .listen('BroadcastNewOrder', (response) => {
                    //console.debug( response );
                    document.getElementById("audio").play();
                    if (response != null && response.order != null && response.order.length > 0) {
                        this.state.orders_filter.unshift(response.order[0]);
                        this.props.incrementOrderCounter();
                    }
                });
        }
    }

    componentWillUnmount() {
        document.removeEventListener("click", this.documentClickEventHandler);

    }

    documentClickEventHandler(event) {
        let index = event.target.getAttribute("data-rowIndex");
        if (index == null) {
            this.setState({ actionListTriggerIndex: null })
        }

    }
    getAllOrder() {
        let orders = request
            .post('/api/get_order_by')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json')
            .field('state', 0);
        orders.end((err, response) => {
            //console.debug("response: ", response );
            if (response.body.error == false) {
                let orderList = response.body.data;
                if (orderList.length > 0) {
                    orderList.map((order, index) => {
                        return orderList[index].order.entities = JSON.parse(order.order.entities);
                    });
                }
                this.setState({ orders: orderList, loading: false });
                this.setState({ orders_filter: orderList });
            }
        });
    }
    onActionOn(event) {
        event.preventDefault();
        let index = event.target.getAttribute("data-rowIndex");
        let orderState = event.target.getAttribute("data-orderState");
        this.setState({ current_order_state: orderState });

        if (this.state.actionListTriggerIndex != index) {
            this.setState({ actionListTriggerIndex: index })
        } else {
            this.setState({ actionListTriggerIndex: null })
        }
    }

    getActionClassName(orderAction) {
        switch (parseInt(orderAction)) {
            case 0:
                return 'order_new';
                break;
            case 1:
                return 'order_delivered';
                break;
            case 2:
                return 'order_sent';
                break;
            case 3:
                return 'order_cancelled';
                break;
            case 4:
                return 'order_confirmed';
                break;
        }
    }

    onSelectAction(event) {
        event.preventDefault();
        let actionOrder = event.target.getAttribute("data-actionOrder");
        let index = event.target.getAttribute("data-rowIndex");
        let order_id = event.target.getAttribute("data-orderId");

        if (this.state.current_order_state == actionOrder) {
            return false;
        }

        // let msgConfirm = 'Are you sure you want to change this order state?';
        // let decision = window.confirm(msgConfirm);
        // if (decision == false) return false;

        var message = window.prompt(i18n.t('pages.order.promptMessage'), "Message");
        if (message == null) return false;

        let orders = request
            .post('/api/change_order_action')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json')
            .field('msg', (message == 'Message' || message == '') ? 'null' : message)
            .field('state', actionOrder)
            .field('order_id', order_id);
        orders.end((err, response) => {
            // console.debug("response: ", response );
            if (response.body.error === false) {
                this.state.orders_filter[index].order.status = actionOrder;
                this.setState({ actionListTriggerIndex: null })
            }
        });
    }
    OnFilter() {
        let search = this.refs.filter_order.value;
        let ordertList = this.state.orders;
        let filterOrder = ordertList.filter((order) => {
            return (
                order.order.order_code.toLowerCase().indexOf(search.toLowerCase()) !== -1
            );
        }
        );
        this.setState({ orders_filter: filterOrder });
    }

    /*
     * Show order details popup
     * @params: each order oboject
     */
    showOrderDetailsPopup(order, event) {
        event.preventDefault();
        this.setState({ active_order: order, order_details_popup_status: !this.state.order_details_popup_status });
    }

    /*
     * Close details popup
     */
    closeOrderDetailsPopup() {
        this.setState({ order_details_popup_status: !this.state.order_details_popup_status });
    }

    orderTable() {
        //console.debug("filter: ",this.state.orders_filter);
        return (
            <ReactTable
                columns={[
                    {
                        Header: i18n.t('pages.order.orderTable.columns.orderCode'), // Custom Header components!
                        accessor: 'order.order_code',
                        width: 180,
                        sortable: true,
                        showFilters: true,
                        Cell: row => (
                            <div style={{ textAlign: 'center' }} className='order_id_holder'>
                                <a onClick={this.showOrderDetailsPopup.bind(this, row.original)} href="#" className="order_details_popup_btn"> <i className="fa fa-location-arrow"></i> </a>
                                <span className="order_id">{row.value}</span>
                            </div>
                        )
                    },
                    {
                        Header: i18n.t('pages.order.orderTable.columns.customer'),
                        accessor: 'profile.first_name',
                        showFilters: true,
                        minWidth: 180,
                        //render: props => <div style={{ textAlign:'center'}} className='number'>{props.value}</div> // Custom cell components!
                        Cell: row => (
                            <div className="details">
                                {row.value} {row.original.profile.last_name}
                            </div>
                        )
                    },
                    {
                        Header: i18n.t('pages.order.orderTable.columns.quantity'),
                        accessor: 'cart.total_quantity', // Custom value accessors!
                        minWidth: 100,
                        Cell: row => (
                            <div className="details text-center">
                                {row.value}
                            </div>
                        )

                    },
                    {
                        Header: i18n.t('pages.order.orderTable.columns.location'),
                        accessor: 'profile.city', // Custom value accessors!
                        minWidth: 100,
                        Cell: row => (
                            <div className="details">
                                {(row.value === null) ? '-' : row.value}
                            </div>
                        )

                    },
                    {
                        Header: i18n.t('pages.order.orderTable.columns.deliver'),
                        accessor: 'order.status_detail.expected_delivery_time', // Custom value accessors!
                        minWidth: 150,
                        Cell: row => (
                            <div className="delivery_status_detail">
                                {(row.value === null) ? null :
                                    <div>
                                        <span> {row.value.date} </span>
                                        <span> - {row.value.time} </span>
                                    </div>}
                            </div>
                        )

                    },
                    {
                        Header: props => <span>{i18n.t('pages.order.orderTable.columns.time')}</span>, // Custom Header components!
                        accessor: 'order.status_detail',
                        Cell: row => (
                            <div style={{ textAlign: 'center' }} className='number'>
                                {(row.value.order_time === null) ? null : <small>{moment(row.value.order_time, "YYYY-MM-DD hh:mm:ss A").format('ddd, hh:mm A')} </small>}
                            </div>
                        )
                    },
                    {
                        Header: props => <span>{i18n.t('pages.order.orderTable.columns.total')}</span>, // Custom Header components!
                        accessor: 'cart.total_price',
                        Cell: row => (
                            <div style={{ textAlign: 'center' }} className='number'>
                                {parseFloat(row.value).toFixed(2)}
                            </div>
                        )
                    },
                    {
                        Header: i18n.t('pages.order.orderTable.columns.action.name'),
                        accessor: 'id',
                        width: 60,
                        hideFilter: false,
                        Cell: row => (
                            <div className="action_container" style={{ textAlign: 'center' }}>
                                <span
                                    onClick={this.onActionOn}
                                    data-rowIndex={row.index}
                                    data-orderState={row.original.order.status}
                                    style={styles.action_bullet}
                                    className={this.getActionClassName(row.original.order.status) + " action-bullet"} >

                                    ...
                                </span>

                                <div className="actions-list" style={(this.state.actionListTriggerIndex == row.index) ? styles.action_list_on : styles.action_list_off} >
                                    <ul>
                                        {this.action_list.map((item, index) => {
                                            return <li
                                                data-rowIndex={row.index}
                                                onClick={this.onSelectAction}
                                                key={index}
                                                data-actionOrder={item.value}
                                                data-orderId={row.original.order.id}
                                            >
                                                <span className={(row.original.order.status == parseInt(item.value)) ? this.getActionClassName(row.original.order.status) : null} ></span>
                                                {item.name}
                                            </li>
                                        })}

                                    </ul>
                                </div>
                            </div>
                        )

                    }

                ]}
                data={this.state.orders_filter}
                defaultPageSize={10}
                onChange={this.OnFilter}

                previousText={i18n.t('common.tableOptions.prevText')}
                nextText={i18n.t('common.tableOptions.nextText')}
                pageText={i18n.t('common.tableOptions.pageText')}
                noDataText={i18n.t('common.tableOptions.noDataText')}
                ofText={i18n.t('common.tableOptions.ofText')}
                rowsText={i18n.t('common.tableOptions.rowsText')}
            />
        )
    }


    render() {
        return (
            <div className="dashboard-widget list order-list">
                <div className="row bottom-margin-15px">
                    <div className="col-md-12 dashboard-order-search">
                        <ul>
                            <li className="big-title">{i18n.t('pages.dashboard.dashboardTable.name')} <span className="counter text-info">{this.state.orders_filter.length}</span> </li>
                            <li className="Search_Bar"> <span className="SearchIcon"> <i className="fa fa-search fa-2x"></i></span> <input type="text" onChange={this.OnFilter} ref="filter_product" className="product_search_field" placeholder={i18n.t('pages.dashboard.dashboardTable.search.placeholder')} /> </li>
                        </ul>
                    </div>
                </div>
                <div className="order_list">
                    {this.orderTable()}
                    {(this.state.order_details_popup_status) ? <OrderPopup onCloseAction={this.closeOrderDetailsPopup.bind(event)} order={this.state.active_order} className="active orderModal" /> : null}
                </div>
            </div>
        )
    }
}


export default DashboardOrders;


const styles = {
    action_bullet: {
        display: 'inline-block',
        width: '30px',
        height: '30px',
        color: "#FFFFFF",
        paddingTop: '5.5px',
        borderRadius: '50px',
        lineHeight: 0,
        fontSize: '22px',
        cursor: 'pointer',
        border: "2px solid rgba(255, 255, 116,0.6)"
    },
    action_list_off: {
        display: 'none',
        opacity: 0,
        zIndex: 99
    },
    action_list_on: {
        display: 'block',
        opacity: 1,
        zIndex: 999
    }
};
