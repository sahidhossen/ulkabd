import React from 'react';
import i18n from "../../plugins/i18n.js"

const Showstyle = {
    transition : 'all 1s linear 0s',
    transform : 'scale(1)',
    display:'block',
    opacity: 1
}
const Hidestyle = {
    transition : 'all 1s linear 0s',
    transform : 'scale(0)',
    display:'none',
    opacity: 0
}

let categoryCollection =  [
    { name:'category_1', 'id':12 },
    { name:'category_2', 'id':13 },
    { name:'category_3', 'id':14 },
    { name:'category_4', 'id':15 }
];


class DeletePopup extends React.Component {
    constructor( props ){
        super(props)
        this.state = {
            isShow : this.props.isShow,
            transfer_category_id : 1,
            delCategory :  this.props.deleteResponse.delCategory,
            message : (this.props.deleteResponse.message === null ) ? null : this.props.deleteResponse.message,
            parentAndSiblings : (this.props.deleteResponse.parentAndSiblings.length == 0 ) ? null : this.props.deleteResponse.parentAndSiblings
        }
        this.ClosePopup = this.ClosePopup.bind(this);
        this.onSelectChange = this.onSelectChange.bind(this);
        this.onTransferAndDelete = this.onTransferAndDelete.bind(this);
    }
    ClosePopup(event){
        event.preventDefault();
        this.props.closePopup(event);
    }
    onSelectChange( event ){
        event.preventDefault();
        //console.debug(event.target.value);
        this.setState({ transfer_category_id : event.target.value })
    }

    getAllDeleteCategoryRecursive( categories ) {
        let categoryList = null;
        let categoriesChild = categories.children_recursive
        if(categoriesChild.length !== 0 && categoriesChild !== null ) {
            categoryList =  categoriesChild.map((cat, index) => {
                return <li key={index}> {cat.name} { this.getAllDeleteCategoryRecursive(cat)} </li>
            })
        }
        return (
            <ul>
                { categoryList }
            </ul>
        )
    }
    getDeleteCategoryList( categories ){
            return(
                <ul>
                    <li>{ categories.name } </li>
                    { this.getAllDeleteCategoryRecursive(categories) }
                </ul>
            )
    }

    onTransferAndDelete(event){
        event.preventDefault();
        this.props.handleTransferAndDelete( this.refs.transfer_category_id.value );
    }

    renderView(){

        let parentAndSiblings = this.props.deleteResponse.parentAndSiblings;
        let selctOptions = (parentAndSiblings.children === null ) ? null :
            parentAndSiblings.children.map( (category, index ) => {
                return <option key={index} value={ category.id } > -- { category.name} </option>
            })

        return (
            <div className="category-form-shadow deletePopup" style={(this.props.isShow == true) ? Showstyle : Hidestyle}>
                <div className="category-form-main" style={ (this.props.isShow == true) ? Showstyle : Hidestyle }>
                    <h3 className="title title-thin"> {i18n.t('pages.category.deletePopup.headerTitle') } </h3>
                    <form className="form-horizontal" onSubmit={this.onTransferAndDelete}>
                        <div className="form-group">
                            <div className="delete_category_list">
                                { this.getDeleteCategoryList(this.props.deleteResponse.delCategory)}
                            </div>
                        </div>
                        <div className="form-group">
                            <div className="warning_product_desc">
                                 <p> {this.props.deleteResponse.message}  </p>
                                <select ref="transfer_category_id" value={this.state.transfer_category_id} onChange={this.onSelectChange} name="transfer_category_id" className="form-control">
                                    <option value={parentAndSiblings.id}> { parentAndSiblings.name } </option>
                                    { selctOptions }
                                </select>

                            </div>
                        </div>
                        <div className="form-group category_btn">
                            <input type="submit" value={i18n.t('pages.category.deletePopup.deleteButton')}   className="btn btn-admin btn-admin-red pull-left"/>
                            <a href="#" className="btn btn-theme-success pull-right" onClick={this.ClosePopup}> {i18n.t('common.buttons.cancel')} </a>
                        </div>
                    </form>
                </div>
            </div>
        )
    }
    render(){
        return (
            this.renderView()
        )
    }
}

export default DeletePopup;