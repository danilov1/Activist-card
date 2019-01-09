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

/* SZ */
function sortTable(listid,elem) {
	sortUsingNestedText($(".databox_"+listid+" table tbody"), "tr", "td."+elem+"");
	setNums(listid);
}

function downloadlist(listid) {
	curtable = $(".databox_"+listid+" table tbody");
	if(curtable.children().length == 0) { return false; }
	render_text = "";
	headertext = HTML.decode($(".temp1").html().replace(/<\/p>/g, "\n")).replace(/<\/?[^>]+(>|$)/g, "");
	if(headertext !== "") { render_text += headertext+"\n"; }
	datetext = HTML.decode($(".temp3").html().replace(/<\/p>/g, "\n")).replace(/<\/?[^>]+(>|$)/g, "");
	if(datetext !== "") { render_text += datetext+"\n\n"; }
	contenttext = HTML.decode($(".temp5").html().replace(/<\/p>/g, "\n")).replace(/<\/?[^>]+(>|$)/g, "");
	if(contenttext !== "") { render_text += contenttext+"\n\n"; }
	for(var c = 0; c<curtable.children().length; c++) {
		render_text += curtable.children().eq(c).find(".c2").text();
		render_text += " "+curtable.children().eq(c).find(".c4").text();
		render_text += "("+curtable.children().eq(c).find(".c5 .datashow_group").text()+")";
		render_text += "-"+curtable.children().eq(c).find(".c5 .datashow_groupnum").text()+"";
		if(c !== (curtable.children().length - 1)) { render_text += "\n"; }
	}
	var pom = document.createElement('a');
	pom.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(render_text));
	pom.setAttribute('download', ""+dd+"."+mm+"."+yy+" СПИСОК-"+listid+".txt");
	pom.style.display = 'none';
	document.body.appendChild(pom);
	pom.click();
	document.body.removeChild(pom);
}

function fillByTemp() {
	thetemp = $(".form_tempselector select option:selected").val();
	if(thetemp == "") {
		$(".temp1, .temp4, .temp5").html("<p><br></p>");
		$(".temp2").html("<p><strong>служебная записка</strong><br></p>");
		$(".temp3").html("<p>"+dd+"."+mm+"."+yy+"<br></p>");
		$(".btn_savecur, .btn_deltemp, .form_savebtns span").fadeOut();
		return true;
	}
	$.ajax({
		data: {
			act: "gettempsz",
			id: thetemp
		},
		beforeSend: function() { startLoading(); },
		success: function(answer) {
			var data = (JSON.parse(answer));
			if(data.error == "ok") {
				$(".temp1").html(HTML.decode(data.temp1));
				$(".temp2").html(HTML.decode(data.temp2));
				$(".temp3").html("<p>"+dd+"."+mm+"."+yy+"<br></p>");
				$(".temp4").html(HTML.decode(data.temp4));
				$(".temp5").html(HTML.decode(data.temp5));
				$(".form_savebtns span, .btn_savecur, .btn_deltemp").show();
				$(".form_ready").slideDown(300);
				$(".form_savebtns, .form_addbtns, .form_line, .form_print").slideDown();
			} else { $.fancybox({ 'content' : m_error(data.error) }); }
		}
	});
}

function saveInTemp() {
	$.ajax({
		data: {
			act: "savetempsz",
			id: $(".form_tempselector select option:selected").val(),
			temp1: $(".temp1").html(),
			temp2: $(".temp2").html(),
			temp3: "-",
			temp4: $(".temp4").html(),
			temp5: $(".temp5").html()
		},
		beforeSend: function() { startLoading(); },
		success: function(answer) {
			var data = (JSON.parse(answer));
			if(data.error == "ok") {
				$.fancybox({ 'content' : m_ok('Шаблон сохранен!') });
			}
			else { $.fancybox({ 'content' : m_error(data.error) }); }
		}
	});
}

function createTempWindow() {
	$("#newtemp_share").prop("checked",true);
	$("#newtemp_name").val("");
	$("html, body").animate({ scrollTop: 0 });
	$(".fillblack, .window_newtemp").fadeIn();
	$("#newtemp_name").focus();
}

function createTemp() {
	$.ajax({
		data: {
			act: "newtempsz",
			name: $("#newtemp_name").val(),
			share: $("#newtemp_share").prop("checked"),
			temp1: $(".temp1").html(),
			temp2: $(".temp2").html(),
			temp3: "-",
			temp4: $(".temp4").html(),
			temp5: $(".temp5").html()
		},
		beforeSend: function() { startLoading(); },
		success: function(answer) {
			var data = (JSON.parse(answer));
			if(data.error == "ok") {
				$(".form_tempselector select").append("<option value='"+data.newid+"'>"+$('#newtemp_name').val()+"</option>");
				$(".form_tempselector select option[value='"+data.newid+"']").prop("selected", true);
				closemw("window_newtemp");
			}
			else { $.fancybox({ 'content' : m_error(data.error) }); }
		}
	});
}

function delTemp() {
	render_massage("Удалить шаблон?","<div class='render_massage_buttons'><a class='btn1' href='' onclick='delTempYES(); $.fancybox.close(); return false;' style='background:#f36b69;'>Удалить</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>");
}

function delTempYES() {
	lastid = $(".form_tempselector select option:selected").val();
	$.ajax({
		data: {
			act: "deltempsz",
			id: lastid,
		},
		beforeSend: function() { startLoading(); },
		success: function(answer) {
			var data = (JSON.parse(answer));
			$(".form_tempselector select[value='']").prop("selected", true);
			$(".form_tempselector select option[value='"+lastid+"']").remove();
			if(data.error == "ok") { $(".btn_savecur, .btn_deltemp, .form_savebtns span").fadeOut(); }
			else { $.fancybox({ 'content' : m_error(data.error) }); }
		}
	});
}

function setNums(listnum) {
	$(".databox_"+listnum+" table tbody").find('tr').each(function() {
		$(this).children('td:first-child').html(($(this).index() + 1))
	});
}

function delthis(elem, listnum) {
	elem.parent().parent().remove();
	setNums(listnum);
}

function addstudent(studentid, listnum) {
	$.ajax({
		data: {
			act: "getstudentsz",
			sid: studentid
		},
		beforeSend: function() {},
		success: function(answer) {
			var data = (JSON.parse(answer));
			if(data.error == "ok") {
				alist = data.alist;
				if($(".databox_"+listnum+" tr[aid='"+alist.a_id+"']").length) {
					$.fancybox({ 'content' : '<p align="center">Студент <b>'+alist.a_sname+' '+alist.a_fname+' '+alist.a_pname+'</b> уже внесен в список.</p><p align="center"><button onclick="$.fancybox.close(); $(\'.databox_'+listnum+' .f_searcher\').focus();" class="btn existstudent">OK</button></p>' });
					$(".existstudent").focus();
					return true;
				}
				tr = $('<tr/>');
				tr.attr("aid", ""+alist.a_id+"");
				tr.append("<td class='c1'></td>");
				tr.append("<td class='c2'><span id='datashow_snane'>"+alist.a_sname+"</span> <span id='datashow_fnane'>"+alist.a_fname+"</span> <span id='datashow_pnane'>"+alist.a_pname+"</span></td>");
				tr.append("<td class='c3'>" + alist.a_course + "</td>");
				tr.append("<td class='c4'>" + alist.a_dep + "</td>");
				tr.append("<td class='c5'><span class='datashow_group'>" + alist.a_group + "</span> <span class='datashow_groupnum'>" + alist.a_gnum + "</span></td>");
				tr.append("<td class='c6'><i class='icon-remove' onclick='delthis($(this), "+listblock+");'></i></td>");
				$(".databox_"+listnum+" table tbody").prepend(tr);
				setNums(listnum);
				$(".databox_"+listnum+" tr[aid='"+alist.a_id+"']").animate({'background-color': '#bde0a0'}, 500, function() {
					$(".databox_"+listnum+" tr[aid='"+alist.a_id+"']").animate({'background-color': "#eee"}, 2000, function() {
						$(".databox_"+listnum+" table tbody tr").each(function() { $(this).removeAttr("style"); });
					});
				});
			}
			else { $.fancybox({ 'content' : m_error(data.error) }); }
		}
	});
}

function addfromevent_window() {
	$(".window_addfromevent input[type='checkbox']").each(function() { $(this).prop("checked",true); });
	$("html, body").animate({ scrollTop: 0 });
	$(".fillblack, .window_addfromevent").fadeIn();
}

function list_addnew() {
	newlistblock = $(".blocksample .block_students").clone().appendTo(".list_blocks");
	listblock += 1;
	newlistblock.addClass("databox_"+listblock);
	newlistblock.attr("list_id",listblock);
	newlistblock.find("h2").html("Список студентов - \"СПИСОК("+listblock+")\"");
	$(".databox_"+listblock+" .sort1, .databox_"+listblock+" .sort2, .databox_"+listblock+" .sort3, .databox_"+listblock+" .sort4, .databox_"+listblock+" .sort5").attr("onclick","sortTable("+listblock+",$(this).parent().attr('class')); return false;");
		$(".databox_"+listblock+" .btn_dellist").attr("onclick","dellist_sz("+listblock+"); return false;");
		$(".databox_"+listblock+" .btn_downloadlist").attr("onclick","downloadlist("+listblock+"); return false;");

	$(".databox_"+listblock).slideDown(500, function() {
		setAutocomplete_sz(listblock);
	});
}

function list_addfromevent(ifall) {
	if(ifall !== 1) {
		if((!$("#addrole_1").is(':checked'))
		 && (!$("#addrole_2").is(':checked'))
		 && (!$("#addrole_3").is(':checked'))
		 && (!$("#addrole_4").is(':checked'))
		 && (!$("#addrole_5").is(':checked'))
		 && (!$("#addrole_6").is(':checked'))
		 && (!$("#addrole_7").is(':checked'))) { return true; }

		rolelist = "";
		if($("#addrole_1").is(':checked')) { rolelist += "b" }
		if($("#addrole_2").is(':checked')) { rolelist += "u" }
		if($("#addrole_3").is(':checked')) { rolelist += "p" }
		if($("#addrole_4").is(':checked')) { rolelist += "w" }
		if($("#addrole_5").is(':checked')) { rolelist += "l" }
		if($("#addrole_6").is(':checked')) { rolelist += "m" }
		if($("#addrole_6").is(':checked')) { rolelist += "h" }
	} else { rolelist = "bupwlmh"; }

	$.ajax({
	  data: {
		act: "getinvolvedsz",
		eid: curevent,
		roles: rolelist
	  },
	  beforeSend: function() { startLoading(); },
		success: function(answer) {
		  var data = (JSON.parse(answer));
		  if(data.error == "ok") {
			if(!data.alist) {
				$.fancybox({ 'content' : "По заданным параметрам студенты не найдены" });
				return true;
			}
			newlistblock = $(".blocksample .block_students").clone().appendTo(".list_blocks");
			listblock += 1;
			newlistblock.addClass("databox_"+listblock);
			newlistblock.attr("list_id",listblock);
			newlistblock.find("h2").html("Список студентов - \"СПИСОК("+listblock+")\"");
			$(".databox_"+listblock+" .sort1, .databox_"+listblock+" .sort2, .databox_"+listblock+" .sort3, .databox_"+listblock+" .sort4, .databox_"+listblock+" .sort5").attr("onclick","sortTable("+listblock+",$(this).parent().attr('class')); return false;");
			$(".databox_"+listblock+" .btn_dellist").attr("onclick","dellist_sz("+listblock+"); return false;");
			$(".databox_"+listblock+" .btn_downloadlist").attr("onclick","downloadlist("+listblock+"); return false;");

			$(".databox_"+listblock).slideDown(500, function() {
				alist = data.alist;
				for (var i = 0; i < alist.length; i++) {
				  tr = $('<tr/>');
				  tr.attr("aid", ""+alist[i].a_id+"");
				  tr.append("<td class='c1'>"+(i+1)+"</td>");
				  tr.append("<td class='c2'><span id='datashow_snane'>"+alist[i].a_sname+"</span> <span id='datashow_fnane'>"+alist[i].a_fname+"</span> <span id='datashow_pnane'>"+alist[i].a_pname+"</span></td>");
				  tr.append("<td class='c3'>" + alist[i].a_course + "</td>");
				tr.append("<td class='c4'>" + alist[i].a_dep + "</td>");
				tr.append("<td class='c5'><span class='datashow_group'>" + alist[i].a_group + "</span> <span class='datashow_groupnum'>" + alist[i].a_gnum + "</span></td>");
				tr.append("<td class='c6'><i class='icon-remove' onclick='delthis($(this), "+listblock+");'></i></td>");
				  $(".databox_"+listblock+" table tbody").append(tr);
				}
				closemw("window_addfromevent");
				setAutocomplete_sz(listblock);
				if(ifall == "1") { sortTable(listblock,"c2"); printSZ('y'); }
			});
		  } else { $.fancybox({ 'content' : m_error(data.error) }); }
		}
	});
}

