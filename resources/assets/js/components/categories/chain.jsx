import React from "react";
import Category from "../../components/categories/category";
import Popup from '../../components/categories/category_popup';
import request from 'superagent';
import Config from "../../components/config";
import DeletePopup from "../../components/categories/category_delete_popup";

let config = new Config();

class CategoryChain extends React.Component {

    constructor( props ){
        super( props );
        this.state = {
            chainList:this.props.chainList,

            activeCategoryIndex: null,
            activeObject:[],
            activeCategoryID:null,
            has_subcategory:false, //Check if there is a subcategory when click on edit btn

            //imageOriginHeight:null,
            //imageOriginWidth:null,
            uploadedFile:null,

            name: null,
            description:null,
            text_response:[],
            external_link:null,
            required_attributes:[],
            synonyms:[],
            uploadedImage: null,

            is_image_delete: 0,
            duplicate_error_message:null,

            isPopup:false,
            error_found : false,
            upload_image_error:false,
            parentID:null,

            isDeletePopup:false,
            delete_category_id:null,
            deleteResponse:null,

            fetched: true,
            rss_feed: false,
            category_product_count: 0
        };

        this.alertOptions = {
            offset: 14,
                position: 'bottom right',
                theme: 'dark',
                time: 5000,
                transition: 'scale'
        };

        this.handleActiveCategory = this.handleActiveCategory.bind(this);
        this.ClosePopup = this.ClosePopup.bind(this);
        this.catchImageUploadFile = this.catchImageUploadFile.bind(this);
        this.showEditBox = this.showEditBox.bind(this);
        this.addNewCategoryBox = this.addNewCategoryBox.bind(this);

        this.onChangeHanlder = this.onChangeHanlder.bind(this);
        this.onSubmit = this.onSubmit.bind(this);
        this.beforeDeleteOperation = this.beforeDeleteOperation.bind(this);
        this.handleTransferAndDelete = this.handleTransferAndDelete.bind(this);
    }

    handleActiveCategory( activeIndex, category_id, selectedCat ) {
        this.props.selectionHistory.length = this.props.index ;
        this.props.selectionHistory.push(selectedCat);
        let parentResonse = this.props.loadNewChain( this.props.index,category_id );
        if(parentResonse == true ) {
            this.setState({activeCategoryIndex: activeIndex})
        }
    }

    ClosePopup(event){
        this.setState({ isPopup : false });
        this.setState({ isDeletePopup : false });
    }


    catchImageUploadFile(file){
        if( file === 'delete' ){
            this.setState({ uploadedImage: '', uploadedFile: null, is_image_delete: 1 })
        }else {
            this.setState({uploadedImage: file[0].preview});
            this.setState({uploadedFile: file[0]});
        }
        // let imageID = document.getElementById("uploadImageId");
        // let that = this;
        // if(imageID !==null ) {
        //     imageID.addEventListener('load', function () {
        //         let img = new Image();
        //         img.src = this.src;
        //         that.getDimention( this );
        //     })
        // }
    }

    /*
     * Get the image dimention
     */
    //getDimention( image ){
    //    let i = new Image();
    //    i.src = image.src;
    //    this.setState({ imageOriginHeight: i.height });
    //    this.setState({ imageOriginWidth: i.width });
    //
    //    if( i.width > 600  ||  i.height > 350 ){
    //        this.setState({ upload_image_error: true })
    //    }else {
    //        this.setState({ upload_image_error: false })
    //    }
    //}


    showEditBox( active_category ){

        if (this.state.isPopup == false) {
            this.setState({name: active_category.name});
            this.setState({description: active_category.description});
            this.setState({external_link: active_category.external_link});
            this.setState({uploadedImage:  (active_category.image == null ) ? null : "/uploads/"+active_category.image });
            this.setState({activeCategoryID: active_category.id});
            this.setState({has_subcategory: active_category.has_subcategory});
            this.setState({category_product_count: active_category.product_count});
            //console.debug( "Selected category: " , active_category );
            let attributes=[];
            if(active_category.required_attributes !== null ){
                attributes = active_category.required_attributes.split(",")
            }
            this.setState({required_attributes: attributes});

            if (active_category.text_response == null) active_category.text_response = [];
            this.state.text_response = active_category.text_response;
            this.state.synonyms = active_category.synonyms == null ? [] : active_category.synonyms;
            this.state.rss_feed = active_category.rss_feed == 1;

            this.setState({isPopup: true});
        } else {
            this.setState({isPopup: false});
        }

    }

