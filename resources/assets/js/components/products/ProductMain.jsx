import React from "react";
import ReactTable from 'react-table'
import request from 'superagent';
import Select from 'react-select';
import Creatable from 'react-select/lib/Creatable';
// import SubComponent from 'SubComponent';
import Dropzone from 'react-dropzone';
import Echo from "laravel-echo";
import Config from "../../components/config";
import ConfirmationAlert from "../Alerts/ConfirmationAlert";
import Promise from 'bluebird';
import i18n from "./../../plugins/i18n.js"

let config = new Config();

const defaultImageStyle = {
    height: '28px',
    marginLeft: "40px",
    transition: 'all .2s ease-in-out',
    position: 'absolute',
    zIndex: '1'
};

const changeImageStyle = {
    height: '64px',
    transform: "scale(2)",
    transition: 'all .2s ease-in-out',
    transformOrigin: 'center',
    position: 'absolute',
    marginLeft: "25px",
    zIndex: '2'
};

const training = {
    needed: 0,
    running: 1,
    done: 2
};

const limits = {
    msgBroadcast: 10,
    fbPost: 20
};

const cacheKey = {
    local: 'usha_products_view_local',
    session: 'usha_products_view_session'
};

const staticText = {
    all_entity: i18n.t('pages.products.productOptions.dropdown.staticText')
};

class ProductMain extends React.Component {
    constructor(props) {
        super(props);

        this.loadCache();

        this.state = {
            product_link: config.root,
            userID: this.props.user_id,
            agent_code: null,
            uploadImageUrl: '/images/create_bot.png',
            product_name: '',
            products: [],
            products_filter: [],
            category_chain: [],
            category_chain_option: [
                {
                    value: 0,
                    label: staticText.all_entity,
                    name: staticText.all_entity
                }
            ],

            loading: true,
            new_product: [],

            isChecked: false,
            selectedData: [],

            selectedCategory: 0,
            selectedCategoryName: this.session.category.name,
            data_uri: null,

            isDeletePopup: false,

            isImageStyle: null,

            isSelectAll: false,

            showBroadcastMessageInputAlert: false,
            showBulkPostOnFacebookTitleMessgeInputAlert: false,

            trainingStatus: training.done,
            viewConfig: {
                listSize: this.local.listSize,
                category: {
                    id: this.session.category.id,
                    name: this.session.category.name
                },
                offset: this.session.offset,
                startPage: this.session.startPage,
                endPage: this.session.endPage,
                loading: false
            },

            editableStockIndex: -1,
            stockUpdateRequestedProducts: {},
            unsavedStock: null
        };

        this.OnFilter = this.OnFilter.bind(this);
        this.handleCheckboxSlectAll = this.handleCheckboxSlectAll.bind(this);
        this.handleCheckboxChange = this.handleCheckboxChange.bind(this);
        this.categoryOptionChange = this.categoryOptionChange.bind(this);
        this.onCsvUploadTrigger = this.onCsvUploadTrigger.bind(this);
        this.onDeleteAction = this.onDeleteAction.bind(this);
        this.onBroadcastAction = this.onBroadcastAction.bind(this);
        this.imageInlarge = this.imageInlarge.bind(this);
        this.documentClickEventHandler = this.documentClickEventHandler.bind(this);
        this.uncategorizedProductsList = this.uncategorizedProductsList.bind(this);
    }

    loadCache() {
        let localS = localStorage.getItem(cacheKey.local);
        if (!localS) {
            this.local = {
                listSize: 10
            };
        }
        else this.local = JSON.parse(localS);

        let sessionS = sessionStorage.getItem(cacheKey.session);
        let parsed = sessionS ? JSON.parse(sessionS) : null;
        if (parsed && parsed.userId != this.props.user_id) parsed = null;

        if (!parsed) {
            this.session = {
                userId: this.props.user_id,
                category: {
                    id: 0,
                    name: staticText.all_entity
                },
                offset: 0,
                startPage: 1,
                endPage: 1
            };
        }
        else this.session = parsed;

        //console.debug("Local Storage: ", this.local);
    };

    updateCache(is_local, key, value) {
        if (is_local) {
            this.local[key] = value;
            localStorage.setItem(cacheKey.local, JSON.stringify(this.local));
        }
        else {
            this.session[key] = value;
            sessionStorage.setItem(cacheKey.session, JSON.stringify(this.session));
        }
    };

