import React from 'react';
import moment from 'moment';
import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";
import i18n from "./../../plugins/i18n.js"

pdfMake.vfs = pdfFonts.pdfMake.vfs;
pdfMake.fonts = {
    Roboto: {
        normal: 'Roboto-Regular.ttf',
        bold: 'Roboto-Medium.ttf',
        italics: 'Roboto-Italic.ttf',
        bolditalics: 'Roboto-MediumItalic.ttf'
    },
    SolaimanLipi: {
        normal: 'SolaimanLipi-Normal.ttf',
        bold: 'SolaimanLipi-Bold.ttf'
    }
};

export default class OrderPopup extends React.Component{
    constructor(props){
        super(props);

        this.onCloseAction = this.onCloseAction.bind(this);
    }

    onCloseAction(event){
        event.preventDefault();
        this.props.onCloseAction(event);
    }
    
    attributeStringForReceipt(attributes) {
        let attributeArray =  attributes.map( (attribute) => {
            return(
                `${attribute.name.charAt(0).toUpperCase() + attribute.name.slice(1)}: ${attribute.value}`
            )
        })
        return attributeArray.join("; ");
    }

    itemListForReceipt( order ) {
        let { entities } = order.cart;
        return (entities.length <= 0 ) ? null : entities.map( (entity) => {
            return ([
                entity.name,
                (entity.attributes.length <= 0 ) ? null : `${this.attributeStringForReceipt(entity.attributes)}`,
                {text: entity.quantity, alignment: 'right'},
                {text: `@${entity.unit_price.toFixed(2)} ${order.cart.currency}`, alignment: 'right'},
                {text: `${entity.total_price.toFixed(2)} ${order.cart.currency}`, alignment: 'right'}
            ])
        })
    }

    getOrderDocDefination(order) {

        var docDef = {};

        let client = order.cart.cart_name;
        let header = `${order.profile.first_name}${order.profile.last_name != null ? ' ' + order.profile.last_name : ''} From ${order.profile.address.length > 0 ? order.profile.address[0] : ''}${(order.order.status_detail.order_time === null ) ? '' : ' AT '+moment(order.order.status_detail.order_time, "YYYY-MM-DD hh:mm:ss A").format('ddd, DD MMM YYYY, hh:mmA ')}`
        let orderNo = `Order No: ${order.order.order_code}`
        let consumer = `Customer: ${order.profile.first_name}${order.profile.last_name != null ? ' ' + order.profile.last_name : ''}`
        let address  = `Address: ${(order.profile.address.length <= 0 ) ? '' : order.profile.address.join(", ")},${order.profile.city !=null ? ' City: ' + order.profile.city : ''},${order.profile.zip != null ? ' Zip: ' + order.profile.zip : ''}`;
        let mobile = `Mobile: ${order.profile.mobile_no}`
        let numberOfItems = `Total Items: ${order.cart.total_quantity}`
        let totalPayable = `Total Payable: ${(order.cart.total_price).toFixed(2)} ${order.cart.currency}`
        let footer = {
            poweredBy: `Powered by `,
            platform: `Usha Chatbot Platform`,
            business: ` | Ulka Bangladesh`
        }
        let tableRows = this.itemListForReceipt(order)

        if (header == null || tableRows == null) return null;

        docDef.content = [
            {text: client, style: ['bigHeader'], margin: [ 0, 0, 0, 30 ]},
            {text: header, style: ['header'], margin: [ 0, 0, 0, 20 ]},
            {text: orderNo, bold: true},
            {text: consumer},
            {text: address},
            {text: mobile, margin: [ 0, 0, 0, 5 ]},
            {text: numberOfItems, bold: true},
            {text: totalPayable, bold: true, margin: [ 0, 0, 0, 10 ]},
            {
                table: {
                    widths: ['auto', 'auto', 'auto', 90, 90],
                    body: [
                        ['Item', 'Detail', {text: 'Quantity', alignment: 'center'}, {text: 'Rate', alignment: 'center'}, {text: 'Amount', alignment: 'center'}],
                        ...tableRows,
                        [
                            '', '', '',
                            {text: 'Total:', bold: true, alignment: 'right'},
                            {text: `${(order.cart.total_price).toFixed(2)} ${order.cart.currency}`, bold: true, alignment: 'right'}
                        ]
                    ]
                },
                layout: {
                    fillColor: function (i, node) {
                        return (i === 0) ? '#CCCCCC' : null;
                    }
                }
            }
        ];

        docDef.footer = {
            columns: [
                { 
                    text: [
                        {text: footer.poweredBy, style: ['footerLightItalic']},
                        {text: footer.platform, style: ['footerBold']},
                        {text: footer.business, style: ['footerNormal']},
                    ],
                    alignment: 'right',
                    margin: [ 0, 0, 40, 0 ]
                }
            ]
        }

        docDef.styles = {
            bigHeader: {
                fontSize: 20,
                bold: true,
                alignment: 'center'
            },
            header: {
                fontSize: 16,
                bold: true,
                alignment: 'center'
            },
            footerLightItalic: {
                font: 'Roboto',
                fontSize: 11,
                italics: true
            },
            footerBold: {
                font: 'Roboto',
                fontSize: 11,
                bold: true
            },
            footerNormal: {
                font: 'Roboto',
                fontSize: 11
            }
        }

        docDef.defaultStyle = {
            fontSize: 12,
            font: 'SolaimanLipi'
        }

        return docDef;
    }