    addNewCategoryBox(event){

        let parent = this.props.selectionHistory[this.props.index - 1];
        // console.debug("Parent Category: " , parent);

        if (this.state.isPopup == false && parent) {

            if (parent.has_subcategory == false) {
                let message = null;

                if (parent.product_count > 0) {
                    message = 'WARNING: Class (' + parent.name + ') has entities, which will become unused if a subclass is created. Press Ok to confirm.';
                }
                else if ((!!+parent.rss_feed) == true) {
                    message = 'WARNING: Class (' + parent.name + ') has RSS link enabled, which will be disabled and deleted if a subclass is created. Press Ok to confirm.';
                }

                if (message) {
                    let decision = window.confirm(message);
                    // console.debug("User decision: ", decision);

                    if (decision == false) return decision;
                }
            }

            this.setState({ name: null});
            this.setState({ description: null});
            this.setState({ external_link: null});
            this.setState({ uploadedImage: null});
            this.setState({activeCategoryID: null});
            this.setState({required_attributes: []});
            this.setState({ imageOriginWidth : null });
            this.setState({ imageOriginHeight : null });
            this.setState({ rss_feed : false });
            this.setState({ synonyms : [] });
            this.setState({text_response: []});
            this.setState({isPopup: true, parentID: parent.id});
        } else {
            this.setState({isPopup: false});
        }
    }

    /*
    On popup form change handler
     */
    onChangeHanlder( event ){
        let target = event.target;
        let name = target.name;
        let value = (name == 'rss_feed') ? target.checked ? true : false : target.value;

        if(name == 'name' && value.length < 3 ) {
            this.setState({ error_found: true })
        }else {
            this.setState({ error_found: false })
        }

        this.setState({ [name] : value  });
    }

    /*
     * On form submition call category add api
     * @args required_attributes from popup modal
     */
    onSubmit( required_attributes, synonyms, text_response = null ){
        let error = false;

        if(this.state.error_found == true )
            error = true;

        if (this.state.name != '' && this.state.name != null ) {
            error = this.state.name.length < 3;
        } else {
            error = true
        }
        if(error == true ){
            this.setState({ error_found : true })
        }

        let attributes = this.state.rss_feed ? [] : required_attributes.toString();

        if(error == false ) {
            this.setState({ fetched: false });
            let saveCategory = request
                .post('/api/add_category')
                .set('X-CSRF-TOKEN', config._token)
                .set('X-Requested-With', 'XMLHttpRequest')
                .set('Accept', 'application/json')
                .field('name', this.state.name)
                .field('description', (this.state.description == null ) ? '' : this.state.description )
                .field('external_link', (this.state.external_link == null ) ? '' : this.state.external_link )
                .field('required_attributes', attributes )
                .field('parent_id', (this.state.parentID == null ) ? '' : this.state.parentID)
                .field('cat_id', (this.state.activeCategoryID == null ) ? '' : this.state.activeCategoryID  )
                .field('file', (this.state.uploadedFile == null ) ? '' : this.state.uploadedFile)
                .field('is_image_delete', this.state.is_image_delete)
                .field('synonyms', JSON.stringify(synonyms))
                .field('text_response', JSON.stringify(text_response))
                .field('rss_feed', this.state.rss_feed);

            saveCategory.end((err, response) => {
                if(err){
                    //console.debug("add category error: ", err.message );
                    this.setState({ duplicate_error_message : err.message, fetched: true})
                }
                else if (response.body.error == false) {
                    let category = response.body.category;

                    if (this.state.activeCategoryID != null) {
                        let activeCategory = this.state.chainList[this.state.activeCategoryIndex];
                        activeCategory.name = category.name;
                        activeCategory.image = category.image;
                        activeCategory.description = category.description;
                        activeCategory.external_link = category.external_link;
                        activeCategory.required_attributes = category.required_attributes;
                        activeCategory.has_subcategory = category.has_subcategory;
                        activeCategory.synonyms = JSON.parse(category.synonyms);
                        activeCategory.text_response = JSON.parse(category.text_response);
                        activeCategory.rss_feed = category.rss_feed;
                        //console.debug("Converted category: ");
                        //console.debug(activeCategory);

                        this.setState({ duplicate_error_message : null, uploadedFile: null, is_image_delete: 0, isPopup: false, fetched: true });
                    } else {
                        let currentChain = this.state.chainList;
                        this.setState({ chainList : currentChain.concat(category) });

                        let parent = this.props.selectionHistory[this.props.index - 1];
                        let parentUpdate = response.body.parent_category;
                        if (parentUpdate) {
                            //console.debug("Parent update: ", parentUpdate);

                            parent.has_subcategory = parentUpdate.has_subcategory;
                            parent.rss_feed = parentUpdate.rss_feed;
                            parent.external_link = parentUpdate.external_link;
                        }

                        this.setState({ duplicate_error_message : null, uploadedFile: null, is_image_delete: 0, isPopup: false, fetched: true });
                        this.props.loadNewChain( this.props.index - 1, parent.id);
                    }
                }
                else if(response.body.error == true ){
                    this.setState({ duplicate_error_message : response.body.message, fetched: true})
                }
            });
        }

    }

