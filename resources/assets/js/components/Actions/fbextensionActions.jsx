export function LoadFbMessengerExtensionThread( type = "open" ){
    return function (dispatch) {
        // let thread_context = {
        //     metadata: null,
        //     psid: "1200611636723498",
        //     signed_request: "52VqdT3D0X1GE7RERgNYykSBjRWsfZDrP4SZAQktts8.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTUxMTU0OTI1NCwibWV0YWRhdGEiOm51bGwsInBhZ2VfaWQiOjcyODYzNTc4NzMxODIwNCwicHNpZCI6IjExMDQ1Njk0MTYzMTQ0OTgiLCJ0aHJlYWRfdHlwZSI6IlVTRVJfVE9fUEFHRSIsInRpZCI6IjExMDQ1Njk0MTYzMTQ0OTgifQ",
        //     thread_type: "USER_TO_PAGE",
        //     tid: "1104569416314498"
        // }
        //
        // if( type=== 'open' ){
        //     dispatch({
        //         type: 'FETCH_FB_THREAD_FULFILLED',
        //         payload: thread_context,
        //         message: "Facebook Messenger Extension Loaded"
        //     });
        // }
        (function(d, s, id){
            let js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.com/en_US/messenger.Extensions.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'Messenger'));
        if( type === 'open' ) {
            window.extAsyncInit = function() {
                    MessengerExtensions.getContext('120759628496384',
                        function success(thread_context) {
                            dispatch({
                                type: 'FETCH_FB_THREAD_FULFILLED',
                                payload: thread_context,
                                message: "Facebook Messenger Extension Loaded"
                            });
                        },
                        function error(err) {
                            // error
                            dispatch({
                                type: 'FETCH_PROFILE_REJECTED',
                                payload: err,
                                message: "Facebook Messenger Extension Failed!"
                            })
                            console.debug("fb ext error: ", err);
                        }
                    );
                }
            }

        if( type === 'close' ){
            MessengerExtensions.requestCloseBrowser(function success() {
                console.debug("Webview close successfull!");
            }, function error(err) {
                console.debug("Webview close failed!");
            });

        }
    }
}

export function ExitFbMessengerExtensionThread(){



}