    loading = (isLoading) => {
        this.state.viewConfig.loading = isLoading;
        this.setState(this.state.viewConfig);
    };

    componentDidMount() {
        this.getTrainingStatus();
        this.getAllSelectableCategory();

        //this.activeProductList();
        this.categoryOptionChange({
            value: this.state.viewConfig.category.id,
            name: this.state.viewConfig.category.name
        });

        document.addEventListener("click", this.documentClickEventHandler);

        this.listenBroadCast();
    }

    componentWillUnmount() {
        document.removeEventListener("click", this.documentClickEventHandler);
    }

    handlePageSizeChange(pageSize) {
        this.updateCache(true, 'listSize', pageSize);
    }

    listenBroadCast() {
        if (typeof io !== 'undefined') {
            window.Echo = new Echo({
                broadcaster: 'socket.io',
                host: config.root + ':6001'
            });
            window.Echo.private('channel-product-uploaded_' + config.userID)
                .listen('BroadcastProductUploaded', (response) => {
                    this.state.products_filter.unshift(response.product);
                    this.setState({ products_filter: this.state.products_filter });
                });
            window.Echo.private('channel-agent-train_' + config.userID)
                .listen('BroadcastTrainingStatus', (response) => {
                    //console.debug("Broadcast TStatus in Products list: ", response);
                    this.setState({ trainingStatus: response.trainingStatus.status });
                });
        }
    }

    documentClickEventHandler(event) {
        /*
        Hide image when clicked outside
         */
        let index = event.target.getAttribute("data-rowIndex");
        if (index == null) {
            this.setState({ isImageStyle: null })
        }

    }

    getTrainingStatus() {
        let trainingStatus = request
            .post('/api/check_training_status')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest');
        trainingStatus.end((err, response) => {
            if (err) {
                console.error(err);
            }
            if (response.error == false) {
                this.setState({ trainingStatus: response.body.status })
            }
        });
    }

    /*
     * Show All active products
     */
    activeProductList() {
        return new Promise((resolve, reject) => {
            try {
                this.loading(true);

                let products = request
                    .post('/api/all_products')
                    .set('agent_code', config.activeAgent)
                    .set('X-CSRF-TOKEN', config._token)
                    .set('X-Requested-With', 'XMLHttpRequest')
                    .set('Accept', 'application/json')
                    .field('listSize', this.state.viewConfig.listSize);
                products.end((err, response) => {
                    if (err) {
                        reject(err.message);
                    }
                    else if (response.body.error == false) {
                        let productList = response.body.product_list;
                        this.setState({ products: productList, loading: false });
                        this.setState({ products_filter: productList });
                        this.setState({ agent_code: response.body.agent_code });
                        this.setState({ userID: response.body.user_id });
                        this.setState({ isSelectAll: false });
                        this.setState({ selectedData: [] });

                        this.updateCache(false, "category", { id: 0, name: staticText.all_entity });
                        resolve();
                    }
                    else {
                        reject(response.body.message);
                    }

                    this.loading(false);
                });
            } catch (e) {
                reject(e.message);
            }
        });
    }

    getAllSelectableCategory() {
        this.loading(true);

        let products = request
            .post('/api/all_selectable_category')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json');
        products.end((err, response) => {
            if (response.error == false) {
                this.setState({ category_chain: response.body.category_list })
                this.categoryChainList(response.body.category_list);

                this.loading(false);
            }
        });
    }

    productByCategory(category) {
        return new Promise((resolve, reject) => {
            try {
                this.loading(true);

                let products = request
                    .post('/api/products_by_category')
                    .set('X-CSRF-TOKEN', config._token)
                    .set('X-Requested-With', 'XMLHttpRequest')
                    .set('Accept', 'application/json')
                    .field('category_id', category.value);
                products.end((err, response) => {
                    //console.debug( response.body );

                    if (err) {
                        reject(err.message);
                    }
                    else if (response.body.error == true) {
                        reject(response.body.message)
                    }
                    else {
                        let productList = response.body.product_list;
                        this.setState({ products: productList, loading: false });
                        this.setState({ products_filter: productList });
                        this.setState({ isSelectAll: false });
                        this.setState({ selectedData: [] });

                        this.updateCache(false, "category", { id: category.value, name: category.name });
                        resolve()
                    }

                    this.loading(false);
                });
            } catch (e) {
                reject(e.message)
            }
        });
    }



