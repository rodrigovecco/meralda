/*!
    * Start Bootstrap - SB Admin v7.0.7 (https://startbootstrap.com/template/sb-admin)
    * Copyright 2013-2023 Start Bootstrap
    * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-sb-admin/blob/master/LICENSE)
    */
    // 
// Scripts
// 

$(function(){
    var keepState=false;//true to kepp lat mnu state
    if(keepState){
        if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            $("body").toggleClass("sb-sidenav-toggled");
        }
    }
    $('#sidebarToggle').click(function(){
        $("body").toggleClass("sb-sidenav-toggled");
        if(keepState){
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        }
    });
});



