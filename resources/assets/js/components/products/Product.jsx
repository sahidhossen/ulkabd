import React from "react";
import ReactDom from 'react-dom';

class Product extends React.Component {

    constructor(props ){
        super( props )
        this.state = {
            Product : this.props.row
        }
    }

    render(){
        let divStyle = {
            color: 'blue',
            padding: '10px'
        }
        let product = this.state.Product;
        let categoryChain = product.active_chain_list; //Need to merge two array
        let categoryChainAttribute = ( categoryChain.required_attributes == null ) ? null : categoryChain.required_attributes.split(',');
        let attributes_array = (product.product_attributes === null ) ? [] :  product.product_attributes.split(';');
        let all_attribute = (categoryChainAttribute ==null ) ?  null : categoryChainAttribute.map((att, i)=>

                <div className="form-group" key={i}>
                    <label htmlFor="ProductName" className="col-md-3"> { (typeof attributes_array[i] !== 'undefined' ) ? attributes_array[i].split(':')[0] : att }  </label>
                    <div className="col-md-9">
                        <input type="text" value={ (typeof attributes_array[i] !== 'undefined') ? attributes_array[i].split(':')[1] : '' } onChange={this.onHandleChange}/>
                    </div>
                </div>
            )

        return (
            <div style={ divStyle }>
                <form className="form form-horizontal">
                    <div className="col-md-6">
                        <div className="form-group">
                            <label htmlFor="ProductName" className="col-md-3"> Name </label>
                            <div className="col-md-9">
                                <input type="text" value={product.name} onChange={this.onHandleChange}/>
                            </div>
                        </div>
                        <div className="form-group">
                            <label htmlFor="ProductCode" className="col-md-3"> Code </label>
                            <div className="col-md-9">
                                <input type="text" value={product.code} onChange={this.onHandleChange}/>
                            </div>
                        </div>
                        <div className="form-group">
                            <label htmlFor="ProductOfferPrice" className="col-md-3"> Offer Price </label>
                            <div className="col-md-9">
                                <input type="text" value={ (product.offer_price) ? product.offer_price : '' } onChange={this.onHandleChange}/>
                            </div>
                        </div>
                        <div className="form-group">
                            <label htmlFor="ProductPrice" className="col-md-3"> Price </label>
                            <div className="col-md-9">
                                <input type="text" value={product.price} onChange={this.onHandleChange}/>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-6">
                        <div className="form-group">
                            <label htmlFor="ProductImage" className="col-md-3"> Image </label>
                            <div className="col-md-9">
                                <Dropzone
                                    className="productImageUpload"
                                    multiple={false}
                                    accept="image/*"
                                    onDrop={this.onImageDrop}>
                                    <div className="dropArea">  <img className="img-circle" src={this.state.uploadImageUrl} alt="Bot Title"/> </div>
                                </Dropzone>
                            </div>
                        </div>
                        { all_attribute }

                    </div>
                </form>
            </div>
        )
    }
}


export default Product;


