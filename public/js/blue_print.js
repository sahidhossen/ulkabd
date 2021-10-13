/**
 * Created by sahidhossen on 3/24/17.
 */

$(function(){
    //Model
    var model = {
        errors : [],

        orderLists : function( arguments ){
            var lists = ['pizza','chocolate'];
        }


    };


    //Controller
    var controller = {

        emptyTemplate : false,
        messages : [],

        addOrder : function(arguments){
            model.orderLists(arguments);
            view.render();
        },

        //Get empty template
        getEmptyTemplate : function(){
            this.emptyTemplate = true;
            view.render();
        },

        init:function(){
            view.init();
        }
    };

    //View
    var view = {

        init : function(){
            var addOrderBtn    = $(".add_orderlist");
            var sechuleAdd = $(".seheduleadd");

            addOrderBtn.click(function(e){
                if(controller.emptyTemplate==false){
                    controller.getEmptyTemplate();
                }
                return false;
            });


            //Save course to database
            $(document).on('click', saveCourseBtn ,function(e){
                 // do something
                return false;
            });

            sechuleAdd.on('click', function(e){

            })



            this.render();
        },


        //Render the view
        render: function(){

            // Everything will be render

        }
    }
    controller.init();
}());