    beforeDeleteOperation(category_id){

        let addCategory = request
            .post('/api/before_delete_operation')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .field('cat_id', category_id);

        addCategory.end((err, response) => {
            if (err) {
                console.error(err);
            }
            if(response.error==false){
                //console.debug(response.body);
                this.setState({ deleteResponse: response.body.data });
                this.setState({ delete_category_id: category_id });
                this.setState({ isDeletePopup: true })
            }
        });
    }

    handleTransferAndDelete(transfer_category_id){
        let transferAndDelete = request
            .post('/api/transfer_and_delete')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .field('del_category_id', this.state.delete_category_id)
            .field('transfer_category_id', transfer_category_id);
        transferAndDelete.end((err, response) => {
            if (err) {
                console.error("Transfer and delete error: ",err);
            }
            if(response.error==false){
                this.setState({ isDeletePopup: false });
                this.props.loadNewChain( this.props.index - 1, transfer_category_id);
            }
        });
    }

    render(){
        //console.debug("change: ",this.state.chainList);
        let shouldPopup = (this.state.isPopup == false ) ? null :
                            <Popup {...this.state}
                               //imageOriginHeight={this.state.imageOriginHeight}
                               //imageOriginWidth={this.state.imageOriginWidth}
                               isShow={this.state.isPopup}
                               closePopup={this.ClosePopup}
                               onImageDrop={this.catchImageUploadFile}
                               category_id={this.state.activeCategoryID}
                               uploadedImage={ (this.state.uploadedImage == null ) ? '' : this.state.uploadedImage }
                               name={ (this.state.name == null ) ? '' : this.state.name }
                               description={(this.state.description==null) ? '' : this.state.description}
                               external_link={(this.state.external_link==null) ? '' : this.state.external_link}
                               //required_attributes={this.state.required_attributes}
                               //has_subcategory={this.state.has_subcategory}
                               onChangeHandler={this.onChangeHanlder}
                               onSubmit={this.onSubmit}
                               //error_found={this.state.error_found}
                               duplicate_error_message={ (this.state.duplicate_error_message == null ) ? '' : this.state.duplicate_error_message}
                            />;

        let deletePopup = (this.state.isDeletePopup == false ) ? null :
                        <DeletePopup
                            isShow={this.state.isDeletePopup}
                            closePopup={this.ClosePopup}
                            deleteViewReady={true}
                            deleteResponse={this.state.deleteResponse}
                            handleTransferAndDelete={this.handleTransferAndDelete}
                        />

        let productList = (this.state.chainList.length<1) ? null :

                        this.state.chainList.map((category, index) => {

                            return <Category index={index}
                                             onActive={index === this.state.activeCategoryIndex}
                                             handleActiveCategory={this.handleActiveCategory}
                                             category={category}
                                             key={index}
                                             chainIndex={this.props.index}
                                             showEditBox={this.showEditBox}
                                             has_subcategory={category.has_subcategory}
                                             beforeDeleteOperation={this.beforeDeleteOperation}
                            />
                        })

        let addButton = (this.props.index == 0 || this.state.chainList.length >= 9) ? null :
                        <li className="add_new_category"> <a href="#" onClick={this.addNewCategoryBox}  className="add_category"> + </a></li>

        return  (
            <div className="category_chain" key={this.props.index} data-category_index={this.props.index}>
                <ul className="category-chain-list">
                    { productList }
                    { addButton }
                </ul>
                { shouldPopup }
                { deletePopup }
            </div>
        )
    }

}

export default CategoryChain;