/* -------------------------------------------------------- */
/* --- © АНО "Центр молодежных и студенческих программ" --- */
/* -------------------------------------------------------- */

/* JS Interface */

/* Мнемоника HTML */
var HTML=function(){
   var x,mnem=
   {34:"quot",38:"amp",39:"apos",60:"lt",62:"gt",402:"fnof",
   338:"OElig",339:"oelig",352:"Scaron",353:"scaron",
   376:"Yuml",710:"circ",732:"tilde",8226:"bull",8230:"hellip",
   8242:"prime",8243:"Prime",8254:"oline",8260:"frasl",8472:"weierp",
   8465:"image",8476:"real",8482:"trade",8501:"alefsym",8592:"larr",
   8593:"uarr",8594:"rarr",8595:"darr",8596:"harr",8629:"crarr",
   8656:"lArr",8657:"uArr",8658:"rArr",8659:"dArr",8660:"hArr",
   8704:"forall",8706:"part",8707:"exist",8709:"empty",8711:"nabla",
   8712:"isin",8713:"notin",8715:"ni",8719:"prod",8721:"sum",
   8722:"minus",8727:"lowast",8730:"radic",8733:"prop",8734:"infin",
   8736:"ang",8743:"and",8744:"or",8745:"cap",8746:"cup",8747:"int",
   8756:"there4",8764:"sim",8773:"cong",8776:"asymp",8800:"ne",
   8801:"equiv",8804:"le",8805:"ge",8834:"sub",8835:"sup",8836:"nsub",
   8838:"sube",8839:"supe",8853:"oplus",8855:"otimes",8869:"perp",
   8901:"sdot",8968:"lceil",8969:"rceil",8970:"lfloor",8971:"rfloor",
   9001:"lang",9002:"rang",9674:"loz",9824:"spades",9827:"clubs",
   9829:"hearts",9830:"diams",8194:"ensp",8195:"emsp",8201:"thinsp",
   8204:"zwnj",8205:"zwj",8206:"lrm",8207:"rlm",8211:"ndash",
   8212:"mdash",8216:"lsquo",8217:"rsquo",8218:"sbquo",8220:"ldquo",
   8221:"rdquo",8222:"bdquo",8224:"dagger",8225:"Dagger",8240:"permil",
   8249:"lsaquo",8250:"rsaquo",8364:"euro",977:"thetasym",978:"upsih",982:"piv"},
   tab=("nbsp|iexcl|cent|pound|curren|yen|brvbar|sect|uml|"+
   "copy|ordf|laquo|not|shy|reg|macr|deg|plusmn|sup2|sup3|"+
   "acute|micro|para|middot|cedil|sup1|ordm|raquo|frac14|"+
   "frac12|frac34|iquest|Agrave|Aacute|Acirc|Atilde|Auml|"+
   "Aring|AElig|Ccedil|Egrave|Eacute|Ecirc|Euml|Igrave|"+
   "Iacute|Icirc|Iuml|ETH|Ntilde|Ograve|Oacute|Ocirc|Otilde|"+
   "Ouml|times|Oslash|Ugrave|Uacute|Ucirc|Uuml|Yacute|THORN|"+
   "szlig|agrave|aacute|acirc|atilde|auml|aring|aelig|ccedil|"+
   "egrave|eacute|ecirc|euml|igrave|iacute|icirc|iuml|eth|ntilde|"+
   "ograve|oacute|ocirc|otilde|ouml|divide|oslash|ugrave|uacute|"+
   "ucirc|uuml|yacute|thorn|yuml").split("|");
   for(x=0;x<96;x++)mnem[160+x]=tab[x];
   tab=("Alpha|Beta|Gamma|Delta|Epsilon|Zeta|Eta|Theta|Iota|Kappa|"+
   "Lambda|Mu|Nu|Xi|Omicron|Pi|Rho").split("|");
   for(x=0;x<17;x++)mnem[913+x]=tab[x];
   tab=("Sigma|Tau|Upsilon|Phi|Chi|Psi|Omega").split("|");
   for(x=0;x<7;x++)mnem[931+x]=tab[x];
   tab=("alpha|beta|gamma|delta|epsilon|zeta|eta|theta|iota|kappa|"+
   "lambda|mu|nu|xi|omicron|pi|rho|sigmaf|sigma|tau|upsilon|phi|chi|"+
   "psi|omega").split("|");
   for(x=0;x<25;x++)mnem[945+x]=tab[x];
   return {
     encode:function(text){
       return text.replace(/[\u00A0-\u2666<>\&]/g,function(a){
         return "&"+(mnem[a=a.charCodeAt(0)]||"#"+a)+";"
       })
     },
     decode:function(text){
       return text.replace(/\&#?(\w+);/g,function(a,b){
         if(Number(b))return String.fromCharCode(Number(b));
         for(x in mnem){
           if(mnem[x]===b)return String.fromCharCode(x);
         }
       })
     }
   }
}()

