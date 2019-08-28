/* -------------------------------------------------------- */
/* --- © АНО "Центр молодежных и студенческих программ" --- */
/* -------------------------------------------------------- */

/* SlideRow */
(function($) {
var sR = {
    defaults: {
        slideSpeed: 400,
        easing: false,
        callback: false
    },
    thisCallArgs: {
        slideSpeed: 400,
        easing: false,
        callback: false
    },
    methods: {
        up: function (arg1,arg2,arg3) {
            if(typeof arg1 == 'object') {
                for(p in arg1) {
                    sR.thisCallArgs.eval(p) = arg1[p];
                }
            }else if(typeof arg1 != 'undefined' && (typeof arg1 == 'number' || arg1 == 'slow' || arg1 == 'fast')) {
                sR.thisCallArgs.slideSpeed = arg1;
            }else{
                sR.thisCallArgs.slideSpeed = sR.defaults.slideSpeed;
            }

            if(typeof arg2 == 'string'){
                sR.thisCallArgs.easing = arg2;
            }else if(typeof arg2 == 'function'){
                sR.thisCallArgs.callback = arg2;
            }else if(typeof arg2 == 'undefined') {
                sR.thisCallArgs.easing = sR.defaults.easing;
            }
            if(typeof arg3 == 'function') {
                sR.thisCallArgs.callback = arg3;
            }else if(typeof arg3 == 'undefined' && typeof arg2 != 'function'){
                sR.thisCallArgs.callback = sR.defaults.callback;
            }
            var $cells = $(this).find('td');
            $cells.wrapInner('<div class="slideRowUp" />');
            var currentPadding = $cells.css('padding');
            $cellContentWrappers = $(this).find('.slideRowUp');
            $cellContentWrappers.slideUp(sR.thisCallArgs.slideSpeed,sR.thisCallArgs.easing).parent().animate({
                                                                                                                paddingTop: '0px',
                                                                                                                paddingBottom: '0px'},{
                                                                                                                complete: function () {
                                                                                                                    $(this).children('.slideRowUp').replaceWith($(this).children('.slideRowUp').contents());
                                                                                                                    $(this).parent().css({'display':'none'});
                                                                                                                    $(this).css({'padding': currentPadding});
                                                                                                                }});
            var wait = setInterval(function () {
                if($cellContentWrappers.is(':animated') === false) {
                    clearInterval(wait);
                    if(typeof sR.thisCallArgs.callback == 'function') {
                        sR.thisCallArgs.callback.call(this);
                    }
                }
            }, 100);
            return $(this);
        },
        down: function (arg1,arg2,arg3) {
            if(typeof arg1 == 'object') {
                for(p in arg1) {
                    sR.thisCallArgs.eval(p) = arg1[p];
                }
            }else if(typeof arg1 != 'undefined' && (typeof arg1 == 'number' || arg1 == 'slow' || arg1 == 'fast')) {
                sR.thisCallArgs.slideSpeed = arg1;
            }else{
                sR.thisCallArgs.slideSpeed = sR.defaults.slideSpeed;
            }

            if(typeof arg2 == 'string'){
                sR.thisCallArgs.easing = arg2;
            }else if(typeof arg2 == 'function'){
                sR.thisCallArgs.callback = arg2;
            }else if(typeof arg2 == 'undefined') {
                sR.thisCallArgs.easing = sR.defaults.easing;
            }
            if(typeof arg3 == 'function') {
                sR.thisCallArgs.callback = arg3;
            }else if(typeof arg3 == 'undefined' && typeof arg2 != 'function'){
                sR.thisCallArgs.callback = sR.defaults.callback;
            }
            var $cells = $(this).find('td');
            $cells.wrapInner('<div class="slideRowDown" style="display:none;" />');
            $cellContentWrappers = $cells.find('.slideRowDown');
            $(this).show();
            $cellContentWrappers.slideDown(sR.thisCallArgs.slideSpeed, sR.thisCallArgs.easing, function() { $(this).replaceWith( $(this).contents()); });

            var wait = setInterval(function () {
                if($cellContentWrappers.is(':animated') === false) {
                    clearInterval(wait);
                    if(typeof sR.thisCallArgs.callback == 'function') {
                        sR.thisCallArgs.callback.call(this);
                    }
                }
            }, 100);
            return $(this);
        }
    }
};

$.fn.slideRow = function(method,arg1,arg2,arg3) {
    if(typeof method != 'undefined') {
        if(sR.methods[method]) {
            return sR.methods[method].apply(this, Array.prototype.slice.call(arguments,1));
        }
    }
};
})(jQuery);

/* COMMON START */

function addme_alerts(addme_data) {
	var div_box = $("<div/>");
	for(var i = 0; i < addme_data.length; i++) {
		var div = $("<div/>").addClass("alert").addClass("alert-addme").addClass("alert-addme-yellow");
		var content;
		div.attr("addme_alert_id",i);
		div.append('<button type="button" class="close" onclick="addme_alert_remove('+i+','+addme_data[i].event_id+')">×</button>');
		if(addme_data[i].status == "c") {
			content = 'отклонена';
		} else {
			content = 'одобрена';
			if(addme_data[i].status == "h") { content = 'частично одобрена'; }
		}
		var answer = '';
		if(addme_data[i].answer) { answer = '<div>'+addme_data[i].answer+'</div>'; }
		div.append('<b>Заявка '+content+'</b><a href="events-'+addme_data[i].event_id+'">'+addme_data[i].event_name+'</a>'+answer+'<span>'+addme_data[i].holder+'<br>'+addme_data[i].time+'</span>');
		div_box.append(div);
	}
	$(".undermenu").after(div_box);
}

