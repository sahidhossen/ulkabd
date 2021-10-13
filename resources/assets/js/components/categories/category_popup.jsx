import React from 'react';
import Dropzone from 'react-dropzone';
import TagsInput from 'react-tagsinput'
import Config from "../../components/config";
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

const styles = {
    charCount : {
        display:'inline-block',
        position: 'absolute',
        top: '-15px',
        fontSize: '.8em',
        color: '#dfdfdf',
        right: 0
    }
}

const characterLimits = {
    title: 28,
    description: 80,
    response: 2000
}

export default class Popup extends React.Component {
    constructor( props ){
        super(props);
        this.state = {
            isShow : this.props.isPopup,
            opacity: 1,
            scale: 1,
            category_id:this.props.category_id,
            isUnique: false,

            isAttributeField:false,
            myCategories:[],
            tags: this.props.required_attributes,

            charCount: (this.props.name) ? this.props.name.length :  0,

            img_upload_status: false,
            defaultImage: '/images/upload_model.png',

            text_response: this.props.text_response,
            synonyms: this.props.synonyms,
            rss_feed: this.props.rss_feed,

            text_response_fields: [1, 0, 0, 0, 0, 0, 0, 0, 0],

            title_limit_cross: false,
            description_limit_cross: false,
            response_limit_cross: [],
            url_validation:false
        };

        this.ClosePopup = this.ClosePopup.bind(this);
        this.onImageDrop = this.onImageDrop.bind(this);
        this.onChangeHandler = this.onChangeHandler.bind(this);
        this.categoryFormSubmit = this.categoryFormSubmit.bind(this);
        this.showAttributeField = this.showAttributeField.bind(this);
        this.handleChange = this.handleChange.bind(this);
    }

    componentDidMount() {
        // console.debug("Popup Props:");
        // console.debug(this.props);

        this.state.text_response_fields.forEach(element => {
            this.state.response_limit_cross.push(false);
        });

        this.refs.category.focus();
        if(this.state.tags.length > 0 )
            this.setState({isAttributeField:true });

        if(this.props.uploadedImage !== '')
            this.setState({ img_upload_status : true });

        let responses = this.state.text_response;
        if (responses.length > 0) {
            var trf = this.state.text_response_fields;
            for (var i = 1; i <= responses.length && i < trf.length; ++i) {
                let j = i - 1;
                if (responses[j] && responses[j].length > 0) trf[i] = 1;
            }
            this.setState({ text_response_fields: trf });
        }
        
    }

    ClosePopup(event){
        event.preventDefault();
        this.props.closePopup(event);
    }

    onChangeHandler( event ){

        let pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
        this.setState({ charCount: event.target.value.length });

        var bLimitCross = false;

        switch (event.target.name) {
            case 'name':
                {
                    bLimitCross = (event.target.value.length >= characterLimits.title) ? true : false;
                    this.setState({title_limit_cross: bLimitCross});
                }
                break;
        
            case 'description':
                {
                    bLimitCross = event.target.value.length > characterLimits.description ? true : false;
                    this.setState({description_limit_cross: bLimitCross});
                }
                break;

            case 'external_link':
                {
                    bLimitCross = event.target.value.length > 0 && !pattern.test(event.target.value) ? true : false;
                    this.setState({ url_validation: bLimitCross });
                }
                break;

            case 'rss_feed':
                this.setState({rss_feed: event.target.checked ? true : false})
                break;
        }

        if (bLimitCross == false) this.props.onChangeHandler( event );

    }

    onResponseChangeHandler(index, event) {
        // console.debug(`Index: ${index}`);

        // Update text_response array for response messages.
        let responses = this.state.text_response;
        responses[index] = event.target.value
        
        // Set visibility for next text response field
        for (var i = this.state.text_response_fields.length - 1; i > index; --i) {
            let firstLength = responses[i - 1] ? responses[i - 1].length : 0;
            let secondLength = responses[i] ? responses[i].length : 0;
            let targetResponseFiledIndexValue = firstLength <= 0 && secondLength <= 0 ? 0 : 1;
    
            let oldValue = this.state.text_response_fields[i];

            // console.debug(`Target index: ${i}, Old value: ${oldValue}, New value: ${targetResponseFiledIndexValue}`);

            if (oldValue != targetResponseFiledIndexValue) {
                var new_text_response_fields = this.state.text_response_fields;
                new_text_response_fields[i] = targetResponseFiledIndexValue;
                this.setState({ text_response_fields: new_text_response_fields })
            }
        }

        // Set alert for character limit cross in field
        let bLimitCross =  (event.target.value.length > characterLimits.response) ? true : false;
        var rlc = this.state.response_limit_cross;
        rlc[index] = bLimitCross;
        this.setState({response_limit_cross: rlc});

    }

