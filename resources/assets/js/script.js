/**
 * Created by sahidhossen on 3/20/17.
 */

var Slider = require("bootstrap-slider");
require("bootstrap-select");

var UshaOption = {
    _token : $('meta[name="csrf-token"]').attr('content'),
    showLoader : function( parent ){
        let loader = document.createElement('div');
        loader.className = "loading-animation";
        parent.append(loader);
    },
    removeLoader: function( parent ){
        parent.find(".loading-animation").remove();
    }

}


$(function(){
    $(".menu-toggle").click(function(e) {
        e.preventDefault();
        $("#app").toggleClass("toggled");
    });

    //Product page selectpicker option for category
    if( $(".selectpicker").length > 0) {
        $('.selectpicker').selectpicker({
            style: 'btn-none',
            size: 10
        });
    }
})



// Direct javascript code
if(document.getElementById("ex18a")) {
    var sliderA = new Slider("#ex18a", {
        labelledby: 'ex18-label-1'
    });
    sliderA.on("slide", function (sliderValue) {
        document.getElementById("showRatingValue").textContent = sliderValue;
    });
}


//Product image upload
if(document.getElementById('featureImageUpload')) {
    var input = document.getElementById('featureImageUpload'),
      featureImage = document.getElementById('featureImage'),
      imgLink = $("#image_link"),
      detaultImageSrc = featureImage.src,
      removeBtn = document.getElementById('removeFeatureImage'),
      dimention_error_found = document.getElementById('dimention_error_found');

    input.addEventListener("change", function () {
        var file = this.files[0],
          img = new Image(),
          dimentionError = null,
          denger = false,
          extensions = ["jpg", "JPG", "JPEG", "jpeg", "png", "PNG",'GIF','gif'];
        img.onload = function () {
            var sizes = {
                width: this.width,
                height: this.height
            };
            URL.revokeObjectURL(this.src);
            if ((sizes.width < 600 && sizes.width > 573) || ( sizes.height < 350 && sizes.height > 300)) {
                dimentionError = '<p class="dimention_warning"> <small> Required size 574x300  | (size: ' + sizes.width + ' x ' + sizes.height + '  )  </small></p>'
            }
            if (sizes.width > 600 || sizes.height > 350) {
                denger = true;
                dimentionError = '<p class="dimention_error"> <small>Required size 574x300  | (size: ' + sizes.width + ' x ' + sizes.height + ' )</small></p> '
            }
            if (dimentionError !== null) {
               // dimention_error_found.innerHTML = dimentionError;
               //  if (denger)
               //      input.value = "";
            } else {
                dimention_error_found.innerHTML = "";
            }
        }
        var ext = file.name.split('.').pop();
        console.debug(extensions.indexOf(ext));
        if (extensions.indexOf(ext) == -1) {
            dimention_error_found.innerHTML = '<p class="dimention_error"> <small> File type not allowed </small></p> '
            input.value = "";
        } else {
            var objectURL = URL.createObjectURL(file);
            img.src = objectURL;
            featureImage.src = img.src;
            removeBtn.style.display = "inline-block";
            imgLink.val("");
        }
    });

    removeBtn.addEventListener('click', function (e) {
        e.preventDefault();
        featureImage.src = detaultImageSrc;
        input.value = "";
        dimention_error_found.innerHTML = "";
        this.style.display = "none";
    })

    /*
        If upload image and also type image link then image should be remove
     */
    imgLink.on("keyup", function(e){
        if($(this).val().length > 5 ) {
            featureImage.src = detaultImageSrc;
            input.value = "";
            dimention_error_found.innerHTML = "";
            removeBtn.style.display = "none";
        }
    })

}


