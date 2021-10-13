@extends('layouts.app')

@section('stylesheet')
    <link href="{{ asset('css/bootstrap-slider.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sa_app.css') }}" rel="stylesheet">
@endsection

@include('elements.dashboard',['sidebar'=>true,'navbar'=>true])

@section('content')

    <div class="container-fluid zero-padding">
        <form action="{{ url('product/update') }}" class="form-horizontal" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $product->id }}">
            <div class="row margin-0 product_body">
                <div class="col-md-12">
                    <div class="row dashboard-widget margin-0 update_header">
                        <h3 class="title pull-left"> {{__('pages.productUpdate.widget.title')}} </h3>
                        <input type="submit" name="submitPrductUpload" class="btn btn-theme btn-theme-success pull-right" value="{{__('common.buttons.save')}}">
                        <a href="{{ route('product.create', ['agent_code'=>$agent_code]) }}" class="btn btn-admin btn-admin-white create_new_product_btn"> {{__('pages.productUpdate.widget.buttons.create')}} </a>

                        @if ($product->social_posts)
                            <input type="submit" name="postOnFacebook" class="btn btn-admin btn-admin-white create_new_product_btn" value="{{__('pages.productUpdate.widget.buttons.remove')}}">
                        @else
                            <input type="submit" name="postOnFacebook" class="btn btn-admin btn-admin-white create_new_product_btn" value="{{__('pages.productUpdate.widget.buttons.post')}}">
                        @endif

                    </div>
                </div>
            </div>

        @if( Session::get('success') )
            <div class="row margin-0 product_body">
                <div class="col-md-12">
                    <div class="row dashboard-widget margin-0">
                       <p class="alert alert-success"> {{ Session::get('success') }}</p>
                    </div>
                </div>
            </div>
            @endif
            @if( Session::get('error') )
                <div class="row margin-0 product_body">
                    <div class="col-md-12">
                        <div class="row dashboard-widget margin-0">
                            <p class="alert alert-danger"> {{ Session::get('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            <div class="row margin-0 product_body">
                <div class="col-md-12">
                    <div class="row dashboard-widget margin-0">
                        <input type="text" class="form-control product_name_field" placeholder="{{__('pages.productUpdate.widget.productName.placeholder')}}" name="name" value="{{ old('name', $product->name) }}">
                        @if ($errors->has('name')) <p class="alert alert-danger">{{ $errors->first('name') }}</p> @endif
                    </div>
                </div>
            </div>
            <div class="row margin-0 product_body">
                <div class="col-md-6">
                    <div class="product_details product-block-left-block dashboard-widget product-block row">
                        <h3 class="sub-title"> {{__('pages.productUpdate.entity.subtitle')}} </h3>
                        <div class="update-form">
                            <div class="form-group">
                                <label for="ProductName" class="col-md-3 col-xs-3"> {{__('pages.productUpdate.entity.form.code.label')}} </label>
                                <div class="col-md-9 col-xs-9">
                                    <input type="text" class="form-control" placeholder="{{__('pages.productUpdate.entity.form.code.placeholder')}}" name="code" value="{{ old('code', $product->code) }}">
                                    @if ($errors->has('code')) <p class="alert alert-danger">{{ $errors->first('code') }}</p> @endif
                                </div>

                            </div>
                            <div class="form-group">
                                <label for="ProductName" class="col-md-3 col-xs-3"> {{__('pages.productUpdate.entity.form.detail.label')}} </label>
                                <div class="col-md-9 col-xs-9">
                                    <textarea name="detail" class="form-control" rows="2">{{ old('detail',$product->detail) }}</textarea>
                                    @if ($errors->has('detail')) <p class="alert alert-danger">{{ $errors->first('detail') }}</p> @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ProductName" class="col-md-3 col-xs-3"> {{__('pages.productUpdate.entity.form.price.label')}} </label>
                                <div class="col-md-9 col-xs-9">
                                    <input type="text" class="form-control" placeholder="{{__('pages.productUpdate.entity.form.price.placeholder')}}" name="price" value="{{ old('price', $product->price) }}">
                                    @if ($errors->has('price')) <p class="alert alert-danger">{{ $errors->first('price') }}</p> @endif

                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ProductName" class="col-md-3 col-xs-3"> {{__('pages.productUpdate.entity.form.offerPrice.label')}}</label>
                                <div class="col-md-9 col-xs-9">
                                    <input type="text" class="form-control" placeholder="{{__('pages.productUpdate.entity.form.offerPrice.placeholder')}}" name="offer_price" value="{{ old('offer_price',$product->offer_price) }}">
                                    @if ($errors->has('offer_price')) <p class="alert alert-danger">{{ $errors->first('offer_price') }}</p> @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="stock" class="col-md-3 col-xs-3 text-right">{{__('pages.productUpdate.entity.form.stock.label')}}</label>
                                <div class="col-md-9 col-xs-9">
                                    <input type="text" class="form-control" placeholder="{{__('pages.productUpdate.entity.form.stock.placeholder')}}" name="stock" value="{{ old('stock', $product->stock) }}">
                                    @if ($errors->has('stock')) <p class="alert alert-danger">{{ $errors->first('stock') }}</p> @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ProductName" class="col-md-3 col-xs-3"> {{__('pages.productUpdate.entity.form.rating.label')}} </label>
                                <div class="col-md-9 col-xs-9">
                                    <div class="product-block-slider">
                                        <input id="ex18a" name="priority" data-slider-id='ex1Slider' type="text" data-slider-min="0" data-slider-max="10" data-slider-step="1" data-slider-value="{{ old('priority', $product->priority) }}"/> <span class="showRatingValue"> {{__('pages.productUpdate.entity.form.rating.rate')}}  <i  id="showRatingValue"> {{old('priority', $product->priority)}}</i> </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ProductName" class="col-md-3 col-xs-3"> {{__('pages.productUpdate.entity.form.unit.label')}}</label>
                                <div class="col-md-9 col-xs-9">
                                    <input type="text" class="form-control" value="{{ old('unit', $product->unit) }}" placeholder="{{__('pages.productUpdate.entity.form.unit.placeholder')}}" name="unit">
                                    @if ($errors->has('unit')) <p class="alert alert-danger">{{ $errors->first('unit') }}</p> @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ExternalWebsiteLink" class="col-md-3 col-xs-3 text-right">{{__('pages.productUpdate.entity.form.web.label')}}</label>
                                <div class="col-md-9 col-xs-9">
                                    <input type="text" class="form-control" placeholder="{{__('pages.productUpdate.entity.form.web.placeholder')}}" name="external_link" value="{{ old('external_link', $product->external_link) }}">
                                    @if ($errors->has('external_link')) <p class="alert alert-danger">{{ $errors->first('external_link') }}</p> @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature_image product-block-right-block dashboard-widget product-block row">
                        <h3 class="sub-title"> {{__('pages.productUpdate.image.subtitle')}} </h3>
                        <div class="update-form">

                            <div class="form-group margin-0" id="uploadFeatureImage">
                                <div id="dimention_error_found"></div>
                                <div class="file-upload">
                                    <label for="featureImageUpload"> &nbsp; </label>
                                    <input type="file" name="file" id="featureImageUpload">

                                    <img id="featureImage" src="{{ ($product->is_image) ? asset('uploads/'.$product->is_image) :  asset('images/upload_model.png') }}" alt="Upload Model">
                                </div>
                                <a id="removeFeatureImage" href="#"> {{__('pages.productUpdate.image.form.remove')}} </a>

                                @if( Session::get('empty_image') )
                                   <p class="alert alert-warning"> <small>{{ Session::get('empty_image') }}</small> </p>
                                @endif

                            </div>
                            <div class="update-form image-link">
                                <label for="image_link"> {{__('pages.productUpdate.image.form.link.label')}} </label>
                                <input type="text" name="image_link" class="form-control" value="{{ old('image_link', $product->image_link) }}" placeholder="{{__('pages.productUpdate.image.form.link.placeholder')}}">
                                @if ($errors->has('image_link')) <p class="alert alert-danger">{{ $errors->first('image_link') }}</p> @endif
                            </div>
                        </div>
                    </div>
                    <div class="product_details product-block-right-block dashboard-widget product-block row">
                        <h3 class="sub-title"> {{__('pages.productUpdate.intent.subtitle')}} <span class="loading-con"> <i class="fa fa-gear"></i> </span></h3>
                        <div class="update-form">
                            <div class="form-group">
                                <label for="ProductName" class="col-md-3 col-xs-3 text-right"> {{__('pages.productUpdate.intent.form.label')}} </label>
                                <div class="col-md-9 col-xs-9">
                                    <div class="category-select">

                                        <select class="selectpicker" id="productCategoryID" data-product_id="{{ $product->id }}" name="category_id" data-live-search="true" data-width="100%">
                                            <option value=""> {{__('pages.productUpdate.intent.form.optionValue')}} </option>
                                            @foreach( $categories as $category )
                                                        @if($category->chainString !=null )
                                                    <optgroup label="{{ $category->chainString }}" data-max-options="2">
                                                        @endif

                                                        @if( old('category_id', $product->category_id ) == $category->id )
                                                            <option value="{{ $category->id }}" selected> {{ $category->name }}</option>
                                                        @else
                                                            <option value="{{ $category->id }}"> {{ $category->name }}</option>
                                                        @endif

                                                        @if($category->chainString !=null )
                                                    </optgroup>
                                                        @endif
                                            @endforeach
                                        </select>
                                        @if ($errors->has('category_id')) <p class="alert alert-danger">{{ $errors->first('category_id') }}</p> @endif

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if( count($product->category_required_attributes) > 0 )
                    <div class="feature_image product-block-right-block dashboard-widget product-block attributeBox row">
                        <h3 class="sub-title"> {{__('pages.productUpdate.attribute.subtitle')}} </h3>
                        <div class="update-form">
                            @foreach( $product->category_required_attributes as $attr_name => $attribute_value )
                            <div class="form-group">
                                <label class="col-md-12" for="attributesName"> {{ ucfirst($attr_name) }} </label>
                                <div class="col-md-12">
                                    <input type="text" class="form-control" value="{{ $attribute_value }}" placeholder="{{ ucfirst($attr_name) }}" name="attribute_list[{{ strtolower(str_replace(' ','',$attr_name)) }}]">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="feature_image product-block-right-block dashboard-widget product-block attributeBox row">
                        <h3 class="sub-title"> {{__('pages.productUpdate.attribute.subtitle')}} </h3>
                        <div class="update-form">

                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </form>
    </div>

@endsection

@section('script')
    <script src="{{ asset('js/bootstrap-slider.min.js') }}"></script>
@endsection