    onImageDrop(file){
        this.setState({ uploadedImage : file[0].preview, img_upload_status: true });
        this.props.onImageDrop(file);
    }

    onImageRemove(event){
        event.preventDefault();
        this.setState({ uploadedImage : '', img_upload_status: false });
        this.props.onImageDrop('delete');
    }

    categoryFormSubmit( event ){
        event.preventDefault();
        // chain.onSubmit() method call passed here through props

        let responses = this.state.text_response;
        for (let index = 0; index < responses.length; index++) {
            const element = responses[index];
            if (element.length <= 0) responses.splice(index, 1), --index;
        }

        this.props.onSubmit( this.state.tags, this.state.synonyms, responses && responses.length > 0 ? responses : null )
    }

    showAttributeField(event){
        event.preventDefault();
        if(this.state.isAttributeField == false ) {
            this.setState({isAttributeField: true})
        }else{
            this.setState({isAttributeField: false})
        }
    }

    handleChange (tags ) {
        this.setState({tags: tags});
    }

    handleSynonymsChange (synonyms ) {
        if (synonyms.length > 0)
            synonyms[synonyms.length - 1] = synonyms[synonyms.length - 1].trim().replace(/\s\s+/g, ' ').toLowerCase();
        var index = synonyms.indexOf(this.props.name.toLowerCase());
        if (index !== -1) {
            synonyms.splice(index, 1);
        }
        synonyms = Array.from(new Set(synonyms));

        //console.debug("New synonyms set: ");
        //console.debug(synonyms);

        this.setState({synonyms: synonyms});
    }

    AttributesField(){
        let selectStyle = (this.state.isAttributeField == true ) ? Showstyle : Hidestyle;
        return(
            this.state.rss_feed ? null :
            <div className="form-group">
                <div className="attributeFields" style={ selectStyle }>
                    <small className="warning-small"> {i18n.t('pages.category.intentForm.attributes.warning')} </small>
                    <TagsInput inputProps={{placeholder : i18n.t('pages.category.intentForm.attributes.placeholder')}} value={this.state.tags} onChange={this.handleChange} />
                </div>
                <p className="text-right"><a href="#" className="add_attribute" onClick={this.showAttributeField}> { (this.state.isAttributeField == false ) ? i18n.t('pages.category.intentForm.attributes.attributeButton') : i18n.t('common.buttons.cancel')} </a></p>
            </div>
        )
    }

    externalLinkField() {
        let field = (this.props.has_subcategory == false && this.props.category_product_count <= 0) ?
            <div className={ (this.state.url_validation == true ) ? 'form-group has-error': ' form-group'}>
            {(this.state.url_validation==true ) ? <span className="limit-error">  {i18n.t('pages.category.intentForm.externalLink.errorMessage')} </span> : null }
            <input style={{marginTop: "13px"}} type="checkbox" name="rss_feed" onChange={this.onChangeHandler} defaultChecked={this.state.rss_feed}/> RSS
            <input style={{width: "90%", float: "right"}} type="text" onChange={this.onChangeHandler} defaultValue={ this.props.external_link } name="external_link" ref="external_link" className="form-control" placeholder={this.state.rss_feed ? i18n.t('pages.category.intentForm.externalLink.rss.placeholder.link') : i18n.t('pages.category.intentForm.externalLink.rss.placeholder.uri')}/>
        </div> :
            <div className={ (this.state.url_validation == true ) ? 'form-group has-error': ' form-group'}>
            {(this.state.url_validation==true ) ? <span className="limit-error">  {i18n.t('pages.category.intentForm.externalLink.errorMessage')} </span> : null }
            <input type="text" onChange={this.onChangeHandler} defaultValue={ this.props.external_link } name="external_link" ref="external_link" className="form-control" placeholder={i18n.t('pages.category.intentForm.externalLink.noRss.placeholder')}/>
        </div>;

        return(
            field
        )
    }

    textResponseFields() {
        var fields = [];
        var i = 0;
        this.state.text_response_fields.forEach(element => {
            if (element != 0) {
                fields.push(
                    <div key={`response-${i}`} className={ (this.state.response_limit_cross[i] == true ) ? 'form-group has-error': 'form-group'}>
                        {(this.state.response_limit_cross[i] == true ) ? <span className="limit-error">  {`maximum limit ${characterLimits.response} character.`} </span> : null }
                        <textarea name='response' onChange={this.onResponseChangeHandler.bind(this, i)} className="form-control" placeholder={`Text Response 0${i + 1}`} defaultValue={ this.state.text_response[i] } />
                    </div>
                )
            }
            ++i;
        });
        return fields;
    }