    /*
     * Load chain list when call ComponentDidMount
     * Intial method
     */
    categoryChainList(categoryChain) {
        if (categoryChain.length > 0) {
            let category = categoryChain.map((cat) => {
                let chainList = (cat.chainString == null) ? cat.name : '(' + cat.chainString + ') ' + cat.name;
                return { value: cat.id, label: chainList, name: cat.name }
            });

            let categoryChainOption = this.state.category_chain_option;
            this.setState({ category_chain_option: categoryChainOption.concat(category) });
        }
    }

    /*
     * Category options change
     * Set the selected category ID when
     */
    categoryOptionChange(val) {

        let storedID = this.state.selectedCategory;
        let storedName = this.state.selectedCategoryName;

        this.setState({ selectedCategory: val.value, selectedCategoryName: val.name });

        if (val.value == 0) {
            this.activeProductList()
                .catch((msg) => {
                    window.alert(msg);
                    this.setState({ selectedCategory: storedID, selectedCategoryName: storedName });
                });
        } else {
            this.productByCategory(val)
                .catch((msg) => {
                    window.alert(msg);
                    this.setState({ selectedCategory: storedID, selectedCategoryName: storedName });
                });
        }

    }

    /*
    Upload CSV triggeer method =
     */
    onCsvUploadTrigger(e) {
        if (this.state.selectedCategory == 0) {
            //console.debug("Sorry Please select category ID ");
            return false;
        }

        this.setState({ isSelectAll: false });
        this.setState({ selectedData: [] });

        let file = e.target.files[0];
        let products = request
            .post('/api/csv_upload')
            .set('agent_code', config.activeAgent)
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json')
            .field('csv_file', file)
            .field('category_id', this.state.selectedCategory);
        products.end((err, response) => {
            //console.debug("UPload File: ",response.body);
        });
    }

    /*
     * All uncategorized product list when press the uncategorized button
     */
    uncategorizedProductsList(event) {
        this.loading(true);

        event.preventDefault();
        let products = request
            .post('/api/uncategorized_products')
            .set('agent_code', config.activeAgent)
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json');
        products.end((err, response) => {
            //console.debug(response.body);

            if (response.body.error == false) {
                let productList = response.body.uncategorized_products;
                this.setState({ products: productList, loading: false });
                this.setState({ products_filter: productList });
                this.setState({ isSelectAll: false });
                this.setState({ selectedData: [] });
                this.setState({ selectedCategoryName: "Uncategorized" });
                this.setState({ selectedCategory: null });
            }

            this.loading(false);
        });
    }

    OnFilter(event) {
        let search = this.refs.filter_product.value;
        let productList = this.state.products;
        let filterProduct = productList.filter((product) => {
            return (
                product.name.toLowerCase().indexOf(search.toLowerCase()) !== -1 ||
                product.code.indexOf(search) !== -1
            );
        }
        );

        this.setState({ products_filter: filterProduct });
    }

    handleCheckboxSlectAll(event) {
        if (event) event.preventDefault();

        if (this.state.products_filter.length <= 0) return;

        this.state.isSelectAll = !this.state.isSelectAll;

        if (this.state.isSelectAll == true) {
            let selectedProducts = this.state.products_filter.slice().map((item, index) => {
                item.index = index;
                return item;
            });
            this.setState({ selectedData: selectedProducts }, function () {
                //console.debug( "To delete: ", this.state.selectedData);
            });
        }
        else {
            this.setState({ selectedData: [] }, function () {
                //console.debug( "To delete: ", this.state.selectedData);
            });
        }
    }

