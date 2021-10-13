import React from "react";
import Config from "../../components/config";
import i18n from "../../plugins/i18n.js"

let config = new Config();

class SingleCategory extends React.Component {

    constructor( props ){
        super( props );
        this.state = {
            name : this.props.category.name,
            next: this.props.category.next,
            prev: this.props.category.prev,
            cat_id: this.props.category.id,
            current_category: this.props.category,

            has_subcategory:this.props.has_subcategory
        }
        this.alertOptions = {
            offset: 14,
            position: 'top right',
            theme: 'dark',
            time: 5000,
            transition: 'fade'
        }
        this.OnActiveState = this.OnActiveState.bind(this);
        this.onDeleteAction = this.onDeleteAction.bind(this);
        this.onEditMode = this.onEditMode.bind(this);
    }

    OnActiveState( event ){
        event.preventDefault();

        this.props.handleActiveCategory( this.props.index, this.state.cat_id, this.props.category );
    }

    //Delete Mode
    onDeleteAction( event ){
        event.preventDefault();
        this.props.beforeDeleteOperation(this.state.cat_id);
    }

    //Edit Mode
    onEditMode(event){
        event.preventDefault();

        this.props.showEditBox(this.props.category)
    }

    categoryAction(){
        return (
            <div className="category_action">
                <span onClick={this.onEditMode} className="edit"> {i18n.t('common.buttons.edit')} </span>
                <span onClick={this.onDeleteAction} className="delete"> {i18n.t('common.buttons.delete')} </span>
            </div>
        )
    }

    render(){

        let label = null;
        if ((!!+this.props.category.rss_feed) == true) {
            label = <span className={this.props.category.has_subcategory == true ? "badge-danger" : "badge-success"}> RSS </span>
        }
        else if(
            (this.props.category.has_subcategory == true || this.props.category.flag == 7) &&
            this.props.category.product_count > 0
        ) {
            label = <span className="badge-danger"> { this.props.category.product_count} </span>
        }
        else if(
            this.props.category.has_subcategory == false &&
            this.props.category.product_count > 0 &&
            this.props.category.flag != 7
        ) {
            label = <span className="badge-success"> { this.props.category.product_count} </span>
        }

        return  (
            <li key={this.props.index}>
                <a href="#" data-lastIndex={this.props.isNewBtn} className={ (this.props.onActive == false) ? 'parent' : 'parent active'} onClick={this.OnActiveState} data-cat_id={this.props.category.id}>
                    { this.props.category.name }
                    { label }
                    </a>
                { (this.props.chainIndex == 0) ? null : (this.props.onActive == true ) ? this.categoryAction() : null }

            </li>

            )
    }

}

export default SingleCategory;