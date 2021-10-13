<div id="sidebar-wrapper">
    <span class="sidebar-times menu-toggle"> <i class="fa fa-times"></i></span>
    <div class="sidebar-nav">
        <div class="bot-avatar">
            <a title="All Bots" href="{{ route('bots') }}"><img src="{{ asset('images/usha_logo.png') }}" alt="Ulka Logo">
            </a>
            @if(isset($name))
            {{ $name }}
            @endif
        </div>
        <div class="nav-list container line-height">
            <ul>
                <li class="has-child {{ (Route::currentRouteName() =='agent' || Route::currentRouteName() === 'orders' || Route::currentRouteName() === 'feedback' ) ? 'active' : null }}">
                    <div class="row-100">
                        <div class="row-80">
                            <a href="{{ url('/dashboard/'.$active_agent->agent_code) }}"><i class="fa fa-tachometer right-padding-15px" aria-expanded="false"></i> <b>{{__('common.sidebar.dashboard.navTitle')}} </b></a>
                        </div>
                        <div class="row-10">
                            <a href="#dashboardSubmenu" data-toggle="collapse" aria-expanded="false"><i id="iconDashboardSubmenu" class="fa {{ (Route::currentRouteName() =='agent' || Route::currentRouteName() === 'orders' || Route::currentRouteName() === 'feedback' ) ? 'fa-angle-down' : 'fa-angle-right' }}" aria-hidden="true"></i></a>
                        </div>
                    </div>
                    <ul class="collapse {{ (Route::currentRouteName() =='agent' || Route::currentRouteName() === 'orders' || Route::currentRouteName() === 'endusers' || Route::currentRouteName() === 'feedback' ) ? 'in' : null }}" id="dashboardSubmenu" style="list-style-type:none">
                        <li class="{{ Route::currentRouteName() === 'orders' ? 'active' : null }}"><a href="{{ url('/'.$active_agent->agent_code.'/orders/') }}"><i class="fa fa-shopping-basket right-padding-15px" aria-hidden="true"></i> {{__('common.sidebar.dashboard.subMenu.orders')}} <span class="label label-primary pull-right">{{ $total_order }}</span> </a></li>
                    </ul>
                </li>
                <li class="has-child {{
                            (   Route::currentRouteName() == 'AI' ||
                                Route::currentRouteName() =='configuration' ||
                                Route::currentRouteName() =='categories' ||
                                Route::currentRouteName() =='products' ||
                                Route::currentRouteName() =='product.update' ||
                                Route::currentRouteName() =='product.create' ||
                                Route::currentRouteName() =='image_upload' ) ? 'active' : null }}">
                    <div class="row-100">
                        <div class="row-80">
                            <a href="{{ url('/artificial_intelligent/'.$active_agent->agent_code) }}"><i class="fa fa-tachometer right-padding-15px" aria-hidden="true"></i><b> {{__('common.sidebar.ai.navTitle')}} </b></a>
                        </div>
                        <div class="row-10">
                            <a href="#aiSubmenu" data-toggle="collapse" aria-expanded="false"><i id="iconAiSubmenu" class="fa {{
                                    (   Route::currentRouteName() == 'AI' ||
                                        Route::currentRouteName() =='configuration' ||
                                        Route::currentRouteName() =='categories' ||
                                        Route::currentRouteName() =='products' ||
                                        Route::currentRouteName() =='product.update' ||
                                        Route::currentRouteName() =='product.create' ||
                                        Route::currentRouteName() =='image_upload' ) ? 'fa-angle-down' : 'fa-angle-right' }}" aria-hidden="true"></i></a>
                        </div>
                    </div>
                    <ul class="collapse {{
                            (   Route::currentRouteName() == 'AI' ||
                                Route::currentRouteName() =='configuration' ||
                                Route::currentRouteName() =='categories' ||
                                Route::currentRouteName() =='products' ||
                                Route::currentRouteName() =='product.update' ||
                                Route::currentRouteName() =='product.create' ||
                                Route::currentRouteName() =='image_upload' ) ? 'in' : null }}" id="aiSubmenu" style="list-style-type:none">
                        <li class="{{ Route::currentRouteName() === 'categories' ? 'active' : null }}"><a href="{{ url('/'.$active_agent->agent_code.'/categories') }}"><i class="fa fa-puzzle-piece right-padding-15px" aria-hidden="true"></i> {{__('common.sidebar.ai.subMenus.intent')}} </a></li>
                        <li class="{{ Route::currentRouteName() === 'products' ? 'active' : null }}"><a href="{{ url('/'.$active_agent->agent_code.'/products') }}"><i class="fa fa-shopping-bag right-padding-15px" aria-hidden="true"></i> {{__('common.sidebar.ai.subMenus.entities')}} <span class="label label-primary pull-right">{{ $total_product }}</span></a></li>
                        <li class="{{ Route::currentRouteName() === 'image_upload' ? 'active' : null }}"><a href="{{ url('/'.$active_agent->agent_code.'/upload/image') }}"><i class="fa fa-picture-o right-padding-15px" aria-hidden="true"></i>{{__('common.sidebar.ai.subMenus.image')}}</a></li>
                    </ul>
                </li>
                <li class="has-child {{ ( Route::currentRouteName() === 'schedule' || Route::currentRouteName() === 'configure' || Route::currentRouteName() =='settings' || Route::currentRouteName() === 'connect_fb') ? 'active' : null }}">
                    <div class="row-100">
                        <div class="row-80">
                            <a href="{{ url('/'.$active_agent->agent_code.'/configure') }}"><i class="fa fa-tachometer right-padding-15px" aria-hidden="true"></i> <b>{{__('common.sidebar.manage.navTitle')}}</b> </a>
                        </div>
                        <div class="row-10">
                            <a href="#manageSubmenu" data-toggle="collapse" aria-expanded="false"><i id="iconManageSubmenu" class="fa {{ ( Route::currentRouteName() === 'schedule' || Route::currentRouteName() === 'faq' || Route::currentRouteName() =='settings' || Route::currentRouteName() === 'connect_fb') ? 'fa-angle-down' : 'fa-angle-right' }}" aria-hidden="true"></i></a>
                        </div>
                    </div>
                    <ul class="collapse {{ ( Route::currentRouteName() === 'schedule' || Route::currentRouteName() === 'configure' || Route::currentRouteName() =='settings'  || Route::currentRouteName() === 'connect_fb') ? 'in' : null }}" id="manageSubmenu" style="list-style-type:none">
                        <li class="{{ Route::currentRouteName() === 'settings' ? 'active' : null }}"><a href="{{ url('/'.$active_agent->agent_code.'/configure') }}"><i class="fa fa-gear right-padding-15px" aria-hidden="true"></i> {{__('common.sidebar.manage.subMenus.configure')}} </a></li>
                        <li class="{{ Route::currentRouteName() === 'schedule' ? 'active' : null }}"><a href="{{ url('/'.$active_agent->agent_code.'/schedules') }}"><i class="fa fa-podcast right-padding-15px" aria-hidden="true"></i> {{__('common.sidebar.manage.subMenus.broadcast')}} </a></li>
                        <li class="{{ Route::currentRouteName() === 'connect_fb' ? 'active' : null }}"><a href="{{ url('/'.$active_agent->agent_code.'/connect_fb_page') }}"><i class="fa fa-plug right-padding-15px" aria-hidden="true"></i> {{__('common.sidebar.manage.subMenus.fb')}} </a></li>
                        <li class="{{ Route::currentRouteName() === 'connect_web' ? 'active' : null }}"><a href="{{ url('/'.$active_agent->agent_code.'/connect_web_page') }}"><i class="fa fa-plug right-padding-15px" aria-hidden="true"></i> {{__('common.sidebar.manage.subMenus.web_connect')}} </a></li>
                        <!-- <li class="{{ Route::currentRouteName() === 'chat_inbox' ? 'active' : null }}"><a href="{{ url('/'.$active_agent->agent_code.'/chat_inbox') }}"><i class="fa fa-plug right-padding-15px" aria-hidden="true"></i> Inbox </a></li> -->
                    </ul>
                </li>
                <!-- new submenu -->
                <li class="has-child {{ ( Route::currentRouteName() === 'change_plan' ) ? 'active' : null }}">
                    <div class="row-100">
                        <div class="row-80">
                            <a href="{{ url('/'.$active_agent->agent_code.'/change_plan') }}"><i class="fa fa-tachometer right-padding-15px" aria-hidden="true"></i> <b>Billing</b> </a>
                        </div>
                        <div class="row-10">
                            <a href="#billingSubmenu" data-toggle="collapse" aria-expanded="false"><i id="iconBillingSubmenu" class="fa {{ ( Route::currentRouteName() === 'change_plan' ) ? 'fa-angle-down' : 'fa-angle-right' }}" aria-hidden="true"></i></a>
                        </div>
                    </div>
                    <ul class="collapse {{ ( Route::currentRouteName() === 'change_plan' ) ? 'in' : null }}" id="billingSubmenu" style="list-style-type:none">
                        <li class="{{ Route::currentRouteName() === 'payment' ? 'active' : null }}"><a href="{{ url('/'.$active_agent->agent_code.'/change_plan') }}"><i class="fa fa-credit-card-alt right-padding-15px" aria-hidden="true"></i> Payment Method </a></li>
                        <li class="{{ Route::currentRouteName() === 'change_plan' ? 'active' : null }}"><a href="{{ url('/'.$active_agent->agent_code.'/change_plan') }}"><i class="fa fa-cogs right-padding-15px" aria-hidden="true"></i> Change Plan </a></li>
                        <li class="{{ Route::currentRouteName() === 'billing' ? 'active' : null }}"><a href="{{ url('/'.$active_agent->agent_code.'/change_plan') }}"><i class="fa fa-money right-padding-15px" aria-hidden="true"></i> Billings </a></li>
                    </ul>
                </li>
                <!-- new submenu ends -->
            </ul>

        </div>

    </div>