function addme_alert_remove(id,eid) {
	$("[addme_alert_id="+id+"]").slideUp(function() {
		$.ajax({
			data: { act: "addme_done", event: eid }
		});
	});
}

/* ACTIVITY */
function activity() {
	$.ajax({
		data: { act: "mylist" },
		success: function(answer) {
		  var data = (JSON.parse(answer));
		  if(data.error == "ok") {
			if(data.events) {
			  var getdata = data.events;
			  dateconvert = /(\d{2}).(\d{2}).(\d{2})/;
			  getdata.sort(function custom_sort(a, b) {
				return new Date(a.date_since.replace(dateconvert, "$2/$1/$3")) - new Date(b.date_since.replace(dateconvert, "$2/$1/$3"));
			  });
			  getdata.reverse();
			  for (var i = 0; i < getdata.length; i++) {
				  var dates = "";
				  if(getdata[i].date_for !== null) { dates = "" + getdata[i].date_since + "<br />"+getdata[i].date_for; }
				  else { dates = getdata[i].date_since; }

				  var role;
				  if(getdata[i].role == "u") { role = "участник"; }
				  else if(getdata[i].role == "p") { role = "призер"; }
				  else if(getdata[i].role == "w") { role = "победитель"; }
				  else if(getdata[i].role == "l") { role = "помощь в оранизации"; }
				  else if(getdata[i].role == "m") { role = "организатор"; }
				  else if(getdata[i].role == "h") { role = "главный организатор"; }
				  else if(getdata[i].role == "b") { role = "-"; }

				  points = getdata[i].points;
				  if(getdata[i].points == "0") { points = "-"; }

				  complex = "";
				  if(getdata[i].complex == "y") { complex = " <img style=\"vertical-align:top; width:15px; opacity:1;\" src=\"img/muscle_black.svg?2\">"; }

				  tr = $('<tr/>');
				  tr.append("<td class=\"blowit center curmydate\">" + dates + "</td>");
				  tr.append("<td class=\"curmyevent\"><a class=\"link\" href=\"events-"+getdata[i].eid+"\" style='text-decoration:none;'>" + getdata[i].name + "</a></td>");
				  tr.append("<td class=\"blowit curmyrole\">" + role + complex + "</td>");
				  tr.append("<td class=\"blowit curevent_by\">" + getdata[i].holder + "</td>");
				  tr.append("<td class=\"center curmypoints\">" + points + "</td>");
				  $('.activities').append(tr);
			  }
			} else {}
			$(".points b").html(data.scores);
			$(".rank b").html(data.current);
			addme_alerts(data.addme_alerts);
		  } else { $.fancybox({ 'content' : data.error }); }
		}
	  });
  }

/* EVENTS */
function init_events() {
	$(".textalert").hide();
    $(".searchinput, .searchholder, .search_since, .search_for").keyup(function(e) { if(e.keyCode == 13) { findevents(); } });
    var setFocus = setInterval(function() { var elem = $(".searchinput"); if(elem.is(":focus")) { clearTimeout(setFocus); } else { elem.focus(); } }, 500);
	$(".search_since, .search_for").mask("99.99.9999");
	$(".search_since, .search_for").datepicker({ dateFormat: "dd.mm.yy" });
	$(".show_tagsE").hide();
	$(".btn_show_tagsE").click(function() {
		$(".show_tagsE").slideDown();
		$(this).slideUp();
		return false;
	});
	$(".showtags_mobile").click(function() {
		if($(".event_tags").is(":visible")) { $(".event_tags").slideUp(); }
		else { $(".event_tags").slideDown(); }
		return false;
	});
	$(".tag").click(function() {
		if($(this).attr("tagsearch") == "n") {
			$(".tag").each(function(index,element) { $(element).attr("tagsearch","n"); });
			$(this).attr("tagsearch","y");
		} else {
			$(this).attr("tagsearch","n");
		}
		page_events(1);
		return false;
	});
    page_events(1);
	$(".searchholder").autocomplete({
      source: function(request, response) {
		  	$(".searchholder").attr("holderid","");
              $.ajax({
                  url: "quick",
                  data: {
                      act: 'd',
                      term: request.term
                  },
                  dataType: "json",
                  beforeSend: function() {},
                  success: function(data) {
                  response($.map(data, function(item) {
                      return {
                          label: item.value,
                          value: item.value,
                          id: item.id
                      }
                  }));
              }
              });
          },
      search: function(){ $(this).addClass('withload'); },
      response: function(){ $(this).removeClass('withload'); },
      delay: 100,
      minLength: 2,
      select: function( event, ui ) {
		  $(".searchholder").attr("holderid",ui.item.id);
		  $(".searchholder").val(ui.item.name);
      }
    });
	$(".searchholder").blur(function() {
		if($(".searchholder").attr("holderid") == "" || $(".searchholder").val() == "") { $(".searchholder").val(""); $(".searchholder").attr("holderid",""); }
	});
}

