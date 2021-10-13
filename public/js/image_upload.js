/**
 * Created by sahidhossen on 3/12/17.
 */
$(function(){
  'use strict'
    var config = {
      _token:null
    }

    config._token = $('meta[name="csrf-token"]').attr('content');

    var rejected_image_container = $("#rejected_image_list");
    var rejected_image_list = rejected_image_container.find('ul');
    var photo_counter = 0;
    var previewNode = document.getElementById("preview-template").innerHTML;
    // var maxImageWidth = 600,
    //     maxImageHeight = 314;

    var maxImageWidth = 2048,
        maxImageHeight = 2048,
        errorFileList = false;
    Dropzone.autoDiscover = false;

    var myDropzone = new Dropzone("#my-awesome-dropzone", {
        uploadMultiple: false,
        parallelUploads: 100,
        dictDefaultMessage:'',
        maxFilesize: 100,
        maxThumbnailFilesize:100,
        autoDiscover:false,
        url: "/upload/image_upload_process",
        previewsContainer: '#dropzonePreview',
        previewTemplate: previewNode,
        // addRemoveLinks: true,
        acceptedFiles: "image/*",
        dictRemoveFile: 'Remove',
        // dictFileTooBig: 'Image is bigger than 1MB',
        headers:{
             'X-CSRF-TOKEN': config._token,
             'X-Requested-With': 'XMLHttpRequest'
        },
        init: function(){
            this.on("thumbnail", function(file) {
                // Do the dimension checks you want to do
                if (file.width > maxImageWidth || file.height > maxImageHeight) {
                    file.rejectDimensions();
                }else if(file.size > (2048 * 2048 * 1)){
                    file.rejectedSize();
                }
                else {
                    file.acceptDimensions();
                }
            });

        },
        accept: function(file, done) {
            file.acceptDimensions = done;
            file.rejectDimensions = function() {
                 done("Maximum possible size 2048x2048 | (size: "+this.width+" x "+this.height+" )");
            };
            file.rejectedSize = function() {
                done("Image is bigger than 2MB");
            }
        },
        uploadprogress: function(file, progress, bytesSent) {
            // Display the progress
        },
        error: function(file, response){
             console.debug("error: ",response);
             var src = file.previewElement.querySelector("img").src;
             if(errorFileList == false)
                 errorFileList = true;

            rejected_image_container.show();
             var listHtml = '<li style="display: none;"><img src="' + src + '" alt="Drop Image"><div class="error-message"> <p> ' + file.name + ' </p><p> ' + response + ' </p> </div></li>';
             rejected_image_list.append(listHtml);
             rejected_image_list.find('li:last-child').fadeIn();
             this.removeFile(file);

        },
        success: function(file, response) {
            photo_counter++;
            if(response.code==200){

                console.debug('success file: ', file);
            }else {
                var drop = this;
                if(errorFileList == false)
                    errorFileList = true;
                rejected_image_container.fadeIn();
                drop.removeFile(file);
                var src = file.previewElement.querySelector("img").src;
                var listHtml = '<li style="display: none;"><img src="'+src+'" alt="Drop Image"><div class="error-message"> <p> '+file.name+' </p><p> '+response.message+' </p> </div></li>';
                rejected_image_list.append( listHtml );
                rejected_image_list.find('li:last-child').fadeIn();

            }
        }

    })

    //Clear the rejected files
    rejected_image_container.find(".removeAll").on('click', function(e){
        e.preventDefault();
        errorFileList = false;
        var listLength = rejected_image_container.find('li').length;
        rejected_image_container.find('li').each(function(index){
           $(this).delay(400*index).fadeOut(300, function(){
               $(this).remove();
               if(listLength == index+1 ){
                   rejected_image_container.fadeOut();
               }
           })

        });
    })


}())