function printSZ(ifpreview) {
	sendlists = [];
	for(var i = 0; i<$(".list_blocks").children().length; i++) {
		newstudlist = [];
		curtable = $(".list_blocks").children().eq(i).find("tbody");
		if(curtable.children().length == 0) { continue; }
		for(var c = 0; c<curtable.children().length; c++) {
			newstudlist.push(curtable.children().eq(c).attr("aid"));
		}
		add_newlist = [];
		add_newlist.push($(".list_blocks").children().eq(i).attr("list_id"));
		add_newlist.push(newstudlist);
		sendlists.push(add_newlist);
	}
	$.ajax({
	  data: {
		act: "printsz",
		temp1: $(".temp1").html(),
		temp2: $(".temp2").html(),
		temp3: $(".temp3").html(),
		temp4: $(".temp4").html(),
		temp5: $(".temp5").html(),
		lists: JSON.stringify(sendlists)
	  },
	  beforeSend: function() { startLoading(); },
		success: function(answer) {
			var data = (JSON.parse(answer));
			if(data.error == "ok") {
				if(ifpreview == "y") { $.fancybox({ 'content' : '<iframe src="printsz?preview=yes&id='+data.view+'" width="600" height="600"></iframe>' }); }
				else {
					$.fancybox({ 'content' : '<iframe src="printsz?id='+data.view+'" width="600" height="600"></iframe>' });
				}
			} else { $.fancybox({ 'content' : m_error(data.error) }); }
		}
	});
}

function setAutocomplete_sz(listnum) {
	$(".databox_"+listnum+" .f_searcher").autocomplete({
	  source: function(request, response) {
		  $.ajax({
			  url: "quick",
			  data: {
				  act: 'a',
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
			  })); }
		  });
	  },
	  search: function(){ $(this).addClass('withload'); },
	  response: function(){ $(this).removeClass('withload'); },
	  delay: 100,
	  minLength: 2,
	  select: function( event, ui ) {
		$(this).val("");
		addstudent(ui.item.id, listnum);
		return false;
	  }
	});
	$(".databox_"+listnum+" .f_searcher").focus();
	$(".databox_"+listnum+" table tbody").sortable({
		update: function(event, ui) { setNums(listnum); }
	});
	$(".temp5").append("<p>СПИСОК("+listnum+")</p>");
	$('body').animate({
		scrollTop: $(".databox_"+listnum).offset().top - $('body').offset().top + $('body').scrollTop()
	}, 500);
}

function dellist_sz(listnum) {
	$(".temp5").html($(".temp5").html().replace(new RegExp("<p>СПИСОК[(]"+listnum+"[)]</p>", "g"),""));
	$(".temp5").html($(".temp5").html().replace(new RegExp("<p>СПИСОК[(]"+listnum+"[)]<br></p>", "g"),""));
	$(".temp5").html($(".temp5").html().replace(new RegExp("СПИСОК[(]"+listnum+"[)]", "g"),""));
	$(".databox_"+listnum).slideUp(500, function() {
		$(".databox_"+listnum).remove();
		if(listblock == "1") { listblock = 0; }
		if($(".list_blocks").children().length == 0) { listblock = 0; }
	});
}