    handleCheckboxChange(event) {
        event.preventDefault();

        let SelectData = this.state.selectedData;
        let selectedValue = event.target.getAttribute("data-value");
        let rowIndexValue = event.target.getAttribute("data-rowIndex");
        let foundedItem = null;
        let foundedIndex = (SelectData.length == 0) ? null : SelectData.map((item, index) => {
            return (item.id == selectedValue) ? foundedItem = index : null
        });

        if (foundedItem === null) {
            let newItem = [{ id: selectedValue, state: true, index: rowIndexValue }];

            let NewFoundItem = [];
            let newData = NewFoundItem.push(newItem);
            this.setState({ selectedData: SelectData.concat(newItem) })
        } else {
            //console.debug("Found : "+ foundedItem);
            SelectData.splice(foundedItem, 1);
            this.setState({ selectedData: SelectData })
        }

        if (this.state.selectedData.length <= 0 && this.state.isSelectAll == true) {
            this.handleCheckboxSlectAll(null)
        }
    }

    objectKeyByValue(item) {
        let objects = this.state.selectedData;
        for (let i = 0; i < objects.length; i++) {
            if (objects[i].id == item) {
                return "active";
            }
        }
        return null;
    }

    multisplice(deletedProducts, products) {
        let productIds = [];
        let args = deletedProducts.map((product) => { productIds.push(product.id); return product.index });
        args.sort(function (a, b) {
            return a - b;
        });
        for (let i = 0; i < args.length; i++) {
            let index = (args[i] == 0) ? args[i] : args[i] - i;
            products.splice(index, 1);
        }
    }

    cancelBroadcastAction() {
        this.setState({ showBroadcastMessageInputAlert: false });
    }

    onBroadcastConfirmationAction(message) {
        if (!message) return;

        this.setState({ showBroadcastMessageInputAlert: false });

        let productIds = this.state.selectedData.map((product) => {
            return product.id;
        });

        console.debug("Product ids: ", productIds);

        let broadcastRequest = request
            .post('/api/broadcast/products')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json')
            .field('product_ids', JSON.stringify(productIds))
            .field('message', message);
        broadcastRequest.end((err, response) => {
            console.debug(response.body);
            if (response.body.error == false) {
                // Show success popup
                window.alert(response.body.message)
            } else {
                //console.debug("Product delete failed: ", response.body.message );
                window.alert(response.body.message)
            }
        });
    }

    onBroadcastAction(event) {
        event.preventDefault();
        if (this.state.selectedData.length < 1)
            return false;

        this.setState({ showBroadcastMessageInputAlert: true });
    }

    cancelFBPostAction() {
        this.setState({ showBulkPostOnFacebookTitleMessgeInputAlert: false });
    }

    onFBPostConfirmationAction(message) {
        if (!message) return;

        this.setState({ showBulkPostOnFacebookTitleMessgeInputAlert: false });

        let productIds = this.state.selectedData.map((product) => {
            return product.id;
        });

        console.debug("Post on facebook");
        console.debug("Product ids: ", productIds);

        let fbPostRequest = request
            .post('/api/facebook_post/products')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json')
            .field('product_ids', JSON.stringify(productIds))
            .field('message', message);
        fbPostRequest.end((err, response) => {
            console.debug(response.body);
            if (response.body.error == false) {
                // Show success popup
                window.alert(response.body.message)
            } else {
                //console.debug("Product delete failed: ", response.body.message );
                window.alert(response.body.message)
            }
        });
    }

    onFBPostAction(event) {
        event.preventDefault();
        if (this.state.selectedData.length < 1)
            return false;

        this.setState({ showBulkPostOnFacebookTitleMessgeInputAlert: true });
    }

    onDeleteAction(event) {
        event.preventDefault();
        if (this.state.selectedData.length < 1)
            return false;

        let message = this.state.selectedData.length > 1 ?
        i18n.t('pages.products.widgetProductOptions.buttons.delete.popup.pluralEntity') :
        i18n.t('pages.products.widgetProductOptions.buttons.delete.popup.singleEntity');
        let decision = window.confirm(message);
        console.debug("User decision: ", decision);

        if (decision == false) return decision;

        let productIds = this.state.selectedData.map((product) => {
            return product.id;
        });

        //console.debug("To be deleted ids: " , productIds);

        let products = request
            .post('/api/delete_product')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json')
            .field('product_ids', JSON.stringify(productIds));
        products.end((err, response) => {
            //console.debug(response.body);
            if (response.body.error == false) {
                let ProductFilter = this.state.products_filter;
                this.multisplice(this.state.selectedData, ProductFilter);
                this.setState({ products_filter: this.state.products_filter });
                this.setState({ selectedData: [] });
                this.setState({ isSelectAll: false });
            } else {
                //console.debug("Product delete failed: ", response.body.message );
            }
        });
    }