function page_events(setcurpage) {
	curpage = setcurpage - 1;
	$.ajax({
		data: {
			act: "getevents",
			query: cursearch,
			holder: $(".searchholder").attr("holderid"),
			date_since: $(".search_since").val(),
			date_for: $(".search_for").val(),
			tag: $(".tag[tagsearch='y']").attr("tagID"),
			page: curpage
		},
		beforeSend: function() { startLoading(); $('.textalert').hide(); },
		success: function(answer) {
			$('.eventstable').html('<tr class="table_head center"><td width="11%"><b>Дата</b></td><td class="event_time" width="11%"><b>Время</b></td><td><b>Наименование</b></td><td class="event_level" width="10%"><b>Уровень</b></td><td class="curevent_by" width="24%"><b>Координатор</b></td><td width="5%"><i class="icon-user icon-white"></i></td></tr>');
			$('.ratingtable').html('');
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					var getdata = (JSON.parse(answer)).events;
					var tr;
					for (var i = 0; i < getdata.length; i++) {
						var dates = "";
						var times = "";
						if(getdata[i].e_date_for !== null) { dates = "" + getdata[i].e_date_since + "<br />"+getdata[i].e_date_for; }
						else { dates = getdata[i].e_date_since; }
						times = getdata[i].e_time_since + "-"+getdata[i].e_time_for;

						var elevel;
						if(getdata[i].e_level == "f") { elevel = "факультет"; }
						else if(getdata[i].e_level == "u") { elevel = "вуз"; }
						else if(getdata[i].e_level == "c") { elevel = "город"; }
						else if(getdata[i].e_level == "r") { elevel = "регион"; }
						else if(getdata[i].e_level == "v") { elevel = "страна"; }
						else if(getdata[i].e_level == "i") { elevel = "мир"; }

						tr = $('<tr/>');
						tr.addClass("rowclick");
						if(getdata[i].e_access == "yes") { tr.addClass("rowedit"); }
						tr.attr("eid", ""+getdata[i].e_id+"");
						tr.append("<td class=\"blowit center\">" + dates + "</td>");
						tr.append("<td class=\"blowit center event_time\">" + times + "</td>");
						tr.append("<td class=\"center event_name\">" + getdata[i].e_name + "</td>");
						tr.append("<td class=\"center blowit event_level\">" + elevel + "</td>");
						tr.append("<td class=\"center curevent_by\">" + getdata[i].e_holder + "</td>");
						tr.append("<td class=\"center\">" + getdata[i].e_involved + "</td>");
						tr.attr("onclick", "window.open('events-"+getdata[i].e_id+"', '_blank');");
						$('.eventstable').append(tr);
				}
				for (var i = 0; i < data.tags.length; i++) {
					$(".tag[tagID='"+data.tags[i][0]+"'] b").html(data.tags[i][1]);
				}
				pager("events",curpage,5,data.maxrows,data.allrows,$(".pager"));
				$(".points b").html(data.scores);
				$(".rank b").html(data.current);
				addme_alerts(data.addme_alerts);
			} else if(data.error == "notfound") {
				$('.pager').html('');
				$('.textalert').show();
			} else { $.fancybox({ 'content' : data.error }); }
		}
	});
}

  function findevents() {
    si = $(".searchinput").val();
    if((si == "") || (si == " ")) { cursearch = ""; }
    else { cursearch = si; }
	$(".filter button, .filter input, .filter select").blur();
    page_events(1);
  }

/* GROUPS */
function init_groups() {
	$(".loadlogo, .fillblack, .textalert").hide();
	$(".searchinput").keyup(function(e) { if(e.keyCode == 13) { findlists(); } });
	var setFocus = setInterval(function() { var elem = $(".searchinput"); if(elem.is(":focus")) { clearTimeout(setFocus); } else { elem.focus(); } }, 500);
	page_groups(1);
}

function page_groups(setcurpage) {
	curpage = setcurpage - 1;
	$.ajax({
		method: "post",
		url: "operator2",
		data: {
			act: "lists",
			query: cursearch,
			page: curpage
		},
		beforeSend: function() { startLoading(); $('.textalert').hide(); },
		success: function(answer) {
			$('.liststable').html('');
			$('.liststable').html('<tr class="table_head"><td><b>Наименование</b></td><td class="center" width="20%"><i class="icon-user icon-white"></i></td></tr>');
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					var getdata = data.lists;
					var tr;
					for (var i = 0; i < getdata.length; i++) {

						addicon = "";
						if(getdata[i].l_icon !== "n") {
              l_icon = getdata[i].l_icon.split('.');
              if(l_icon[1] == 'svg') { addicon = "<img class='inlinesvg' src='content/svg/"+getdata[i].l_icon+"' /> "; }
              else { addicon = "<img class='inlinesvg' src='content/img/"+getdata[i].l_icon+"' /> "; }
            }

						tr = $('<tr/>');
						tr.addClass("rowclick");
						tr.attr("eid", ""+getdata[i].l_id+"");
						tr.append("<td class=\"curname\">"+addicon+""+ getdata[i].l_name+"</td>");
						tr.append("<td class=\"center\">" + getdata[i].l_involved + "</td>");
						tr.attr("onclick", "if(goonit == 'yes') { window.location='groups-"+getdata[i].l_id+"'; }");
						$('.liststable').append(tr);
				}
				pager("groups",curpage,5,data.maxrows,data.allrows,$(".pager"));
				$(".points b").html(data.scores);
				$(".rank b").html(data.current);
				addme_alerts(data.addme_alerts);
			} else if(data.error == "notfound") {
				$('.pager').html('');
				$('.textalert').show();
				$(".points b").html(data.scores);
				$(".rank b").html(data.current);
				addme_alerts(data.addme_alerts);
			} else { $.fancybox({ 'content' : data.error }); }
		}
	});
}

function findlists() {
	si = $(".searchinput").val();
	if((si == "") || (si == " ")) { cursearch = ""; }
	else { cursearch = si; }
	page_groups(1);
}

/* RATING */
function init_rating() {
	$(".textalert").hide();
	$(".searchinput").keypress(function(e) { if(e.keyCode == 13) { findstudents(); $(".searchinput").blur(); } });
	$(".closemw").click(function(e) { $('.searchinput').focus(); });
	var setFocus = setInterval(function() { var elem = $(".searchinput"); if(elem.is(":focus")) { clearTimeout(setFocus); } else { elem.focus(); } }, 500);
	page_rating(1);
}

