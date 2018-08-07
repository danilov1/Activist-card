/* -------------------------------------------------------- */
/* --- © АНО "Центр молодежных и студенческих программ" --- */
/* -------------------------------------------------------- */

/* JS Interface */

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
  
  if(IsIE8()) {
      $('[placeholder]').each(function () {
          objie = $(this);
          if (objie.attr('placeholder') != '') {
              objie.addClass('IePlaceHolder');
              if ($.trim(objie.val()) == '' && objie.attr('type') !== 'password') {
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
});

function IsIE8() {
    var rv = -1;
    var ua = navigator.userAgent;
    var re = new RegExp("Trident\/([0-9]{1,}[\.0-9]{0,})");
    if (re.exec(ua) != null) {
        rv = parseFloat(RegExp.$1);
    }
    return (rv == 4);
}

function login(token) {
  loginInput = $("input[name=loginauth_login]");
  pwInput = $("input[name=loginauth_pw]");
  loginTransform = loginInput.val();
  if((loginInput.val() !== '') && (pwInput.val() !== '')) {
	if((loginInput.val().length == 11) && (!isNaN(loginInput.val())) && ((loginInput.val().substring(0,2) == "79") || (loginInput.val().substring(0,2) == "89"))) {
		loginTransform = loginInput.val().substring(1,loginInput.val().length);
	}
    $.ajax({
      type: "POST",
      url: "login",
      data: {
        act: "login",
        l: loginTransform,
        p: pwInput.val(),
        token: token
      },
	  beforeSend: function() { $(".loadover").fadeIn(); },
    })
    .done(function(answer) {
	  
      var preanswer = JSON.parse(answer);
      var logindata = preanswer.error;
      if(logindata == 'access_wrong') {
		grecaptcha.reset();
		$(".loginauth_submit").prepend("Вход");
		$(".loadover").fadeOut();
        pwInput.val('');
        $(".loginauth_info span").html("<strong>Неверный</strong> логин или пароль.");
        $(".loginauth_info").slideDown();
        pwInput.focus();
      }
      else if(logindata == 'ok') {
          window.location.reload();
      }
      else {
		grecaptcha.reset();
		$(".loginauth_submit").prepend("Вход");
		$(".loadover").fadeOut();
        $(".loginauth_info span").html(logindata);
        $(".loginauth_info").slideDown();
      }
    });
  }
  return false;
}

function authError(errtext) {
	return '<h1 class="render_massage_title">Ошибка авторизации</h1><p class="render_massage" style="color:#666;">'+errtext+'</p>';
}

function authSocial(authResult) {
	if(authResult == "vktoken_error") { $.fancybox({ 'content' : authError('Сервер ВКонтакте не авторизовал пользователя.') }); }
	else if(authResult == "vktoken_noid") { $.fancybox({ 'content' : authError('Ваш аккаунт ВКонтакте еще не привязан к аккаунту Карты активиста. Для привязки войдите в личный кабинет используя логин и пароль, после чего нажмите ПРИВЯЗАТЬ АККАУНТ.') }); }
	else if(authResult == "ok") { window.location.reload(); }
	else { $.fancybox({ 'content' : authError(authResult) }); }
}