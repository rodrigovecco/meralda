function mw_ui_debug_test(info){
	this.info=new mw_obj();
	this.info.set_params(info);
	this.debug_mode=true;
	
	this.openkcfinder=function(){
		   window.KCFinder = {};
    window.KCFinder.callBackMultiple = function(files) {
        for (var i; i < files.length; i++) {
            // Actions with files[i] here
        }
        window.KCFinder = null;
    };
	var url=this.params.get_param_or_def("kcfinderurl",false);
    window.open(url, 'kcfinder_multiple');	
	}
	this.openkcfinderNo=function(){
		   window.KCFinder = {};
    window.KCFinder.callBackMultiple = function(files) {
        for (var i; i < files.length; i++) {
            // Actions with files[i] here
        }
        window.KCFinder = null;
    };
    window.open('/kcfinder/browse.php', 'kcfinder_multiple');	
	}
	
	this.test_kcfinder=function(){
		var e;
		var _this=this;
		if(e=this.get_ui_elem("container")){
			var textarea = e.appendChild( document.createElement( 'textarea' ) );
			var cfg=this.params.get_param_or_def("ckeditorcfg",{});
			CKEDITOR.replace( textarea ,cfg);
		}
		
		e=this.get_ui_elem("btn")
		if(!e){
			return false;	
		}
		e.onclick=function(){_this.openkcfinder()};
		
		e=this.get_ui_elem("btnno")
		if(!e){
			return false;	
		}
		e.onclick=function(){_this.openkcfinderNo()};
		
	
	}
	
	this.test_sort=function(){
		this.load_sort_test();
		console.log("by index",this.sort_col.get_items_by_index());
		console.log("by index rev",this.sort_col.get_items_by_index_reverse());
		console.log("by name",this.sort_col.get_sorted_items(function(a,b){
			if (a.name < b.name)
				return -1;
			else if (a.name > b.name)
				return 1;
			else 
				return 0;
		}));
		console.log("by time rev",this.sort_col.get_sorted_items(function(a,b){
			if (a.time > b.time)
				return -1;
			else if (a.time < b.time)
				return 1;
			else 
				return 0;
		}));
		this.sort_col.sort_items(function(a,b){
			if (a.name < b.name)
				return -1;
			else if (a.name > b.name)
				return 1;
			else 
				return 0;
		});
		
		console.log("by index",this.sort_col.get_items_by_index());
		
	}
	this.load_sort_test=function(){
		if(this.sort_col){
			return;
		}
		this.sort_col= new mw_objcol();	
		var list=this.params.get_param_as_list("sorttest");
		if(list){
			for(var i=0;i<list.length;i++){
				this.sort_col.add_item(list[i].cod,list[i]);
			}
		}
		
	}
	
	this.onDatePickersChanged=function(){
			
	}

	this.init_date_picker=function(){
		var c=this.get_ui_elem("datepicker");
		var _this=this;
		if(!c){
			return false;	
		}
		var e=document.createElement("div");
		e.style.display="inline-block";
		c.appendChild(e);
		$($(e)).dxDateBox({
			onValueChanged: function(data) {
				_this.onDatePickersChanged();
			}
		});
		this.datePickerFrom=$($(e)).dxDateBox('instance');
		e=document.createElement("div");
		e.style.display="inline-block";
		c.appendChild(e);
		$($(e)).dxDateBox({
			showClearButton:true,
			value:null,
			maxZoomLevel:'year',
			onValueChanged: function(data) {
				_this.onDatePickersChanged();
			}
		});
		this.datePickerTo=$($(e)).dxDateBox('instance');
		
		
	}
	this.test_load_json=function(){
			
	}
	this.after_init=function(){
		
		
		var e;
		var _this=this;
		
		if(e=this.get_ui_elem("container")){
			this.set_container(e);
		}else{
			return false;	
		}
		//this.test_sort();
		this.init_date_picker();

		
		//this.test_kcfinder();

		//this.test_load_json();
		
		//this.testDG();
		
		
		/*
		this.min_helper=new mw_datainput_item_minutes_helper();
		
		this.min_input=document.createElement("input");
		this.min_input.onkeyup=function(){_this.updateMin()};
		e.appendChild(this.min_input);
		this.min_display=document.createElement("div");
		e.appendChild(this.min_display);
		
		*/
			
	}
	
	this.testDG=function(){
		var e;
		var _this=this;
		var c;
		c=this.get_ui_elem("container");
		e=document.createElement("div");
		c.appendChild(e);
		
		
		var employees = [{
			"ID": 1,
			"FirstName": "John",
			"LastName": "Heart",
			"Prefix": "Mr.",
			"Position": "CEO",
			"Picture": "images/employees/01.png",
			"BirthDate": "1964/03/16",
			"HireDate": "1995/01/15",
			"Notes": "John has been in the Audio/Video industry since 1990. He has led DevAv as its CEO since 2003.\r\n\r\nWhen not working hard as the CEO, John loves to golf and bowl. He once bowled a perfect game of 300.",
			"Address": "351 S Hill St."
		}, {
			"ID": 20,
			"FirstName": "Olivia",
			"LastName": "Peyton",
			"Prefix": "Mrs.",
			"Position": "Sales Assistant",
			"Picture": "images/employees/09.png",
			"BirthDate": "1981/06/03",
			"HireDate": "2012/05/14",
			"Notes": "Olivia loves to sell. She has been selling DevAV products since 2012. \r\n\r\nOlivia was homecoming queen in high school. She is expecting her first child in 6 months. Good Luck Olivia.",
			"Address": "807 W Paseo Del Mar"
		}, {
			"ID": 4,
			"FirstName": "Robert",
			"LastName": "Reagan",
			"Prefix": "Mr.",
			"Position": "CMO",
			"Picture": "images/employees/03.png",
			"BirthDate": "1974/09/07",
			"HireDate": "2002/11/08",
			"Notes": "Robert was recently voted the CMO of the year by CMO Magazine. He is a proud member of the DevAV Management Team.\r\n\r\nRobert is a championship BBQ chef, so when you get the chance ask him for his secret recipe.",
			"Address": "4 Westmoreland Pl."
		}, {
			"ID": 5,
			"FirstName": "Greta",
			"LastName": "Sims",
			"Prefix": "Ms.",
			"Position": "HR Manager",
			"Picture": "images/employees/04.png",
			"BirthDate": "1977/11/22",
			"HireDate": "1998/04/23",
			"Notes": "Greta has been DevAV's HR Manager since 2003. She joined DevAV from Sonee Corp.\r\n\r\nGreta is currently training for the NYC marathon. Her best marathon time is 4 hours. Go Greta.",
			"Address": "1700 S Grandview Dr."
		}, {
			"ID": 6,
			"FirstName": "Brett",
			"LastName": "Wade",
			"Prefix": "Mr.",
			"Position": "IT Manager",
			"Picture": "images/employees/05.png",
			"BirthDate": "1968/12/01",
			"HireDate": "2009/03/06",
			"Notes": "Brett came to DevAv from Microsoft and has led our IT department since 2012.\r\n\r\nWhen he is not working hard for DevAV, he coaches Little League (he was a high school pitcher).",
			"Address": "1120 Old Mill Rd."
		}, {
			"ID": 7,
			"FirstName": "Sandra",
			"LastName": "Johnson",
			"Prefix": "Mrs.",
			"Position": "Controller",
			"Picture": "images/employees/06.png",
			"BirthDate": "1974/11/15",
			"HireDate": "2005/05/11",
			"Notes": "Sandra is a CPA and has been our controller since 2008. She loves to interact with staff so if you've not met her, be certain to say hi.\r\n\r\nSandra has 2 daughters both of whom are accomplished gymnasts.",
			"Address": "4600 N Virginia Rd."
		}, {
			"ID": 10,
			"FirstName": "Kevin",
			"LastName": "Carter",
			"Prefix": "Mr.",
			"Position": "Shipping Manager",
			"Picture": "images/employees/07.png",
			"BirthDate": "1978/01/09",
			"HireDate": "2009/08/11",
			"Notes": "Kevin is our hard-working shipping manager and has been helping that department work like clockwork for 18 months.\r\n\r\nWhen not in the office, he is usually on the basketball court playing pick-up games.",
			"Address": "424 N Main St."
		}, {
			"ID": 11,
			"FirstName": "Cynthia",
			"LastName": "Stanwick",
			"Prefix": "Ms.",
			"Position": "HR Assistant",
			"Picture": "images/employees/08.png",
			"BirthDate": "1985/06/05",
			"HireDate": "2008/03/24",
			"Notes": "Cindy joined us in 2008 and has been in the HR department for 2 years. \r\n\r\nShe was recently awarded employee of the month. Way to go Cindy!",
			"Address": "2211 Bonita Dr."
		}, {
			"ID": 30,
			"FirstName": "Kent",
			"LastName": "Samuelson",
			"Prefix": "Dr.",
			"Position": "Ombudsman",
			"Picture": "images/employees/02.png",
			"BirthDate": "1972/09/11",
			"HireDate": "2009/04/22",
			"Notes": "As our ombudsman, Kent is on the front-lines solving customer problems and helping our partners address issues out in the field.    He is a classically trained musician and is a member of the Chamber Orchestra.",
			"Address": "12100 Mora Dr"
		}];		
		
		$($(e)).dxDataGrid({
			dataSource: employees,
			columns: [{
					dataField: 'Picture',
					width: 100,
					allowFiltering: false,
					allowSorting: false,
					cellTemplate: function (container, options) {
						
						$(container).on("contextmenu", function(evt) {evt.preventDefault();});
						var menuItems = [
							{ text: "Hide" },
							{ text: "Delete" },
							{
								text: "Clipboard",
								items: [
									{ text: "Copy text" },
									{ text: "Clear text" },
									{ text: "Paste text" }
								]
							}
						];
						var c=document.createElement("div");
						c.style.border="#f00 solid 1px";
						c.style.height="300px";
						c.style.margin="0 auto";
						c.style.position="relative";
						
						var t=document.createElement("div");
						t.innerHTML="Make a right click";
						var m=document.createElement("div");
						t.style.border="#ff0 solid 1px";
						t.style.backgroundColor="#00c";
						t.style.height="50px";
						t.style.width="50px";
						t.style.position="absolute";
						t.style.top="0px";
						t.style.zIndex="1000";
						
						c.appendChild(t);
						c.appendChild(m);
						$(c).appendTo(container);
						
						$($(m)).dxContextMenu({
							items: menuItems,
							target: $(t)
						});
						
						
						/*
						<div id="targetElement">Make a right click</div>
    <div id="myContextMenu"></div>
						container.height(100);
						$('<img />')
							.height(100)
							.attr('src', options.value)
							.appendTo(container);
							
							*/
					}
				}, {
					dataField: 'Prefix',
					caption: 'Title',
					width: 70
				}, 'FirstName',
				'LastName', {
					dataField: 'Position',
					caption: 'Position'
				}, {
					dataField: 'BirthDate',
					caption: 'BirthDate',
					dataType: 'date'
				}, {
					dataField: 'HireDate',
					dataType: 'date'
				}
			]
		});
		
	}

	this.updateMin=function(){
		var r=this.min_helper.get_result(this.min_input.value);
		var str=this.min_helper.format_selUnit(r)+"<br>";	
		str=str+this.min_helper.format_txt(r)+"<br>";
		str=str+this.min_helper.format_txt(r,", ")+"<br>";
		str=str+this.min_helper.format_DHM(r)+"<br>";
		for(var cod in r){
			str=str+cod+": "+r[cod]+"<br>";	
		}
		this.min_display.innerHTML=str;
	}

}

mw_ui_debug_test.prototype=new mw_ui();

