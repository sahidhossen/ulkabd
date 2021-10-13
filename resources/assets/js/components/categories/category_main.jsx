import React from 'react';
import Config from "../../components/config";
import request from 'superagent';
import ReactCSSTransitionGroup from 'react-addons-css-transition-group';
import CategoryChain from '../../components/categories/chain';

let config = new Config();

class Category extends React.Component{

    constructor( props ){
        super( props );
        this.state = {
            imageOriginalHeight:null,
            parentCategoryList:[],
            activeCategory:null,
            imageOriginWidth:null,
            selectionHistory:[],
            previousCategoryID:null
        }
        this.loadNewChain = this.loadNewChain.bind(this);
    }


    componentDidMount(){
        let parentCategory = request
            .post('/api/category_list_by_parent')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json')
            .field( 'parent_id', 'null');
        parentCategory.end((err, response) => {
            //console.debug( response.body );
            let categoryList = response.body.category_list;
            let parentCategory = this.state.parentCategoryList;
            let newCategory = [];
            newCategory.push(categoryList);
            this.setState({ parentCategoryList : parentCategory.concat( newCategory) });
        });
    }


    getAllCategoryByParent( category_id, parentCategory ){
         let getCategory = request
             .post('/api/category_list_by_parent')
             .set('X-CSRF-TOKEN', config._token)
             .set('X-Requested-With', 'XMLHttpRequest')
             .field('parent_id', category_id)

         getCategory.end((err, response) => {
             if (err) {
                 //console.error("Category list by parent error: ",err);
             }
             if( response.error == false ){
                 let categoryList = response.body.category_list;
                 //console.debug("Sub Category response: ");
                 //console.debug(response.body);
                 let newCategory = [];
                 newCategory.push(categoryList);
                 parentCategory = parentCategory.concat(newCategory);
                 this.setState({ parentCategoryList : parentCategory });

             }
         });
     }

    loadNewChain( category_index, category_id ){
        let height = this.state.parentCategoryList.length;

        if (this.state.previousCategoryID !== category_id) {
            //console.debug('category index changed from: ' + this.state.previousCategoryID + ' to: ' + category_id);
            //console.debug('selected category index (height): ' + category_index + ' actual height: ' + height)

            if (category_index + 1 < height) {
                this.state.parentCategoryList.length = category_index + 1;
            }

            this.getAllCategoryByParent( category_id , this.state.parentCategoryList);
        }

        this.setState({previousCategoryID: category_id});

        return true;
    }

    /*
     * Render main view
     */
    renderView(){

        let categoryChainList = null;
        if(this.state.parentCategoryList.length != 0 ) {
            categoryChainList = this.state.parentCategoryList.map((chainList, i) => {
                   return <CategoryChain selectionHistory={this.state.selectionHistory} chainList={chainList} key={i}  index={i} loadNewChain={this.loadNewChain}/>
                }
            )
        }
        return (
            <div className="category_chain_holder">
                {categoryChainList}
            </div>

        )
    }



    render(){
        return this.renderView()
    }
}

export default Category;