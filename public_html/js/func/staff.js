/* -------------------------------------------------------- */
/* --- © АНО "Центр молодежных и студенческих программ" --- */
/* -------------------------------------------------------- */

$(document).ready(function () {
    $(".searchinput").keyup(function(e) { if(e.keyCode == 13) { finduser(); } });
    $(".closemw").click(function(e) { $('.searchinput').focus(); });
    $("#add_phone").mask("(999)999-99-99");
    var setFocusSearcher = setInterval(function() { var elem = $(".searchinput"); if(elem.is(":focus")) { clearTimeout(setFocusSearcher); } else { elem.focus(); } }, 500);
    page(1);
  });
  
  var globaluid, global_type, global_surname, global_firstname, global_patronumic, global_sex, global_phone, global_dep, global_depid, global_post, global_created, curpage;
  var cursearch = "";
  
  function page(setcurpage, hideload) {
    curpage = setcurpage - 1;
    $.ajax({
      data: {
        act: "getusers",
        query: cursearch,
        page: curpage
      },
      beforeSend: function() { if(hideload !== 1) { startLoading(); } $('.textalert').hide(); }
    })
    .done(function(answer) {
      clearLoading();
      $('.userstable').html('<tr class="table_head center"><td width="10%"><b>Доступ</b></td><td><b>ФИО пользователя</b></td><td width="40%"><b>Подразделение</b></td></tr>');
      var data = (JSON.parse(answer));
      if(data.error == "ok") {
        var getdata = (JSON.parse(answer)).users;
        var tr;
        for (var i = 0; i < getdata.length; i++) {
          tr = $('<tr/>');
          tr.addClass("rowclick");
          tr.attr("id","id" + getdata[i].id + "");
          tr.addClass("center");
          tr.append("<td class=\"blowit\">" + getdata[i].type + "</td>");
          tr.append("<td>" + getdata[i].name + "</td>");
          tr.append("<td class=\"blowit\">" + getdata[i].dep + "</td>");
          $('.userstable').append(tr);
          tr.click(function() { user($(this).attr("id").substr(2)); });
        }
        var countpages;
        $(".pager").html("");
        for(var ai=0; ai < Math.ceil(data.allrows/data.maxrows); ai++) {
          apager = $('<a/>');
          if(ai == curpage) { apager.addClass("active"); }
          apager.attr("href","javascript:page("+(ai+1)+")");
          apager.append(""+(ai+1)+"");
          $(".pager").append(apager);
        }
      } else if(data.error == "notfound") {
        $('.pager').html('');
        $('.textalert').show();
      } else { $.fancybox({ 'content' : data.error }); }
    });
  }
  function user(userid) {
    globaluid = userid;
    $(".box_user_info, .box_user_add, .owntable").html("");
    $.ajax({
      data: {
        act: "byuser",
        uid: userid
      },
    })
    .done(function(answer) {
      var data = JSON.parse(answer);
      if(data.error == "ok") {
        global_type = data.type;
        global_surname = data.surname;
        global_firstname = data.firstname;
        global_patronumic = data.patronymic;
        global_sex = data.sex;
        global_phone = data.phone;
        global_dep = data.department_name;
        global_depid = data.department_id;
        global_post = data.post;
        global_created = data.added;
        
        var accesstype;
        if(data.type == "s") { accesstype = "администратор"; }
        else if(data.type == "k") { accesstype = "специалист"; }
        else if(data.type == "t") { accesstype = "преподаватель"; }
        var infobox = $(".box_user_info");
        infobox.html("<b>ФИО:</b> "+data.surname+" "+data.firstname+" "+data.patronymic+"<br /><b>Тип доступа:</b> "+accesstype+"<br /><b>Номер телефона:</b> +7"+data.phone+"<br /><b>Подразделение:</b> "+data.department_name+"<br /><b>Должность:</b> "+data.post+"");
        
        if(data.events) {
          var tablehead = '<tr class="table_head"><td width="12%"><b>Дата</b></td><td><b>Наименование мероприятия</b></td><td width="17%"><b>Уровень</b></td><td class="center" width="17%"><i class="icon-user icon-white"></i></td></tr>';
          $('.owntable').append(tablehead);
          var getdata = data.events;
          var tr;
          for (var i = 0; i < getdata.length; i++) {
            var dates = "";
            if(getdata[i].e_date_for !== null) { dates = "c &nbsp;" + getdata[i].e_date_since + "<br />по "+getdata[i].e_date_for; }
            else { dates = getdata[i].e_date_since; }
            
            var elevel;         
            if(getdata[i].e_level == "f") { elevel = "факультет"; }
            else if(getdata[i].e_level == "u") { elevel = "университет"; }
            else if(getdata[i].e_level == "c") { elevel = "город"; }
            else if(getdata[i].e_level == "r") { elevel = "регион"; }
            else if(getdata[i].e_level == "v") { elevel = "страна"; }
            else if(getdata[i].e_level == "i") { elevel = "международный"; }
            
            tr = $('<tr/>');
            tr.attr("eid", ""+getdata[i].e_id+"");
            tr.append("<td class=\"blowit center\">" + dates + "</td>");
            tr.append("<td><a class=\"link\" href=\"events-"+getdata[i].e_id+"\">" + getdata[i].e_name + "</a></td>");
            tr.append("<td class=\"blowit\">" + elevel + "</td>");
            tr.append("<td class=\"center\">" + getdata[i].e_involved + "</td>");
            $('.owntable').append(tr);
          }
        } else {
          $(".box_user_add").append("<p class=\"center\"><b>Мероприятий нет</b></p>");
        }
        
        var incontent = $(".box_user").html();
        $.fancybox({ 'width' : 500, 'content' : incontent });
        clearLoading();
      } else if(data.error == "u_notexist") {
        $(".searchinput").blur();
        clearLoading();
        $.fancybox({
          'afterClose':function () {
            $(".searchinput").focus();
          },
          'height' : 250,
          'content' : m_error('Пользователь не найден!')
        });
        
      } else {
        clearLoading();
        $.fancybox({ 'content' : data.error });
      }
    });
  }
  
  function finduser() {
    ui = $(".searchinput").val();
    if((ui == "") || (ui == " ")) { cursearch = ""; }
    else { cursearch = ui; }
    page(1);
  }
  
  function reguser() {
    if(($("#add_type option:selected").val() == "") || ($("#add_surname").val() == "") || ($("#add_firstname").val() == "") || ($("#add_patronymic").val() == "") || ($("#add_sex option:selected").val() == "") || ($("#add_phone").val() == "") || ($("#add_dep option:selected").val() == "") || ($("#add_post").val() == "")) {
      $.fancybox({ 'content' : 'Заполните все поля' });
    } else {
      phoneformat = $("#add_phone").val().replace(/[-()]/g,"");
      $.ajax({
        data: {
          act: "adduser",
          type: $("#add_type option:selected").val(),
          as: "n",
          phone: phoneformat,
          surname: $("#add_surname").val(),
          firstname: $("#add_firstname").val(),
          patronymic: $("#add_patronymic").val(),
          sex: $("#add_sex option:selected").val(),
          depid: $("#add_dep option:selected").val(),
          post: $("#add_post").val(),
        }
      })
      .done(function(answer) {
        clearLoading();
        var data = JSON.parse(answer);
        if(data.error == "ok") {
          $.fancybox({
            closeEffect : 'none',
            'afterClose':function () { window.location.reload(); },
            'content' : m_ok('Пользователь успешно зарегистрирован!')
          });
        } else if(data.error == "ok_notify") {
          $.fancybox({
            closeEffect : 'none',
            'afterClose':function () { window.location.reload(); },
            'content' : m_error('Пользователь зарегистрирован, НО '+data.notifyres)
          });
        } else { alert(data.error); }
      });
    }
  }
  
  function addwindow() {
    $("#savebtn").hide();
    $("#regbtn").show();
    $("#add_surname, #add_firstname, #add_patronymic, #add_phone, #add_post").val('');
    $("#add_type [value='']").prop("selected", true);
    $("#add_sex [value='']").prop("selected", true);
    $("#add_dep [value='']").prop("selected", true);
    $(".fillblack, .addwindow").fadeIn(400);
    $("#add_type").focus();
  }
  
  function editwindow() {
    $("#savebtn").show();
    $("#regbtn").hide();
	$(".addwindow h1").html("Редактирование пользователя");
    $("#add_type [value='"+global_type+"']").prop("selected", true);
    $("#add_surname").val(HTML.decode(global_surname));
    $("#add_firstname").val(HTML.decode(global_firstname));
    $("#add_patronymic").val(HTML.decode(global_patronumic));
    $("#add_sex [value='"+global_sex+"']").prop("selected", true);
    $("#add_phone").val(global_phone);
    $("#add_phone").mask("(999)999-99-99");
    $("#add_dep [value='"+global_depid+"']").prop("selected", true);
    $("#add_post").val(global_post);
    $.fancybox.close();
    $(".fillblack, .addwindow").fadeIn(400);
  }
  function closemw(elemclass) {
    $(".fillblack, ."+elemclass).fadeOut(400);
  }
  function onsave() {
    if(($("#add_type option:selected").val() == "") || ($("#add_surname").val() == "") || ($("#add_firstname").val() == "") || ($("#add_patronymic").val() == "") || ($("#add_sex option:selected").val() == "") || ($("#add_phone").val() == "") || ($("#add_dep option:selected").val() == "") || ($("#add_post").val() == "")) {
      $.fancybox({ 'content' : 'Заполните все поля' });
    } else {
      phoneformat = $("#add_phone").val().replace(/[-()]/g,"");
      $.ajax({
        data: {
          act: "edituser",
          id: globaluid,
          type: $("#add_type option:selected").val(),
          as: "n",
          phone: phoneformat,
          surname: $("#add_surname").val(),
          firstname: $("#add_firstname").val(),
          patronymic: $("#add_patronymic").val(),
          sex: $("#add_sex option:selected").val(),
          depid: $("#add_dep option:selected").val(),
          post: $("#add_post").val(),
        }
      })
      .done(function(answer) {
        clearLoading();
        var data = JSON.parse(answer);
        if(data.error == "ok") {
			closemw("addwindow");
		    page((curpage+1), 1);
          $.fancybox({
            'afterClose':function () { user(globaluid); },
            'content' : m_ok('Данные успешно сохранены!')
          });
        } else { alert(data.error); }
      });
    }
  }
  
  function returnaccess() {
	  render_massage("Восстановление доступа","<div class='render_massage'>При восстановлении доступа будет сгенерирован новый пароль</div><div class='render_massage_buttons'><a class='btn1' href='' onclick='returnaccessYES(); $.fancybox.close(); return false;' style='background:#f36b69;'>Восстановить по SMS</a> <a class='btn1' href='' onclick='returnaccessYES(1); $.fancybox.close(); return false;' style='background:#f36b69;'>Отобразить новый пароль</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>");
  }

  function returnaccessYES(ifDisplay) {
	  _ifDisplay = "y";
	  if(ifDisplay == 1) { _ifDisplay = "n"; }
      $.ajax({
      data: {
        act: "genpw",
        uid: globaluid,
        as: _ifDisplay
      },
      success: function(answer) {
        var data = JSON.parse(answer);
        if(data.error == "ok") {
          $.fancybox({
			  'afterClose':function () {
				user(globaluid);
            },
            'content' : m_ok('Доступ успешно восстановлен!')
          });
		} else if(data.error == "ok_notify") {
			$.fancybox({
				'afterClose':function () {
				user(globaluid);
            },
				'content' : m_error(data.notifyres)
			  });
		} else if(data.error == "ok_display") {
			$.fancybox({
				'afterClose':function () {
				user(globaluid);
            },
				'content' : '<h1 class="render_massage_title"><i class="icon-ok"></i> Доступ успешно восстановлен!</h1><p class="render_massage">Логин: <b>'+data.l+'</b><br>Пароль: <b>'+data.p+'</p>'
			  });
		} else {
          $.fancybox({ 'content' : m_error(data.error) });
        }
	  }
    });
  }
  
  function deluser() {
    if(confirm("Вы уверены, что хотите удалить этого пользователя. Если да, нажмите \"OK\".")) {
      $.ajax({
      data: {
        act: "deluser",
        id: globaluid
      }
      })
      .done(function(answer) {
        clearLoading();
        var data = JSON.parse(answer);
        if(data.error == "ok") {
          $.fancybox({
            closeEffect : 'none',
            'afterClose':function () {
              window.location.reload();
            },
            'content' : m_ok('Пользователь успешно удален!')
          });
        } else {
          alert(data.error);
        }
      });
    }
  }