var loading;
function startLoading() {
	loading = setTimeout("$('.fillpage').fadeIn(300)", 1000);
}
function clearLoading() {
	clearTimeout(loading);
	$('.fillpage').fadeOut();
}


$(function() {
  $('.alert .close').click(function() { $(this).parent().slideUp(); });
  $('.logout').click(function() { logout(); return false; });
  $.ajaxSetup({
    type: "GET",
    url: "operator",
    cache: false,
	timeout:15000,
    beforeSend: function() { startLoading(); },
    error: function(jqXHR, exception) {
		clearLoading();
		error = '';
        if (jqXHR.status === 0) {
          error = 'Нет соединения с сервером. Проверьте подключение к Интернет.';
        } else if (jqXHR.status == 404) {
          error = 'Запрашиваемые данные не найдены. [404]';
        } else if (jqXHR.status == 500) {
          error = 'Ошибка сервера [500].';
        } else if (exception === 'parsererror') {
          error = 'Получен нестандартный ответ от сервера.';
        } else if (exception === 'timeout') {
          error = 'Время ожидания ответа от сервера истекло.';
        } else if (exception === 'abort') {
          error = 'Запрос остановлен.';
        } else {
          error = 'Произошла неизвестная ошибка.\n' + jqXHR.responseText;
        }
		$.fancybox({'content' : m_error(error) });
		return false;
     }
  });

	$.ajaxPrefilter(function(options,originalOptions,jqXHR) {
		var originalSuccess = options.success;
		options.success = function (data) {
			clearLoading();
			try {
				ifErrorInJSON = JSON.parse(data);
				if(ifErrorInJSON.error == "authError") { window.location.href = "/"; }
				else { if (originalSuccess != null) { originalSuccess(data); } }
			} catch (e) {
				if (originalSuccess != null) { originalSuccess(data); }
			}
		};
	});

  $("a.filter").click(function() {
	  if($("div.filter").is(":visible")) { $("div.filter").slideUp(); }
	  else { $("div.filter").slideDown(); }
	  return false;
  });

  if(IsIE8Browser()) {
      $('[placeholder]').each(function () {
          var objie = $(this);
          if (objie.attr('placeholder') != '') {
              objie.addClass('IePlaceHolder');
              if ($.trim(objie.val()) == '' && objie.attr('type') != 'password') {
                  objie.val(objie.attr('placeholder'));
              }
          }
      });
      $('.IePlaceHolder').focus(function () {
          var objie = $(this);
          if (objie.val() == objie.attr('placeholder')) {
              objie.val('');
          }
      });
      $('.IePlaceHolder').blur(function () {
          var objie = $(this);
          if ($.trim(objie.val()) == '') {
              objie.val(objie.attr('placeholder'));
          }
      });
  }

  $("body").one('load', function() { $("body").show(); }).each(function() { if(this.complete) $(this).load(); });
});

