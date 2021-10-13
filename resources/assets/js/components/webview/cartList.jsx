import React from 'react';
import CartHeader from '../webview/cartHeader';
import { connect } from 'react-redux';
import { Authentication } from './Auth/Authenticate';
import { fetchProfile } from  '../Actions/profileActions';
import Loader from '../Loader';
import { LoadFbMessengerExtensionThread } from '../Actions/fbextensionActions';
import { updateCart } from '../Actions/profileActions';


@connect( (store) => {
    return {
        profile : store.profile,
        thread_context : store.thread_context,
    }
})

class cartList extends React.Component{
    constructor(props){
        super(props)
        this.state = {
            counter_value:0,

            extension_platform: "facebook",
            cart: {
                entities: []
            },
            is_checkedout:0,

            fetched : false,

            cart_status:false
        }
    }
    componentDidMount(){
        Authentication();
        let { fetched, profile } = this.props.profile;
        let cart = this.state.cart;
        console.debug("mounting cart..", fetched );
        if( fetched === true) {
            cart = profile.cart;
            this.setState({ cart: cart, fetched:true, cart_status:false })
        }

        if( this.props.thread_context.fetched === false )
            this.props.dispatch(LoadFbMessengerExtensionThread());

    }

    fetchUserProfile(thread_context){
        let  context = thread_context.thread_context;
        let data = { external_id: context.psid, extension_platform:'facebook' }
        this.props.dispatch(fetchProfile(data));
    }

    componentWillReceiveProps(nextProps){
        let { profile, thread_context } = nextProps;
        if( thread_context.fetched === true ){
            this.props.dispatch({ type:"FETCH_FB_THREAD_COMPLETE"});
            console.debug("only one time", thread_context);
            this.fetchUserProfile( thread_context );
        }

        if( typeof profile.checkout_fetched !== 'undefined' && profile.checkout_fetched === true ){
            this.setState({  cart_status:false })
        }

        if( typeof profile.fetched !== 'undefined' && profile.fetched === true ){
            let { cart } = this.state;
            if(profile.profile !== null )
                 cart = profile.profile.cart;
           this.setState({ cart, fetched:true,  cart_status:false})
        }

        if( typeof profile.cart_update !== 'undefined' &&   profile.cart_update === true ){
            //this.props.dispatch({ type:'FETCH_PROFILE' });
            this.props.history.push('/webview/profile_update', true);
        }

    }

    _handlerOnChange(event){
        this.setState({ counter_value: event.target.value })
    }

    onMaximumOrderCounterUpdate(step, index, event){
        event.preventDefault();
        let {cart} = this.state;
        let currentEntities = cart.entities[index];
        let updatePrice = currentEntities.total_price;

        //console.debug("current: ", updatePrice, ' limit: ',currentEntities.available_quantity );

        if( step ){
            if( currentEntities.quantity < currentEntities.available_quantity ) {
                currentEntities.quantity += 1;
                cart.total_quantity +=1;
                updatePrice = parseFloat(currentEntities.quantity*currentEntities.unit_price);
                cart.total_price =  cart.total_price+currentEntities.unit_price;
            }
        }else {
            if(currentEntities.quantity > 0 ) {
                currentEntities.quantity -= 1;
                cart.total_quantity -=1;
                updatePrice = parseFloat(currentEntities.quantity*currentEntities.unit_price);
                cart.total_price = cart.total_price-currentEntities.unit_price;
            }
        }
        currentEntities.is_remove = false;
        currentEntities.total_price = updatePrice;
        console.debug("update: ", updatePrice );
        this.setState({ cart, cart_status:true })
    }

    removeCartItem(index, event ){
        event.preventDefault();
        let {cart} = this.state;
        let currentEntities = cart.entities[index];

        cart.total_price -= currentEntities.total_price;
        cart.total_quantity -= currentEntities.quantity;

        currentEntities.total_price = 0;
        currentEntities.quantity = 0;
        currentEntities.is_remove = true;
        this.setState({ cart, cart_status:true })
    }

    onCheckoutProcess(event){
        event.preventDefault();
        let { profile } = this.props.profile;
        let { cart } = this.state;
        profile.cart = cart;
        this.props.dispatch({ type: 'PROFILE_UPDATE_CART_FULFILLED', payload: profile })
    }