    onPrintAction(event) {
        let { order } = this.props;
        let fileName = order.order.order_code + '|' +  order.profile.first_name + order.profile.last_name + '.pdf';
        let docDef = this.getOrderDocDefination(order);
        if (docDef != null) pdfMake.createPdf(docDef).download(fileName);
    }
    
    attributeList( attributes ){
        return attributes.map( (attribute, index) => {
            return(
                <span key={index}> <small><strong>{attribute.name}:</strong> {attribute.value} </small></span>
            )
        })
    }

    itemList( order ) {
        let { entities } = order.cart;
        return (entities.length <= 0 ) ? null : entities.map( (entity, index) => {
                return (
                    <div className="product" key={index}>
                        <div className="flex-box space-between product-header">
                            <div className="flex per-unit"> @{entity.unit_price.toFixed(2)} {order.cart.currency}</div>
                            <div className="flex counter"><span>{entity.quantity}</span></div>
                            <div className="flex total-price">{order.cart.currency_sign}{entity.total_price.toFixed(2)} {order.cart.currency} </div>
                        </div>
                        <div className="flex-box product-details flex-start">
                            <div className="flex feature-img">
                                <div className="img-box">
                                    <i style={{backgroundImage:`url(${entity.img_url})`}} ></i>
                                </div>
                                <div className="product-code">
                                    {entity.entity_code}
                                </div>
                            </div>
                            <div className="flex-1 details">
                                <h3 className="title"> {entity.name} </h3>
                                <div className="attributes">
                                    <p> <strong> {i18n.t('pages.order.orderPopup.details')} </strong></p>
                                    {(entity.attributes.length <= 0 ) ? null : this.attributeList( entity.attributes) } <p> </p>
                                </div>
                            </div>
                        </div>
                    </div>
                )
            })

    }

    render(){
        let { className, order } = this.props;
        let address  = (order.profile.address.length <= 0 ) ? null : order.profile.address.map( (address, index) => { return <span key={index}>{ address }<br/></span> });
        return (
            <div className={"ulka-modal-shadow "+className}>
                <div className="ulka-modal-container">
                    <div className="ulka-modal-header flex-box">
                        <span onClick={this.onPrintAction.bind(this)} className="ulka-popup-close"> &#x1F5A8; </span>
                        <div className="extend-header flex-1">
                            { order.profile.first_name } { order.profile.last_name } {i18n.t('pages.order.orderPopup.from')} { order.profile.address.length > 0 ? order.profile.address[0] : null } <small>{ (order.order.status_detail.order_time === null ) ? '' : '@ '+moment(order.order.status_detail.order_time, "YYYY-MM-DD hh:mm:ss A").format('ddd, MMM hh:mmA ')}</small>
                        </div>
                        <span onClick={this.onCloseAction.bind(this)} className="ulka-popup-close"> ✕ </span>
                    </div>
                    <div className="ulka-modal-body">
                       <div className="customer-address-area">
                           <div className="flex address street"> { address } </div>
                           <div className="flex-box">
                               <div className="flex-1 address city"> {i18n.t('pages.order.orderPopup.city')} {order.profile.city} </div>
                               <div className="flex-1 address zip"> {i18n.t('pages.order.orderPopup.zip')} {order.profile.zip} </div>
                           </div>
                           <div className="flex-box">
                               <div className="flex-1 address city"> {i18n.t('pages.order.orderPopup.mobile')} {order.profile.mobile_no} </div>
                               <div className="flex-1 address zip"> {i18n.t('pages.order.orderPopup.email')} {(order.profile.emails === null ) ? null : order.profile.emails[0]} </div>
                           </div>
                       </div>
                        <div className="flex-box order-no-total">
                            <div className="flex-1 order-no"> {i18n.t('pages.order.orderPopup.orderno')} {order.order.order_code} </div>
                            <div className="flex-1 total text-right"> {i18n.t('pages.order.orderPopup.total')} {order.cart.currency_sign} {(order.cart.total_price).toFixed(2)} <small>{order.cart.currency}</small> </div>
                        </div>
                        <div className="flex-box text-center item-no">
                            <div className="flex-1"> {order.cart.total_quantity} {i18n.t('pages.order.orderPopup.items')} </div>
                        </div>
                        <div className="product-list">
                            { this.itemList(order)}
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