    onDisable(event){
        event.preventDefault();
        return false;
    }

    renderView(){

        //Get the props/intializ variables
        //let imgHeight = this.props.imageOriginHeight;
        //let imgWidth = this.props.imageOriginWidth;
        //let dimentionError = null;
        //
        ////Check image dimention between (600x350)
        //if( (imgWidth < 600 && imgWidth > 573) ||  (imgHeight < 350 && imgHeight > 300) ){
        //    dimentionError = <p className="dimention_warning"> <small> Required size 574x300  | (size: {imgWidth} x { imgHeight } )  </small></p>
        //}
        //
        ////Check image dimention on greater than (600x350)
        //if( imgWidth > 600  ||  imgHeight > 350 ){
        //    dimentionError = <p className="dimention_error"> <small> Required size 574x300  | (size: {imgWidth} x { imgHeight } )  </small></p>
        //}

        let { fetched } = this.props;
        //console.debug("fetched: ", fetched)
        //Attribute fields show if doesn't have sub-category
        let attributeField = (this.props.has_subcategory == false ) ? this.AttributesField() : null;
        let externalLinkField = this.externalLinkField();

        return (
            <div className="category-form-shadow" style={(this.props.isShow == true) ? Showstyle : Hidestyle}>
                <div className="category-form-main original" style={ (this.props.isShow == true) ? Showstyle : Hidestyle }>
                    <h3 className="title title-thin"> {i18n.t('pages.category.intentForm.headerTitle')} </h3>
                    <form className="form-horizontal" onSubmit={this.categoryFormSubmit}>
                        <div className="form-group category-image-upload-box">
                            { (this.state.img_upload_status) ? <span className="category-img-remove-btn" onClick={this.onImageRemove.bind(this)}> ✕ </span> : null }
                            <Dropzone
                                className="categoryImageUpload"
                                multiple={false}
                                accept="image/*"
                                onDrop={this.onImageDrop}>
                                <div className="dropArea">
                                    <img  id="uploadImageId" src={ (this.props.uploadedImage === '' ) ? this.state.defaultImage : this.props.uploadedImage  } alt="category Image"/>
                                    <span className="fa fa-camera"></span>
                                </div>
                            </Dropzone>
                        </div>
                        <div className={ (this.state.title_limit_cross == true ) ? 'form-group has-error': ' form-group'}>
                            <span style={ styles.charCount }> { this.state.charCount } </span>{(this.state.title_limit_cross == true ) ? <span className="limit-error">  {`maximum limit ${characterLimits.title} character.`} </span> : null }
                            {(this.props.duplicate_error_message) ? <span className="limit-error"> { this.props.duplicate_error_message } </span> : null }
                            <input type="text" name="name" onChange={this.onChangeHandler} value={ this.props.name } ref="category" className="form-control" placeholder={i18n.t('pages.category.intentForm.name.placeholder')}/>
                        </div>
                        <div className="form-group">
                            <div style={ Showstyle }>
                                <TagsInput inputProps={{placeholder : i18n.t('pages.category.intentForm.phrase.placeholder')}} value={this.state.synonyms} onChange={this.handleSynonymsChange.bind(this)} />
                            </div>
                        </div>
                        <div className={ (this.state.description_limit_cross == true ) ? 'form-group has-error': 'form-group'}>
                            {(this.state.description_limit_cross == true ) ? <span className="limit-error">  {`maximum limit ${characterLimits.description} character.`} </span> : null }
                            <textarea name="description" onChange={this.onChangeHandler} className="form-control" placeholder={i18n.t('pages.category.intentForm.description.placeholder')} defaultValue={ this.props.description } />
                        </div>
                        { this.textResponseFields() }
                        { externalLinkField }
                        { attributeField }
                        <div className="form-group category_btn">
                            <input disabled={(fetched === false) ? 'disabled' : ''} type="submit" value={(fetched) ? i18n.t('common.buttons.submit') : i18n.t('pages.category.intentForm.updatingButton')  }  className="btn btn-admin pull-left"/>
                            <a href="#" disabled={(fetched === false) ? 'disabled' : ''} className="btn btn-theme btn-admin-red pull-right" onClick={(fetched === true) ? this.ClosePopup : this.onDisable.bind(this)}> {i18n.t('common.buttons.cancel') }</a>
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

// export default Popup;