function page_rating(setcurpage, hideload) {
	curpage = setcurpage - 1;
	$.ajax({
		data: {
			act: "getrating",
			query: cursearch,
			dep: cursearchdep,
			course: cursearchcourse,
			tag: cursearchtagA,
			page: curpage
		},
		beforeSend: function() { if(hideload !== 1) { startLoading(); } $('.textalert').hide(); },
		success: function(answer) {
			$('.ratingtable').html('');
			var data = (JSON.parse(answer));
			if(data.error == "ok") {
				var getdata = (JSON.parse(answer)).students;
				$('.ratingtable').html('<tr class="table_head center"><td width="9%"><b>Место</b></td><td width="9%"><b>Баллы</b></td><td><b>ФИО активиста</b></td><td width="5%" class="owncourse"><b>Курс</b></td><td class="curdep" width="29%"><b>Институт/ВШ</b></td></tr>');
				var tr;
				icons_array = [];
				for (var i = 0; i < getdata.length; i++) {
					listsicons = "";
					if(getdata[i].lists.length !== 0) {
						for (var c = 0; c < getdata[i].lists.length; c++) {
							var icons_new = [];
							icons_new.push(getdata[i].lists[c][0]);
							icons_new.push(getdata[i].lists[c][1]);
							icons_new.push(getdata[i].lists[c][2]);
							icons_new.push(getdata[i].lists[c][3]);
							icons_new.push(getdata[i].name);
							icons_array.push(icons_new);
							newicon = "<a href='' onclick='showlist("+(icons_array.length-1)+"); return false;' onmouseover='goonit = \"no\"' onmouseout='goonit = \"yes\"'>";
              l_icon = getdata[i].lists[c][3].split('.');
              if(l_icon[1] == 'svg') { newicon += "<img class='inlinesvg' src='content/svg/"+getdata[i].lists[c][3]+"' alt='"+getdata[i].lists[c][1]+"' /></a>"; }
              else { newicon += "<img class='inlinesvg' src='content/img/"+getdata[i].lists[c][3]+"' alt='"+getdata[i].lists[c][1]+"' /></a>"; }
							listsicons += newicon+" ";
						}
					}

					tr = $('<tr/>');
					tr.addClass("rowclick");
					tr.attr("id","id" + getdata[i].id + "");
					tr.addClass("center");

					if($(".searchinput").val().trim() == "" && cursearchdep == "" && cursearchcourse == "" && cursearchtagA == "") {
						tr.append("<td>" + getdata[i].rate + "</td>");
					} else {
						tr.append("<td>" + getdata[i].rate_filter + "</td>");
					}


					tr.append("<td class=\"curpoints blowit\"><b>" + getdata[i].points + "</b></td>");
					tr.append("<td class=\"curfio\">" + listsicons + "" + getdata[i].name + "</td>");
					tr.append("<td class=\"blowit owncourse\">" + getdata[i].course + "</td>");
					tr.append("<td class=\"curdep\">" + getdata[i].dep + "</td>");
					$('.ratingtable').append(tr);
					tr.click(function() { if(goonit == "yes") { student($(this).attr("id").substr(2)); } });
				}
				onpage = 5;
				numpages = Math.ceil(data.allrows/data.maxrows); prev = "n"; next = "y";
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
					apager.attr("href","javascript:page_rating("+(startnum-1)+")");
					apager.html("..."); $(".pager").append(apager);
				}
				for(var ai=startnum; ai <= endnum; ai++) {
					apager = $('<a/>');
					if(ai == (curpage+1)) { apager.addClass("active"); }
					apager.attr("href","javascript:page_rating("+ai+")"); apager.html(""+ai+""); $(".pager").append(apager);
				}
				if(next == "y") {
					apager = $('<a/>');
					apager.attr("href","javascript:page_rating("+(endnum+1)+")");
					apager.html("..."); $(".pager").append(apager);
				}

				$(".points b").html(data.scores);
				$(".rank b").html(data.current);
				addme_alerts(data.addme_alerts);

			} else if(data.error == "notfound") {
				$('.ratingtable').html('<tr class="table_head center"><td width="9%"><b>Место</b></td><td width="9%"><b>Баллы</b></td><td><b>ФИО активиста</b></td><td width="5%" class="owncourse"><b>Курс</b></td><td width="29%"><b>Институт/ВШ</b></td></tr>');
				$('.pager').html('');
				$('.textalert').show();
			} else { $.fancybox({ 'content' : data.error }); }
		}
	});
}