/* EVENTS */
function init_events() {
	$(".textalert, .addwindow, .checkwindow, .forgetcode, .changebook, #savebtn").hide();
    $(".searchinput, .searchholder, .search_since, .search_for").keyup(function(e) { if(e.keyCode == 13) { findevents(); } });
    var setFocus = setInterval(function() { var elem = $(".searchinput"); if(elem.is(":focus")) { clearTimeout(setFocus); } else { elem.focus(); } }, 500);
	$(".search_since, .search_for").mask("99.99.9999");
	$(".search_since, .search_for, #add_date_since, #add_date_for").datepicker({ dateFormat: "dd.mm.yy" });
    $("#add_date_since, #add_date_for").mask("99.99.9999");
    $("#add_time_since, #add_time_for").mask("99:99");
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
    $("#add_holder, #add_fixers").autocomplete({
      source: function(request, response) {
              $.ajax({
                  url: "quick",
                  data: {
                      act: 'h',
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
		  if($(this).attr("id") == "add_holder") {
			$("#add_holderend").html(ui.item.value);
			newholder = ui.item.id;
			$(this).val("");
			return false;
		  }
		  if($(this).attr("id") == "add_fixers") {
			for(var i = 0; i<fixers.length; i++) {
				if(fixers[i][0] == ui.item.id) {
					$(this).val("");
					$("#add_fixers").focus();
					return false;
				}
			}
			newfixer = [];
			newfixer.push(ui.item.id);
			newfixer.push(ui.item.value);
			fixers.push(newfixer);
			recount_fixers();
			$(this).val("");
			$("#add_fixers").focus();
			return false;
		  }
      }
    });
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
	$("#add_name").autocomplete({
      source: function(request, response) {
              $.ajax({
                  url: "quick",
                  data: {
                      act: 'e',
                      term: request.term
                  },
                  dataType: "json",
                  beforeSend: function() {},
                  success: function(data) {
                  response($.map(data, function(item) {
                      return {
                          label: HTML.decode(item.value),
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
		  startLoading();
		  window.location.href = "events-" + ui.item.id;
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
			$('.eventstable').html('<tr class="table_head center"><td width="11%"><b>Дата</b></td><td class="event_time" width="11%"><b>Время</b></td><td><b>Наименование</b></td><td class="event_level" width="10%"><b>Уровень</b></td><td class="curevent_by" width="24%"><b>Координатор</b></td><td width="5%"><i class="icon-user icon-white"></i></td><td width="3%"></td></tr>');
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
						else if(getdata[i].e_level == "u") { elevel = "университет"; }
						else if(getdata[i].e_level == "c") { elevel = "город"; }
						else if(getdata[i].e_level == "r") { elevel = "регион"; }
						else if(getdata[i].e_level == "v") { elevel = "страна"; }
						else if(getdata[i].e_level == "i") { elevel = "мировой"; }

						tr = $('<tr/>');
						tr.addClass("rowclick");
						if(getdata[i].e_access == "yes") { tr.addClass("rowedit"); }
						tr.attr("eid", ""+getdata[i].e_id+"");
						tr.append("<td class=\"blowit center\">" + dates + "</td>");
						tr.append("<td class=\"blowit center event_time\">" + times + "</td>");
						var event_name = "<td class=\"center event_name\">" + getdata[i].e_name + "";
            if(getdata[i].e_addme) { tr.addClass("rowyellow"); event_name += '<span class="table-badge"><div>'+getdata[i].e_addme+'</div></span>'; }
            tr.append(event_name+"</td>");
						tr.append("<td class=\"blowit event_level\">" + elevel + "</td>");
						tr.append("<td class=\"center curevent_by\">" + getdata[i].e_holder + "</td>");
						tr.append("<td class=\"center\">" + getdata[i].e_involved + "</td>");
						tr.attr("onclick", "if(goonit == 'yes') { window.open('events-"+getdata[i].e_id+"', '_blank'); }");
						if(getdata[i].e_edit == "yes") { tr.append("<td class=\"center\"><i class='icon-edit' onclick='_edit_events("+getdata[i].e_id+")' onmouseover='goonit = \"no\"' onmouseout='goonit = \"yes\"'></i></td>"); }
						else { tr.append("<td class=\"center\"></td>"); }
						$('.eventstable').append(tr);
				}
				for (var i = 0; i < data.tags.length; i++) {
					$(".tag[tagID='"+data.tags[i][0]+"'] b").html(data.tags[i][1]);
				}
				pager("events",curpage,5,data.maxrows,data.allrows,$(".pager"));
			} else if(data.error == "notfound") {
				$('.pager').html('');
				$('.textalert').show();
			} else { $.fancybox({ 'content' : m_error(data.error) }); }
		}
	});
}

function addevent() {
    if(($("#add_name").val() == "") || ($("#add_date_since").val() == "") || ((!$("#add_date_if").is(':checked') && $("#add_date_for").val() == "")) || ($("#add_level option:selected").val() == "") || (newholder == "") || (($("#add_dep_if").is(':checked') && $("#add_dep option:selected").val() == ""))) {
      $.fancybox({ 'content' : 'Заполните все поля' });
    } else {
	  sendfixers = [];
	  for (var i = 0; i < fixers.length; i++) { sendfixers.push(fixers[i][0]); }
	  event_tags = [];
	  $(".add_tagA:checked, .add_tagE:checked").each(function() {
		  event_tags.push($(this).attr("tagID"));
	  });
      $.ajax({
        data: {
          act: "addevent",
          name: $("#add_name").val(),
          place: $("#add_place").val(),
          level: $("#add_level option:selected").val(),
          date_start: $("#add_date_since").val(),
          date_end: $("#add_date_for").val(),
          time_start: $("#add_time_since").val(),
          time_finish: $("#add_time_for").val(),
          dep: $("#add_dep option:selected").val(),
          holder: newholder,
		  fixers: JSON.stringify(sendfixers),
		  outside: $("#add_outside").prop("checked"),
		  complex: $("#add_complex").prop("checked"),
		  tags: JSON.stringify(event_tags),
          comment: $("#add_comment").val(),
        },
		  success: function(answer) {
			var data = JSON.parse(answer);
			if(data.error == "ok") {
			  closemw("addwindow");
			  $.fancybox({
				'afterClose':function () {
				  window.location.reload();
				},
				'content' : m_ok('Мероприятие успешно добавлено!')
			  });
			} else {
			  $.fancybox({ 'content' : m_error(data.error) });
			}
		  }
      });
    }
  }

  function saveevent() {
    if(($("#add_name").val() == "") || ($("#add_date_since").val() == "") || ((!$("#add_date_if").is(':checked') && $("#add_date_for").val() == "")) || ($("#add_level option:selected").val() == "") || (newholder == "") || (($("#add_dep_if").is(':checked') && $("#add_dep option:selected").val() == ""))) {
      $.fancybox({ 'content' : 'Заполните все поля' });
    } else {
	  sendfixers = [];
	  for (var i = 0; i < fixers.length; i++) { sendfixers.push(fixers[i][0]); }
	  event_tags = [];
	  $(".add_tagA:checked, .add_tagE:checked").each(function() {
		  event_tags.push($(this).attr("tagID"));
	  });
      $.ajax({
        data: {
          act: "editevent",
          id: global_eid,
          name: $("#add_name").val(),
          place: $("#add_place").val(),
          level: $("#add_level option:selected").val(),
          date_start: $("#add_date_since").val(),
          date_end: $("#add_date_for").val(),
          time_start: $("#add_time_since").val(),
          time_finish: $("#add_time_for").val(),
          dep: $("#add_dep option:selected").val(),
          holder: newholder,
		  fixers: JSON.stringify(sendfixers),
		  outside: $("#add_outside").prop("checked"),
		  complex: $("#add_complex").prop("checked"),
		  tags: JSON.stringify(event_tags),
          comment: $("#add_comment").val(),
        },
		  success: function(answer) {
			var data = JSON.parse(answer);
			if(data.error == "ok") {
			  closemw("addwindow");
			  $.fancybox({
				'afterClose':function () {
				  page_events((curpage+1));
				},
				'content' : m_ok('Изменения успешно сохранены!')
			  });
			} else {
			  $.fancybox({ 'content' : m_error(data.error) });
			}
		  }
      });
    }
  }

  function delevent() {
	  render_massage("Удалить мероприятие?","<div class='render_massage'>ВНИМАНИЕ! При удалении мероприятия активисты лишаются заработанных за него баллов.</div><div class='render_massage_buttons'><a class='btn1' href='' onclick='deleventYES(); $.fancybox.close(); return false;' style='background:#f36b69;'>Удалить</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>");
  }

  function deleventYES() {
    $.ajax({
      data: {
        act: "delevent",
        eid: global_eid
      },
      success: function(answer) {
        var data = JSON.parse(answer);
        if(data.error == "ok") {
          $.fancybox({
            'afterClose':function () {
              window.location.reload();
            },
            'content' : m_ok('Мероприятие успешно удалено!')
          });
        } else {
          $.fancybox({ 'content' : m_error(data.error) });
        }
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

  function addwindow_events() {
    $("#savebtn, #delbtn").hide();
    $("#regbtn").show();
	$(".addwindow h1").html("Регистрация мероприятия");
    $("#add_name, #add_place, #add_date_since, #add_date_for, #add_time_since, #add_time_for, #add_holder, #add_comment").val("");
	$("#add_holderend").html('<span style="color:#924242;">Еще не выбран</span>');
    $("#add_level [value='']").prop("selected", true);
    $("#add_dep [value='']").prop("selected", true);
    newholder = "";
	$("#add_outside").prop("checked", false);
    $("#add_date_for, #add_dep").hide();
    $("#add_date_if").prop("checked", true);
    $("#add_dep_if").prop("checked", false);
	$(".add_tagA, .add_tagE").each(function() {
		 $(this).prop("checked", false);
	});
	$("#add_complex").prop("checked", false);
	fixers = [];
	recount_fixers();
	$("html, body").animate({ scrollTop: 0 });
    $(".fillblack, .addwindow").fadeIn(400);
    $("#add_name").focus();
  }

  function editwindow_events() {
    $("#savebtn, #delbtn").show();
    $("#regbtn").hide();
    $(".addwindow h1").html("Редактирование мероприятия");
    $("#add_name").val(HTML.decode(global_name));
    $("#add_place").val(HTML.decode(global_place));
    $("#add_date_since").val(global_date_since);
    $("#add_time_since").val(global_time_since);
    $("#add_time_for").val(global_time_for);
    $("#add_level [value='"+global_level+"']").prop("selected", true);

    if(global_date_for !== null) {
      $("#add_date_if").prop("checked", false);
      $("#add_date_for").val(global_date_for);
      $("#add_date_for").show();
    }
    else {
      $("#add_date_if").prop("checked", true);
      $("#add_date_for").val("");
      $("#add_date_for").hide();
    }

    if(global_dep !== null) {
      $("#add_dep_if").prop("checked", true);
      $("#add_dep [value='"+global_dep+"']").prop("selected", true);
      $("#add_dep").show();
    }
    else {
      $("#add_dep_if").prop("checked", false);
      $("#add_dep [value='']").prop("selected", true);
      $("#add_dep").hide();
    }

	$("#add_outside").prop("checked", global_outside);
	$("#add_complex").prop("checked", global_complex);

	$("input.add_tagA, input.add_tagE").each(function() {
		$(this).prop("checked", false);
	});

	for (var i = 0; i < global_tags.length; i++) {
		$("input[tagID='"+global_tags[i]+"']").prop("checked", true);
	}

    newholder = global_holderid;
    $("#add_holderend").html(HTML.decode(global_holdername));

	fixers = [];
	if(global_fixers !== null) { fixers = global_fixers; recount_fixers(); }

	$("#add_comment").val(HTML.decode(global_comment));
    $("html, body").animate({ scrollTop: 0 });
    $(".fillblack, .addwindow").fadeIn(400);
  }

  function _edit_events(_eventid) {
    $.ajax({
      data: {
        act: "getevent",
        eid: _eventid
      },
		success: function(answer) {
		  var data = JSON.parse(answer);
		  if(data.error == "ok") {
			einfo = data.einfo;
			global_eid = einfo.eid;
			global_name = einfo.name;
			if(einfo.place !== null) { global_place = einfo.place; } else { global_place = ""; }
			global_date_since = einfo.ds;
			global_date_for = einfo.df;
			global_time_since = einfo.ts;
			global_time_for = einfo.tf;
			global_level = einfo.level;
			global_holderid = einfo.hid;
			if(einfo.fixers !== null) { global_fixers = einfo.fixers; }
			global_holdername = einfo.hname;
			global_dep = einfo.dep;
			if(einfo.comment !== null) { global_comment = einfo.comment; } else { global_comment = ""; }
			global_outside = einfo.outside;
			global_complex = einfo.complex;
			global_tags = einfo.tags;
			editwindow_events();
		  } else {
			$.fancybox({ 'content' : m_error(data.error) });
		  }
		}
    });
    return false;
  }

  function recount_fixers() {
	_div = $("#add_fixersend");
	_div.html("");
	for(var c = 0; c<fixers.length; c++) {
		newF = $('<div/>');
		newF.attr("fid", fixers[c][0]);
		newF.append(fixers[c][1]);
		newF.append("<a href='' onclick='del_fixer("+fixers[c][0]+"); return false;'><i class='icon-remove'></i></a>");
		_div.append(newF);
	}
  }

  function del_fixer(fixer_id) {
	for (var i = 0; i < fixers.length; i++) {
		if(fixers[i][0] == fixer_id) { fixers.splice(i,1); }
	}
	recount_fixers();
  }

  function changedate() {
    if($("#add_date_if").is(':checked')) { $("#add_date_for").hide(); $("#add_date_for").val(""); }
    else { $("#add_date_for").show(); }
  }

  function changedep() {
    if($("#add_dep_if").is(':checked')) { $("#add_dep").show(); }
    else { $("#add_dep").hide(); $("#add_dep [value='']").prop("selected", true); }
  }

/* GROUPS */
function init_groups() {
	$("input[name='ue'][value='r3'], input[name='uv'][value='r3']").next("span").html(depName);
	$("input[name='ue'], input[name='uv']").change(function() {
		if($(this).attr("name") == "uv"
		 && $("input[name='uv']").filter(':checked').val() == "r1"
		 && $("input[name='ue']").filter(':checked').val() !== "r1")
		{ $("input[name='ue'][value='r1']").prop("checked", true); changeUserRights($("input[name='ue']")); }
		changeUserRights($(this));
	});

	$(".loadlogo, .fillblack, .textalert, .addwindow, .checkwindow, .forgetcode, .changebook").hide();
	$(".searchinput").keyup(function(e) { if(e.keyCode == 13) { findlists(); } });
	var setFocus = setInterval(function() { var elem = $(".searchinput"); if(elem.is(":focus")) { clearTimeout(setFocus); } else { elem.focus(); } }, 500);
	$("input[type='file']").change(function () {
		var ext = $(this).val().split('.').pop().toLowerCase();
		if($.inArray(ext, ['svg','png']) == -1) {
			$(this).val("");
			$.fancybox({ 'content' : m_error("Поддерживается только формат .SVG и .PNG") });
		}
		$(".uploadFile").val($(this).val());
	});

	$("#ue_search, #uv_search").autocomplete({
      source: function(request, response) {
              $.ajax({
                  url: "quick",
                  data: {
                      act: 'h',
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
		_setvar = $(this).attr("id");
		_setvar = _setvar.split("_");
		_setvar = _setvar[0];
		eval("sm = "+_setvar+";");
		for (var i = 0; i < sm.length; i++) {
			if(sm[i][0] == ui.item.id) {
				$.fancybox({ 'content' : '<p align="center"><b>'+ui.item.value+'</b> уже есть в списке.</p><p align="center"><button onclick="$.fancybox.close(); $(\'#'+_setvar+'_search\').val(\'\'); $(\'#'+_setvar+'_search\').focus();" class="btn existstudent">OK</button></p>' });
				$(".existstudent").focus();
				return true;
			}
		}
		newHVAR = [];
		newHVAR.push(ui.item.id);
		newHVAR.push(ui.item.value);
		eval(""+_setvar+".push(newHVAR); recount(\""+_setvar+"\");");
		$(this).val("");
        $("#add_comment").focus();
          return false;
      }
    });
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
			$('.liststable').html('<tr class="table_head"><td><b>Наименование</b></td><td class="center" width="20%"><i class="icon-user icon-white"></i></td><td width="3%"></td></tr>');
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					var getdata = data.lists;
					var tr;
					for (var i = 0; i < getdata.length; i++) {

						addicon = "";
            _addicon = 'svg';
            l_icon = getdata[i].l_icon.split('.');
            if(l_icon[1] !== 'svg') { _addicon = 'img'; }
						if(getdata[i].l_icon !== "n") { addicon = "<img class='inlinesvg' src='content/"+_addicon+"/"+getdata[i].l_icon+"' alt='"+getdata[i].l_name+"' /> "; }

						tr = $('<tr/>');
						tr.addClass("rowclick");
						tr.attr("eid", ""+getdata[i].l_id+"");
						tr.append("<td class=\"curname\">"+addicon+""+ getdata[i].l_name+"</td>");
						tr.append("<td class=\"center\">" + getdata[i].l_involved + "</td>");
						tr.attr("onclick", "if(goonit == 'yes') { window.location='groups-"+getdata[i].l_id+"'; }");
						if(getdata[i].l_edit == "y") { tr.append("<td class=\"center\"><i class='icon-edit' onclick='editlistwindow("+getdata[i].l_id+"); return false;' onmouseover='goonit = \"no\"' onmouseout='goonit = \"yes\"'></i></td>"); }
						else { tr.append("<td class=\"center\"></td>"); }
						$('.liststable').append(tr);
				}
				pager("groups",curpage,5,data.maxrows,data.allrows,$(".pager"));
			} else if(data.error == "notfound") {
				$('.pager').html('');
				$('.textalert').show();
			} else { $.fancybox({ 'content' : m_error(data.error) }); }
		}
	});
}

function changeUserRights(element) {
	_itsname = element.attr("name");
	_selected = element.filter(':checked').val();
	if(_selected == "r1" || _selected == "r2") {
		eval(""+_itsname+" = [];");
		$("#"+_itsname+"_search").hide();
	}
	else {
		if(_selected == "r3") { $("#"+_itsname+"_search").attr("placeholder","Добавить других сотрудников..."); $("#"+_itsname+"_search").show(); }
		else { $("#"+_itsname+"_search").attr("placeholder","Выбрать сотрудников..."); $("#"+_itsname+"_search").show(); $("#"+_itsname+"_search").focus(); }
	}
	eval("recount(\""+_itsname+"\");");
}

function findlists() {
	si = $(".searchinput").val();
	if((si == "") || (si == " ")) { cursearch = ""; }
	else { cursearch = si; }
	page_groups(1);
}

function editlistwindow(lid) {
	$.ajax({
		method: "post",
		url:"operator2",
		data: {
			act: "getlistdata",
			lid: lid
		},
		success: function(answer) {
			var data = JSON.parse(answer);
			if(data.error == "ok") {
				$("#savebtn, #delbtn").show();
				$("#addbtn").hide();
				var getdata = data.listdata[0];
				curLID = HTML.decode(getdata.l_id);
				$("#list_name").val(HTML.decode(getdata.l_name));
				$("input[name='ue'][value='"+HTML.decode(getdata.l_ue)+"']").prop("checked", true);
				$("input[name='uv'][value='"+HTML.decode(getdata.l_uv)+"']").prop("checked", true);
				$("input[name='sv'][value='"+HTML.decode(getdata.l_sv)+"']").prop("checked", true);
				ue = getdata.l_aue;
				uv = getdata.l_auv;
				changeUserRights($("input[name='ue']"));
				changeUserRights($("input[name='uv']"));
				$(".btn_noicon").show();
        _addicon = 'svg';
        l_icon = getdata.l_icon.split('.');
        if(l_icon[1] !== 'svg') { _addicon = 'img'; }
				if(getdata.l_icon !== "n") { $(".fileUpload").find("span").html('Заменить <img class="inlinesvg" src="content/'+_addicon+'/'+getdata.l_icon+'" alt="" style="margin:0;" />'); }
				else { $(".fileUpload").find("span").html("Выбрать"); }
				$(".addwindow h1").html("Настройки группы");
				$("html, body").animate({ scrollTop: 0 });
				$(".fillblack, .addwindow").fadeIn(400);
				$("#list_name").focus();
			} else {
				$.fancybox({ 'content' : m_error(data.error) });
			}
		}
	});
}

function noicon(elem) {
	delicon();
	$(".fileUpload").find("span").html("Выбрать");
	elem.hide();
}

function delicon() {
	$("#list_icon_path, #list_icon").val("");
}

function showSome(ulist) {
	eval("elem = "+ulist+";");
	renderdiv = $("<div/>");
	for(var i = 0; i < elem.length; i++) {
		renderdiv.append("<div class='pl_row'>"+elem[i][1]+"</div>");
	}
	$.fancybox({ 'content' : renderdiv });
}

function findlists() {
	si = $(".searchinput").val();
	if((si == "") || (si == " ")) { cursearch = ""; }
	else { cursearch = si; }
	page_groups(1);
}

function recount(recount_method) {
	eval("rm = "+recount_method+";");
	_div = $("#"+recount_method+"_add");
	_div.html("");
	for(var i = 0; i<rm.length; i++) {
		newH = $('<div/>');
		newH.attr("hid", rm[i][0]);
		newH.append(rm[i][1]);
		newH.append("<a href='' onclick='delh(\""+recount_method+"\","+rm[i][0]+"); return false;'><i class='icon-remove'></i></a>");
		_div.append(newH);
	}
	byresize();
}

function delh(del_method,hid) {
	eval("dm = "+del_method+";");
	for (var i = 0; i < dm.length; i++) {
		if(dm[i][0] == hid) { eval(""+del_method+".splice(i,1);"); }
	}
	recount(del_method);
}

function addlist_groups(ifedit) {
	if(!is("#list_name")
	 || (($("input[name='ue']").filter(':checked').val() == "r4") && (ue.length == 0))
	 || (($("input[name='uv']").filter(':checked').val() == "r4") && (uv.length == 0)))
	{ $.fancybox({ 'content' : 'Заполните все поля' }); return false; }

	var fd = new FormData();
	if (ifedit !== 1) { fd.append('act', 'addlist'); }
	else {
		if($('#list_icon').val() !== "") { newicon = "y"; }
		else {
			if($(".btn_noicon").is(":visible")) { newicon = "n"; }
			else { newicon = "y"; }
		}
		fd.append('act', 'editlist');
		fd.append('lid', curLID);
		fd.append('newicon', newicon);
	}
	fd.append('name', $.trim($("#list_name").val()));
	if($('#list_icon').val() !== "") { fd.append('icon', $('#list_icon')[0].files[0]); }
	fd.append('ue', $("input[name='ue']").filter(':checked').val());
	fd.append('uv', $("input[name='uv']").filter(':checked').val());

	aue = []; auv = [];
	for (var i = 0; i < ue.length; i++) { aue.push(ue[i][0]); }
	for (var i = 0; i < uv.length; i++) { auv.push(uv[i][0]); }

	fd.append('aue', JSON.stringify(aue));
	fd.append('auv', JSON.stringify(auv));
	fd.append('sv', $("input[name='sv']").filter(':checked').val());

	$.ajax({
		method: "post",
		url:"operator2",
		data: fd,
		processData: false,
		contentType: false,
		success: function(answer) {
			var data = JSON.parse(answer);
			if(data.error == "ok") {
				closemw("addwindow");
				if(ifedit !== 1 ) { alerttext = 'Группа успешно создана!'; }
				else { alerttext = 'Изменения успешно сохранены!'; }
				$.fancybox({
					'afterClose':function () {
					  page_groups((curpage+1));
					},
					'content' : m_ok(alerttext)
				});
			} else {
				$.fancybox({ 'content' : m_error(data.error) });
			}
		}
	});
}

function addwindow_groups() {
	$("#savebtn, #delbtn").hide();
	$("#addbtn").show();
	$("#list_name, #list_icon, #list_icon, #list_icon_path").val("");
	$("input[name='ue'][value='r3'], input[name='uv'][value='r2'], input[name='sv'][value='r1']").prop("checked", true);
	ue = [];
	uv = [];
	changeUserRights($("input[name='ue']"));
	changeUserRights($("input[name='uv']"));
	$(".btn_noicon").hide();
	$(".fileUpload").find("span").html("Выбрать");
	$(".addwindow h1").html("Добавление группы");
	$("html, body").animate({ scrollTop: 0 });
	$(".fillblack, .addwindow").fadeIn(400);
	$("#list_name").focus();
	byresize();
}

function dellist_groups() {
	  render_massage("Удалить группу?","<div class='render_massage_buttons'><a class='btn1' href='' onclick='dellistYES_groups(); $.fancybox.close(); return false;' style='background:#f36b69;'>Удалить</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>");
  }

function dellistYES_groups() {
	$.ajax({
		method: "post",
		url: "operator2",
		data: {
			act: "dellist",
			lid: curLID
		},
		success: function(answer) {

			closemw("addwindow");
			var data = JSON.parse(answer);
			if(data.error == "ok") {
				$.fancybox({
					'afterClose':function () {
					page_groups(1);
				},
				'content' : m_ok('Группа успешно удалена!')
			  });
			} else {
				$.fancybox({ 'content' : m_error(data.error) });
			}
		}
	});
}




/* RATING */
function init_rating() {
	$(".textalert, .addwindow, .checkwindow, .forgetcode, .changebook, #savebtn").hide();
	$(".searchinput").keypress(function(e) { if(e.keyCode == 13) { findstudents(); $(".searchinput").blur(); } });
	$("#bookcode").keypress(function(e) { if(e.keyCode == 13) { if($("#bookcode").val() !== "") { regstudent(); } } });
	$("#newbookcode").keypress(function(e) { if(e.keyCode == 13) { if($("#newbookcode").val() !== "") { trychangebook(); } } });
	$("#add_groupnum").keyup(function(e) { if(e.keyCode == 13) { if($("#regbtn").is(':visible')) { oncheck(); } else { onsave(); } } });
	$("#bookcode").keydown(function(downit) { if(downit.keyCode == 9) { downit.preventDefault(); $("#bookcode").focus(); } });
	$(".closemw").click(function(e) { $('.searchinput').focus(); });

	$("#newbookcode").blur(function() {
	  if($(".changebook").is(":visible")) { var setFocusNC = setInterval(function() { thiselem = $("#newbookcode"); if(thiselem.is(":focus")) { clearTimeout(setFocusNC); } else { thiselem.focus(); } }, 100); }
	});

	$("#bookcode").blur(function() {
	  $(this).val("");
	  $(this).attr("placeholder","Нажмите на поле для подтверждения");
	});
	$("#bookcode").focus(function() {
	  $(this).attr("placeholder","Сканируйте карту для завершения");
	});
	$(".cancelcode").click(function() {
	  $(".checkwindow").fadeOut(200);
	  $(".forgetcode").fadeOut(200);
	  isoncheck = "n";
	  return false;
	});

	$("#add_birthday").mask("99.99.9999");
	$("#add_phone").mask("(999)999-99-99");
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
              _addicon = 'svg';
              l_icon = getdata[i].lists[c][3].split('.');
              if(l_icon[1] !== 'svg') { _addicon = 'img'; }
							newicon = "<a href='' onclick='showlist("+(icons_array.length-1)+"); return false;' onmouseover='goonit = \"no\"' onmouseout='goonit = \"yes\"'><img class='inlinesvg' src='content/"+_addicon+"/"+getdata[i].lists[c][3]+"' alt='"+getdata[i].lists[c][1]+"' /></a>";
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
			} else if(data.error == "notfound") {
				$('.ratingtable').html('<tr class="table_head center"><td width="9%"><b>Место</b></td><td width="9%"><b>Баллы</b></td><td><b>ФИО активиста</b></td><td width="5%" class="owncourse"><b>Курс</b></td><td width="29%"><b>Институт/ВШ</b></td></tr>');
				$('.pager').html('');
				$('.textalert').show();
			} else { $.fancybox({ 'content' : m_error(data.error) }); }
		}
	});
}

function showlist(iconid) {
	elem = icons_array[iconid];
	renderdiv = $("<div/>").addClass("row-fluid listicon").css("max-width","400px");
  _addicon = 'svg';
  l_icon = elem[3].split('.');
  if(l_icon[1] !== 'svg') { _addicon = 'img'; }
	renderdiv.append("<div class='span4'><img src='content/"+_addicon+"/"+elem[3]+"' alt='"+elem[1]+"' /></div>");
	renderdiv.append("<div class='span8'><p><b class='listicon_name'>"+elem[4]+"</b></p><p><b class='listicon_group'>"+elem[1]+"</b></p><div>"+HTML.decode(elem[2])+"</div><p><a href='groups-"+elem[0]+"' style='font-size:12px; text-decoration:underline; color:#4b8ab5;'><i>Просмотреть всех...</i></a></p></div>");
	$.fancybox({ 'content' : renderdiv });
}

  function reportwindow() {
    $(".reportwindow h1").html("Выгрузка студента");
	$("html, body").animate({ scrollTop: 0 });
    $(".fillblack, .reportwindow").fadeIn(400);
    $("#add_surname").focus();
	$.fancybox.close();
  }

  function changeevents() {
	  if($("#report_events").prop("checked") == true) { $(".report_events_window").slideDown(); }
	  else { $(".report_events_window").slideUp(); }
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
      global_access = data.access;
			globalsid = data.id;
      global_sinid = data.sinid;
			global_surname = data.surname;
			global_firstname = data.firstname;
			global_patronumic = data.patronymic;
			global_sex = data.sex;
			global_birthday = data.birthday;
			global_phone = data.phone;
			global_facid = data.department_id;
			global_groupid = data.group_id;
			global_course = data.educourse;
			global_level = data.edulevel;
			global_budget = data.budget;
			global_groupnum = data.groupnum;

			addbirthday = "";
			if(data.birthday !== "unknown") { addbirthday = " <b>("+data.birthday+")</b>"; }

			var infobox = $(".box_student_info");
			if(data.patronymic.length < 2) {
				infobox.html("<b style='text-transform:uppercase; color:#0081ca;'>"+data.surname+" "+data.firstname+"</b>"+addbirthday);
			} else {
				infobox.html("<b style='text-transform:uppercase; color:#0081ca;'>"+data.surname+" "+data.firstname+" "+data.patronymic+"</b>"+addbirthday);
			}
			var edulevel;
			if(data.edulevel == "b") { edulevel = "бак."; }
			else if(data.edulevel == "s") { edulevel = "спец."; }
			else if(data.edulevel == "m") { edulevel = "маг."; }
			else if(data.edulevel == "c") { edulevel = "СПО"; }
			if(global_budget == "y") { curbudget = "бюд."; } else { curbudget = "пвз"; }
			infobox.append("<br /><b>"+data.department+" ("+data.group+") - "+data.groupnum+"</b>; <i>"+edulevel+"</i>"); // , "+curbudget+"
			$(".box_student h1").html("Карта активиста (ID: "+HTML.encode(data.sinid)+")");
			if(data.phone) {
				formatnewphone = data.phone;
				formatnewphone = formatnewphone.replace(/(\d{3})(\d{3})(\d{2})(\d{2})/, '($1)$2-$3-$4');
				if(data.phone == "unknown") { /*infobox.append("<br />тел.: <b><span style=\"color:red;\">неизвестный</span></b>");*/ }
				else { infobox.append("<br /><b>тел.:</b> <b style='text-decoration:underline;'><a href='tel:+7"+formatnewphone+"'>+7"+formatnewphone+"</a></b>"); }
			}

			var infobox_lists = $(".box_student_lists");
			infobox_lists.html("");
			if(data.lists.length == 0) { infobox_lists.hide(); }
			else {
				infobox_lists.show();
				for (var c = 0; c < data.lists.length; c++) {
          _addicon = 'svg';
          l_icon = data.lists[c][3].split('.');
          if(l_icon[1] !== 'svg') { _addicon = 'img'; }
					$(".box_student_lists").append('<div class="greybox"><div class="event_info_inner" style="margin-bottom:-1px;"><div><img class="inlinesvg" src="content/'+_addicon+'/'+data.lists[c][3]+'" alt="'+data.lists[c][1]+'" style="margin:0 8px 0 0; vertical-align:middle;"><span style="display:inline-block; vertical-align:middle; padding:3px 0 0 0; color:#666;"><a target="_blank" href="groups-'+data.lists[c][0]+'">'+data.lists[c][1]+'</a></span></div></div></div>');
				}
			}

			if(data.events) {
			  var tablehead = '<tr class="table_head"><td class="hideit" width=""><b>№</b></td><td class="hideit" width="12%"><b>Дата</b></td><td><b>Наименование мероприятия</b></td><td width="17%"><b>Статус</b></td><td class="event_holder" width="17%"><b>Ответственный</b></td><td width="7%"><b>Баллы</b></td><!--<td width="4%"></td>--></tr>';
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
				/*if(getdata[i].isedit == "yes") {
				  tdaus = $('<td/>');
				  tdaus.addClass("center");
				  aus = $('<a/>');
				  aus.addClass("a_icon_unset");
				  aus.attr("href","javascript:delActivity_rating("+getdata[i].aid+");");
				  aus.append("<i class=\"icon-remove\"></i>");
				  tdaus.append(aus);
				  tr.append(tdaus);
				}
				else { tr.append("<td class=\"center\"></td>"); }*/
				$('.owntable').append(tr);
			  }
			} else {
			  $(".box_student_add").append("<p class=\"center\"><b>Мероприятий нет</b></p>");
			}

			var incontent = $(".box_student").html();

			if(ifnothistory !== 1) {
				history.pushState(null, data.sinid, "/rating#"+data.sinid);

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
			$.fancybox({ 'content' : m_error(data.error) });
		  }

		}
    });
  }

  function findstudents() {
    si = $(".searchinput").val();
    maycode = /^[0-9]{10}$/;
    if(!maycode.test(si)) {
      getstudby = "i";
      if((si == "") || (si == " ")) { cursearch = ""; }
      else { cursearch = si; }
      cursearchdep = $(".searchdep option:selected").val();
      cursearchcourse = $(".searchcourse option:selected").val();
	  cursearchtagA = $(".searchtagA option:selected").val();
      page_rating(1);
    } else {
      cursearchdep = "";
      cursearchcourse = "";
	  cursearchtagA = "";
      getstudby = "b";
      student(si);
      $(".searchinput").val("");
    }
  }

  function editwindow_rating() {
    $("#savebtn").show();
    $("#regbtn").hide();
    $(".addwindow h1").html("Редактирование данных студента");
    $("#add_id").val(HTML.decode(global_sinid));
    $("#add_surname").val(HTML.decode(global_surname));
    $("#add_firstname").val(HTML.decode(global_firstname));
    $("#add_patronymic").val(HTML.decode(global_patronumic));
    $("#add_sex [value='"+global_sex+"']").prop("selected", true);
    if(global_birthday !== "unknown") { $("#add_birthday").val(global_birthday); }
    $("#add_phone").val(global_phone);
    $("#add_phone").mask("(999)999-99-99");
    $("#add_faculty [value='"+global_facid+"']").prop("selected", true);
    loadgroups(1);
    $("#add_course [value='"+global_course+"']").prop("selected", true);
    $("#add_level [value='"+global_level+"']").prop("selected", true);
    $("#add_budget [value='"+global_budget+"']").prop("selected", true);
    $("#add_groupnum").val(global_groupnum);
    $("#add_id, #add_surname, #add_firstname, #add_patronymic").prop("disabled", false).attr("style","");
    if(global_access !== 1) {
      $("#add_id, #add_surname, #add_firstname, #add_patronymic").prop("disabled", true).attr("style","border:none;");;
    }
    $.fancybox.close();
    $(".fillblack, .addwindow").fadeIn(400);
  }

  function oncheck() {
    if(($("#add_surname").val() == "") || ($("#add_firstname").val() == "") || ($("#add_patronymic").val() == "") || ($("#add_sex option:selected").val() == "") || ($("#add_birthday").val() == "") || ($("#add_phone").val() == "") || ($("#add_faculty option:selected").val() == "") || ($("#add_group option:selected").val() == "") || ($("#add_course option:selected").val() == "") || ($("#add_level option:selected").val() == "") || ($("#add_budget option:selected").val() == "") || ($("#add_groupnum").val() == "")) {
      $.fancybox({ 'content' : 'Заполните все поля' });
    } else {
      isoncheck = "y";

      $(".check_surname").text($("#add_surname").val());
      $(".check_firstname").text($("#add_firstname").val());
      $(".check_patronymic").text($("#add_patronymic").val());
      $(".check_sex").text($("#add_sex option:selected").text());
      $(".check_birthday").text($("#add_birthday").val());
      $(".check_phone").text("+7 "+$("#add_phone").val());
      $(".check_faculty").text($("#add_faculty option:selected").text());
      $(".check_group").text($("#add_group option:selected").text());
      $(".check_course").text($("#add_course option:selected").text()+" ("+$("#add_level option:selected").text()+")");
      $(".check_budget").text($("#add_budget option:selected").text());
      $(".check_groupnum").text($("#add_groupnum").val());

      $(".checkwindow").fadeIn(200);
      /*
        var
          el = document.documentElement
          , rfs =
             el.requestFullScreen
            || el.webkitRequestFullScreen
            || el.mozRequestFullScreen
        ;
        rfs.call(el);
      */
      $(document).keydown(function(event) {
      if(event.keyCode == 13) {
        if(isoncheck == "y") {
          $(".forgetcode").fadeIn(200);
          $(".forgetcode input").focus();
        }
      }
      else if(event.keyCode == 27) {
        if(isoncheck == "y") {
          $(".checkwindow").fadeOut(200);
          $(".forgetcode").fadeOut(200);
          isoncheck = "n";
        }
      }
      });
    }
  }

  function onsave() {
    if((($("#add_id").val() == "") || $("#add_surname").val() == "") || ($("#add_firstname").val() == "") || ($("#add_patronymic").val() == "") || ($("#add_sex option:selected").val() == "") || ($("#add_faculty option:selected").val() == "") || ($("#add_group option:selected").val() == "") || ($("#add_course option:selected").val() == "") || ($("#add_level option:selected").val() == "") || ($("#add_budget option:selected").val() == "") || ($("#add_groupnum").val() == "")) {
      $.fancybox({ 'content' : 'Заполните все поля' });
    } else {
      phoneformat = $("#add_phone").val().replace(/[-()]/g,"");
      $.ajax({
		  data: {
			act: "edituser",
			id: globalsid,
			type: "a",
			as: "n",
			phone: phoneformat,
      sin: $("#add_id").val(),
			surname: $("#add_surname").val(),
			firstname: $("#add_firstname").val(),
			patronymic: $("#add_patronymic").val(),
			sex: $("#add_sex option:selected").val(),
			birthday: $("#add_birthday").val(),
			depid: $("#add_group option:selected").val(),
			groupnum: $("#add_groupnum").val(),
			course: $("#add_course option:selected").val(),
			level: $("#add_level option:selected").val(),
			budget: $("#add_budget option:selected").val()
		  },
		  success: function(answer) {
			var data = JSON.parse(answer);
			if(data.error == "ok") {
			  closemw("addwindow");
			  page_rating((curpage+1), 1);
			  $.fancybox({
				'afterClose':function () {
					student(globalsid);
				},
				'content' : m_ok('Данные успешно сохранены!')
			  });
			} else {
			  $.fancybox({ 'content' : m_error(data.error) });
			}
		  }
      });
    }
  }
  function changebook() {
    $(".changebook_holder").html(HTML.decode(global_surname)+" "+HTML.decode(global_firstname)+" "+HTML.decode(global_patronumic)+" ("+global_birthday+")");
    $(".changebook_info").hide();
	$("#newbookcode").prop('disabled', false);
	$("html, body").animate({ scrollTop: 0 });
    $(".fillblack, .changebook").fadeIn(400);
    $.fancybox.close();
    $("#newbookcode").focus();
  }
  function trychangebook() {
    newcodeline = $("#newbookcode").val();
    $("#newbookcode").val("");
    newcodechech = /^[0-9]{10}$/;
    if(!newcodechech.test(newcodeline)) {
    $(".changebook_info span").html("Неверный формат кода");
    $(".changebook_info").slideDown();
    } else {
      $.ajax({
      data: {
        act: "changebook",
        sid: globalsid,
        newcode: newcodeline
      },
      success: function(answer) {
        var data = JSON.parse(answer);
        if(data.error == "ok") {
          $("#newbookcode").prop('disabled', true);
          $(".changebook_info").slideUp();
          closemw("changebook");
		  page_rating((curpage+1), 1);
          $.fancybox({
            'afterClose':function () {
				student(globalsid);
            },
            'content' : m_ok('Операция успешно выполнена!')
          });
        } else {
          $(".changebook_info span").html(data.error);
          $(".changebook_info").slideDown();
          $("#newbookcode").focus();
        }
	  }
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
        uid: globalsid,
        as: _ifDisplay
      },
      success: function(answer) {
        var data = JSON.parse(answer);
        if(data.error == "ok") {
          $.fancybox({
			  'afterClose':function () {
				student(globalsid);
            },
            'content' : m_ok('Доступ успешно восстановлен!')
          });
		} else if(data.error == "ok_notify") {
			$.fancybox({
				'afterClose':function () {
				student(globalsid);
            },
				'content' : m_error(data.notifyres)
			  });
		} else if(data.error == "ok_display") {
			$.fancybox({
				'afterClose':function () {
				student(globalsid);
            },
				'content' : '<h1 class="render_massage_title"><i class="icon-ok"></i> Успешно!</h1><p class="render_massage">Доступ успешно восстановлен!<br>Логин: <b>'+data.l+'</b><br>Пароль: <b>'+data.p+'</p>'
			  });
		} else {
          $.fancybox({ 'content' : m_error(data.error) });
        }
	  }
    });
  }

  function loadgroups(ifedit) {
    groupel = $("#add_group");
    groupel.html("");
    if($("#add_faculty option:selected").val() !== "") {
      $.ajax({
		  data: {
			act: "getgroups",
			f: $("#add_faculty option:selected").val()
		  },
		  success: function(answer) {
			var data = JSON.parse(answer);
			if(!data.error) {
			  groupel.append('<option value="" selected></option>');
			  for (var i = 0; i < data.length; i++) {
				groupel.append('<option value="'+data[i].id+'">'+data[i].name+'</option>');
			  }
			}
			else { $.fancybox({ 'content' : m_error(data.error) }); }
			if(ifedit == "1") { $("#add_group [value='"+global_groupid+"']").prop("selected", true); }
			else { $('#add_group').focus(); }
		  }
      });
    }
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
									window.location = "/";
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
				var members = data.members;
				if(data.icon !== "n") {
          _addicon = 'svg';
          l_icon = data.icon.split('.');
          if(l_icon[1] !== 'svg') { _addicon = 'img'; }
					$(".titleline h1").prepend("<img class='inlinesvg inlinesvg_title' src='content/"+_addicon+"/"+data.icon+"' alt='' />");
				}
        if(data.isediticon) {
          if(data.public == "y") { $(".listpublicicon").attr("publicset","y"); $(".listpublicicon").html("Скрыть значок группы в Рейтинге"); }
          else { $(".listpublicicon").attr("publicset","n"); $(".listpublicicon").html("Отобразить значок группы в Рейтинге"); }
        }
				if(data.isedit == "y") {
					$(".editSortFIO").html("<b><a href='' onclick='sortFIO(); return false;'>ФИО студента</a></b>");
					ue = data.rights[3];
					uv = data.rights[4];
					rights_ue = "";
					if(data.rights[0] == "r1") { rights_ue = "только я (создатель)"; }
					if(data.rights[0] == "r2") { rights_ue = "все сотрудники образовательной организации"; }
					if(data.rights[0] == "r3") {
						rights_ue = "все сотрудники "+depname+"";
						if(data.rights[3].length !== 0) { rights_ue += " и <i><a href='' onclick='showSome(\"ue\"); return false;'>некоторые пользователи</a></i>"; }
					}
					if(data.rights[0] == "r4") { rights_ue = "<i><a href='' onclick='showSome(\"ue\"); return false;'>некоторые пользователи</a></i>"; }

					rights_v = "";
					if(data.rights[1] == "r2" && data.rights[2] == "r1") { rights_v = "все студенты и сотрудники образовательной организации"; }
					else {
						if(data.rights[2] == "r1") { rights_v = "все студенты образовательной организации"; }
						if(data.rights[2] == "r2") { rights_v = "только студенты из этой группы"; }
						if(data.rights[1] == "r1" && data.rights[2] == "r3") { rights_v = "только я (создатель)"; }
						if(data.rights[2] !== "r3") { rights_v += " и "; }
						if(data.rights[1] == "r2") { rights_v += "все сотрудники образовательной организации"; }
						if(data.rights[1] == "r3") {
							rights_v += "все сотрудники "+depname+"";
							if(data.rights[4].length !== 0) { rights_v += " и <i><a href='' onclick='showSome(\"uv\"); return false;'>некоторые пользователи</a></i>"; }
						}
						if(data.rights[1] == "r4") { rights_v += "<i><a href='' onclick='showSome(\"uv\"); return false;'>некоторые пользователи</a></i>"; }
					}
					$(".list_info").append($("<div/>").addClass("span12 inforow").html("<div><b>Редактирование:</b> <span>"+rights_ue+"</span></div><div><b>Просмотр:</b> <span>"+rights_v+"</span></div><div><b>Кол-во студентов:</b> <span class='memberscount'>"+members.length+"</span></div>"));

					$(".searchinput").autocomplete({
					  source: function(request, response) {
							  $.ajax({
								  url: "quick",
								  data: {
									  act: 'a',
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
						if($(".personslist .pl_row[hid='"+ui.item.id+"']").length) {
							$.fancybox({ 'content' : '<p align="center"><b>'+ui.item.value+'</b> уже есть в списке.</p><p align="center"><button onclick="$.fancybox.close(); $(\'.searchinput\').val(\'\'); $(\'.searchinput\').focus();" class="btn existstudent">OK</button></p>' });
							$(".existstudent").focus();
							return true;
						}
						addmembers(ui.item.id);
						$(this).val("");
						$(this).focus();
						return false;
					  }
					});
					$(".searchbox").show();
					$(".searchinput").focus();
				} else {
					$(".list_info").append($("<div/>").addClass("span12 inforow").html("<div><b>Кол-во студентов:</b> <span class='memberscount'>"+members.length+"</span></div>"));
				}

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
			} else { $.fancybox({ 'content' : m_error(data.error) }); }
		}
	});
}

function listpublicicon() {
	sendpublic = "y";
	if($(".listpublicicon").attr("publicset") == "y") { sendpublic = "n"; }
	$.ajax({
		method: "post",
		url: "operator2",
		data: {
			act: "listpublicicon",
			lid: listid,
			public: sendpublic
		},
		beforeSend: function() { startLoading(); },
		success: function(answer) {
			var data = (JSON.parse(answer));
			if(data.error !== "ok") { $.fancybox({ 'content' : m_error(data.error) }); }
			else {
				if(data.public == "y") { $(".listpublicicon").attr("publicset","y"); $(".listpublicicon").html("Скрыть значок группы в Рейтинге"); }
				else { $(".listpublicicon").attr("publicset","n"); $(".listpublicicon").html("Отобразить значок группы в Рейтинге"); }
			}
		}
	});
}

function sortFIO() {
	sortUsingNestedText($(".personslist"), ".pl_row", ".name");
	sortmembers();
}

function sortable() {
	$(".personslist").sortable({
		handle: ".name",
		start: function(event, ui) { ui.item.addClass("highlight"); },
		stop: function(event, ui) { ui.item.removeClass("highlight"); sortmembers(); },
		update: function(event, ui) { }
	});
}

function applychanges(elem,elemid) {
	newtext = elem.getContent();
	newtext = newtext.replace(/\\n/g, "\\n")
		   .replace(/\\'/g, "\\'")
		   .replace(/\\"/g, '\\"')
		   .replace(/\\&/g, "\\&")
		   .replace(/\\r/g, "\\r")
		   .replace(/\\t/g, "\\t")
		   .replace(/\\b/g, "\\b")
		   .replace(/\\f/g, "\\f");
	$.ajax({
		method: "post",
		url: "operator2",
		data: {
			act: "editinlist",
			lid: listid,
			hid: elemid,
			content: newtext
		},
		beforeSend: function() { startLoading(); },
		success: function(answer) {
			var data = (JSON.parse(answer));
			if(data.error !== "ok") { $.fancybox({ 'content' : m_error(data.error) }); }
		}
	});
}

function editor(elemid) {
	oldcontent = "<p></p>";
	tinymce.init({
		selector: ".pl_row[hid='"+elemid+"'] .comment",
		inline: true,
		mode : "textareas",
		plugins: "textcolor",
		toolbar: "bold italic underline forecolor backcolor removeformat",
		menubar: false,
		paste_word_valid_elements: "b,strong,i",
		setup : function(ed){
			ed.on('focus', function(e) { oldcontent = ed.getContent(); });
			ed.on('blur', function(e) { if(oldcontent !== ed.getContent()) { applychanges(ed,elemid); } });
		},
		init_instance_callback : function() { /*tinymce.execCommand('mceFocus',false,".comment");*/ }
	});
}

function addmembers(memberid) {
	$.ajax({
		method: "post",
		url: "operator2",
		data: {
			act: "addinlist",
			lid: listid,
			hid: memberid,
		},
		beforeSend: function() {
			newrow = '<div class="pl_row pl_loading" loadid="'+memberid+'" style="display:none;">\n<div class="pl_row_box">\n<div class="row-fluid"><img src="img/loading.gif" alt="" width="20px" /></div>\n</div>\n';
			$('.personslist').prepend(newrow);
			$(".personslist .pl_loading[loadid='"+memberid+"']").slideDown();
		},
		success: function(answer) {
			var data = (JSON.parse(answer));
			if(data.error == "ok") {
				$(".personslist .pl_loading[loadid='"+memberid+"']").slideUp(100, function() {
					$(".personslist .pl_loading[loadid='"+memberid+"']").remove();
					newrow = '<div class="pl_row pl_editable" hid="'+data.id+'" style="display:none;">\n<div class="pl_row_box">\n<div class="row-fluid">\n<div class="span5 name"><a href="" onclick="student('+memberid+',1); return false;">'+data.name+'</a><div>'+data.afrom+'</div></div>\n<div class="span6 commentside"><div class="comment"></div></div>\n<div class="span1 funcside"><a class="btn_edit" href="" onclick="delmembers('+memberid+'); return false;"><i class="icon-remove"></i></a></div>\n</div>\n</div>\n</div>\n';
					$('.personslist').prepend(newrow);
					$(".personslist .pl_row[hid='"+data.id+"']").slideDown();
					$(".memberscount").html(parseInt($(".memberscount").html())+1);
					$('.textalert').slideUp();
					editor(memberid);
					sortable();
				 });
			} else {
				$(".personslist .pl_loading[loadid='"+memberid+"']").slideUp(100, function() {
					$(".personslist .pl_loading[loadid='"+memberid+"']").remove();
				});
				$.fancybox({ 'content' : m_error(data.error) });
			}
		}
	});
}

function sortmembers() {
	neworder = [];
	curorder = $(".personslist").children(".pl_row[hid != '']");
	for(var i = 0; i < curorder.length; i++) {
		neworder.push(curorder.eq(i).attr("hid"));
	}
	$.ajax({
		method: "post",
		url: "operator2",
		data: {
			act: "sortlist",
			lid: listid,
			order: JSON.stringify(neworder)
		},
		beforeSend: function() {},
		success: function(answer) {
			var data = (JSON.parse(answer));
			if(data.error !== "ok") { $.fancybox({ 'content' : m_error(data.error) }); }
		}
	});
}

function delmembers(memberid) {
	curmemberid = memberid;
	render_massage("Удалить из группы?","<div class='render_massage_buttons'><a class='btn1' href='' onclick='delmembersYES(); $.fancybox.close(); return false;' style='background:#f36b69;'>Удалить</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>");
}

function delmembersYES() {
	$.ajax({
		method: "post",
		url: "operator2",
		data: {
			act: "delfromlist",
			lid: listid,
			hid: curmemberid
		},
		beforeSend: function() {
			$(".personslist .pl_row[hid='"+curmemberid+"']").slideUp(100);
		},
		success: function(answer) {
			var data = (JSON.parse(answer));
			if(data.error == "ok") {
				$(".personslist .pl_row[hid='"+curmemberid+"']").slideUp(100, function() {
					$(".personslist .pl_row[hid='"+curmemberid+"']").remove();
					$(".memberscount").html(parseInt($(".memberscount").html())-1);
					if($(".personslist .pl_row[hid != '']").length == 0) { $('.textalert').slideDown(); }
				});
			} else {
				$.fancybox({ 'content' : m_error(data.error) });
				$(".personslist .pl_row[hid='"+curmemberid+"']").slideDown(100);
			}
		}
	});
}

/* EVENT */
  function changerole(activityid) {
    $.ajax({
      data: {
        act: "editrole",
        aid: activityid,
        rid: $('tr[aid="'+activityid+'"] .selectrole option:selected').val(),
		cv: $('tr[aid="'+activityid+'"] .complexcheck').prop("checked")
      },
		success: function(answer) {
		  var data = JSON.parse(answer);
		  if(data.error == "ok") { $(".state_first, .state_second").slideUp(); si_already = ""; if(ismobile()) { $("#add_activity").focus(); } }
		  else { $.fancybox({ 'content' : m_error(data.error) }); }
		}
    });
  }

  function init_event_control() {
	$(".regbox").hide();
    $(".regnewbtn").parent().parent().hide();
    $("#add_activity").blur(function() {
      $(this).val("");
      $(this).attr("placeholder","НАЖМИТЕ ДЛЯ ДОБАВЛЕНИЯ СТУДЕНТА");
    });
    $("#add_activity").focus(function() {
      $(this).attr("placeholder","ДОБАВИТЬ СТУДЕНТА ПО ФИО...");
    });
    $("#add_activity").keydown(function(e) { if(e.keyCode == 13) { findbc(); } });

    $("#add_activity").autocomplete({
      source: function(request, response) {
              $.ajax({
                  url: "quick",
                  data: {
                      act: 'a',
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
        $(this).val("");
        _add(ui.item.id);
          return false;
      }
    });

	page_event_control();
  }

  function page_event_control() {
    $.ajax({
      data: {
        act: "getinvolved",
        eid: curevent
      },
      beforeSend: function() { startLoading(); $('.textalert').hide(); },
		success: function(answer) {
		  var data = (JSON.parse(answer));
		  if(data.error == "ok") {
			$(".activitytable").html('<tr class="table_head"><td><b>ФИО [курс/группа]</b></td><td width="20%" class="curevent_role"><b>Роль</b></td><td width="4%" style="padding:3px !important;"><img src="img/muscle.svg?2" /></td><td width="14%" class="curevent_added"><b>Добавлено</b></td><td width="18%" class="curevent_by"><b>Фиксатор</b></td><td width="4%"></td></tr>');

			var getdata = (JSON.parse(answer)).einfo;

			var elevel, edates;
			if(getdata.level == "f") { elevel = "факультетский"; }
			else if(getdata.level == "u") { elevel = "университетский"; }
			else if(getdata.level == "c") { elevel = "городской"; }
			else if(getdata.level == "r") { elevel = "региональный"; }
			else if(getdata.level == "v") { elevel = "всероссийский"; }
			else if(getdata.level == "i") { elevel = "международный"; }

			$("h1").html(getdata.name);
			$("h1").append("<a href='#' class='headicon' onclick='printdata(); return false;'><i class='icon-print'></i></a>");
			$(".menu").after('<div class="row-fluid sz_event" style="margin-top:20px;"><a href="" class="span12 btn" onclick="printdata(); return false;">С/з по мероприятию</a></div><div class="row-fluid sz_event" style="margin-top:5px;"><button href="" class="span12 btn btn-success" onclick="printdata(1); return false;">С/з на освобождение</button></div>');
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

      // Addme
      if(data.addme.length > 0) {
        for (var i = 0; i < data.addme.length; i++) {
          if(data.addme[i].type == "w") { continue; }
          tr = $('<tr/>');
  				tr.attr("addme_id",data.addme[i].id);
          tr.attr("add_role",data.addme[i].role);
          if(data.addme[i].complex == "y") { tr.attr("add_complex","true"); }
          if(data.addme[i].complex == "n") { tr.attr("add_complex","false"); }
  				tr.append("<td><a href='' onclick='student("+data.addme[i].sid+",1); return false;'><b>" + data.addme[i].name + "</b></a> [" + data.addme[i].from + "]</td>");
  				tr.append('<td><select class="span12 selectrole"><option value="b">-</option><option value="u">участник</option><option value="p">призер</option><option value="w">победитель</option><option value="l">помощник организатора</option><option value="m">организатор</option><option value="h">главный организатор</option></select></td>');
  				tr.append('<td class="center"><input class="complexcheck" type="checkbox"></td>');
          var statuses = $('<td/>').attr("colspan", 2);
          for (var c = 0; c < data.addme[i].story.length; c++) {
            var status = $('<div/>').addClass("addme_status");
            if(data.addme[i].story[c].status == "n") {
              status.append(data.addme[i].comment);
              status.append("<span>"+data.addme[i].story[c].time+"</span>");
            }
            if(data.addme[i].story[c].status == "r") {
              status.append("<b>"+data.addme[i].story[c].name_from+" &#8594; "+data.addme[i].story[c].name_to+"</b>");
              status.append($("<i/>").html(data.addme[i].story[c].comment));
              status.append("<span>"+data.addme[i].story[c].time+"</span>");
            }
            statuses.append(status);
          }
          tr.append(statuses);
  				tr.append('<td><div class="btn-group btn_mini_options"><a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a><ul class="dropdown-menu"><li><a href="" onclick="addme_accept('+data.addme[i].id+'); return false;">Одобрить</a></li><li><a href="" onclick="addme_reassign('+data.addme[i].id+'); return false;">Переназначить</a></li><li><a href="" onclick="addme_cancel('+data.addme[i].id+'); return false;">Отказать</a></li></ul></div></td>');
  				$('.activitytable').append(tr);
          tr.addClass("rowyellow");
  				tr.find(".selectrole [value='"+data.addme[i].role+"']").prop("selected", true);
  				if(data.addme[i].complex == "y") { tr.find(".complexcheck").prop("checked", true); }
        }
      }

			if(data.allrows == '0') {
			  $('.textalert').show();
			} else {
			  var alist = data.alist;
			  for (var i = 0; i < alist.length; i++) {
  				tr = $('<tr/>');
  				tr.attr("aid",alist[i].a_id);
          tr.attr("uid",alist[i].a_uid);
  				tr.append("<td><a href='' onclick='student("+alist[i].a_uid+",1); return false;'><b>" + alist[i].a_name + "</b></a> [" + alist[i].a_from + "]</td>");
  				selectblock = "";
  				if(alist[i].a_edit == "n") { selectblock = " disabled=\"disabled\""; }
  				tr.append('<td><select class="span12 selectrole" onchange="changerole('+alist[i].a_id+')"'+selectblock+'><option value="b">-</option><option value="u">участник</option><option value="p">призер</option><option value="w">победитель</option><option value="l">помощник организатора</option><option value="m">организатор</option><option value="h">главный организатор</option></select></td>');
  				tr.append('<td class="center"><input class="complexcheck" type="checkbox" onchange="changerole('+alist[i].a_id+')"'+selectblock+'></td>');
  				tr.append("<td class=\"curevent_added\">" + alist[i].a_time + "</td>");
  				tr.append("<td class=\"curevent_by\">" + alist[i].a_by + "</td>");
  				if(alist[i].a_edit == "y") { tr.append("<td><i class='icon-remove' onclick='delActivity_event("+alist[i].a_id+")'></i></td>"); }
  				else { tr.append("<td></td>"); }
  				$('.activitytable').append(tr);
  				tr.find(".selectrole [value='"+alist[i].a_role+"']").prop("selected", true);
  				if(alist[i].a_complex == "y") { tr.find(".complexcheck").prop("checked", true); }
			  }
        if(data.addme.length > 0) {
          for (var i = 0; i < data.addme.length; i++) {
            if(data.addme[i].type == "a") { continue; }
            $("tr[uid='"+data.addme[i].sid+"']").attr("addme_id",data.addme[i].id);
            $("tr[uid='"+data.addme[i].sid+"'] td:nth-child(1), tr[uid='"+data.addme[i].sid+"'] td:nth-child(2), tr[uid='"+data.addme[i].sid+"'] td:nth-child(3), tr[uid='"+data.addme[i].sid+"'] td:nth-child(6)").attr("rowspan", 2);
            $("tr[uid='"+data.addme[i].sid+"']").after($("<tr/>").addClass("rowyellow"));
            $("tr[uid='"+data.addme[i].sid+"']").addClass("rowyellow");
            $("tr[uid='"+data.addme[i].sid+"']").attr("new_role",data.addme[i].role);
            $("tr[uid='"+data.addme[i].sid+"'] td:nth-child(6)").html('<div class="btn-group btn_mini_options"><a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret" style=""></span></a><ul class="dropdown-menu"><li><a href="" onclick="addme_accept('+data.addme[i].id+'); return false;">Одобрить</a></li><li><a href="" onclick="addme_reassign('+data.addme[i].id+'); return false;">Переназначить</a></li><li><a href="" onclick="addme_cancel('+data.addme[i].id+'); return false;">Отказать</a></li></ul></div>');
            if(data.addme[i].complex == "y") { $("tr[uid='"+data.addme[i].sid+"']").attr("new_complex","true"); }
            if(data.addme[i].complex == "n") { $("tr[uid='"+data.addme[i].sid+"']").attr("new_complex","false"); }
            $("tr[uid='"+data.addme[i].sid+"']").attr("old_role", $("tr[uid='"+data.addme[i].sid+"'] select option:selected").val());
            $("tr[uid='"+data.addme[i].sid+"']").attr("old_complex", $("tr[uid='"+data.addme[i].sid+"'] .complexcheck").prop("checked"));
            $("tr[uid='"+data.addme[i].sid+"'] select").prop("disabled", false);
            $("tr[uid='"+data.addme[i].sid+"'] select").removeAttr("onchange");
            $("tr[uid='"+data.addme[i].sid+"'] .complexcheck").removeAttr("onchange");
            if($("tr[uid='"+data.addme[i].sid+"'] select option:selected").val() !== data.addme[i].role) {
              $("tr[uid='"+data.addme[i].sid+"'] td:nth-child(2)").prepend("<div class='addme_new_role' style='text-decoration:line-through;'>"+$("tr[uid='"+data.addme[i].sid+"'] select option:selected").text()+"</div>");
              $("tr[uid='"+data.addme[i].sid+"'] select option[value='"+data.addme[i].role+"']").prop("selected", true);
            }
            var cur_boolean_new_complex = false;
            if($("tr[uid='"+data.addme[i].sid+"']").attr("new_complex") == "true") { cur_boolean_new_complex = true; }
            if(cur_boolean_new_complex !== $("tr[uid='"+data.addme[i].sid+"'] .complexcheck").prop("checked")) {
              if(cur_boolean_new_complex == true) {
                $("tr[uid='"+data.addme[i].sid+"'] td:nth-child(3)").prepend("<div style='position:relative;'><div class='complexcheck_addme_no'></div></div>");
                $("tr[uid='"+data.addme[i].sid+"'] .complexcheck").prop("checked",true);
              } else {
                $("tr[uid='"+data.addme[i].sid+"'] td:nth-child(3)").prepend("<div style='position:relative;'><div class='complexcheck_addme_no complexcheck_addme_no2'></div></div>");
                $("tr[uid='"+data.addme[i].sid+"'] .complexcheck").prop("checked",false);
              }
            }
            var statuses = $('<td/>').attr("colspan", 2).addClass("yellowcolspan");
            for (var c = 0; c < data.addme[i].story.length; c++) {
              var status = $('<div/>').addClass("addme_status");
              if(data.addme[i].story[c].status == "n") {
                status.append(data.addme[i].comment);
                status.append("<span>"+data.addme[i].story[c].time+"</span>");
              }
              if(data.addme[i].story[c].status == "r") {
                status.append("<b>"+data.addme[i].story[c].name_from+" &#8594; "+data.addme[i].story[c].name_to+"</b>");
                status.append($("<i/>").html(data.addme[i].story[c].comment));
                status.append("<span>"+data.addme[i].story[c].time+"</span>");
              }
              statuses.append(status);
            }
            $("tr[uid='"+data.addme[i].sid+"']").next().append(statuses);
          }
        }
			}
		  } else { $.fancybox({ 'content' : m_error(data.error) }); }
		}
    });
  }

  function addme_accept(addme_id) {
    var addme_tr = $("tr[addme_id='"+addme_id+"']");
     var addme_accept_text = "Одобрить полностью заявку?";
     var addme_accept_status = true;
     var addme_change_role = false;
     var addme_change_complex = false;
     if(addme_tr.is("[new_role]")) {
       if(addme_tr.attr("new_role") == addme_tr.find("select option:selected").val())  {
         addme_change_role = true;
       }
       var cur_boolean_new_complex = false;
       if(addme_tr.attr("new_complex") == "true") { cur_boolean_new_complex = true; }
       if(cur_boolean_new_complex == addme_tr.find(".complexcheck").prop("checked")) {
         addme_change_complex = true;
       }
       if(addme_change_role == false || addme_change_complex == false) {
         addme_accept_text = "Вы одобрили предложенные изменения частично. Одобрить заявку частично?";
         var cur_boolean_old_complex = false;
         if(addme_tr.attr("old_complex") == "true") { cur_boolean_old_complex = true; }
         if(addme_tr.attr("old_role") == addme_tr.find("select option:selected").val() && cur_boolean_old_complex == addme_tr.find(".complexcheck").prop("checked")) {
           addme_accept_text = "Вы вернули предложенные изменения на те, что уже были внесены в систему. В таком случае откажите заявителю.";
           addme_accept_status = false;
         }
       }
     } else {
       if(addme_tr.attr("add_role") == addme_tr.find("select option:selected").val())  {
        addme_change_role = true;
       }
       var cur_boolean_add_complex = false;
       if(addme_tr.attr("add_complex") == "true") { cur_boolean_add_complex = true; }
       if(cur_boolean_add_complex == addme_tr.find(".complexcheck").prop("checked")) {
         addme_change_complex = true;
       }
       if(addme_change_role == false || addme_change_complex == false) {
         addme_accept_text = "Вы собираетесь одобрить предложенную заявку частично. Одобрить заявку частично?";
       }
     }
     var addme_accept_text_render = "<div class='render_massage'>"+addme_accept_text+"</div>";
     if(addme_accept_status == true) {
       addme_accept_text_render += "<div class='render_massage_buttons'><a class='btn1' href='' onclick='addme_acceptYES("+addme_id+"); $.fancybox.close(); return false;' style='background:#f36b69;'>Одобрить</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>";
     }
     render_massage("Заявка",addme_accept_text_render);
  }

  function addme_acceptYES(addme_id) {
    var addme_tr = $("tr[addme_id='"+addme_id+"']");
    $.ajax({
      data: {
        act: "addme_accept",
        id: addme_id,
        rid: addme_tr.find("select option:selected").val(),
        complex: addme_tr.find(".complexcheck").prop("checked")
      },
      beforeSend: function() { startLoading(); $('.textalert').hide(); },
  		success: function(answer) {
  		  var data = (JSON.parse(answer));
  		  if(data.error == "ok") {
          var gotrow = data.a_info;
          tr = $('<tr/>');
          tr.attr("aid",gotrow.a_id);
		      tr.css("display","none");
          tr.append("<td><a href='' onclick='student("+gotrow.a_uid+",1); return false;'><b>" + gotrow.a_name + "</b><a> [" + gotrow.a_from + "]</td>");
          tr.append('<td><select class="span12 selectrole" onchange="changerole('+gotrow.a_id+')"><option value="b">-</option><option value="u">участник</option><option value="p">призер</option><option value="w">победитель</option><option value="l">помощник организатора</option><option value="m">организатор</option><option value="h">главный организатор</option></select></td>');
		      tr.append('<td class="center"><input class="complexcheck" type="checkbox" onchange="changerole('+gotrow.a_id+')"></td>');
          tr.append("<td class=\"curevent_added\">" + gotrow.a_time + "</td>");
          tr.append("<td class=\"curevent_by\">" + gotrow.a_by + "</td>");
          tr.append("<td><i class='icon-remove' onclick='delActivity_event("+gotrow.a_id+")'></i></td>");
          addme_tr.after(tr);
          tr.find(".selectrole [value='"+gotrow.a_role+"']").prop("selected", true);
          var complexcheck = false;
          if(gotrow.a_complex == "y") { complexcheck = true; }
          tr.find(".complexcheck").prop("checked", complexcheck);

    		  $('tr[aid="'+gotrow.a_id+'"]').animate({'background-color': 'rgba(14,175,113,1.0)'}, 500, function() {
    				$('tr[aid="'+gotrow.a_id+'"]').animate({'background-color': "rgba(255,255,255,0.7)"}, 2000, function() {
    					$('tr[aid="'+gotrow.a_id+'"]').each(function() { $(this).removeAttr("style"); });
    				});
    		  });

    		  $("tr[aid='"+gotrow.a_id+"']").slideRow('down');
    		  $("tr[addme_id='"+addme_id+"']").slideRow('up', 500, function() {
            if(addme_tr.is("[new_role]")) {
              addme_tr.next().next().remove();
            }
            $("tr[addme_id='"+addme_id+"']").remove();
          });
          $(".involvednum").html(parseInt($(".involvednum").html())+1);
          $('.textalert').hide();
        } else { $.fancybox({ 'content' : m_error(data.error) }); }
    	}
    });
  }

  function addme_reassign(addme_id) {
    $(".commentwindow h1").html("Переназначение заявки");
    $("#addme_holder").parent().parent().show();
    $("#addme_holderend").html('<span style="color:#924242;">Еще не выбран</span>');
    $("#addme_holderend").attr("hid","");
    $("#addme_holder").autocomplete({
      source: function(request, response) {
          $.ajax({
            url: "quick",
            data: {
                act: 'h',
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
  		  if($(this).attr("id") == "addme_holder") {
    			$("#addme_holderend").html(ui.item.value);
    			$("#addme_holderend").attr("hid", ui.item.id);
    			$(this).val("");
          $("#addme_comment").focus();
    			return false;
  		  }
      }
    });
    $("#addme_comment").val("");
    $("#addme_comment").attr("placeholder","Сообщение пользователю");
    $("#addme_savebtn").attr("onclick", "addme_reassignYES("+addme_id+"); return false;").html("Переназначить");
    $("html, body").animate({ scrollTop: 0 });
    $(".fillblack, .commentwindow").fadeIn(400);
    $("#addme_holder").focus();
  }

  function addme_reassignYES(addme_id) {
    if($("#addme_holderend").attr("hid") == "" || $("#addme_comment").val().trim() == "") { return false; }
    var addme_tr = $("tr[addme_id='"+addme_id+"']");
    $.ajax({
      data: {
        act: "addme_reassign",
        id: addme_id,
        hid: $("#addme_holderend").attr("hid"),
        comment: $("#addme_comment").val().trim()
      },
      beforeSend: function() { startLoading(); $('.textalert').hide(); },
  		success: function(answer) {
  		  var data = (JSON.parse(answer));
  		  if(data.error == "ok") {
          closemw("commentwindow");
          $.fancybox({
  				'afterClose':function () {
            if(data.remove == "y") {
              $("tr[addme_id='"+addme_id+"']").slideRow('up', 500, function() {
                if(addme_tr.is("[new_role]")) {
                  $("tr[addme_id='"+addme_id+"']").next().remove();
                }
                $("tr[addme_id='"+addme_id+"']").remove();
              });
            } else {
              var status = $('<div/>').addClass("addme_status");
              status.append("<b>"+data.story.name_from+" &#8594; "+data.story.name_to+"</b>");
              status.append($("<i/>").html(HTML.encode($("#addme_comment").val().trim())));
              status.append("<span>"+data.story.time+"</span>");
              addme_tr.find(".addme_status").last().after(status);
            }
  				},
  				'content' : m_ok('Заявка успешно переназначена!')
  			  });
        } else { $.fancybox({ 'content' : m_error(data.error) }); }
    	}
    });
  }

  function addme_cancel(addme_id) {
    $(".commentwindow h1").html("Отказ заявки");
    $("#addme_holder").parent().parent().hide();
    $("#addme_comment").val("");
    $("#addme_comment").attr("placeholder","Причина отказа");
    $("#addme_savebtn").attr("onclick", "addme_cancelASK("+addme_id+"); return false;").html("Отказать");
    $("html, body").animate({ scrollTop: 0 });
    $(".fillblack, .commentwindow").fadeIn(400);
    $("#addme_comment").focus();
  }

  function addme_cancelASK(addme_id) {
    if($("#addme_comment").val().trim() == "") { return false; }
    render_massage("Заявка","<div class='render_massage'>Отказать заявителю?</div><div class='render_massage_buttons'><a class='btn1' href='' onclick='addme_cancelYES("+addme_id+"); $.fancybox.close(); return false;' style='background:#f36b69;'>Отказать</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>");
  }

  function addme_cancelYES(addme_id) {
    var addme_tr = $("tr[addme_id='"+addme_id+"']");
    $.ajax({
      data: {
        act: "addme_cancel",
        id: addme_id,
        comment: $("#addme_comment").val().trim()
      },
      beforeSend: function() { startLoading(); $('.textalert').hide(); },
  		success: function(answer) {
  		  var data = (JSON.parse(answer));
  		  if(data.error == "ok") {
          closemw("commentwindow");
          $("tr[addme_id='"+addme_id+"']").slideRow('up', 500, function() {
            if(addme_tr.is("[new_role]")) {
              $("tr[addme_id='"+addme_id+"']").next().remove();
              window.location.reload();
            }
            $("tr[addme_id='"+addme_id+"']").remove();
          });
        } else { $.fancybox({ 'content' : m_error(data.error) }); }
    	}
    });
  }

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
			$(".activitytable").html('<tr class="table_head"><td><b>ФИО [курс/группа]</b></td><td width="40%"><b>Роль</b></td></tr>');

			var getdata = (JSON.parse(answer)).einfo;

			var elevel, edates;
			if(getdata.level == "f") { elevel = "факультетский"; }
			else if(getdata.level == "u") { elevel = "университетский"; }
			else if(getdata.level == "c") { elevel = "городской"; }
			else if(getdata.level == "r") { elevel = "региональный"; }
			else if(getdata.level == "v") { elevel = "всероссийский"; }
			else if(getdata.level == "i") { elevel = "международный"; }

			$("h1").html(getdata.name);
			$("h1").append("<a href='#' class='headicon' onclick='printdata(); return false;'><i class='icon-print'></i></a>");
			$(".menu").after('<div class="row-fluid sz_event" style="margin-top:20px;"><a href="" class="span12 btn" onclick="printdata(); return false;">С/з по мероприятию</a></div><div class="row-fluid sz_event" style="margin-top:0;"><button href="" class="span12 btn btn-success" onclick="printdata(1); return false;">С/з на освобождение</button></div>');
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

			if(data.allrows == '0') {
			  $('.textalert').show();
			} else {
			  var alist = data.alist;
			  for (var i = 0; i < alist.length; i++) {
				tr = $('<tr/>');
				addcomplex = '';
				tr.append("<td><a href=\"\" onclick=\"student("+alist[i].a_uid+",1); return false;\"><b>" + alist[i].a_name + "</b></a> [" + alist[i].a_from + "]</td>");
				var role;
				if(alist[i].a_complex == "y") { addcomplex = ' <img width="15px" src="img/muscle_black.svg?2">'; }
				if(alist[i].a_role == "u") { tr.append('<td>участник'+addcomplex+'</td>'); }
				else if(alist[i].a_role == "p") { tr.append('<td>призер'+addcomplex+'</td>'); }
				else if(alist[i].a_role == "w") { tr.append('<td>победитель'+addcomplex+'</td>'); }
				else if(alist[i].a_role == "l") { tr.append('<td>помощник организатора'+addcomplex+'</td>'); }
				else if(alist[i].a_role == "m") { tr.append('<td>организатор'+addcomplex+'</td>'); }
				else if(alist[i].a_role == "h") { tr.append('<td>главный организатор'+addcomplex+'</td>'); }
				else if(alist[i].a_role == "b") { tr.append('<td>-</td>'); }
				$('.activitytable').append(tr);
			  }
			}
		  } else { $.fancybox({ 'content' : m_error(data.error) }); }
		}
    });
  }

  function printdata(ifSpec) {
	  if(ifSpec == "1") {  window.location.href = 'sz?l=1&id='+curevent; }
	  else { window.location.href = 'sz?id='+curevent; }
  }

  function _add(userid) {
    si_already = userid;
    $.ajax({
	  timeout: 15000,
      beforeSend: function() {
        beforeRow = $('<tr uid="'+userid+'" style="display:none;"><td><b><img src="img/loading.gif" alt="" width="20px" /></b></td><td></td><td class="center"></td><td class="curevent_added"></td><td class="curevent_by"></td><td></td></tr>');
        $('.activitytable tr:first').after(beforeRow);
		    $("tr[uid='"+userid+"']").slideRow('down');
      },
      error: function(jqXHR, exception) {
        // Вывод ошибки
		$("tr[uid='"+userid+"']").slideRow('up', 500, function() { $("tr[uid='"+userid+"']").remove(); });
		  addAlert = $("<div/>").addClass("alert").addClass("alert-error").append('<span><b>Ошибка соединения. Студенты будут внесены в мероприятие после обновления страницы.</b></span>').css("display","none");
		  $(".fastmassage").append(addAlert);
		  addAlert.slideDown(function() { $(this).delay("3500").slideUp(function() { $(this).remove(); }); });
      },
      data: {
        act: "addactive",
        eid: curevent,
        uid: userid,
        rid: $('#add_role option:selected').val()
      },
      success: function(answer) {

        if(answer == "") {
          // Ответ не получен (вряд ли)
        }
        var data = JSON.parse(answer);
        var usersex1 = "";
        var usersex2 = "";
        if(data.error == "ok") {
          var gotrow = data.gr;
          tr = $('<tr/>');
          tr.attr("aid",gotrow.a_id);
		      tr.css("display","none");
          tr.append("<td><a href='' onclick='student("+gotrow.a_uid+",1); return false;'><b>" + gotrow.a_name + "</b><a> [" + gotrow.a_from + "]</td>");
          tr.append('<td><select class="span12 selectrole" onchange="changerole('+gotrow.a_id+')"><option value="b">-</option><option value="u">участник</option><option value="p">призер</option><option value="w">победитель</option><option value="l">помощник организатора</option><option value="m">организатор</option><option value="h">главный организатор</option></select></td>');
		      tr.append('<td class="center"><input class="complexcheck" type="checkbox" onchange="changerole('+gotrow.a_id+')"></td>');
          tr.append("<td class=\"curevent_added\">" + gotrow.a_time + "</td>");
          tr.append("<td class=\"curevent_by\">" + gotrow.a_by + "</td>");
          tr.append("<td><i class='icon-remove' onclick='delActivity_event("+gotrow.a_id+")'></i></td>");
          $('.activitytable tr[uid="'+gotrow.a_uid+'"]').after(tr);
          tr.find(".selectrole [value='"+gotrow.a_role+"']").prop("selected", true);

		  $("tr[aid='"+gotrow.a_id+"']").animate({'background-color': 'rgba(14,175,113,1.0)'}, 500, function() {
				$("tr[aid='"+gotrow.a_id+"']").animate({'background-color': "rgba(255,255,255,0.7)"}, 2000, function() {
					$("tr[aid='"+gotrow.a_id+"']").each(function() { $(this).removeAttr("style"); });
				});
		  });

		  $("tr[aid='"+gotrow.a_id+"']").slideRow('down');
		  $("tr[uid='"+gotrow.a_uid+"']").slideRow('up', 500, function() { $("tr[uid='"+gotrow.a_uid+"']").remove(); });
          $(".involvednum").html(parseInt($(".involvednum").html())+1);
          $('.textalert').hide();
          $("#add_activity").focus();
        }
        else if(data.error == "a_already") {
          // Уже добавлен
		  var gotrow = data.gr;
		  if(gotrow.a_sex == "f") { usersex1 = "а"; usersex2 = "ка"; }

		  addAlert = $("<div/>").addClass("alert").append('<span><b>'+gotrow.a_name+'</b> уже добавлен'+usersex1+'</span>').css("display","none");
		  $(".fastmassage").append(addAlert);
		  addAlert.slideDown(function() { $(this).delay("3500").slideUp(function() { $(this).remove(); }); });

		  $("tr[aid='"+gotrow.a_id+"']").animate({'background-color': '#ffa200'}, 500, function() {
				$("tr[aid='"+gotrow.a_id+"']").animate({'background-color': ""}, 2000, function() {
					$("tr[aid='"+gotrow.a_id+"']").each(function() { $(this).removeAttr("style"); });
				});
		  });

		  $("tr[uid='"+gotrow.a_uid+"']").slideRow('up', 500, function() { $("tr[uid='"+gotrow.a_uid+"']").remove(); });
          $("#add_activity").focus();
        }
        else if(data.error == "a_notexist") {
          // Карта с таким id не существует
    		  $("tr[uid='"+userid+"']").slideRow('up', 500, function() { $("tr[uid='"+userid+"']").remove(); });
    		  addAlert = $("<div/>").addClass("alert").addClass("alert-error").append('<span><b>Карта с таким id не существует</b></span>').css("display","none");
    		  $(".fastmassage").append(addAlert);
    		  addAlert.slideDown(function() { $(this).delay("3500").slideUp(function() { $(this).remove(); }); });
              $("#add_activity").focus();
            }
            else if(data.error == "a_wrongcode") {
              // Неверный формат кода
    		  $("tr[uid='"+userid+"']").slideRow('up', 500, function() { $("tr[uid='"+userid+"']").remove(); });
    		  addAlert = $("<div/>").addClass("alert").addClass("alert-error").append('<span><b>Введен неверный формат кода карты</b></span>').css("display","none");
    		  $(".fastmassage").append(addAlert);
    		  addAlert.slideDown(function() { $(this).delay("3500").slideUp(function() { $(this).remove(); }); });
          $("#add_activity").focus();
        }
        else {
          $.fancybox({
            'height' : 250,
            'content' : m_error(data.error)
          });
          $("tr[uid='"+userid+"']").slideRow('up', 500, function() { $("tr[uid='"+userid+"']").remove(); });
        }
      }
    });
  }

  function findbc() {
    si = $("#add_activity").val();
    maycode = /^[0-9]{10}$/;
    if(maycode.test(si)) {
      $("#add_activity").val("");
      if(si !== si_already) {
        _add(si);
      }
    }
  }

  function delActivity_event(aid) {
	  curaid = aid;
	  render_massage("Удалить студента из мероприятия?","<div class='render_massage_buttons'><a class='btn1' href='' onclick='delActivityYES_event(); $.fancybox.close(); return false;' style='background:#f36b69;'>Удалить</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>");
  }

  function delActivityYES_event(activityid) {
      $.ajax({
        data: {
          act: "delactive",
          aid: curaid
        },
		  success: function(answer) {
			var data = JSON.parse(answer);
			if(data.error == "ok") {
			  $(".state_first, .state_second").slideUp();
			  si_already = "";
			  $('tr[aid="'+curaid+'"]').remove();
			  $(".involvednum").html(parseInt($(".involvednum").html())-1);

			  if($('.activitytable tr').length == '1') {
				$('.pager').html('');
				$('.textalert').show();
				if(!ismobile()) { $("#add_activity").focus(); }
			  }
			}
			else { $.fancybox({ 'content' : m_error(data.error) }); }
		  }
      });
  }
