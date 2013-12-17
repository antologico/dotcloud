/*
Name: AttentionBox
Author: Denon Studio
URL: http://codecanyon.net/user/denonstudio/
License: http://codecanyon.net/wiki/support/legal-terms/licensing-terms/
Copyright: GNU 2010 Denon Studio


Modificado por anto@usal.es. Incluyendo soporte para select (Marcado como @CAMBIO)
*/

var AttentionBox=new function()
	{
	this.container=null;
	this.modal=true;
	this.timerHandle=null;
	this.callback=null;
	this.isShaking=false;
	this.isIE6=(jQuery.browser.msie&&jQuery.browser.version=="6.0");
	var h=this;
	this.showMessage=function(a,b)
		{
		if(this.container!=null)
			{
			this.shake();
			return
		}
		
		// @CAMBIO this.modal=this.generatModalPanel((b)?b.modalcolor: "#000");
		this.modal=this.generatModalPanel();
		jQuery(document.body).append(this.modal)
		
		this.container=jQuery(document.createElement("div")).attr("class","attention-box");
		var c=this.generateInputsRow((b&&b.inputs)?b.inputs:null);
		var d=jQuery(document.createElement("div")).attr("class","message").append(a);
		var e=this.generateButtonsRow((b&&b.buttons)?b.buttons:[
			{
			caption:"Okay"
		}
		]);
		h.callback=(b)?b.callback:null;
		if(this.isIE6)
			{
			d.addClass("bubbleie6");
			this.container.addClass("attention-box-ie6")
		}
		else
			{
			d.addClass("bubble")
		}
		this.container.append(d).append(c).append(e).hide();
		jQuery(document.body).append(this.container);
		if(this.modal)
			{
			// @CAMBIO this.modal.fadeTo(400,0.5);	
			this.modal.fadeTo(400,1);
		}
		this.center(this);
		this.container.fadeIn(200,this.bindListeners)
	};
	this.bindListeners=function()
		{
		jQuery(window).bind("scroll",h.onResize);
		jQuery(window).bind("resize",h.onResize);
		jQuery(document).bind("keyup",h.onKeyup)
	};
	this.unbindListeners=function()
		{
		jQuery(window).unbind("scroll",h.onResize);
		jQuery(window).unbind("resize",h.onResize);
		jQuery(document).unbind("keyup",h.onKeyup)
	};
	this.onResize=function()
		{
		if(h.container)h.center(h,true);
		if(h.modal)h.modal.css("height",jQuery("body").outerHeight())
	};
	this.onKeyup=function(e)
		{
		if(h.container&&h.container!=null&&e.keyCode==27)
			{
			h.closeWindow(e,true)
		}
		e.result=true;
		return true
	};
	this.shake=function()
		{
		if(this.container!=null&&!this.isShaking)
			{
			h.isShaking=true;
			this.container.effect("shake",
				{
				times:2,direction:"up"
			}
			,100,function()
				{
				h.isShaking=false
			}
			);
			return
		}
	};
	this.center=function(a,b)
		{
		var c=(jQuery(window).height()-a.container.height())/2+jQuery(window).scrollTop()+"px";
		var d=(jQuery(window).width()-a.container.width())/2+jQuery(window).scrollLeft()+"px";
		if(b)
			{
			clearTimeout(this.timerHandle);
			this.timerHandle=setTimeout(function()
				{
				a.container.animate(
					{
					"top":c,"left":d
				}
				,600,"easeInOutExpo")
			}
			,300)
		}
		else
			{
			a.container.css("top",c);
			a.container.css("left",d)
		}
	};
	this.generateButtonsRow=function(a)
		{
		var b=jQuery(document.createElement("div")).attr("class","buttons");
		for(var i=a.length-1;
		i>=0;
		i--)
			{
			var c=a[i];
			var d=jQuery(document.createElement("button")).text(c.caption);
			if(c.important&&c.important==true)d.attr("class","important");
			if(this.isIE6)d.addClass("ie6IsCrap");
			if(c.cancel&&c.cancel==true)d.attr("cancel","yes");
			d.bind("click",h.closeWindow);
			b.append(d)
		}
		return b
	};

	this.closeWindow=function(e,a,action, value)
		{
		a=(a||jQuery(e.target).attr("cancel")=="yes");
		if(!a)
			{
			var b=h.container.find("input[value=][req=yes]");
			if(b.length!=0)
				{
				jQuery(b).effect("highlight",
					{
					color:"#FFA3A3"
				}
				,2000);
				return
			}
			// @CAMBIO var d=h.container.find("input");
			// Así pilla todos los ".input" definidos más abajo
			var d=h.container.find(".inputs");
			var f=[];
			if(d.length>0)
				{
				for(var i=0;
				i<d.length;
				i++)
					{
					var g=jQuery(d[i]);
					f[i]=
						{
						caption:g.attr("rel"),value:g.val()
					}
				}
			}
		}
		if(h.modal)h.modal.fadeOut(200);
		h.container.fadeOut(200,function()
			{
			h.container.detach();
			h.container=null;
			h.unbindListeners();
			if(h.modal)
				{
				h.modal.detach();
				h.modal=null
			}
			if(h.callback)
				{
				var c=h.callback;
				h.callback=null;
				window.setTimeout(function()
					{
						if(a)
						{
							if (action) c(action, value);
							else c("CANCELLED")
						}
						else
						{
							c(jQuery(e.target).text(),f)
						}
					}
				,0)
			}
		}
		)
	};
	this.generateInputsRow=function(a)
		{
		if(!a||a.length==0)return;
		var b=jQuery(document.createElement("div")).attr("class","input-container");
		
		for(var i=0;
			i<a.length;
			i++)
			{
			var c=a[i];
			var d=jQuery(document.createElement("label")).attr("for",i).append(c.caption);
			
			
			
			// @CAMBIO MODIFICADO POR anto@usal.es
			// ----------------------------------------------
			var tipo 		= "input";
			var subtipo 	= "text";
			if(c.type) tipo =c.type;

			if(c.type&&c.type=="password") 
				{
					tipo = "input";
					subtipo = "password";
				}
				// Si es input ..
			var e=jQuery(document.createElement(tipo)).attr(
				{
					"type":subtipo,"name":i,"rel":c.caption
				}
				);

			if(c.type&&c.type=="select"&&c.values)
			{
				// cargamos los valores en el select
				var opciones = e.prop('options');
				$.each(c.values, function(text, val) 
				{
				    opciones[opciones.length] = new Option(text, val);
				});
				e.val (opciones);
			}  

			if(c.id)e.attr("id",c.id);
			if(c.name)e.attr("id",c.name);
			if(c.disabled)e.attr("disabled",c.disabled);

			// Acción definia en cambio
			if(c.change)e.change(c.change);

			if(c.selectvalue&&c.id) 
				{
					//var opciones = e.options;
					//for(i=0; i< opciones.length;i++)
                    //if (opciones[i].value == c.selectvalue) opciones[i].selected=true
					e.val(c.selectvalue);
					//$("#"+c.id+" option[value='"+c.selectvalue+"']").attr('selected', true);
				}

			e.addClass("inputs");
			// Fin de @CAMBIO
			// ----------------------------------------------
			if(c.required&&c.required==true)e.attr("req","yes");
			if(c.value)e.attr("value",c.value);
			b.append(d);
			if(c.error)d.append("<span class=\"error\">"+c.error+"</span>");
			b.append(e.wrap("<div></div>").parent())
		}
		return b
	};
	this.generatModalPanel=function(a)
		{
		var b=jQuery(document.createElement("div")).attr("class","attention-box-modal").fadeTo(0,0).css("height",jQuery(window).height());
		if(a)b.css(
			{
			"background-color":a
		}
		);
		if(this.isIE6)
			{
			b.css("position","absolute");
			b.css("height",jQuery("body").outerHeight())
		}
		return b
	}
};
