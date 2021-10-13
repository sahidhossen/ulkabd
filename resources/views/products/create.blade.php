@extends('layouts.app')
@section('stylesheet')
    <link href="{{ asset('css/bootstrap-slider.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sa_app.css') }}" rel="stylesheet">
@endsection

@include('elements.dashboard',['sidebar'=>true,'navbar'=>true])

@section('content')
    <div class="container-fluid zero-padding">
        <form action="{{url('product/store')}}" class="form-horizontal" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="row margin-0 product_body">
                <div class="col-md-12">
                    <div class="row dashboard-widget margin-0">
                        <h3 class="title pull-left"> {{__('pages.productCreate.widget.title')}} </h3>
                        <input type="submit" class="btn btn-theme btn-theme-success pull-right" value="{{__('common.buttons.save')}}">
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
            <div class="row margin-0 product_body">
                <div class="col-md-12">
                    <div class="row dashboard-widget margin-0">
                        <input type="text" class="form-control product_name_field" placeholder="{{__('pages.productCreate.widget.productName.placeholder')}}" name="name" value="{{ old('name') }}">
                        @if ($errors->has('name')) <p class="alert alert-danger">{{ $errors->first('name') }}</p> @endif
                     </div>
                </div>
            </div>
            <div class="row margin-0 product_body">
                <div class="col-md-6">
                    <div class="product_details product-block-left-block dashboard-widget product-block row">
                        <h3 class="sub-title"> {{__('pages.productCreate.entity.subtitle')}} </h3>
                        <div class="update-form">
                            <div class="form-group">
                                <label for="ProductName" class="col-md-3 col-xs-3 text-right">{{__('pages.productCreate.entity.form.code.label')}}</label>
                                <div class="col-md-9 col-xs-9">
                                    <input type="text" class="form-control" placeholder="{{__('pages.productCreate.entity.form.code.placeholder')}}" name="code" value="{{ old('code') }}">
                                    @if ($errors->has('code')) <p class="alert alert-danger">{{ $errors->first('code') }}</p> @endif

                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ProductName" class="col-md-3 col-xs-3 text-right">{{__('pages.productCreate.entity.form.detail.label')}}</label>
                                <div class="col-md-9 col-xs-9">
                                    <textarea type="text" class="form-control" rows="5" placeholder="{{__('pages.productCreate.entity.form.detail.placeholder')}}" name="detail">{{ old('detail') }}</textarea>
                                    @if ($errors->has('detail')) <p class="alert alert-danger">{{ $errors->first('detail') }}</p> @endif

                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ProductName" class="col-md-3 col-xs-3 text-right">{{__('pages.productCreate.entity.form.price.label')}}</label>
                                <div class="col-md-9 col-xs-9">
                                    <input type="text" class="form-control" placeholder="{{__('pages.productCreate.entity.form.price.placeholder')}}" name="price" value="{{ old('price') }}">
                                    @if ($errors->has('price')) <p class="alert alert-danger">{{ $errors->first('price') }}</p> @endif

                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ProductName" class="col-md-3 col-xs-3 text-right">{{__('pages.productCreate.entity.form.offerPrice.label')}}</label>
                                <div class="col-md-9 col-xs-9">
                                    <input type="text" class="form-control" placeholder="{{__('pages.productCreate.entity.form.offerPrice.placeholder')}}" name="offer_price" value="{{ old('offer_price') }}">
                                    @if ($errors->has('offer_price')) <p class="alert alert-danger">{{ $errors->first('offer_price') }}</p> @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="stock" class="col-md-3 col-xs-3 text-right"> {{__('pages.productCreate.entity.form.stock.label')}}</label>
                                <div class="col-md-9 col-xs-9">
                                    <input type="text" class="form-control" placeholder="{{__('pages.productCreate.entity.form.stock.placeholder')}}" name="stock" value="{{ old('stock') }}">
                                    @if ($errors->has('stock')) <p class="alert alert-danger">{{ $errors->first('stock') }}</p> @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ProductName" class="col-md-3 col-xs-3 text-right">{{__('pages.productCreate.entity.form.priority.label')}}</label>
                                <div class="col-md-9 col-xs-9">
                                    <div class="product-block-slider">
                                        <input id="ex18a" data-slider-id='ex1Slider' name="priority" type="text" data-slider-min="0" data-slider-max="10" data-slider-step="1" data-slider-value="0"/>
                                        <span class="showRatingValue"> {{__('pages.productCreate.entity.form.priority.rate')}} <i id="showRatingValue">0</i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ProductName" class="col-md-3 col-xs-3 text-right">{{__('pages.productCreate.entity.form.unit.label')}}</label>
                                <div class="col-md-9 col-xs-9">
                                    <input type="text" class="form-control" placeholder="{{__('pages.productCreate.entity.form.unit.placeholder')}}" name="unit" value="{{ old('unit') }}">
                                    @if ($errors->has('unit')) <p class="alert alert-danger">{{ $errors->first('unit') }}</p> @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ExternalWebsiteLink" class="col-md-3 col-xs-3 text-right">{{__('pages.productCreate.entity.form.web.label')}}</label>
                                <div class="col-md-9 col-xs-9">
                                    <input type="text" class="form-control" placeholder="{{__('pages.productCreate.entity.form.web.placeholder')}}" name="external_link" value="{{ old('external_link') }}">
                                    @if ($errors->has('external_link')) <p class="alert alert-danger">{{ $errors->first('external_link') }}</p> @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="feature_image product-block-right-block  dashboard-widget product-block row">
                        <h3 class="sub-title"> {{__('pages.productCreate.image.subtitle')}} </h3>
                        <div class="update-form">
                            <div class="form-group margin-0" id="uploadFeatureImage">
                                <div id="dimention_error_found"></div>
                                <div class="file-upload">
                                    <label for="featureImageUpload"> &nbsp; </label>
                                    <input type="file" name="file" id="featureImageUpload">

                                    <img id="featureImage" src="{{ (isset($product->image_src)) ? asset('uploads/'.$product->image_src) :  asset('images/upload_model.png') }}" alt="Upload Model">
                                </div>
                                <a id="removeFeatureImage" href="#"> {{__('pages.productCreate.image.form.remove')}} </a>
                            </div>

                        </div>
                        <div class="update-form image-link">
                            <label for="image_link"> {{__('pages.productCreate.image.form.link.label')}} </label>
                            <input type="text" name="image_link" id="image_link" class="form-control" value="{{ old('image_link') }}" placeholder="{{__('pages.productCreate.image.form.link.placeholder')}}">
                            @if ($errors->has('image_link')) <p class="alert alert-danger">{{ $errors->first('image_link') }}</p> @endif
                        </div>
                    </div>

                    <div class="product_details product-block-right-block dashboard-widget product-block row">
                        <h3 class="sub-title"> {{__('pages.productCreate.intent.subtitle')}} <span class="loading-con"> <i class="fa fa-gear"></i> </span></h3>
                        <div class="update-form">
                            <div class="form-group">
                                <label for="ProductName" class="col-md-3 col-xs-3 text-right"> {{__('pages.productCreate.intent.form.label')}} </label>
                                <div class="col-md-9 col-xs-9">
                                    <div class="category-select">

                                        <select class="selectpicker" id="productCategoryID" name="category_id" data-live-search="true" data-width="100%">
                                            <option value=""> {{__('pages.productCreate.intent.form.optionValue')}} </option>
                                            @foreach( $categories as $category )
                                                @if($category->chainString !=null )
                                                <optgroup label="{{ $category->chainString }}" data-max-options="2">
                                                @endif
                                                  @if( old('category_id') == $category->id )
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
                    <div class="product_details product-block-right-block dashboard-widget product-block row attributeBox">
                        <h3 class="sub-title"> {{__('pages.productCreate.attribute.subtitle')}} </h3>
                        <div class="update-form">
                            <div class="form-group">
                                <label class="col-md-12" for="attributesName"> {{__('pages.productCreate.attribute.form.label')}} </label>
                                <div class="col-md-12">
                                    {{--<input type="text" class="form-control" placeholder="{{__('pages.productCreate.attribute.form.placeholder')}}" name="attribute_list[size]">--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>



@endsection
