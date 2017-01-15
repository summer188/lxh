/*
 * artDialog v2.1.2
 * Date: 2010-09-08
 * http://code.google.com/p/artdialog/
 * (c) 2009-2010 tangbin, http://www.planeArt.cn
 *
 * This is licensed under the GNU LGPL, version 2.1 or later.
 * For details, see: http://creativecommons.org/licenses/LGPL/2.1/
 */
(function(){
	this.art={};
	var X=function($,C,_){
		if(typeof $==="string"){
			var B=$;
			$={};
			$.content=B;
			$.fixed=true
		}
		if($.id&&K($.id)){
			return I($.id)
		}
		if(typeof $.lock==='undefined'){
			$.lock=true
		}
		if($.lock){
			$.fixed=true
		}
		if($.menuBtn){
			$.fixed=false
		}
		if(typeof C==="function"){
			$.yesFn=C
		}
		if(typeof _==="function"){
			$.noFn=_
		}
		if($.url){
			$.iframe=$.url
		}
		if ($.iframe && typeof(pc_hash) != 'undefined'){
			if ($.iframe.indexOf('?') > -1) {
				$.iframe = $.iframe+'&pc_hash='+pc_hash;
			} else {
				$.iframe = $.iframe+'?pc_hash='+pc_hash;
			}
		}
		var A={
			id:$.id,
			title:$.title||"\u63d0\u793a",
			content:$.content,
			iframe:$.iframe,
			yesText:$.yesText||"\u786e\u5b9a",
			noText:$.noText||"\u53d6\u6d88",
			yesFn:$.yesFn,
			noFn:$.noFn,
			closeFn:$.noFn,
			width:$.width,
			height:$.height,
			menuBtn:$.menuBtn,
			left:$.left,
			top:$.top,
			fixed:$.fixed,
			style:$.style,
			lock:$.lock,
			time:$.time
		};
		return I($.id).int(A)
	},
	d=4,
	_=999999,
	O="temp_artDialog",
	S=100000,
	D="\xd7",
	W="Loading..",
	L=0,
	C={},
	R,
	e=[],
	P,N,U,
	M=!-[1,],
	J=M&&([/MSIE (\d)\.0/i.exec(navigator.userAgent)][0][1]==6),
	B=function(_,$){
		for(var B=0,A=_.length;B<A;B++){
			$(_[B],B)
		}
	},
	Z=function(B){
		var _=B?B.document.documentElement:document.documentElement,
		$=B?B.document.body:document.body,
		A=_||$;
		return{
			width:Math.max(A.clientWidth,A.scrollWidth),
			height:Math.max(A.clientHeight,A.scrollHeight),
			left:Math.max(_.scrollLeft,$.scrollLeft),
			top:Math.max(_.scrollTop,$.scrollTop),
			winWidth:A.clientWidth,
			winHeight:A.clientHeight
		}
	},
	f=function($,_,A){
		if($.attachEvent){
			$["e"+_+A]=A;
			$[_+A]=function(){
				$["e"+_+A](window.event)
			};
			$.attachEvent("on"+_,$[_+A])
		}else{
			$.addEventListener(_,A,false)
		}
	},
	T=function(_,A,B){
		if(_.detachEvent){
			try{
				_.detachEvent("on"+A,_[A+B]);
				_[A+B]=null
			}catch($){}
		}else{
			_.removeEventListener(A,B,false)
		}
	},
	c=function($){
		$.stopPropagation?$.stopPropagation():$.cancelBubble=true
	},
	F=function($){
		$.preventDefault?$.preventDefault():$.returnValue=false},
		V=function(){
			try{
				window.getSelection?window.getSelection().removeAllRanges():document.selection.empty()
			}catch($){}
		},
		G=function($){
			return document.createElement($)
		},
		g=function($){
			return document.createTextNode($)
		},
		K=function($){
			return document.getElementById($)
		},
		A=function($){
			return document.getElementsByTagName($)
		},
		Y=function(A,$){
			var _=new RegExp("(\\s|^)"+$+"(\\s|$)");
			return A.className.match(_)
		},
		$=function(_,$){
			if(!Y(_,$)){
				_.className+=" "+$}},
				b=function(A,$){
					if(Y(A,$)){
						var _=new RegExp("(\\s|^)"+$+"(\\s|$)");
						A.className=A.className.replace(_," ")
					}
				},
				H=function(_){
					var $=this.style;
					if(!$){
						$=this.style=G("style");
						$.setAttribute("type","text/css");
						A("head")[0].appendChild($)
					}
					$.styleSheet&&($.styleSheet.cssText+=_)||$.appendChild(g(_))
				},
				a=function(G,J){
					var _=G||window.event,
					B=this.data.box,
					H=this.data.moveTemp,
					F=Z(),
					I=this.security(B);
					C.box=this;
					this.zIndex();
					P=true;
					C.x=_.clientX;
					C.y=_.clientY;
					C.top=parseInt(B.style.top);
					C.left=parseInt(B.style.left);
					C.width=B.offsetWidth;
					C.height=B.offsetHeight;
					C.winWidth=F.winWidth;
					C.winHeight=F.winHeight;
					C.pageWidth=F.width;
					C.pageHeight=F.height;
					C.pageLeft=F.left;
					C.pageTop=F.top;
					C.maxX=I.maxX;
					C.maxY=I.maxY;
					var D=setInterval(function(){V()},40);
					V();
					if(C.width*C.height>=S&&!J){
						R=true;
						H.style.width=C.width+"px";
						H.style.height=C.height+"px";
						H.style.left=C.left+"px";
						H.style.top=C.top+"px";
						H.style.visibility="visible"
					}
					$(C.box.data.wrap,"ui_move");
					$(A("html")[0],"ui_page_move");
					document.onmousemove=function($){
						J?E.call(B,$,J):Q.call(B,$)};
						document.onmouseup=function(){
							P=false;
							document.onmouseup=null;
							if(document.body.releaseCapture){
								B.releaseCapture()
							}
							b(C.box.data.wrap,"ui_move");
							b(A("html")[0],"ui_page_move");
							clearInterval(D);
							if(R){
								H.style.visibility="hidden";
								B.style.left=H.style.left;
								B.style.top=H.style.top;
								R=false
							}
						};
						if(document.body.setCapture){
							B.setCapture()
						}
					},
					Q=function(B){
						if(P===false){
							return false
						}
						var _=B||window.event,
						E=R?C.box.data.moveTemp:C.box.data.box,
						G=_.clientX,
						F=_.clientY,
						A=Z(),
						D=parseInt(C.left-C.x+G-C.pageLeft+A.left),
						$=parseInt(C.top-C.y+F-C.pageTop+A.top);
						if(D>C.maxX){
							D=C.maxX
						}
						if($>C.maxY){
							$=C.maxY
						}
						if(D<0){
							D=0
						}
						if($<0){
							$=0
						}
						E.style.left=D+"px";
						E.style.top=$+"px";
						return false
					},
					E=function(A,B){
						if(P===false){
							return false
						}
						var _=A||window.event,
						F=_.clientX,E=_.clientY,
						$=C.width+F-C.x+B.w,
						D=C.height+E-C.y+B.h;
						if($>0){
							B.obj.style.width=$+"px"
						}
						if(D>0){
							B.obj.style.height=D+"px"
						}
						if(J){
							C.box.data.selectMask.style.width=C.box.data.box.offsetWidth+"px";
							C.box.data.selectMask.style.height=C.box.data.box.offsetHeight+"px"
						}
						return false
					},
					I=function(Q){
						var v=-1;
						B(e,function($,_){
							if(Q&&$.data.wrap.id===Q){
								v=_
							}else{
								if($.free===true){
									v=_
								}
							}
						}
						);
						if(v>=0){
							return e[v]
						}
						var M=G("td"),
						n=G("div"),X=G("div"),
						t=G("a");
						M.className="ui_title_wrap";
						n.className="ui_title";
						X.className="ui_title_text";
						t.className="ui_close";
						t.href="#";
						t.setAttribute("accesskey","c");
						t.appendChild(g(D));
						n.appendChild(X);
						n.appendChild(t);
						M.appendChild(n);
						var H=G("td"),
						x=G("div"),
						z=G("div"),
						q=G("div");
						q.className="ui_loading_tip";
						q.appendChild(g(W));
						H.className="ui_content_wrap";
						x.className="ui_content";
						z.className="ui_content_mask";
						H.appendChild(x);
						H.appendChild(q);
						var S=G("button"),
						r=G("span"),
						_0=G("button"),
						k=G("span");
						S.setAttribute("accesskey","y");
						r.className="ui_yes";
						_0.setAttribute("accesskey","n");
						k.className="ui_no";
						var o=G("td"),
						I=G("div"),
						i=G("div"),
						R=G("div");
						o.className="ui_bottom_wrap";
						I.className="ui_bottom";
						i.className="ui_btns";
						R.className="ui_resize";
						I.appendChild(i);
						I.appendChild(R);
						o.appendChild(I);
						var u=G("table"),
						$0=G("tbody");
						u.className="ui_dialog_main";
						for(var y=0;y<3;y++){
							var j=G("tr");
							if(y==0){
								j.appendChild(M)
							}
							if(y==1){
								j.appendChild(H)
							}
							if(y==2){
								j.appendChild(o)
							}
							$0.appendChild(j)
						}
						u.appendChild($0);
						var l=G("table"),
						V=G("tbody");
						for(y=0;y<3;y++){
							j=G("tr");
							for(var p=0;p<3;p++){
								var K=G("td");
								if(y==1&&p==1){
									K.className="ui_td_"+y+p;
									K.appendChild(u)
								}else{
									K.className="ui_border "+"ui_td_"+y+p
								}
								j.appendChild(K)
							}
							V.appendChild(j)
						}
						l.appendChild(V);
						var h=G("div");
						h.className="ui_dialog";
						if(J){
							var Y=G("iframe");
							Y.className="ui_ie6_select_mask";
							h.appendChild(Y)
						}
						h.appendChild(l);
						var E=G("div");
						E.className="ui_overlay";
						E.appendChild(G("div"));
						var w=G("div");
						w.className="ui_move_temp";
						w.appendChild(G("div"));
						document.body.appendChild(w);
						var m=G("div");
						m.className="ui_dialog_wrap";
						m.appendChild(E);
						m.appendChild(h);
						m.appendChild(w);
						X.onmousedown=function($){
							a.call(s,$,false);
							return false
						};
						R.onmousedown=function(A){
							var $=h,_=H;
							a.call(s,A,{
								obj:_,
								w:_.offsetWidth-$.offsetWidth,
								h:_.offsetHeight-$.offsetHeight});
							return false
						};
						S.onfocus=S.onblur=function(){
							s.data.btnTab=_0
						};
						_0.onfocus=_0.onblur=function(){
							s.data.btnTab=S
						};
						document.body.appendChild(m);
						var s={
							data:{box:h,moveTemp:w,selectMask:Y,wrap:m},
							int:function($){
								s.data.config=$;
								if(typeof $.id==="string"){
									m.id=$.id
								}
								if(typeof $.style==="string"){
									l.className=$.style
								}
								s.content($.title,$.content,$.iframe).yesBtn($.yesFn,$.yesText).noBtn($.noFn,$.noText).closeBtn($.closeFn).size($.width,$.height).align($.menuBtn,$.left,$.top,$.fixed);
								if($.lock){
									s.lock.show()
								}
								if($.time){
									s.time($.time)
								}
								return s
							},
							content:function(A,_,B){
								s.free=false;
								s.data.content=x;
								if(_){
									x.innerHTML='<span class="ui_dialog_icon"></span>'+_;
									s.btnFocus()
								}else{
									if(B){
										s.loading.show();
										s._iframe=G("iframe");
										s._iframe.setAttribute("frameborder",0,0);
										s._iframe.src=B;
										$(m,"ui_iframe");
										x.appendChild(s._iframe);
										x.appendChild(z);
										s.data.iframeLoad=function(){
											var $=s.data.config;
											s.loading.hide();
											if(!$.width&&!$.height){
												try{
													var A=Z(s._iframe.contentWindow);
													s.size(A.width,A.height)
												}catch(_){}
											}
											s._iframe.style.cssText="width:100%;height:100%";
											if(!$.left&&!$.top){
												s.center()
											}
											s.btnFocus()
										};
										f(s._iframe,"load",s.data.iframeLoad);
										s.data.iframe=s._iframe.contentWindow||s._iframe
									}else{
										return s
									}
								}
								X.innerHTML='<span class="ui_title_icon"></span>'+A;
								m.style.visibility="visible";
								return s
							},
							size:function($,_){
								if(parseInt($)==$){
									$=$+"px"
								}
								if(parseInt(_)==_){
									_=_+"px"
								}
								H.style.width=$||"";
								H.style.height=_||"";
								if(J){
									Y.style.width=h.offsetWidth;
									Y.style.height=h.offsetHeight
								}
								return s
							},
							security:function(D){
								var G,H,E,B,_,$;
								s.data.boxWidth=D.offsetWidth;
								s.data.boxHeight=D.offsetHeight;
								var A=Z();
								C.winWidth=A.winWidth;
								C.winHeight=A.winHeight;
								C.pageWidth=A.width;
								C.pageHeight=A.height;
								C.pageLeft=A.left;
								C.pageTop=A.top;
								if(s.data.config.fixed){
									G=0;E=C.winWidth-s.data.boxWidth;
									_=E/2;H=0;B=C.winHeight-s.data.boxHeight;
									var F=C.winHeight*0.382-s.data.boxHeight/2;
									$=(s.data.boxHeight<C.winHeight/2)?F:B/2
								}else{
									G=C.pageLeft;
									E=C.winWidth+G-s.data.boxWidth;
									_=E/2;
									H=C.pageTop;
									B=C.winHeight+H-s.data.boxHeight;
									F=C.winHeight*0.382-s.data.boxHeight/2+H;
									$=(s.data.boxHeight<C.winHeight/2)?F:(B+H)/2
								}
								if(_<0){
									_=0
								}
								if($<0){
									$=0
								}
								return{minX:G,minY:H,maxX:E,maxY:B,centerX:_,centerY:$}
							},
							center:function(){
								var $=s.security(h);
								h.style.left=$.centerX+"px";
								h.style.top=$.centerY+"px";
								return s
							},
							align:function(I,E,K,G){
								var B=s.security(h);
								if(I&&I.getBoundingClientRect){
									var _=s.data.boxWidth/2-I.offsetWidth/2,
									H=I.offsetHeight,
									F=I.getBoundingClientRect().left,
									D=I.getBoundingClientRect().top;
									if(_>F){_=0}
									if(D+H>C.winHeight-s.data.boxHeight){
										H=-s.data.boxHeight
									}
									E=F+C.pageLeft-_;
									K=D+C.pageTop+H
								}
								if(G){
									if(J){
										$(A("html")[0],"ui_ie6_fixed")
									}
									$(m,"ui_fixed")
								}
								if(!E){
									s.data.boxLeft=B.centerX
								}else{
									if(E=="left"){
										s.data.boxLeft=B.minX
									}else{
										if(E=="right"){
											s.data.boxLeft=B.maxX
										}else{
											E=G?E-C.pageLeft:E;
											E=E<B.minX?B.minX:E;
											E=E>B.maxX?B.maxX:E;
											s.data.boxLeft=E
										}
									}
								}
								if(!K){
									s.data.boxTop=B.centerY
								}else{
									if(K=="top"){
										s.data.boxTop=B.minY
									}else{
										if(K=="bottom"){
											s.data.boxTop=B.maxY
										}else{
											K=G?K-C.pageTop:K;
											K=K<B.minY?B.minY:K;
											K=K>B.maxY?B.maxY:K;
											s.data.boxTop=K
										}
									}
								}
								if(m.id==O){
									s.data.boxLeft="-99999"
								}
								h.style.left=s.data.boxLeft+"px";
								h.style.top=s.data.boxTop+"px";
								s.zIndex(h);
								return s
							},
							yesBtn:function($,_){
								if(typeof $==="function"){
									S.innerHTML=_;
									r.appendChild(S);
									i.appendChild(r);
									S.onclick=function(){
										var _=$();
										if(_!=false){
											s.close()
										}
									};
									h.onkeyup=function(_){
										var $=_||window.event;
										if($.ctrlKey&&$.keyCode==13){
											S.click()
										}
									}
								}
								return s
							},
							noBtn:function($,_){
								if(typeof $==="function"){
									_0.innerHTML=_;
									k.appendChild(_0);
									i.appendChild(k);
									_0.onclick=function(){
										var _=$();
										if(_!=false){
											s.close()
										}
									}
								}
								return s
							},
							closeBtn:function($){
								t.onclick=function(){
									if(typeof $==="function"){
										var _=$();
										if(_!=false){
											s.close()
										}
									}else{
										s.close()
									}
									return false
								};
								return s
							},
							btnFocus:function(){
								setTimeout(function(){
									try{
										if(s.data.config.noFn){
											_0.focus()
										}else{
											if(s.data.config.yesFn){
												S.focus()
											}else{
												t.focus()
											}
										}
									}catch($){}
								},40);
								return s
							},
							close:function($){
								if($){
									if(typeof $==="function"){
										s.data.closeFn=$
									}
									return s
								}
								if(s.data.closeFn){
									var _=s.data.closeFn();
									if(_!=false){
										s.data.closeFn=null
									}else{
										return s
									}
								}
								if(s._iframe){
									s._iframe.src="javascript:false";
									s._iframe=null
								}
								l.className=h.style.cssText=X.innerHTML=x.innerHTML=i.innerHTML=m.id="";
								B(["ui_fixed","ui_loading","ui_focus","ui_iframe"],
									function($,_){
										b(m,$)});
								m.style.visibility="hidden";
								s.lock.hide();
								P=false;
								s.free=true
							},
							time:function($){
								if(typeof $==="number"){
									setTimeout(
										function(){
											s.close()
										},1000*$
										)
								}
								return s
							},
							zIndex:function(){
								_++;h.style.zIndex=E.style.zIndex=m.style.zIndex=_;
								w.style.zIndex=_+1;
								if(N){
									b(N,"ui_focus")
								}
								$(m,"ui_focus");
								N=m;
								if(U){
									T(document,"keyup",U)
								}
								U=function(_){
									var $=_||window.event;
									if($.keyCode==27){
										t.onclick()
									}
								};
								f(document,"keyup",U);
								return s
							},
							loading:{
								show:function(){
									$(m,"ui_loading");
									return s
								},
								hide:function(){
									b(m,"ui_loading");
									return s
								}
							},
							lock:{
								show:function(){
									if(L>=1){
										return s
									}
									var D=A("html")[0];
									$(m,"ui_lock");
									$(D,"ui_page_lock");
									s.zIndex(E);
									h.onkeydown=function(_){
										var $=_||window.event,A=$.keyCode;
										if(A==9||A==38||A==40){c($)
										}
									};
									h.oncontextmenu=function(_){
										var $=_||window.event;
										c($)
									};
									var _=Z();
									C.pageLeft=_.left,C.pageTop=_.top;
									s.data.lockMouse=function(_){
										var $=_||window.event;c($);
										F($);scroll(C.pageLeft,C.pageTop)
									};
									B(["DOMMouseScroll","mousewheel","scroll"],function($,_){
										f(document,$,s.data.lockMouse)});
									s.data.lockKey=function(_){
										var $=_||window.event,B=$.keyCode;if(B==37||B==39||B==9){
											try{
												s.data.btnTab.focus()
											}catch(A){}
										}if((B==116)||($.ctrlKey&&B==82)||($.ctrlKey&&B==65)||(B==9)||(B==38)||(B==40)){
											try{
												$.keyCode=0
											}catch(A){}
											F($)}};
											f(document,"keydown",s.data.lockKey);
											E.onclick=E.oncontextmenu=function(){
												s.btnFocus();
												return false
											};
											s.alpha(E,0,function(){L++});
											return s
										},
										hide:function(){
											if(m.className.indexOf("ui_lock")>-1){
												s.alpha(E,1,function(){
													b(m,"ui_lock");
													if(L==1){
														b(A("html")[0],"ui_page_lock")
													}
													B(["DOMMouseScroll","mousewheel","scroll","contextmenu"],function($,_){
														T(document,$,s.data.lockMouse)
													});
													T(document,"keydown",s.data.lockKey);L--})
											}
											return s
										}
									},
									alpha:function(B,A,D){
										var C=B.filters?100:1,$=C/d;
										$=A==0?$:-$;A=(B.filters&&A==1)?100:A;
										var _=function(){A=A+$;
											B.filters?B.filters.alpha.opacity=A:B.style.opacity=A;
											if(0>=A||A>=C){if(D){
												D()
											}
											clearInterval(s.data.startFx)
										}
									};
									_();
									clearInterval(s.data.startFx);
									s.data.startFx=setInterval(_,40);
									return s}
								};
								return e[e.push(s)-1]};
								H(".ui_dialog_wrap{visibility:hidden}.ui_title_icon,.ui_content,.ui_dialog_icon,.ui_btns span{display:inline-block;*zoom:1;*display:inline}.ui_dialog{text-align:left;position:absolute;top:0}.ui_dialog table{border:0;margin:0;border-collapse:collapse}.ui_dialog td{padding:0}.ui_title_icon,.ui_dialog_icon{vertical-align:middle;_font-size:0}.ui_title_text{overflow:hidden;cursor:default}.ui_close{display:block;position:absolute;outline:none}.ui_content_wrap{text-align:center}.ui_content{margin:10px;text-align:left}.ui_iframe .ui_content{margin:0;*padding:0;display:block;height:100%;position:relative}.ui_iframe .ui_content iframe{border:none;overflow:auto}.ui_content_mask {visibility:hidden;width:100%;height:100%;position:absolute;top:0;left:0;background:#FFF;filter:alpha(opacity=0);opacity:0}.ui_bottom{position:relative}.ui_resize{position:absolute;right:0;bottom:0;z-index:1;cursor:nw-resize;_font-size:0}.ui_btns{text-align:right;white-space:nowrap}.ui_btns span{margin:5px 10px}.ui_btns button{cursor:pointer}* .ui_ie6_select_mask{position:absolute;top:0;left:0;z-index:-1;filter:alpha(opacity=0)}.ui_loading .ui_content_wrap{position:relative;min-width:9em;min-height:3.438em}.ui_loading .ui_btns{display:none}.ui_loading_tip{visibility:hidden;width:5em;height:1.2em;text-align:center;line-height:1.2em;position:absolute;top:50%;left:50%;margin:-0.6em 0 0 -2.5em}.ui_loading .ui_loading_tip,.ui_loading .ui_content_mask{visibility:visible}.ui_loading .ui_content_mask{filter:alpha(opacity=100);opacity:1}.ui_move .ui_title_text{cursor:move}.ui_page_move .ui_content_mask{visibility:visible}.ui_move_temp{visibility:hidden;position:absolute;cursor:move}.ui_move_temp div{height:100%}html>body .ui_fixed .ui_move_temp{position:fixed}html>body .ui_fixed .ui_dialog{position:fixed}* .ui_ie6_fixed{background:url(*) fixed}* .ui_ie6_fixed body{height:100%}* html .ui_fixed{width:100%;height:100%;position:absolute;left:expression(documentElement.scrollLeft+documentElement.clientWidth-this.offsetWidth);top:expression(documentElement.scrollTop+documentElement.clientHeight-this.offsetHeight)}* .ui_page_lock select,* .ui_page_lock .ui_ie6_select_mask{visibility:hidden}* .ui_page_lock .ui_content select{visibility:visible;}.ui_overlay{visibility:hidden;_display:none;position:fixed;top:0;left:0;width:100%;height:100%;filter:alpha(opacity=0);opacity:0;_overflow:hidden}.ui_lock .ui_overlay{visibility:visible;_display:block}.ui_overlay div{height:100%}* html body{margin:0}");if(J){document.execCommand("BackgroundImageCache",false,true)}f(window,"load",function(){if(!N){artDialog({id:O,style:"confirm alert error succeed",time:10,lock:false},function(){},function(){})}});art.dialog=X;this.artDialog=X})();