    imageInlarge(event) {
        event.preventDefault();
        let index = event.target.getAttribute("data-rowIndex");

        if (this.state.isImageStyle != index) {
            this.setState({ isImageStyle: index })
        } else {
            this.setState({ isImageStyle: null })
        }

    }

    /*
     * Fetch downloadable file
     */
    onCsvProcessStart(event) {
        event.preventDefault();

        if (this.state.selectedCategory === 0)
            return false;

        let csvGenerateProcess = request
            .post('/api/dynamic_csv')
            .set('agent_code', config.activeAgent)
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json')
            .field('category_id', this.state.selectedCategory);
        csvGenerateProcess.end((err, response) => {
            console.debug(response);
            if (!err && response.text) {
                var fileDownload = require('react-file-download');
                fileDownload(response.text, 'demo.csv');
            }
        });
    }

    onSaveStockChange = (product) => {
        this.state.editableStockIndex = -1;
        
        let newStock = this.state.unsavedStock != null ? this.state.unsavedStock.value.trim() : null;
        var isNumber = false;
        if (newStock !== null) {
            isNumber = /^[0-9-+]*$/.test(newStock);
            newStock = parseInt(newStock);
        }

        if (newStock == null || !isNumber || newStock == NaN || newStock < -1) {
            return;
        }
        
        product.isLoading = true;
        this.setState({ products_filter: this.state.products_filter });

        this.state.stockUpdateRequestedProducts[product.id] = product;

        let productPatchReq = request
            .post(`/api/product/${product.id}`)
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json')
            .field('stock', newStock);
        productPatchReq.end((err, response) => {
            if (!err && response.body !== null && response.body.error == false) {
                let resProduct = response.body.product;
                if (resProduct) {
                    let targetProduct = this.state.stockUpdateRequestedProducts[resProduct.id]
                    if (targetProduct) {
                        delete this.state.stockUpdateRequestedProducts[resProduct.id];
                        targetProduct.stock = resProduct.stock;
                        targetProduct.isLoading = false;
                    }
                }
            }
            else {
                let productId = (response.body !== null && response.body.id !== undefined) ? response.body.id : null;
                if (productId !== null) {
                    let targetProduct = this.state.stockUpdateRequestedProducts[productId]
                    if (targetProduct) {
                        delete this.state.stockUpdateRequestedProducts[productId];
                        targetProduct.isLoading = false;
                    }
                }
            }

            this.setState({ products_filter: this.state.products_filter });
        });
    }

