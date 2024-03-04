//var console = window.console || { log: function () {} };
function CropAvatar($element,params) {
    this.$container = $element;
	this.params=new mw_obj();
	this.params.set_params(params);

    this.$avatarView = this.$container.find('.avatar-view');
    this.$avatar = this.$avatarView.find('img');
    this.$avatarModal = this.$container.find('#avatar-modal');
    this.$loading = this.$container.find('.loading');

    this.$avatarForm = this.$avatarModal.find('.avatar-form');
    this.$avatarUpload = this.$avatarForm.find('.avatar-upload');
    this.$avatarSrc = this.$avatarForm.find('.avatar-src');
    this.$avatarData = this.$avatarForm.find('.avatar-data');
    this.$avatarInput = this.$avatarForm.find('.avatar-input');
    this.$avatarSave = this.$avatarForm.find('.avatar-save');
	mw_hide_obj( this.$avatarSave);
	
    this.$avatarBtns = this.$avatarForm.find('.avatar-btns');
	this.$avatarBtns.hide();

    this.$avatarWrapper = this.$avatarModal.find('.avatar-wrapper');
    this.$avatarPreview = this.$avatarModal.find('.avatar-preview');

    this.init();
    this.consolelog(this);
  }
CropAvatar.prototype = {
    constructor: CropAvatar,
    support: {
      fileList: !!$('<input type="file">').prop('files'),
      blobURLs: !!window.URL && URL.createObjectURL,
      //formData: !!window.FormData
      formData: false
    },
	consolelog: function (data) {
		if(window.console){
			window.console.log(data);	
		}
	},

    init: function () {
		//this.support.datauri =false;
      this.support.datauri = this.support.fileList && this.support.blobURLs;
      /*
	  if (!this.support.formData) {
        //this.initIframe();
      }*/
      this.initTooltip();
      this.initModal();
      this.addListener();
    },

    addListener: function () {
      this.$avatarView.on('click', $.proxy(this.click, this));
      this.$avatarInput.on('change', $.proxy(this.change, this));
      this.$avatarForm.on('submit', $.proxy(this.submit, this));
      this.$avatarBtns.on('click', $.proxy(this.cmdEvt, this));
      this.$avatarSave.on('click', $.proxy(this.submitFrm, this));
    },
    submitFrm: function () {
		//this.$avatarForm.show();
		this.$avatarForm.submit();
		return false;
    },
	

    initTooltip: function () {
      this.$avatarView.tooltip({
        placement: 'bottom'
      });
    },

    initModal: function () {
      this.$avatarModal.modal({
        show: false
      });
    },

    initPreview: function () {
      var url = this.$avatar.attr('src');

      this.$avatarPreview.empty().html('<img src="' + url + '">');
    },

    click: function () {
      this.$avatarModal.modal('show');
      this.initPreview();
    },

    change: function () {
      var files,
          file;
 	  mw_hide_obj( this.$avatarSave);
	  this.$avatarBtns.hide();
      if (this.support.datauri) {
        files = this.$avatarInput.prop('files');

        if (files.length > 0) {
          file = files[0];

          if (this.isImageFile(file)) {
            if (this.url) {
              URL.revokeObjectURL(this.url); // Revoke the old one
            }

            this.url = URL.createObjectURL(file);
            this.startCropper();
			 mw_show_obj( this.$avatarSave);
			 this.$avatarBtns.show();
          }
        }
      } else {
        file = this.$avatarInput.val();
        if (this.isImageFile(file)) {
		   mw_show_obj( this.$avatarSave);
		   this.$avatarBtns.show();
		}
      }
    },

    submit: function () {
      if (!this.$avatarSrc.val() && !this.$avatarInput.val()) {
		  
        return false;
      }
    },
	cmd_fullW:function(data){
		if (!this.active) {
			return;
		}
		var canD= this.$img.cropper('getCanvasData');
		//console.log("canD",canD);
		var ncbD={
			width:canD.width,
			left:canD.left,
		}
		//console.log("ncbD",ncbD);
		this.$img.cropper('setCropBoxData',{width:canD.width});
		this.$img.cropper('setCropBoxData',{left:canD.left});
		
	},
	cmd_fullH:function(data){
		if (!this.active) {
			return;
		}
		var canD= this.$img.cropper('getCanvasData');
		this.$img.cropper('setCropBoxData',{height:canD.height});
		this.$img.cropper('setCropBoxData',{top:canD.top});

		
	},
	
	doCmd:function(cod,data){
		if(!cod){
			return;	
		}
		var c="cmd_"+cod;
		if(!this[c]){
			return;
		}
		this[c](data);
		
	},
    cmdEvt: function (e) {
      var data;

      if (this.active) {
        data = $(e.target).data();
		this.cmd(data);
      }
    },
    cmd: function (data) {
      if (this.active) {
        if (data.method) {
          this.$img.cropper(data.method, data.option);
        }else{
			if (data.cmd) {
				this.doCmd(data.cmd,data);
			}
		}
      }
    },

    rotate: function (e) {
      var data;

      if (this.active) {
        data = $(e.target).data();

        if (data.method) {
          this.$img.cropper(data.method, data.option);
        }
      }
    },
    transform: function (method,option) {
      if (this.active) {
        if (method) {
          this.$img.cropper(method, option);
        }
      }
    },

    isImageFile: function (file) {
      if (file.type) {
        return /^image\/\w+$/.test(file.type);
      } else {
        return /\.(jpg|png|gif)$/.test(file);
      }
    },

    startCropper: function () {
      var _this = this;
	 
	  

      if (this.active) {
        this.$img.cropper('replace', this.url);
      } else {
        this.$img = $('<img src="' + this.url + '">');
        this.$avatarWrapper.empty().html(this.$img);
        this.$img.cropper({
          aspectRatio: this.params.get_param_or_def("aspectRatio",1) ,
		  strict:true,
		  responsive:true,
		  autoCropArea:1,
          //preview: this.$avatarPreview.selector,
          crop: function (data) {
			  console.log(data);
            var json = [
                  '{"x":' + data.detail.x,
                  '"y":' + data.detail.y,
                  '"height":' + data.detail.height,
                  '"width":' + data.detail.width,
                  '"rotate":' + data.detail.rotate + '}'
                ].join();

            _this.$avatarData.val(json);
          }
        });
        this.active = true;
      }
    },
	

    stopCropper: function () {
      if (this.active) {
        this.$img.cropper('destroy');
        this.$img.remove();
        this.active = false;
      }
    },

    syncUpload: function () {
      this.$avatarSave.click();
    },


    cropDone: function () {
      this.$avatarForm.get(0).reset();
      this.$avatar.attr('src', this.url);
      this.stopCropper();
      this.$avatarModal.modal('hide');
    },

    alert: function (msg) {
      var $alert = [
            '<div class="alert alert-danger avater-alert">',
              '<button type="button" class="close" data-dismiss="alert">&times;</button>',
              msg,
            '</div>'
          ].join('');

      this.$avatarUpload.after($alert);
    }
};