    onUpdateProcess(event){
        event.preventDefault();
        let { thread_context } = this.props.thread_context;
        let { profile } = this.props.profile;
        let { cart } = this.state;
        cart.external_id = thread_context.psid;
        cart.extension_platform = "facebook";
        cart.is_checkedout = false;
        //console.debug("update profile: ", profile);
       // console.debug("update cart: ", cart);
        this.props.dispatch( updateCart(cart, profile ) );
    }

    renderCartItem( ){
        let { cart } = this.state;
        let { entities } = cart;
        return (entities.length === 0 ) ? null :
            entities.map( (entity, index) => {
                let is_remove = ( entity.is_remove !== 'undefined' && entity.is_remove !== false) ? entity.is_remove : null ;
                let imgUrl = (entity.img_url === null ) ? "/images/img_unavailable.png" : entity.img_url;
                return (
                    <div className={(is_remove) ? "cart-item item-remove" : "cart-item"} key={index}>
                        <div className="cart-quantity-details">
                            <div className="col per-unit"> @ {cart.currency_sign}{entity.unit_price} </div>
                            <div className="col p-total"> {cart.currency_sign} {entity.total_price} {cart.currency} </div>
                        </div>
                        <div className="product">
                            <div className="col-1">
                                <div className="p-img-box">
                                    <i style={{backgroundImage:`url(${imgUrl})`}} ></i>
                                </div>
                                <div className="p-code"> {entity.entity_code} </div>
                            </div>
                            <div className="col-4">
                                <div className="p-details">
                                    <p className="p-name"> {entity.name} </p>
                                    <div className="attributes-box">
                                        <p className="att-title"> Attributes: </p>
                                        <div className="attributes">
                                            {
                                                (entity.attributes.length === 0 ) ? null :  entity.attributes.map( (attr, index) => { return  <span key={index}> {attr.name}: <strong> {attr.value} </strong></span> })
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="product-action">
                            <div className="col">
                                { (is_remove) ? <a href="#" onClick={this.onMaximumOrderCounterUpdate.bind( this, true, index )} className="close-btn active-btn"> + </a> : <a href="#" onClick={this.removeCartItem.bind(this, index )} className="close-btn"> ✕ </a>}
                            </div>
                            <div className="col">
                                <div className="count-engine">
                                    <a href="#" onClick={this.onMaximumOrderCounterUpdate.bind(this, false, index )}> <i className="fa fa-minus"></i> </a>
                                    <input type="text" disabled={true} name="max_order_counter" value={entity.quantity} onChange={this._handlerOnChange.bind(this)}/>
                                    <a href="#" onClick={this.onMaximumOrderCounterUpdate.bind(this, true, index )}> <i className="fa fa-plus"></i> </a>
                                </div>
                            </div>
                        </div>
                    </div>
                )
            })
    }

    renderCartView(){
        let { cart } = this.state;
        let { user } = this.props.profile.profile;
        return (
            <div className="webview-cart-view">
                <CartHeader user={user}/>
                <div className="cart-container">
                    <div className="cart-header">
                        <p className="cart_item_counter"> <span> {cart.total_quantity} </span> Items in Bag </p>
                        <p className="order_number"> Order Number: {cart.order_number } </p>
                    </div>
                    <div className="cart-body">
                        { this.renderCartItem() }
                    </div>
                    <div className="grand-total">
                        <div className="grand-total-price">
                            <div className="col total-text"> Total  </div>
                            <div className="col total-amount"> {cart.currency_sign} {cart.total_price.toFixed(2)} {cart.currency} </div>
                        </div>
                        {
                            (this.state.cart_status === false )
                                ?
                                    <div className="checkout-btn"><a href="#" onClick={this.onCheckoutProcess.bind(this)}> Checkout </a>  </div>
                                :
                                    <div className="checkout-btn update-cart"><a href="#" onClick={this.onUpdateProcess.bind(this)}> Update Cart </a>  </div>
                        }
                    </div>
                </div>
            </div>
        )
    }

    renderEmptyCart(){
        let { message } = this.props.profile;
        return (
            <div className="empty-cart-cartList">
                <h3 className="title"> { message } </h3>
            </div>
        )
    }

    render(){
        let { fetched } = this.state;
        let { profile } = this.props.profile;
        //console.debug("f-: ", fetched)
        return ( fetched ) ? ( profile === null ) ? this.renderEmptyCart() : this.renderCartView() : <Loader/>;
    }
}

export default cartList;