    renderView() {

        return (
            <ReactTable
                columns={[
                    {
                        Header: props => (
                            <div style={{ textAlign: 'left', paddingLeft: '10px' }} className="rowtableOnSelect">
                                <a href="#" onClick={this.handleCheckboxSlectAll} className={this.state.isSelectAll == false ? 'inactive' : 'active'}> &nbsp; </a>
                            </div>
                        ),
                        accessor: 'id',
                        width: 50,
                        sortable: false,
                        Cell: row => (

                            <div style={{ textAlign: 'left', paddingLeft: '10px' }} className="rowtableOnSelect">
                                <a href="#" data-value={row.value} data-rowIndex={row.index} onClick={this.handleCheckboxChange} className={
                                    (this.objectKeyByValue(row.value) == null) ? 'inactive' : 'active'}> &nbsp; </a>
                            </div>
                        )
                    },
                    {
                        Header: i18n.t('pages.products.productMainTable.columns.code'),
                        accessor: 'code',
                        showFilters: true,
                        Cell: props => <div style={{ textAlign: 'center' }} className='number'>{props.value}</div> // Custom cell components!
                    },
                    {
                        Header: i18n.t('pages.products.productMainTable.columns.entity'),
                        accessor: 'name', // Custom value accessors!
                        minWidth: 250
                    },
                    {
                        Header: props => <span>{i18n.t('pages.products.productMainTable.columns.price')}</span>, // Custom Header components!
                        accessor: 'price',
                        Cell: props => <div style={{ textAlign: 'center' }} className='number'>{props.value}</div>

                    },
                    {
                        Header: props => <span>{i18n.t('pages.products.productMainTable.columns.offerPrice')}</span>, // Custom Header components!
                        accessor: 'offer_price',
                        Cell: props => <div style={{ textAlign: 'center' }} className='number'>{props.value}</div>
                    },
                    {
                        Header: props => <span>{i18n.t('pages.products.productMainTable.columns.stock.title')}</span>, // Custom Header components!
                        accessor: 'stock',
                        width: 120,
                        Cell: props =>
                            ((props.index !== this.state.editableStockIndex &&
                                (props.original.isLoading == undefined || (props.original.isLoading !== undefined && props.original.isLoading == false))) ?
                                <div className="stock-container">
                                    <div className="text-center stock-pos-abs">
                                        {props.value == -1 ? i18n.t('pages.products.productMainTable.columns.stock.available') : i18n.t('pages.products.productMainTable.columns.stock.value', {val : props.value})}
                                    </div>
                                    <div className="edit-icon-pos-abs">
                                        <span onClick={() => this.setState({ editableStockIndex: props.index, unsavedStock: null })}>
                                            <i className="fa fa-edit appear-icon-product"></i>
                                        </span>
                                    </div>
                                </div> :
                                <div>
                                    {(props.original.isLoading !== undefined && props.original.isLoading == true) ?
                                        <div className="text-center">
                                            <img src="../files/loading.gif" height="15px" />
                                        </div> :
                                        <div className="edit-container">
                                            <div className="edit-field-width">
                                                <Creatable
                                                    placeholder={<span className="text-primary">{props.value == -1 ? i18n.t('pages.products.productMainTable.columns.stock.stockTable.available') : i18n.t('pages.products.productMainTable.columns.stock.stockTable.value', {val : props.value})}</span>}
                                                    autosize={ false }
                                                    onChange={(event) => this.setState({unsavedStock: event})}
                                                    value={this.state.unsavedStock}
                                                    options={[{ value: '-1', label: i18n.t('pages.products.productMainTable.columns.stock.stockTable.available') }]}
                                                    promptTextCreator={(value) => { return "Stock: " + value }}
                                                />
                                            </div>
                                            <div className="save-icon-pos-abs">
                                                <span onClick={() => this.onSaveStockChange(props.original)}>
                                                    <i className="fa fa-floppy-o save-icon-product"></i>
                                                </span>
                                            </div>
                                        </div>}
                                </div>
                            )
                    },
                    {
                        Header: i18n.t('pages.products.productMainTable.columns.image'),
                        accessor: 'is_image',
                        hideFilter: false,
                        Cell: row => (
                            <div style={{ textAlign: 'center' }}>
                                {
                                    (row.original.is_image == null && row.original.image_link == null) ? null : <img onClick={this.imageInlarge} className="img-responsive" data-rowIndex={row.index} style={(this.state.isImageStyle == row.index) ? changeImageStyle : defaultImageStyle} src={(row.original.is_image == null) ? row.original.image_link : config.root + "/uploads/" + row.original.is_image} alt="" />}

                                {(row.original.is_image == null && row.original.image_link == null) ?
                                    <span>
                                        <span style={row.original.stock == 0 ? { color: 'white', transition: 'all .3s ease' } : { color: '#ff2e00', transition: 'all .3s ease' }}> &#x25cf; </span>
                                    </span>
                                    : null}
                            </div>
                        )

                    },
                    {
                        Header: i18n.t('pages.products.productMainTable.columns.options.title'), // Custom Header components!
                        accessor: 'id',
                        sortable: false,
                        Cell: row => (
                            <div style={{ textAlign: 'center' }}>
                                <a href={config.path_name + '/' + row.value} className="btn btn-xs btn-admin btn-admin-success"> {i18n.t('common.buttons.edit')} </a>
                            </div>
                        )
                    },
                ]}
                loading={this.state.viewConfig.loading}
                data={this.state.products_filter}
                defaultPageSize={this.state.viewConfig.listSize}
                onChange={this.OnFilter}
                onPageSizeChange={this.handlePageSizeChange.bind(this)}

                getTrProps={(state, rowInfo, column) => {
                    if (rowInfo) {
                        if (rowInfo.row.stock == 0) {
                            return {
                                style: {
                                    background: "#EE7770EE",
                                    color: "white"
                                }
                            };
                        }
                        else if (rowInfo.row.stock > 0 && rowInfo.row.stock < 5) {
                            return {
                                style: {
                                    background: "#FFD200CC"
                                }
                            };
                        }
                        else return {};
                    }
                    else return {};
                }}
                previousText={i18n.t('common.tableOptions.prevText')}
                nextText={i18n.t('common.tableOptions.nextText')}
                pageText={i18n.t('common.tableOptions.pageText')}
                noDataText={i18n.t('common.tableOptions.noDataText')}
                ofText={i18n.t('common.tableOptions.ofText')}
                rowsText={i18n.t('common.tableOptions.rowsText')}
            />
        )
    }