</div>


<div id="page-content-wrapper" data-active_agent="{{ $active_agent->agent_code }}"></div>
<script>
    var dashboardSubmenu = localStorage.dashboardSubmenu;
    if (document.getElementById("dashboardSubmenu").className == "collapse in") {
        localStorage.dashboardSubmenu = "open";
    } else if (dashboardSubmenu) {
        if (dashboardSubmenu == "open") {
            document.getElementById("dashboardSubmenu").classList.add("in");
            document.getElementById("iconDashboardSubmenu").classList.remove("fa-angle-right");
            document.getElementById("iconDashboardSubmenu").classList.add("fa-angle-down");
        }
    } else {
        localStorage.dashboardSubmenu = "close";
    }

    var aiSubmenu = localStorage.aiSubmenu;
    if (document.getElementById("aiSubmenu").className == "collapse in") {
        localStorage.aiSubmenu = "open";
    } else if (aiSubmenu) {
        if (aiSubmenu == "open") {
            document.getElementById("aiSubmenu").classList.add("in");
            document.getElementById("iconAiSubmenu").classList.remove("fa-angle-right");
            document.getElementById("iconAiSubmenu").classList.add("fa-angle-down");
        }
    } else {
        localStorage.aiSubmenu = "close";
    }

    var manageSubmenu = localStorage.manageSubmenu;
    if (document.getElementById("manageSubmenu").className == "collapse in") {
        localStorage.manageSubmenu = "open";
    } else if (manageSubmenu) {
        if (manageSubmenu == "open") {
            document.getElementById("manageSubmenu").classList.add("in");
            document.getElementById("iconManageSubmenu").classList.remove("fa-angle-right");
            document.getElementById("iconManageSubmenu").classList.add("fa-angle-down");
        }
    } else {
        localStorage.manageSubmenu = "close";
    }

    var billingSubmenu = localStorage.billingSubmenu;
    if (document.getElementById("billingSubmenu").className == "collapse in") {
        localStorage.billingSubmenu = "open";
    } else if (billingSubmenu) {
        if (billingSubmenu == "open") {
            document.getElementById("billingSubmenu").classList.add("in");
            document.getElementById("iconBillingSubmenu").classList.remove("fa-angle-right");
            document.getElementById("iconBillingSubmenu").classList.add("fa-angle-down");
        }
    } else {
        localStorage.billingSubmenu = "close";
    }


    var myTimer = setInterval(checkJquery, 10);

    function checkJquery() {
        if (jQuery) {
            $('.collapse').on('show.bs.collapse', function() {
                var id = $(this)[0].id;
                if (id == "dashboardSubmenu") {
                    localStorage.dashboardSubmenu = "open";
                } else if (id == "aiSubmenu") {
                    localStorage.aiSubmenu = "open";
                } else if (id == "manageSubmenu") {
                    localStorage.manageSubmenu = "open";
                } else if (id == "billingSubmenu") {
                    localStorage.billingSubmenu = "open";
                }
                $(this).parent().find(".fa-angle-right").removeClass("fa fa-angle-right").addClass("fa fa-angle-down");
            });
            $('.collapse').on('hide.bs.collapse', function() {
                var id = $(this)[0].id;
                if (id == "dashboardSubmenu") {
                    localStorage.dashboardSubmenu = "close";
                } else if (id == "aiSubmenu") {
                    localStorage.aiSubmenu = "close";
                } else if (id == "manageSubmenu") {
                    localStorage.manageSubmenu = "close";
                } else if (id == "billingSubmenu") {
                    localStorage.billingSubmenu = "close";
                }
                $(this).parent().find(".fa-angle-down").removeClass("fa fa-angle-down").addClass("fa fa-angle-right");
            });
            clearInterval(myTimer);
        }
    }
</script> 