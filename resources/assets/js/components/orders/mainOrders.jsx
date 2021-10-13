import React from "react";
import ReactTable from 'react-table'
import request from 'superagent';
import Config from "../../components/config";
import Echo from "laravel-echo";
import OrderPopup from './OrderPopup';
import moment from 'moment';
import {json2excel} from 'js2excel';
import i18n from "./../../plugins/i18n.js"


let config = new Config();
class OrderMain extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            orders:[],
            orders_filter:[],
            current_order_state : null,
            actionListTriggerIndex:null,

            current_action_btn_state:0,
            current_action_btn:0,
            unseenNewOrders:0,

            order_search_value:null,
            order_details_popup_status:false,
            active_order:null,
        }
        this.action_list = [
            { name: i18n.t('pages.order.orderTable.columns.action.actionList.new'), value: '0' },
            { name: i18n.t('pages.order.orderTable.columns.action.actionList.confirmed'), value: '4' },
            { name: i18n.t('pages.order.orderTable.columns.action.actionList.sent'), value: '2' },
            { name: i18n.t('pages.order.orderTable.columns.action.actionList.delivered'), value: '1' },
            { name: i18n.t('pages.order.orderTable.columns.action.actionList.cancelled'), value: '3' }
        ]
        this.action_btn_list = [
            { name:i18n.t('pages.order.actionButton.new'), value:'0', class:'new_order' },
            { name:i18n.t('pages.order.actionButton.confirmed'), value:'4', class:'confirmed_order'},
            { name:i18n.t('pages.order.actionButton.sent'), value:'2', class:'sent_order'},
            { name:i18n.t('pages.order.actionButton.delivered'), value:'1', class:'delivery_order' },
            { name:i18n.t('pages.order.actionButton.cancelled'), value:'3', class:'cancelled_order'},
            { name:i18n.t('pages.order.actionButton.all'), value:'-1', class:'default_order'}
        ]
        this.onActionOn = this.onActionOn.bind(this);
        this.documentClickEventHandler = this.documentClickEventHandler.bind(this);
        this.OnFilter = this.OnFilter.bind(this);
        this.onSelectAction = this.onSelectAction.bind(this);
        this.onOrderByAction = this.onOrderByAction.bind(this);
        this.closeOrderDetailsPopup = this.closeOrderDetailsPopup.bind(this);
    }
    componentDidMount(){
        this.getAllOrder();
        this.listenBroadCast();
        document.addEventListener("click",this.documentClickEventHandler);
    }

    componentWillUnmount() {
        document.removeEventListener("click",this.documentClickEventHandler);
    }

    documentClickEventHandler(event){
        let index = event.target.getAttribute("data-rowIndex");
        if( index == null ){
            this.setState({ actionListTriggerIndex: null })
        }

    }

    listenBroadCast() {
        if (typeof io !== 'undefined') {
            window.Echo = new Echo({
                broadcaster: 'socket.io',
                host: config.root + ':6001'
            });
            window.Echo.private('channel-new-order_' + config.userID)
                .listen('BroadcastNewOrder', (response) => {
                    //console.debug("New broadcasted: ", response);
                    if (response != null && response.order != null && response.order.length > 0) {
                        document.getElementById("audio").play();
                        if (this.state.current_action_btn == 0) {
                            this.state.orders_filter.unshift( response.order[0] );
                            this.setState({orders_filter: this.state.orders_filter});
                        }
                        this.setState({ unseenNewOrders : this.state.unseenNewOrders + response.order.length })
                    }
                });
        }
    }

    getAllOrder(){
        let orders = request
            .post('/api/get_order_by')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json')
            .field('state', 0);
        orders.end((err, response) => {
            //console.debug("response: ", response );
            if( response.body.error == false ) {
                let orderList = response.body.data;
                if( orderList.length > 0 ){
                    orderList.map( (order, index)=>  {
                        return orderList[index].order.entities = JSON.parse( order.order.entities );
                    });
                }
                this.setState({orders: orderList, loading: false});
                this.setState({orders_filter: orderList});
            }
        });
    }

    onActionOn(event){
        event.preventDefault();
        let index = event.target.getAttribute("data-rowIndex");
        let orderState = event.target.getAttribute("data-orderState");
        this.setState({ current_order_state : orderState });

        if( this.state.actionListTriggerIndex != index ) {
            this.setState({actionListTriggerIndex: index })
        }else {
            this.setState({actionListTriggerIndex: null})
        }
    }

    attributeString(attributes) {
        let attributeArray =  attributes.map( (attribute) => {
            return(
                `${attribute.name.charAt(0).toUpperCase() + attribute.name.slice(1)}: ${attribute.value}`
            )
        })
        return attributeArray.join("; ");
    }

    onPrintOrderListAction(event) {
        event.preventDefault();
        // console.debug(`Print order receipt count: ${this.state.orders_filter.length}`);
        // console.debug(this.state.orders_filter);

        let msgConfirm = i18n.t('pages.order.confirmMessage');
        let decision = window.confirm(msgConfirm);
        if (decision == false) return false;

        let data = this.state.orders_filter.map((order) => {
            let { entities } = order.cart;
            var itemsDetail = '';
            if ( entities.length >= 0 ) {
                entities.forEach(entity => {
                    let attributes = (entity.attributes.length <= 0 ) ? '' : `${this.attributeString(entity.attributes)}`;
                    itemsDetail += `${entity.quantity} ${entity.name} @${entity.unit_price.toFixed(2)} ${order.cart.currency}\n${attributes}\nItem Total = ${entity.total_price.toFixed(2)} ${order.cart.currency}\n\n`
                });
            }

            return {
                'Order No': order.order.order_code,
                'Customer Name': `${order.profile.first_name}${order.profile.last_name != null ? ' ' + order.profile.last_name : ''}`,
                'Address': `${(order.profile.address.length <= 0 ) ? null : order.profile.address.join(", ")},${order.profile.city != null ? ' City: ' + order.profile.city : ''},${order.profile.zip != null ? ' Zip: ' + order.profile.zip : ''}`,
                'Mobile': `${order.profile.mobile_no}`,
                'Total Items': `${order.cart.total_quantity}`,
                'Items Detail': `${itemsDetail}`,
                'Grand Total': `${(order.cart.total_price).toFixed(2)} ${order.cart.currency}`
            }
        })

        try {
            json2excel({
                data,
                name: `Orders@${moment().format('ddd-DD-MMM-YYYY')}`
            });
        } catch (e) {
            console.error('export error');
        }
    }

    getActionClassName( orderAction ){
        // console.debug("class status: ", orderAction );
        switch( parseInt(orderAction) ){
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

    onSelectAction(event){
        event.preventDefault();

        let actionOrder = event.target.getAttribute("data-actionOrder");
        let index = event.target.getAttribute("data-rowIndex");
        let order_id = event.target.getAttribute("data-orderId");

        if( this.state.current_order_state == actionOrder ) {
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
            if( response.body.error === false ) {
                this.state.orders_filter[index].order.status = actionOrder;
                this.setState({actionListTriggerIndex: null})
            }
        });
    }

    onOrderByAction(event){
        event.preventDefault();
        let actionState = event.target.getAttribute("data-actionValue");
        let actionIndex = event.target.getAttribute("data-actionIndex");

        let orders = request
            .post('/api/get_order_by')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json')
            .field('state', actionState );
        orders.end((err, response) => {
            let orderList = response.body.data;
            if( orderList.length > 0 ){
                orderList.map( (order, index)=>  {
                    return orderList[index].order.entities = JSON.parse( order.order.entities );
                });
            }
            this.setState({orders: orderList, loading: false});
            this.setState({orders_filter: orderList});
        });

        this.setState({current_action_btn_state: actionIndex, current_action_btn: actionState})
        if (actionState == 0) this.setState({ unseenNewOrders: 0 });
    }

    OnFilter(){
        let search = this.refs.filter_order.value;
        let ordertList = this.state.orders;
        console.debug(ordertList);
        let filterOrder = ordertList.filter(( order )=> {
                return (
                    order.order.order_code.toLowerCase().indexOf( search.toLowerCase() ) !== -1 || order.profile.first_name.toLowerCase().indexOf( search.toLowerCase() ) !== -1
                );
            }
        );
        this.setState({ orders_filter : filterOrder });
    }

    /*
     * Show order details popup
     * @params: each order oboject
     */
    showOrderDetailsPopup(order, event){
        event.preventDefault();
        this.setState({ active_order: order, order_details_popup_status: !this.state.order_details_popup_status});
    }

    /*
     * Close details popup
     */
    closeOrderDetailsPopup(){
        this.setState({ order_details_popup_status: !this.state.order_details_popup_status});
    }

    orderTable() {
        return (
            <ReactTable
                columns = {[
                    {
                        Header: i18n.t('pages.order.orderTable.columns.orderCode'), // Custom Header components!
                        accessor: 'order.order_code',
                        width:180,
                        sortable:true,
                        showFilters:true,
                        Cell: row =>(
                            <div style={{ textAlign:'center'}} className='order_id_holder'>
                                <a onClick={this.showOrderDetailsPopup.bind(this, row.original)} href="#" className="order_details_popup_btn"> <i className="fa fa-location-arrow"></i> </a>
                                <span className="order_id">{row.value}</span>
                            </div>
                        )
                    },
                    {
                        Header: i18n.t('pages.order.orderTable.columns.customer'),
                        accessor: 'profile.first_name',
                        showFilters:true,
                        minWidth:120,
                        Cell: row => (
                            <div className="details">
                                {row.value}
                            </div>
                        )
                    },
                    {
                        Header: i18n.t('pages.order.orderTable.columns.quantity'),
                        accessor: 'cart.total_quantity', // Custom value accessors!
                        minWidth:80,
                        Cell: row => (
                            <div className="details text-center">
                                { row.value }
                            </div>
                        )

                    },
                    {
                        Header: i18n.t('pages.order.orderTable.columns.location'),
                        accessor: 'profile.city', // Custom value accessors!
                        minWidth:100,
                        Cell: row => (
                            <div className="details">
                                { (row.value === null ) ? '-' : row.value }
                            </div>
                        )

                    },
                    {
                        Header: i18n.t('pages.order.orderTable.columns.deliver'),
                        accessor: 'order.status_detail.expected_delivery_time', // Custom value accessors!
                        minWidth:200,
                        Cell: row => (
                            <div className="delivery_status_detail">
                                { ( row.value === null )  ? null :
                                    <div>
                                        <small>
                                            <span> {row.value.date} </span>
                                            <span> - {row.value.time} </span>
                                        </small>
                                    </div>}
                            </div>
                        )

                    },
                    {
                        Header: props => <span>{i18n.t('pages.order.orderTable.columns.time')}</span>, // Custom Header components!
                        accessor: 'order.status_detail',
                        Cell: row => (
                            <div style={{ textAlign:'center'}} className='number'>
                                { (row.value.order_time === null ) ? null :  <small>{ moment(row.value.order_time, "YYYY-MM-DD hh:mm:ss A").format('ddd, hh:mm A')} </small> }
                            </div>
                        )
                    },
                    {
                        Header: props => <span>{i18n.t('pages.order.orderTable.columns.total')}</span>, // Custom Header components!
                        accessor: 'cart.total_price',
                        Cell: row => (
                            <div style={{ textAlign:'center'}} className='number'>
                                { parseFloat(row.value).toFixed(2) }
                            </div>
                        )
                    },
                    {
                        Header: i18n.t('pages.order.orderTable.columns.action.name'),
                        accessor: 'id',
                        hideFilter: false,
                        width:60,
                        Cell: row => (
                            <div className="action_container" style={{ textAlign: 'center' }}>
                                {/* {  console.debug("option: ",row) } */}
                                <span
                                    onClick={this.onActionOn}
                                    data-rowIndex={row.index}
                                    data-orderState={row.original.order.status}
                                    style={ styles.action_bullet }
                                    className={this.getActionClassName( row.original.order.status ) + " action-bullet" } >

                                    ...
                                </span>

                                <div className="actions-list" style={ (this.state.actionListTriggerIndex == row.index ) ? styles.action_list_on : styles.action_list_off } >
                                    <ul>
                                        { this.action_list.map( (item, index )=>  {
                                            return <li
                                                data-rowIndex={row.index}
                                                onClick={ this.onSelectAction }
                                                key={index}
                                                data-actionOrder={ item.value }
                                                data-orderId={ row.original.order.id }
                                            >
                                                <span className={ (row.original.order.status == parseInt(item.value) ) ? this.getActionClassName( row.original.order.status ) : null } ></span>
                                                { item.name }
                                            </li>
                                        }) }

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


    mainView(){
        return (
            <div className="dashboard-widget list order-list">
                <div  className="row bottom-margin-15px order-option">
                    <div className="col-md-6 left_option">
                        <ul>
                            <li className="big-title"> <span onClick={this.onPrintOrderListAction.bind(this)} className="menu_links" onMouseOver="" style={{cursor: 'pointer'}}> &#x1F5A8; </span> {i18n.t('pages.order.orderTable.name')}  <span className="counter text-info">{ this.state.orders_filter.length }</span>
                            </li>
                            <li className="Search_Bar"> <span className="SearchIcon"> <i className="fa fa-search fa-2x"></i></span> <input type="text" onChange={this.OnFilter} ref="filter_order"  className="product_search_field" placeholder={i18n.t('pages.order.orderTable.search.placeholder')}/> </li>
                        </ul>
                    </div>
                    <div className="col-md-6 text-right action_btn_container">
                        {
                            this.action_btn_list.map( (btn, index ) => {
                                return <a key={ index }
                                          data-actionValue={ btn.value }
                                          data-actionIndex = { index }
                                          onClick={ this.onOrderByAction }
                                          href="#"
                                          className={ (this.state.current_action_btn_state == index ) ? "active action-btn "+btn.class : "action-btn "+btn.class } >
                                    { (btn.value == 0 && this.state.unseenNewOrders > 0  ) ? <span className="notifyNewOrder">{ this.state.unseenNewOrders }</span> : null  }
                                    { btn.name }
                                    </a>
                            })
                        }
                    </div>
                </div>
                <div className="order_list">
                    { this.orderTable() }
                    { (this.state.order_details_popup_status) ? <OrderPopup onCloseAction={this.closeOrderDetailsPopup.bind(event)} order={this.state.active_order}  className="active orderModal"/> : null }
                </div>
            </div>
        )
    }

    render(){
        return  this.mainView()
    }

}

export default OrderMain;

const styles = {
    action_bullet: {
        display:'inline-block',
        width:'30px',
        height:'30px',
        color:"#FFFFFF",
        paddingTop:'5.5px',
        borderRadius:'50px',
        lineHeight: 0,
        fontSize:'22px',
        cursor:'pointer',
        border:"2px solid rgba(255, 255, 116,0.6)"
    },
    action_list_off: {
        display:'none',
        opacity:0,
        zIndex:99
    },
    action_list_on: {
        display:'block',
        opacity:1,
        zIndex:999
    }
}