function showlist(iconid) {
	elem = icons_array[iconid];
	renderdiv = $("<div/>").addClass("row-fluid listicon").css("max-width","400px");
  l_icon = elem[3].split('.');
  if(l_icon[1] == 'svg') { renderdiv.append("<div class='span4'><img src='content/svg/"+elem[3]+"' alt='"+elem[1]+"' /></div>"); }
  else { renderdiv.append("<div class='span4'><img src='content/img/"+elem[3]+"' alt='"+elem[1]+"' /></div>"); }

	renderdiv.append("<div class='span8'><p><b class='listicon_name'>"+elem[4]+"</b></p><p><b class='listicon_group'>"+elem[1]+"</b></p><div>"+HTML.decode(elem[2])+"</div><p><a href='groups-"+elem[0]+"' style='font-size:12px; text-decoration:underline; color:#4b8ab5;'><i>Просмотреть всех...</i></a></p></div>");
	$.fancybox({ 'content' : renderdiv });
}

  function student(studentid,ifnothistory) {
    globalsid = studentid;
    $(".box_student_info, .box_student_add, .owntable").html("");
    $.ajax({
      data: {
        act: "getactive",
        by: getstudby,
        uid: studentid
      },
		success: function(answer) {
		  getstudby = "i";
		  var data = JSON.parse(answer);
		  if(data.error == "ok") {
			globalsid = data.id;
			global_surname = data.surname;
			global_firstname = data.firstname;
			global_patronumic = data.patronymic;
			global_sex = data.sex;
			global_course = data.educourse;
			global_level = data.edulevel;

			var infobox = $(".box_student_info");
			var edulevel;
			if(data.edulevel == "b") { edulevel = "бак."; }
			else if(data.edulevel == "s") { edulevel = "спец."; }
			else if(data.edulevel == "m") { edulevel = "маг."; }
			else if(data.edulevel == "c") { edulevel = "СПО"; }
			if(data.patronymic.length < 2) {
				infobox.html("<b style='text-transform:uppercase; color:#0081ca;'>"+data.surname+" "+data.firstname+"</b> <b>("+data.department+" "+data.educourse+" курс "+edulevel+")</b>");
			} else {
				infobox.html("<b style='text-transform:uppercase; color:#0081ca;'>"+data.surname+" "+data.firstname+" "+data.patronymic+"</b> <b>("+data.department+" "+data.educourse+" курс "+edulevel+")</b>");
			}
			$(".box_student h1").html("Карта активиста");

			var infobox_lists = $(".box_student_lists");
			infobox_lists.html("");
			if(data.lists.length == 0) { infobox_lists.hide(); }
			else {
				infobox_lists.show();
				for (var c = 0; c < data.lists.length; c++) {
          addicon = 'svg';
          l_icon = data.lists[c][3].split('.');
          if(l_icon[1] !== 'svg') { addicon = 'img'; }
					$(".box_student_lists").append('<div class="greybox"><div class="event_info_inner" style="margin-bottom:-1px;"><div><img class="inlinesvg" src="content/'+addicon+'/'+data.lists[c][3]+'" alt="'+data.lists[c][1]+'" style="margin:0 8px 0 0; vertical-align:middle;"><span style="display:inline-block; vertical-align:middle; padding:3px 0 0 0; color:#666;"><a target="_blank" href="groups-'+data.lists[c][0]+'">'+data.lists[c][1]+'</a></span></div></div></div>');
				}
			}

			if(data.events) {
			  var tablehead = '<tr class="table_head"><td class="hideit" width=""><b>№</b></td><td class="hideit" width="12%"><b>Дата</b></td><td><b>Наименование мероприятия</b></td><td width="17%"><b>Статус</b></td><td class="event_holder" width="17%"><b>Ответственный</b></td><td width="7%"><b>Баллы</b></td></tr>';
			  $('.owntable').append(tablehead);
			  var getdata = data.events;
			  dateconvert = /(\d{2}).(\d{2}).(\d{2})/;
			  getdata.sort(function custom_sort(a, b) {
				  return new Date(a.date_since.replace(dateconvert, "$2/$1/$3")) - new Date(b.date_since.replace(dateconvert, "$2/$1/$3"));
			  });
			  getdata.reverse();
			  var tr;
			  for (var i = 0; i < getdata.length; i++) {
				var dates = "";
				if(getdata[i].date_for !== null) { dates = "" + getdata[i].date_since + "<br />"+getdata[i].date_for; }
				else { dates = getdata[i].date_since; }

				var role;
				if(getdata[i].role == "u") { role = "участн."; }
				else if(getdata[i].role == "p") { role = "призер"; }
				else if(getdata[i].role == "w") { role = "победит."; }
				else if(getdata[i].role == "l") { role = "помощь в орган."; }
				else if(getdata[i].role == "m") { role = "организ."; }
				else if(getdata[i].role == "h") { role = "глав. организ."; }
				else if(getdata[i].role == "b") { role = "-"; }

				complex = "";
				if(getdata[i].complex == "y") { complex = " <img style=\"vertical-align:top; width:15px; opacity:0.8;\" src=\"img/muscle_black.svg?2\">"; }

				tr = $('<tr/>');
				tr.attr("aid", ""+getdata[i].aid+"");
				tr.append("<td class=\"blowit center hideit\">" + (i+1) + "</td>");
				tr.append("<td class=\"blowit center hideit\">" + dates + "</td>");
				tr.append("<td><a class=\"link\" target=\"_blank\" href=\"events-"+getdata[i].eid+"\">" + getdata[i].name + "</a></td>");
				tr.append("<td class=\"blowit\">" + role + complex + "</td>");
				tr.append("<td class=\"blowit event_holder\">" + getdata[i].holder + "</td>");
				tr.append("<td class=\"center\">" + getdata[i].points + "</td>");
				$('.owntable').append(tr);
			  }
			} else {
			  $(".box_student_add").append("<p class=\"center\"><b>Мероприятий нет</b></p>");
			}

			var incontent = $(".box_student").html();

			if(ifnothistory !== 1) {
				history.pushState(null, data.sinid, "/rating#info");

				window.onpopstate = function(event) {
					if(($(".fancybox-skin").is(":visible")) && (!$(".mw").is(":visible"))) {
					  $.fancybox.close();
					} else {
						window.location = "/";
					}
				};
			}

			$.fancybox({
				'afterClose':function () {
					if(ifnothistory !== 1) { history.pushState(null, null, "/rating"); }
				  },
				'width' : 500,
				'content' : incontent
			});

		  } else if(data.error == "s_notexist") {
			$(".searchinput").blur();

			$.fancybox({
			  'afterClose':function () {
				$(".searchinput").focus();
			  },
			  'height' : 250,
			  'content' : m_error('Студент c заданным кодом не найден!')
			});

		  } else {

			$.fancybox({ 'content' : data.error });
		  }
		}
    });
  }

  function findstudents() {
    si = $(".searchinput").val();
    getstudby = "i";
    if((si == "") || (si == " ")) { cursearch = ""; }
    else { cursearch = si; }
    cursearchdep = $(".searchdep option:selected").val();
    cursearchcourse = $(".searchcourse option:selected").val();
	cursearchtagA = $(".searchtagA option:selected").val();
    page_rating(1);
  }