function IsIE8Browser() {
    var rv = -1;
    var ua = navigator.userAgent;
    var re = new RegExp("Trident\/([0-9]{1,}[\.0-9]{0,})");
    if (re.exec(ua) != null) {
        rv = parseFloat(RegExp.$1);
    }
    return (rv == 4);
}

function m_ok(m_ok_text) {
  return '<h1 class="render_massage_title"><i class="icon-ok"></i> Успешно!</h1><p class="render_massage">'+m_ok_text+'</p><script>removeRenderMassage();</script>';
}

function m_error(m_error_text) {
  return '<h1 class="render_massage_title">Ошибка!</h1><p class="render_massage" style="color:#666;">'+m_error_text+'</p>';
}

function removeRenderMassage() {
	var rrm = setTimeout(function() { $.fancybox.close(); clearTimeout(rrm); }, 3500);
}

function logout() {
  $.ajax({
    type: "POST",
    url: "login",
    data: { act: "logout" }
  })
  .done(function(answer) {
    var preanswer = JSON.parse(answer);
    var logindata = preanswer.error;
    if(logindata == 'ok') {
      $('.page').fadeOut(500, function() { window.location.reload(); });
    }
    else {
      alert(logindata);
    }
  });
}

function setpw() {
  if(($("input[name=setpw_pw1]").val() !== '') && ($("input[name=setpw_pw2]").val() !== '')) {
    var re = /^(?=.*\d)(?=.*[a-z])[0-9a-zA-Z]{6,30}$/;
    if(!re.test($("input[name=setpw_pw1]").val())) {
      $(".setpw_info span").html("Неверный формат пароля");
      $(".setpw_info").slideDown();
    } else {
      if($("input[name=setpw_pw1]").val() !== $("input[name=setpw_pw2]").val()) {
        clearLoading();
        $(".setpw_info span").html("Повтор пароля <strong>неверный</strong>");
        $(".setpw_info").slideDown();
      }
      else {
        $.ajax({
          data: {
            act: "cpw",
            "new": MD5($("input[name=setpw_pw1]").val())
          }
        })
        .done(function(answer) {
          var preanswer = JSON.parse(answer);
          var spwres = preanswer.error;
          if(spwres == 'ok') {
            $('header').fadeOut(480);
            $('.page').fadeOut(500, function() {
              window.location.reload();
            });
          }
          else {
            clearLoading();
            $(".setpw_info span").html(spwres);
            $(".setpw_info").slideDown();
          }
        });
      }
    }
  }
}

jQuery.fn.center = function () {
    this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) +
                                                $(window).scrollLeft()) + "px");
    return this;
}


function byresize() {
  $(".mw").each(function(index, element) {
	if($(window).width()<600) {
	  $(this).width($(window).width()-20);
	} else { $(this).width(600); }
	/*if(($(window).height())<($(this).height()-60)) {
		$(this).height($(window).height()-60);
	} else {*/
	  //$(this).height("auto");
	/*}*/
	$(this).center();
  });
}

$(window).resize(function() { byresize(); });
$(function(){ byresize(); });

function ismobile() {
 if( navigator.userAgent.match(/Android/i)
 || navigator.userAgent.match(/webOS/i)
 || navigator.userAgent.match(/iPhone/i)
 || navigator.userAgent.match(/iPad/i)
 || navigator.userAgent.match(/iPod/i)
 || navigator.userAgent.match(/BlackBerry/i)
 || navigator.userAgent.match(/Windows Phone/i)
 ){
    return true;
  }
 else {
    return false;
  }
}

function is(vars) {
	checked = "y";
	$(vars).each(function(){
		thistype = $(this).get(0).tagName.toLowerCase();
		if(thistype == "input" || thistype == "textarea") {
			if($.trim($(this).val()) == '') {
				checked = "n";
				return true;
			}
		}
		else if(thistype == "select") {
			if($(this).find("option:selected").val() == "") {
				checked = "n";
				return true;
			}
		}
		else { alert("Внутренняя ошибка клиента: выбран неизвестный формат элемента."); }
	});
	if(checked == "n") { return false; }
	else { return true; }
}