    mainView() {
        //console.debug("select: ",this.state.products_filter);

        let popupAlert = null;

        if (this.state.showBroadcastMessageInputAlert == true) {
            popupAlert = <ConfirmationAlert
                titleHeader= {i18n.t('pages.products.widgetProductOptions.buttons.broadcast.popup.title')}
                subTitleHeader={i18n.t('pages.products.widgetProductOptions.buttons.broadcast.popup.subTitle') + require('os').EOL + i18n.t('pages.products.widgetProductOptions.buttons.broadcast.popup.subTitleNote')}
                inputMsgPlaceHolder={i18n.t('pages.products.widgetProductOptions.buttons.broadcast.popup.messagePlaceholder')}
                saveButtonTitle={i18n.t('common.buttons.send')}
                cancelAction={this.cancelBroadcastAction.bind(this)}
                saveAction={this.onBroadcastConfirmationAction.bind(this)}
            />;
        }
        else if (this.state.showBulkPostOnFacebookTitleMessgeInputAlert == true) {
            popupAlert = <ConfirmationAlert
                titleHeader={i18n.t('pages.products.widgetProductOptions.buttons.postFacebook.popup.title')}
                subTitleHeader={i18n.t('pages.products.widgetProductOptions.buttons.postFacebook.popup.subTitle')}
                inputMsgPlaceHolder={i18n.t('pages.products.widgetProductOptions.buttons.postFacebook.popup.messagePlaceholder')}
                saveButtonTitle={i18n.t('pages.products.widgetProductOptions.buttons.postFacebook.popup.postButton')}
                cancelAction={this.cancelFBPostAction.bind(this)}
                saveAction={this.onFBPostConfirmationAction.bind(this)}
            />;
        }

        let total = null;
        if (this.state.selectedData.length > 0) {
            total = <li className="small-info"> <small> {this.state.selectedData.length} {i18n.t('pages.products.widgetProductOptions.totalCount.selected')} </small></li>;
        }
        else {
            total = <li className="small-info"> <small> {this.state.products_filter.length} {i18n.t('pages.products.widgetProductOptions.totalCount.total')}  </small></li>;
        }

        return (
            <div className="col-lg-12 col-md-12">
                <form encType="multipart/form-data">
                    <div className="row margin-0 category_list">
                        <div className="col-md-7 left-option">
                            <ul>
                                <li className="category-list-options" title={i18n.t('pages.products.productOptions.dropdown.tooltip')}>
                                    <Select
                                        name="form-field-name"
                                        value={this.state.selectedCategory}
                                        options={this.state.category_chain_option}
                                        onChange={this.categoryOptionChange}
                                    />
                                </li>
                                <li>
                                    <a href="#" onClick={this.uncategorizedProductsList} className="btn btn-admin btn-admin-white" title={i18n.t('pages.products.productOptions.buttons.uncategorized.tooltip')}> {i18n.t('pages.products.productOptions.buttons.uncategorized.name')} </a>
                                </li>
                            </ul>
                        </div>
                        <div className="col-md-5 right-option">
                            <p className="text-right file-upload">
                                <a disabled={(this.state.selectedCategory == 0) ? "disabled" : ""} onClick={this.onCsvProcessStart.bind(this)} href="#" className="btn btn-admin btn-admin-white btn-md" title={this.state.selectedCategory == 0 ? i18n.t('pages.products.productOptions.buttons.demoCSV.tooltipDisabled') : i18n.t('pages.products.productOptions.buttons.demoCSV.tooltipEnabled')}> {i18n.t('pages.products.productOptions.buttons.demoCSV.name')} </a>
                                <input ref="file" type="file" onClick={(event) => { event.target.value = null }} onChange={this.onCsvUploadTrigger} name="file" id="csvUploadFiled" />
                                <label disabled={(this.state.selectedCategory == 0) ? "disabled" : ""} htmlFor={(this.state.selectedCategory == 0) ? "" : "csvUploadFiled"} className="btn btn-admin btn-admin-white" title={this.state.selectedCategory == 0 ? i18n.t('pages.products.productOptions.buttons.uploadCSV.tooltipDisabled') : i18n.t('pages.products.productOptions.buttons.uploadCSV.tooltipEnabled')}> <i className="fa fa-file-text"> </i> {i18n.t('pages.products.productOptions.buttons.uploadCSV.name')}</label>
                                <a href={"/" + config.activeAgent + "/product/create"} className="btn btn-admin btn-admin-white btn-md" title={i18n.t('pages.products.productOptions.buttons.create.tooltip')}> <i className="fa fa-edit"></i> {i18n.t('common.buttons.create')} </a>
                            </p>
                        </div>
                    </div>

                    <div className="row margin-0 dashboard-widget product-option">
                        <div className="col-md-7 left_option">
                            <ul>
                                <li className="big-title"> <h4 className="title"> {this.state.selectedCategoryName} </h4></li>
                                {total}
                                <li className="Search_Bar"> <span className="SearchIcon"> <i className="fa fa-search fa-2x"></i></span> <input type="text" onChange={this.OnFilter} ref="filter_product" className="product_search_field" placeholder={i18n.t('pages.products.widgetProductOptions.search.placeholder')} /> </li>
                            </ul>
                        </div>
                        <div className="col-md-5 right-option text-right">
                            <a
                                href="#"
                                disabled={(this.state.selectedData.length > 0 || this.state.isSelectAll == true) && this.state.selectedData.length <= limits.fbPost ? '' : "disabled"}
                                onClick={this.onFBPostAction.bind(this)}
                                className="btn btn-admin btn-admin-default btn-broadcast-product"
                                title={this.state.selectedData.length > 0 && this.state.selectedData.length <= limits.fbPost ? i18n.t('pages.products.widgetProductOptions.buttons.postFacebook.tooltipEnabled') : i18n.t('pages.products.widgetProductOptions.buttons.postFacebook.tooltipDisabled')}
                            > {i18n.t('pages.products.widgetProductOptions.buttons.postFacebook.name')}</a>
                            <a
                                href="#"
                                disabled={(this.state.selectedData.length > 0 && this.state.selectedData.length <= limits.msgBroadcast && this.state.trainingStatus == training.done) ? '' : "disabled"}
                                onClick={this.onBroadcastAction}
                                className="btn btn-admin btn-admin-default btn-broadcast-product"
                                title={this.state.selectedData.length > 0 && this.state.selectedData.length <= limits.msgBroadcast ? this.state.trainingStatus == training.done ? i18n.t('pages.products.widgetProductOptions.buttons.broadcast.tooltipEnabled') : i18n.t('pages.products.widgetProductOptions.buttons.broadcast.tooltipTrainAgent') : i18n.t('pages.products.widgetProductOptions.buttons.broadcast.tooltipEntities')}
                            > {i18n.t('pages.products.widgetProductOptions.buttons.broadcast.name')} </a>
                            <a
                                href="#"
                                disabled={(this.state.selectedData.length > 0 || this.state.isSelectAll == true) ? '' : "disabled"}
                                onClick={this.onDeleteAction}
                                className="btn btn-admin btn-admin-default"
                                title={this.state.selectedData.length > 0 ? i18n.t('pages.products.widgetProductOptions.buttons.delete.tooltipEnabled') : i18n.t('pages.products.widgetProductOptions.buttons.delete.tooltipDisabled')}
                            > {i18n.t('common.buttons.delete') }</a>
                        </div>
                    </div>
                    <div id="products-list" className="dashboard-widget list products-list">
                        <div id="product_lists">
                            {this.renderView()}
                        </div>
                    </div>
                </form>

                {popupAlert}

            </div>
        )
    }

    render() {
        return this.mainView();
    }
}

export default ProductMain;