/* SET */
function changepw() {
	if(($("#pw_old").val() !== '') && ($("#pw_new").val() !== '') && ($("#pw_newrepeat").val() !== '')) {
		var re = /^(?=.*\d)(?=.*[a-z])[0-9a-zA-Z]{6,30}$/;
		if(!re.test($("#pw_old").val())) {
			$.fancybox({ 'content' : 'Неверный формат нового пароля' });
		} else {
			if($("#pw_new").val() !== $("#pw_newrepeat").val()) {
				$.fancybox({ 'content' : 'Повтор пароля <strong>неверный</strong>' });
			}
			else {
				$.ajax({
					data: {
						act: "cpw",
						old: MD5($("#pw_old").val()),
						"new": MD5($("#pw_new").val())
					},
					success: function(answer) {
						var preanswer = JSON.parse(answer);
						var spwres = preanswer.error;
						if(spwres == 'ok') {
							$.fancybox({
								'afterClose':function () {
									window.location = "index";
								},
								'content' : m_ok('Пароль успешно изменен!')
							});
						}
						else {
							$.fancybox({ 'content' : spwres });
						}
					}
				});
			}
		}
	}
}

/* GROUP */
function page_group() {
	$.ajax({
		method: "post",
		url: "operator2",
		data: {
			act: "getlist",
			lid: listid
		},
		beforeSend: function() { startLoading(); $('.textalert').hide(); },
		success: function(answer) {
			$('.personslist').html('');
			var data = (JSON.parse(answer));
			if(data.error == "ok") {
				$(".titleline h1").html(data.name);
				$(".editSortFIO").html("<b><a href='' onclick='sortFIO(); return false;'>ФИО студента</a></b>");
				var members = data.members;
				if(data.icon !== "n") {
          addicon = 'svg';
          l_icon = data.icon.split('.');
          if(l_icon[1] !== 'svg') { addicon = 'img'; }
					$(".titleline h1").prepend("<img class='inlinesvg inlinesvg_title' src='content/"+addicon+"/"+data.icon+"' alt='' />");
				}
				$(".list_info").append($("<div/>").addClass("span12 inforow").html("<div><b>Кол-во студентов:</b> <span class='memberscount'>"+members.length+"</span></div>"));

				if(members.length == 0) {
					$('.textalert').show();
				} else {
					for(var i = 0; i < members.length; i++) {
						if(data.isedit == "y") {
							newrow = '<div class="pl_row pl_editable" hid="'+members[i][0]+'">\n<div class="pl_row_box">\n<div class="row-fluid">\n<div class="span5 name"><a href="" onclick="student('+members[i][0]+',1); return false;">'+members[i][1]+'</a><div>'+members[i][2]+'</div></div>\n<div class="span6 commentside"><div class="comment">'+HTML.decode(members[i][3])+'</div></div>\n<div class="span1 funcside"><a href="" class="btn_edit" onclick="delmembers('+members[i][0]+'); return false;"><i class="icon-remove"></i></a></div>\n</div>\n</div>\n</div>\n';
							$('.personslist').append(newrow);
							editor(members[i][0]);
							sortable();
						} else {
							newrow = '<div class="pl_row" hid="'+members[i][0]+'">\n<div class="pl_row_box">\n<div class="row-fluid">\n<div class="span5 name"><a href="" onclick="student('+members[i][0]+',1); return false;">'+members[i][1]+'</a><div>'+members[i][2]+'</div></div>\n<div class="span6 commentside">'+HTML.decode(members[i][3])+'</div>\n<div class="span1 funcside"></div>\n</div>\n</div>\n</div>\n';
							$('.personslist').append(newrow);
						}
					}
				}
				$(".points b").html(data.scores);
				$(".rank b").html(data.current);
				addme_alerts(data.addme_alerts);
			} else { $.fancybox({ 'content' : data.error }); }
		}
	});
}

function sortFIO() {
	sortUsingNestedText($(".personslist"), ".pl_row", ".name");
}