function closemw(elemclass) {
	$(".fillblack, ."+elemclass).fadeOut(400);
}

function pager(curfunc,curpage,onpage,maxrows,allrows) {
	$('.pager').html('');
	numpages = Math.ceil(allrows/maxrows); prev = "n"; next = "y";
	if(numpages <= onpage) { startnum = 1; endnum = numpages; next = "n";
	} else {
		if((curpage+1)>onpage) {
			pagecount = Math.ceil((curpage+1)/onpage);
			startnum = ((pagecount-1)*onpage+1);
			endnum = (startnum+onpage-1);
			prev = "y";
			if((numpages-endnum) <= 0) { endnum = numpages; next = "n"; }
		} else { startnum = 1; endnum = onpage; }
	}

	$(".pager").html("");
	if(prev == "y") {
		apager = $('<a/>');
		apager.attr("href","javascript:page_"+curfunc+"("+(startnum-1)+")");
		apager.html("..."); $(".pager").append(apager);
	}
	for(var ai=startnum; ai <= endnum; ai++) {
		apager = $('<a/>');
		if(ai == (curpage+1)) { apager.addClass("active"); }
		apager.attr("href","javascript:page_"+curfunc+"("+ai+")"); apager.html(""+ai+""); $(".pager").append(apager);
	}
	if(next == "y") {
		apager = $('<a/>');
		apager.attr("href","javascript:page_"+curfunc+"("+(endnum+1)+")");
		apager.html("..."); $(".pager").append(apager);
	}
}

function sortUsingNestedText(parent, childSelector, keySelector) {
    var items = parent.children(childSelector).sort(function(a, b) {
        var vA = $(keySelector, a).text();
        var vB = $(keySelector, b).text();
        return (vA < vB) ? -1 : (vA > vB) ? 1 : 0;
    });
    parent.append(items);
}

function closemw(elemclass) {
  $(".fillblack, ."+elemclass).fadeOut(400);
}

function openNewTab(link) {
     var frm = $('<form   method="get" action="' + link + '" target="_blank"></form>')
     $("body").append(frm);
     frm.submit().remove();
}

function authError(errtext) {
	return '<h1 class="render_massage_title">Ошибка авторизации</h1><p class="render_massage" style="color:#666;">'+errtext+'</p>';
}

function authSocial(authResult) {
	if($(".socialinfo").is(":visible")) {
		if(authResult == "vktoken_error") {
			$.fancybox({ 'content' : authError('Сервер ВКонтакте ответил отказом. Данные введены неверно или допущена ошибка в настройках приложения.') }); }
		else {
			eval("config_socialinfo_activate();");
			//closemw("socialinfo"); $.fancybox({ 'content' : m_ok("Авторизация через соцсети успешно активирована!") });
		}
	} else {
		if(authResult == "vktoken_error") { $.fancybox({ 'content' : authError('Сервер ВКонтакте не авторизовал пользователя.') }); }
		else if(authResult == "vktoken_already") { $.fancybox({ 'content' : authError('Ваш профиль в системе Карты активиста уже привязан к одному из аккаунтов ВКонтакте.') }); }
		else if(authResult == "vktoken_anotheruser") { $.fancybox({ 'content' : authError('Данный аккаунт ВКонтакте уже используется одним из пользователей системы Карта активиста.') }); }
		else if(authResult == "ok") {
			$.fancybox({ 'content' : m_ok("Аккаунт ВКонтакте успешно привязан!") });
		} else { $.fancybox({ 'content' : authError(authResult) }); }
	}
}

function render_massage(massagetitle, massagetext, massageact) {
	$.fancybox({
		tpl: { closeBtn: '' },
		'content' : '<h1 class="render_massage_title">'+massagetitle+'</h1><p class="render_massage">'+massagetext+'</p>',
		closeEffect : 'none',
		'afterClose': massageact
	});
}
