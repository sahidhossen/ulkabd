import React from 'react';

class SubComponent extends  React.Component {

    constructor( props ){
        super(props);
        this.state = {
            Product: this.props.row.row
        }
    }

    render() {
        let divStyle = {
            color: 'blue',
            padding: '10px'
        }
        let product = this.state.Product;

        let categoryChain = product.active_chain_list; //Need to merge two array
        let categoryChainAttribute = ( categoryChain.required_attributes == null ) ? null : categoryChain.required_attributes.split(',');

        let attributes_array = (product.product_attributes === null ) ? [] : product.product_attributes.split(';');

        let all_attribute = (categoryChainAttribute == null ) ? null : categoryChainAttribute.map((att, i) =>

                <div className="form-group" key={i}>
                    <label htmlFor="ProductName"
                           className="col-md-3"> { (typeof attributes_array[i] !== 'undefined' ) ? attributes_array[i].split(':')[0] : att }  </label>
                    <div className="col-md-9">
                        <input type="text" ref='name'
                               value={ (typeof attributes_array[i] !== 'undefined') ? attributes_array[i].split(':')[1] : '' }
                               onChange={this.onHandleChange}/>
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
                                <input type="text" value={product.name} defaultValue={this.state.product_name}
                                       onChange={this.onHandleChange}/>
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
                                <input type="text" value={ (product.offer_price) ? product.offer_price : '' }
                                       onChange={this.onHandleChange}/>
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
                                    <div className="dropArea"><img className="img-circle"
                                                                   src={this.state.uploadImageUrl} alt="Bot Title"/>
                                    </div>
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



<ReactTable
    data={this.state.products}
    defaultPageSize={3}
    columns = {[{
        header: 'ID',
        accessor: 'id' // String-based value accessors!
    },
        {
            header: 'Code',
            accessor: 'code',
            showFilters:true,
            render: props => <span className='number'>{props.value}</span> // Custom cell components!
        }, {
            header: 'Product',
            accessor: 'name', // Custom value accessors!
        },
        {
            header: props => <span>Price</span>, // Custom header components!
            accessor: 'price'
        },
        {
            header: 'Image',
            accessor: 'is_image',
            hideFilter: true,
            render: row => (
                <div style={{textAlign: 'center'}}>
                                        <span>
                                            <span style={{
                                                color: row.value === 0 ? '#ff2e00'
                                                    : row.value === 1 ? '#ffbf00' : '#57d500',
                                                transition: 'all .3s ease'
                                            }}>
                                                &#x25cf;
                                            </span>
                                          </span>
                </div>
            )

        },
        {
            header: 'Options', // Custom header components!
            accessor: 'id',
            render: row => (
                <div style={{textAlign: 'center'}}>

                    <a href={ config.path_name+'/'+row.value } className="btn btn-xs btn-success"> Edit </a>
                    <a href="#" data-product_id={ row.value } ref="product_delete" onClick={this.onDeleteAction} className="btn btn-xs btn-danger"> Delete </a>
                </div>
            )
        },
    ]}

    defaultFilterMethod={(filter, row) => (String(row[filter.id]) === filter.value)}
/>