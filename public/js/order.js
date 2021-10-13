$(document).ready(function(e){


    var token  = $('meta[name="csrf-token"]').attr('content');

    $(".edit_order_btn").on('click', function(e){
        e.preventDefault();
        var order_id = $(this).data('order_id');
        console.debug( order_id );
        $.ajax({
            url: "/get_current_order",
            type: 'POST',
            dataType: "JSON",
            data: {
                "id": order_id,
                "_token": token
            },
            beforeSend: function () {

                //  $(".ajax_loader").show();
            },
            complete: function () {
                // $(".ajax_loader").hide();
            },
            success: function ( response ) {
                console.debug('batch request received!');
                console.debug(response);
                if( response.error == true )
                    return false;

                $('#orderModal').modal('show');
                var order = response.order;
                var updateForm = '<div class="form-group">' +
                    '<label for="cityName" class="col-sm-4 control-label">City :</label>' +
                '<div class="col-sm-8">'+
                    '<input type="hidden" value="'+order.id+'" name="id">'+
                    '<input type="text" value="'+order.city+'" name="city" class="form-control" placeholder="Enter City">'+
                    '</div>'+
                    '</div>'+
                    '<div class="form-group">'+
                    '<label for="buyerAddress" class="col-sm-4 control-label">Delivery Address :</label>'+
                '<div class="col-sm-8">'+
                    '<input type="text" name="address" class="form-control" value="'+order.address+'" placeholder="Enter Address">'+
                    '</div>'+
                    '</div>'+
                    '<div class="form-group">'+
                    '<label for="productSize" class="col-sm-4 control-label">Product Size :</label>'+
                '<div class="col-sm-8">'+
                    '<input type="text" name="product_size" class="form-control" value="'+order.product_size+'"  placeholder="Enter Product Size">'+
                    '</div>'+
                    '</div>'+
                    '<div class="form-group">'+
                    '<label for="productColor" class="col-sm-4 control-label">Product Color :</label>'+
                '<div class="col-sm-8">'+
                    '<input type="text" name="product_color" class="form-control" value="'+order.product_color+'" placeholder="Enter Product Color">'+
                    '</div>'+
                    '</div>'+
                    '<div class="form-group">'+
                    '<label for="quantity" class="col-sm-4 control-label">Quantity :</label>'+
                '<div class="col-sm-8">'+
                    '<input type="text" name="quantity" class="form-control" value="'+order.quantity+'" placeholder="Enter Quantity">'+
                    '</div>'+
                    '</div>'+
                    '<div class="form-group">'+
                    '<label for="deliveryCharge" class="col-sm-4 control-label">Delivery Charge :</label>'+
                '<div class="col-sm-8">'+
                    '<input type="text" name="delivery_charge" class="form-control" value="'+order.delivery_charge+'" placeholder="Enter Amount">'+
                    '</div>'+
                    '</div>'+
                    '<div class="form-group">'+
                    '<label for="advancePayment" class="col-sm-4 control-label">Advance Payment :</label>'+
                '<div class="col-sm-8">'+
                    '<input type="text" name="paid" class="form-control" value="'+order.paid+'" placeholder="Enter Amount">'+
                    '</div>'+
                    '</div>'+
                    '<div class="form-group">'+
                    '<label for="status" class="col-sm-4 control-label">Delivery Status :</label>'+
                '<div class="col-sm-8">'+
                    '<textarea class="form-control" name="status" rows="3" placeholder="Enter Delivery Status"> '+order.status+' </textarea>'+
                    '</div>'+
                '</div>';

                $("#orderUpdateForm").html( updateForm );
            },
            error : function( error ){
                console.debug( error );
            }
        });
    })

    /*
    Update order
     */
    $(".update_order").on('click',function(e){
        e.preventDefault();
        var model = $(this).parents('#orderModal');
        var serializeData = model.find('form').serialize();
        var product_id = model.find('input[name="id"]').val();
        console.debug( product_id);
        $.ajax({
            url: "/update_order",
            type: 'POST',
            dataType: "JSON",
            data: {
                "order_id": product_id,
                "data": serializeData,
                "_token": token
            },
            beforeSend: function () {

                //  $(".ajax_loader").show();
            },
            complete: function () {
                // $(".ajax_loader").hide();
            },
            success: function ( response ) {
                console.debug( response);
            }
        })
    })

});