$(function(){

    //Check if the select category ID exists
    if( $("select#productCategoryID").length > 0 ){
        var categorySelectBox = $("select#productCategoryID");
        var process = 'new';
        var productID = null;

        // If ID is selected then send the ajax request instance and
        // show attribute fields
        if( categorySelectBox.val() !== '' && categorySelectBox.val().length > 0 ) {
            if( categorySelectBox.attr('data-product_id') ){
                process = 'update';
                productID = categorySelectBox.attr('data-product_id');
            }
            requestForAttribute( categorySelectBox.val(), productID, process );
        }

        //Send ajax request each change in category lists
        categorySelectBox.on('change', function(e){
            e.preventDefault();
            if( $(this).attr('data-product_id') ){
                process = 'update';
                productID = $(this).attr('data-product_id');
            }
            var categoryID = $(this).val();

            if(categoryID == '' && categoryID.length < 1 ) {
                // Remove prev attributes
                removeAttributes();

                return false;
            }

            requestForAttribute( categoryID, productID, process );
        })
    }

    //Aajax request for face the attribute lists
    function requestForAttribute( categoryID, productID, process ){
        let attributeMainBox = $(".attributeBox"),
            attributeHolder = attributeMainBox.find(".update-form");

        $.ajax({
            url: "/api/get_category_and_product_attributes",
            type: 'POST',
            dataType: "JSON",
            data: {
                "cat_id": categoryID,
                'process': process,
                'product_id': productID
            },
            beforeSend: function (request ) {
                request.setRequestHeader("X-CSRF-TOKEN", UshaOption._token);
                // $(".ajax_loader").show();
            },
            complete: function () {
                // $(".ajax_loader").hide();
            },
            success: function (data) {
                if( data.error == false ){
                    console.debug( "Attributes ",  data.attributes);
                    let attributes = data.attributes;

                    if( attributes !== null ) {
                        let htmlEntities='';
                        for (let k in attributes) {

                            if (attributes.hasOwnProperty(k)) {
                                if( data.process == 'update' ){
                                    let attrValue = (attributes[k] === null ) ? '' : attributes[k];
                                    console.debug("attribute: ", attributes);
                                    htmlEntities += ' <div class="form-group"> ' +
                                        '<label class="col-md-12" for="attributesName"> ' + k + ' </label>' +
                                        '<div class="col-md-12">' +
                                        '<input type="text" class="form-control" value="'+ attrValue +'" placeholder="' + k + '" name="attribute_list[' + k + ']">' +
                                        '</div>' +
                                        '</div>';
                                }else {
                                    htmlEntities += ' <div class="form-group"> ' +
                                        '<label class="col-md-12" for="attributesName"> ' + attributes[k] + ' </label>' +
                                        '<div class="col-md-12">' +
                                        '<input type="text" class="form-control" placeholder=" ' + attributes[k] + ' " name="attribute_list[' + k + ']">' +
                                        '</div>' +
                                        '</div>';
                                }
                            }
                        }
                        attributeMainBox.fadeIn();
                        attributeHolder.html(htmlEntities)
                    }else {
                        attributeMainBox.fadeOut();
                    }
                }else {
                    console.debug( "Category request error: "+  data.message );
                }
            }

        });
    }

    // Remove previously selected attributes
    function removeAttributes() {
        const attributeMainBox = $(".attributeBox");

        // Remove inner content
        attributeMainBox.hide();
    }

    /*
     * Agent disable or Enable ajax call
     * @call API routes for action this operation
     */

  if(document.getElementById("botEngineSwitch")) {
    let botEngineSwitch = $("#botEngineSwitch");
    botEngineSwitch.on("change", function(e){
        let botAction = null;
        let that = $(this);
          botAction = !!$(this).is(":checked");
         console.debug(botAction);
          if(botAction !=null ){

             $.ajax({
                 url: "/api/agent_engine_switcher",
                 type: 'POST',
                 dataType: "JSON",
                 data:{ switch: botAction },
                 beforeSend: function (request) {
                     request.setRequestHeader("X-CSRF-TOKEN", UshaOption._token);
                 },
                 complete: function () {
                 },
                 success: function (data) {
                     console.debug(data);
                     if (data.error == false) {
                         console.debug(data);
                     } else {
                        if(botAction == true ) {
                            that.prop('checked', false);
                        }else {
                            that.prop('checked', true);
                        }
                         console.debug("Error on agent training: " + data.message);
                     }
                 }

             });

         }
    })
  }

  $(".selectOrderAction").on("change", function(e){
      let orderAction = $(this).val();
      let orderID = $(this).data("order_id");
      let tr = $(this).parents("tr");

      let orderField = '';
        if( orderAction == 1 ){
            orderField = "is_new";
        }
      if( orderAction == 2 ){
          orderField = "is_sent";
      }
      if( orderAction == 3 ){
          orderField = "is_delivered";
      }
      if( orderAction == 4 ){
          orderField = "is_cancelled";
      }

      // console.debug("action", orderField + ": "+ orderAction + ": "+ orderID );
      // return false;

      $.ajax({
          url: "/api/change_order_action",
          type: 'POST',
          dataType: "JSON",
          data:{ field: orderField ,state:1, order_id:orderID  },
          beforeSend: function (request) {
              request.setRequestHeader("X-CSRF-TOKEN", UshaOption._token);
          },
          complete: function () {
          },
          success: function (data) {
              console.debug(data);
              if (data.error == false) {
                  tr.attr("class","");
                  tr.addClass(data.field);
              } else {
                  console.debug("Error on agent training: " + data.message);
              }
          }

      });
  })



    $(".active_fb_page").on("click", function(e){
        e.preventDefault();
        let fbAccessToken  = $(this).data('page_access_token');
        let fbPageName  = $(this).data('fb_page_name');
        let fbPageId  = $(this).data('fb_page_id');
        let that = $(this);
        let parent = $(this).parents(".dashboard-widget");

        $.ajax({
            url: "/api/connect_facebook_page",
            type: 'POST',
            dataType: "JSON",
            data:{ fb_access_token: fbAccessToken, fb_page_name: fbPageName, fb_page_id:fbPageId  },
            beforeSend: function (request) {
                UshaOption.showLoader( parent );
                request.setRequestHeader("X-CSRF-TOKEN", UshaOption._token);
            },
            complete: function () {
                UshaOption.removeLoader( parent );
            },
            success: function (data) {
                if(data.error == false ) {
                    that.removeClass("active_fb_page").addClass("deactivate_fb_page");
                    that.html('Disconnect');
                    let li = that.parents('li');
                    li.siblings('li').not(li).slideUp(300, function(){
                        $(this).remove();
                    })
                    parent.find('.title').html("Active Page");
                    parent.find(".fb-connection-btn").addClass("hide");
                }else {
                    console.debug("facebook page error: ", data.message );
                }
            }

        });
    })

    $(document).on('click','.deactivate_fb_page', function(e) {
    // $(".deactivate_fb_page").on("click", function(e){
        e.preventDefault();
        let fbAccessToken  = $(this).data('page_access_token');
        let that = $(this);
        let parent = $(this).parents(".dashboard-widget");

        $.ajax({
            url: "/api/disconnect_facebook_page",
            type: 'POST',
            dataType: "JSON",
            data:{ fb_access_token: fbAccessToken },
            beforeSend: function (request) {
                UshaOption.showLoader( parent );
                request.setRequestHeader("X-CSRF-TOKEN", UshaOption._token);
            },
            complete: function () {
                UshaOption.removeLoader( parent );
            },
            success: function (data) {
                console.debug(data);
                if(data.error == false ) {
                    that.parents('.facebook_pages').slideUp().remove();
                    // parent.find(".fb-connection-btn").removeClass("hide");
                    window.location = '/connect_fb_page';
                 }else {
                    console.debug("facebook page error: ", data.message );
                }
            }

        });
    })


})