/* EVENT */
  function init_event_user() {
    $.ajax({
      data: {
        act: "getinvolved",
        eid: curevent
      },
      beforeSend: function() { startLoading(); $('.textalert').hide(); },
		success: function(answer) {
		  var data = (JSON.parse(answer));
		  if(data.error == "ok") {
			$(".activitytable").html('<tr class="table_head"><td><b>ФИО [факультет/курс]</b></td><td width="20%" class="curevent_role"><b>Роль</b></td><td width="4%" style="padding:3px !important;"><img src="img/muscle.svg?2" style="max-width:20px;" /></td><td width="14%" class="curevent_added"><b>Добавлено</b></td><td width="18%" class="curevent_by"><b>Фиксатор</b></td></tr>');

			var getdata = (JSON.parse(answer)).einfo;

			var elevel, edates;
			if(getdata.level == "f") { elevel = "факультет"; }
			else if(getdata.level == "u") { elevel = "вуз"; }
			else if(getdata.level == "c") { elevel = "город"; }
			else if(getdata.level == "r") { elevel = "регион"; }
			else if(getdata.level == "v") { elevel = "страна"; }
			else if(getdata.level == "i") { elevel = "мир"; }

			$("h1").html(getdata.name);
			$(".event_i1").html("<div><b>Уровень:</b> <span>"+elevel+"</span></div>");
			if(getdata.place) { $(".event_i1").append("<div><b>Место:</b> <span>"+getdata.place+"</span></div>"); }
			if(getdata.df !== null) { edates = "c " + getdata.ds + " по "+getdata.df; }
			else { edates = getdata.ds; }
			$(".event_i1").append("<div><b>Дата:</b> <span>"+edates+"</span></div>");
			if(getdata.ts !== null) { $(".event_i1").append("<div><b>Время:</b> <span>"+getdata.ts+" - "+getdata.tf+"</span></div>"); }

			if(getdata.creator) { $(".event_i2").html("<div><b>Регистратор:</b> <span>"+HTML.decode(getdata.creator)+"</span></div>"); }
			if(getdata.dep) { $(".event_i2").append("<div><b>Организация:</b> <span>"+getdata.dep+"</span></div><div><b>Координатор:</b> <span>"+getdata.hname+"</span></div>"); }
			else { $(".event_i2").append("<div><b>Координатор:</b> <span>"+getdata.hname+"</span></div>"); }
			$(".event_i2").append("<div><b>Задействовано:</b> <span class='involvednum'>"+data.allrows+"</span></div>");

			if(getdata.comment) { $(".event_comment_box").html("<div class='event_comment'><span style='width:100%;'>"+getdata.comment+"</span></div>"); }

			var isme = "n";
			if(data.allrows == '0') {
			  $('.textalert').show();
			} else {
			  var alist = data.alist;
			  for (var i = 0; i < alist.length; i++) {
				tr = $('<tr/>');
				var addme_button = "";
				if( alist[i].isme ) {
					isme = "y";
					tr.attr("isme","y");
					if(!data.addme) {
						tr.addClass("rowclick").addClass("rowedit");
						addme_button = ' <button onclick="addme_window();"><i class="icon-edit icon-white"></i> изменить</button>';
					} else {
						tr.attr("new_role",data.addme.role);
						if(data.addme.complex == "y") { tr.attr("new_complex","true"); }
						else { tr.attr("new_complex","false"); }

						addme_button = ' <button onclick="addme_remove();"><i class="icon-remove icon-white"></i> отменить заявку</button>';
						tr.addClass("rowyellow");
					}
					tr.attr("old_role",alist[i].a_role);
					if(alist[i].a_complex == "y") { tr.attr("old_complex","true"); }
					else { tr.attr("old_complex","false"); }
				}

				tr.append("<td><a href='' onclick='student("+alist[i].a_uid+",1); return false;'><b>" + alist[i].a_name + "</b></a> [" + alist[i].a_from + "]"+addme_button+"</td>");
				var role;
				addcomplex = ' <div class="complexcheck-show" style="margin:-4px 0 0 -2px;"></div>';
				if(alist[i].a_complex == "y") { addcomplex = ' <div class="complexcheck-show complexcheck-show2" style="margin:-4px 0 0 -2px;"></div>'; }
				if(alist[i].a_role == "u") { tr.append('<td>участник</td>'); }
				else if(alist[i].a_role == "p") { tr.append('<td>призер</td>'); }
				else if(alist[i].a_role == "w") { tr.append('<td>победитель</td>'); }
				else if(alist[i].a_role == "l") { tr.append('<td>помощник организатора</td>'); }
				else if(alist[i].a_role == "m") { tr.append('<td>организатор</td>'); }
				else if(alist[i].a_role == "h") { tr.append('<td>главный организатор</td>'); }
				else if(alist[i].a_role == "b") { tr.append('<td>-</td>'); }
				tr.append("<td class=\"center\">" + addcomplex + "</td>");
				tr.append("<td class=\"curevent_added\">" + alist[i].a_time + "</td>");
				tr.append("<td class=\"curevent_by\">" + alist[i].a_by + "</td>");
				$('.activitytable').append(tr);
			  }
			}

			if( data.addme ) {
				if( isme == "y" ) {
					var addme_tr = $("tr[isme='y']");
					if(addme_tr.attr("new_role") !== addme_tr.attr("old_role")) {
						addme_tr.find("td:nth-child(2)").html("<div class='addme_new_role' style='text-decoration:line-through;'>"+addme_tr.find("td:nth-child(2)").html()+"</div>");
						var addme_newrole = "";
						if(addme_tr.attr("new_role") == "u") { addme_newrole = "участник"; }
						else if(addme_tr.attr("new_role") == "p") { addme_newrole = "призер"; }
						else if(addme_tr.attr("new_role") == "w") { addme_newrole = "победитель"; }
						else if(addme_tr.attr("new_role") == "l") { addme_newrole = "помощник организатора"; }
						else if(addme_tr.attr("new_role") == "m") { addme_newrole = "организатор"; }
						else if(addme_tr.attr("new_role") == "h") { addme_newrole = "главный организатор"; }
						else if(addme_tr.attr("new_role") == "b") { addme_newrole = "-"; }
						addme_tr.find("td:nth-child(2)").append(addme_newrole);
					}
					var cur_boolean_new_complex = false;
					if(addme_tr.attr("new_complex") == "true") { cur_boolean_new_complex = true; }
					var cur_boolean_old_complex = false;
					if(addme_tr.attr("old_complex") == "true") { cur_boolean_old_complex = true; }
					if(cur_boolean_new_complex !== cur_boolean_old_complex) {
						if(cur_boolean_new_complex == true) {
							addme_tr.find("td:nth-child(3)").prepend("<div style='position:relative;'><div class='complexcheck_addme_no user-a'></div></div>");
							addme_tr.find(".complexcheck-show").addClass("complexcheck-show2");
						} else {
							addme_tr.find("td:nth-child(3)").prepend("<div style='position:relative;'><div class='complexcheck_addme_no user-a complexcheck_addme_no2'></div></div>");
							addme_tr.find(".complexcheck-show").removeClass("complexcheck-show2");
						}
					}
				} else {
					var addme_button = ' <button onclick="addme_remove();"><i class="icon-remove icon-white"></i> отменить заявку</button>';
					tr = $('<tr/>');
					tr.addClass("rowyellow");
					tr.attr("isme","y");
					tr.append("<td><a href='' onclick='student("+data.addme.sid+",1); return false;'><b>" + data.addme.name + "</b></a> [" + data.addme.from + "]"+addme_button+"</td>");
					if(data.addme.role == "u") { tr.append('<td>участник</td>'); }
					else if(data.addme.role == "p") { tr.append('<td>призер</td>'); }
					else if(data.addme.role == "w") { tr.append('<td>победитель</td>'); }
					else if(data.addme.role == "l") { tr.append('<td>помощник организатора</td>'); }
					else if(data.addme.role == "m") { tr.append('<td>организатор</td>'); }
					else if(data.addme.role == "h") { tr.append('<td>главный организатор</td>'); }
					else if(data.addme.role == "b") { tr.append('<td>-</td>'); }
					var role;
					addcomplex = ' <div class="complexcheck-show" style="margin:-4px 0 0 -2px;"></div>';
					if(data.addme.complex == "y") { addcomplex = ' <div class="complexcheck-show complexcheck-show2" style="margin:-4px 0 0 -2px;"></div>'; }
					tr.append("<td class=\"center\">" + addcomplex + "</td>");
					tr.append("<td class=\"curevent_added\"></td>");
					tr.append("<td class=\"curevent_by\"></td>");
					$('.activitytable tbody tr:first-child').after(tr);
				}

				// for all addme cases
				var status = $('<div/>').addClass("addme_status");
				status.append(data.addme.comment);
				status.append("<span>"+data.addme.time+"</span>");
				$("tr[isme=y] td:nth-child(1)").append(status);
			}

			if(!data.addme && isme == "n") {
				$(".undermenu").after('<div class="row-fluid" style="margin-top:5px;"><button href="" class="span12 btn btn-yellow" onclick="addme_window();"><i class="icon-plus icon-white"></i> Меня не добавили</button></div>')
			}
			$(".points b").html(data.scores);
			$(".rank b").html(data.current);
			addme_alerts(data.addme_alerts);
		  } else { $.fancybox({ 'content' : data.error }); }
		}
    });
  }

	function addme_window() {
		if($("tr[isme=y]").length > 0) {
			$(".addmewindow h1").html("Меня добавили неправильно");
			$("#addme_role [value='"+$("tr[isme=y]").attr("old_role")+"']").prop("selected", true);
			if($("tr[isme=y]").attr("old_complex") == "true") { $("#addme_complex").prop("checked", true); }
			else { $("#addme_complex").prop("checked", false); }
			$("#addme_comment").attr("placeholder","почему данные нужно изменить");
			$("#removemebtn").show();
		} else {
			$(".addmewindow h1").html("Меня не добавили");
			$("#addme_role [value='']").prop("selected", true);
	    $("#addme_complex").prop("checked", false);
			$("#addme_comment").attr("placeholder","почему меня нужно добавить");
			$("#removemebtn").hide();
		}
		$("#addme_comment").val("");
		$("html, body").animate({ scrollTop: 0 });
    $(".fillblack, .addmewindow").fadeIn(400);
  }

	function addme() {
		if($("#addme_comment").val().trim() == "") { return false; }
		if($("tr[isme=y]").length > 0) {
			var new_complex = false;
			if($("tr[isme=y]").attr("old_complex") == "true") { new_complex = true; }
			if($("tr[isme=y]").attr("old_role") == $("#addme_role option:selected").val()
			&& new_complex == $("#addme_complex").prop("checked")) {
				$.fancybox({ 'content' : "Данные в заявке не отличаются от текущих данных." });
				return false;
			}
		}
		$.ajax({
      data: {
        act: "addme_profile",
				eid: curevent,
        rid: $("#addme_role option:selected").val(),
				complex: $("#addme_complex").prop("checked"),
				comment: $("#addme_comment").val().trim()
      },
			success: function(answer) {
		  	var data = (JSON.parse(answer));
		  	if(data.error == "ok") {
					$.fancybox({
						'afterClose':function () {
						  window.location.reload();
						},
						'content' : m_ok("Заявка успешно отправлена")
					});
				} else {
					$.fancybox({ 'content' : data.error });
				}
			}
		});
	}

	function addme_remove() {
		render_massage("Отменить заявку?","<div class='render_massage_buttons'><a class='btn1' href='' onclick='addme_removeYES(); $.fancybox.close(); return false;' style='background:#f36b69;'>Отменить заявку</a></div>");
	}

	function addme_removeYES() {
		$.ajax({
      data: {
        act: "addme_remove",
				event: curevent
      },
			success: function(answer) {
		  	var data = (JSON.parse(answer));
		  	if(data.error == "ok") {
					$.fancybox({
						'afterClose':function () {
						  window.location.reload();
						},
						'content' : m_ok("Заявка успешно отменена")
					});
				} else {
					$.fancybox({ 'content' : data.error });
				}
			}
		});
	}

	function removeme() {
		render_massage("Удалить информацию о вашем участии на данном мероприятии?","<div class='render_massage_buttons'><a class='btn1' href='' onclick='removemeYES(); $.fancybox.close(); return false;' style='background:#f36b69;'>Удалить</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>");
	}

	function removemeYES() {
		$.ajax({
      data: {
        act: "removeme",
				event: curevent
      },
			success: function(answer) {
		  	var data = (JSON.parse(answer));
		  	if(data.error == "ok") {
					closemw("addmewindow");
					$.fancybox({
						'afterClose':function () {
							$("tr[isme='y']").slideRow('up', 500, function() {
								$("tr[isme='y']").remove();
							});
						},
						'content' : m_ok("Информация о вашем участии успешно удалена.")
					});
				} else {
					$.fancybox({ 'content' : data.error });
				}
			}
		});
	}
