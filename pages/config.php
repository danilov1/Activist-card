<?php accesspage(); accessto("s"); render_doctype(); ?>
<head>
	<?php render_meta("Конфигурация","config"); ?>

	<!-- js md5       --> <script src="js/md5.js"></script>

	<script type="text/javascript">
	var checkEditContent = "";
	$(function() {
		$(".depsinfo_box, .deplist_box, .grouplist_box, .orglist_box, .studentsinfo_box").hide();
		$("input[type='file'][id=config_organization_logo]").change(function () {
			var ext = $(this).val().split('.').pop().toLowerCase();
			if($.inArray(ext, ['svg','png']) == -1) {
				$(this).val("");
				$.fancybox({ 'content' : m_error("Поддерживается только формат .SVG и .PNG") });
			}
			$("#config_organization_logo_path").val($(this).val());
		});
		$("input[type='file'][id=config_organization_favicon]").change(function () {
			var ext = $(this).val().split('.').pop().toLowerCase();
			if($.inArray(ext, ['ico','png']) == -1) {
				$(this).val("");
				$.fancybox({ 'content' : m_error("Поддерживается только формат Favicon (.ICO или .PNG)") });
			}
			$("#config_organization_favicon_path").val($(this).val());
		});
		$("#image").fancybox({ 'content' : '<style>.fancybox-inner h1 { width:auto !important; }</style><h1></h1>'+$("#image").html() });
	});

	function mainmenu_window(windowname) {
		$("."+windowname+"").hide('slide', {direction: 'right'}, function() {
			$(".mainmenu_box").show('slide', {direction: 'left'});
		});
	}

	function config_commoninfo_window() {
		$("#config_organization_form, #config_organization_fullname, #config_organization_shortname, #config_organization_department").val();
		$(".config_organization_logo_image").attr("src","");
		$(".config_organization_logo_favicon").attr("src","");
		$.ajax({
		  data: {
			act: "getorginfo"
		  },
		  beforeSend: function() { startLoading(); },
			success: function(answer) {
			  var data = (JSON.parse(answer));
			  if(data.error == "ok") {
				  $("#config_organization_form").val(data.organization_form);
				  $("#config_organization_fullname").val(data.organization_fullname);
				  $("#config_organization_shortname").val(data.organization_shortname);
				  $("#config_organization_department").val(data.organization_department);
				  $(".config_organization_logo_image").attr("src",data.organization_logo);
				  $(".config_organization_logo_favicon").attr("src",data.organization_favicon);
				  $("html, body").animate({ scrollTop: 0 });
				  $(".fillblack, .commoninfo").fadeIn();
			  }
			}
		});
	}

	function config_smsinfo_window() {
		$(".config_sms_alert, .config_sms_auth_box, .config_sms_add_box, .btn_sms_auth, .btn_sms_unlink, .config_sms_user_label").hide();
		$("#config_sms_login, #config_sms_pw").val("");
		$(".config_sms_user").html("");
		$(".sms_auth_error_alert, .sms_auth_sender_alert").remove();
		$("#config_sms_name option[value='']").prop("selected",true);

		$.ajax({
		  data: {
			act: "getsmsparams"
		  },
		  beforeSend: function() { startLoading(); },
		  success: function(answer) {
			  var data = (JSON.parse(answer));
			  if(data.error == "ok") {
				  $(".btn_sms_unlink, .config_sms_add_box, .config_sms_user_label").show();
				  $(".config_sms_user").html("<input class='span12' style='margin-bottom:5px;' type='text' value='"+data.sms_login+"' disabled />");
				  $(".config_sms_balance").html(data.sms_balance);
				  $("#config_sms_name").html('<option value="">Не выбрано</option>');
				  for(var i = 0; i<(data.sms_senders).length; i++) {
					  $("#config_sms_name").append('<option value="'+data.sms_senders[i]+'">'+data.sms_senders[i]+'</option>');
				  }
				  if($("#config_sms_name option[value='"+data.sms_name+"']").length) { $("#config_sms_name option[value='"+data.sms_name+"']").prop("selected",true); }
				  else {
					  $("#config_sms_name option[value='']").prop("selected",true);
					  $("#config_sms_name").before('<div class="alert alert-error sms_auth_sender_alert" style="font-size:12px; line-height:12px;"><span>Раннее выбранное имя отправителя <b>'+data.sms_name+'</b> теперь не доступно в Вашем личном кабинете SMSAero. Войдите в личный кабинет на сайте SMSAero для внесения изменений в список имен отправителя.</span></div>');
				  }
					if(data.sms_channel) { $("#config_sms_channel option[value='"+data.sms_channel+"']").prop("selected",true); }
					else { $("#config_sms_channel option[value=4]").prop("selected",true); }
				  $("html, body").animate({ scrollTop: 0 }); $(".fillblack, .smsinfo").fadeIn();
			  }
			  else if(data.error == "sms_auth_new") {
				  $(".config_sms_alert, .config_sms_auth_box, .btn_sms_auth").show();
				  $("html, body").animate({ scrollTop: 0 }); $(".fillblack, .smsinfo").fadeIn();
			  }
			  else if(data.error == "sms_auth_error") {
				  $(".config_sms_auth_box, .btn_sms_auth").show();
				  $(".config_sms_alert").after('<div class="alert alert-error sms_auth_error_alert" style="font-size:12px; line-height:12px;"><span>Раннее введенные учетные данные теперь недействительны. Возможно, в личном кабинете SMSAero Вами был изменен пароль или аккаунт был удален.</span></div>');
				  $("html, body").animate({ scrollTop: 0 }); $(".fillblack, .smsinfo").fadeIn();
			  } else {
				  $.fancybox({ 'content' : m_error(data.error) });
			  }
			}
		});
	}

	function config_smsinfo_unlink() {
		render_massage("Отключить аккаунт?","<div class='render_massage'>Отправка SMS будет невозможна до подключения нового аккаунта.</div><div class='render_massage_buttons'><a class='btn1' href='' onclick='config_smsinfo_unlinkYES(); $.fancybox.close(); return false;' style='background:#f36b69;'>Удалить</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>");
	}

	function config_smsinfo_unlinkYES() {
		$(".config_sms_alert, .config_sms_auth_box, .config_sms_add_box, .btn_sms_auth, .btn_sms_unlink, .config_sms_user_label").hide();
		$("#config_sms_login, #config_sms_pw").val("");
		$(".config_sms_user").html("");
		$(".sms_auth_error_alert, .sms_auth_sender_alert").remove();
		$("#config_sms_name option[value='']").prop("selected",true);

		$(".config_sms_alert, .config_sms_auth_box, .btn_sms_auth").show();
		config_smsinfo_save();
	}

	function config_socialinfo_window() {
		$("#config_vk_id, #config_vk_secret").val("");
		$("#config_vk_id, #config_vk_secret").prop("disabled",false);
		$(".btn_socialinfo_activate, .btn_socialinfo_unlink, .btn_socialinfo_prepare, .btn_socialinfo_edit").hide();
		$.ajax({
			data: {
				act: "getsnparams"
			},
			beforeSend: function() { startLoading(); },
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					if(data.vk_id !== "") {
						$("#config_vk_id, #config_vk_secret").prop("disabled",true);
						$("#config_vk_id").val(data.vk_id);
						$("#config_vk_secret").val(data.vk_secret);
						$(".btn_socialinfo_unlink").show();
					} else {
						$(".btn_socialinfo_prepare").show();
					}
					$("html, body").animate({ scrollTop: 0 });
					$(".fillblack, .socialinfo").fadeIn();
				}
			}
		});
	}

	function config_socialinfo_prepare() {
		if($("#config_vk_id").val().trim() == "" || $("#config_vk_secret").val().trim() == "") { return false; }
		$.ajax({
			data: {
				act: "sn_vk_prepare",
				vk_id: $("#config_vk_id").val().trim(),
				vk_secret: $("#config_vk_secret").val().trim()
			},
			beforeSend: function() { startLoading(); },
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					$("#config_vk_id, #config_vk_secret").prop("disabled",true);
					$(".btn_socialinfo_prepare").hide();
					$(".btn_socialinfo_activate, .btn_socialinfo_edit").show();
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function config_socialinfo_edit() {
		$("#config_vk_id, #config_vk_secret").prop("disabled",false);
		$(".btn_socialinfo_prepare").show();
		$(".btn_socialinfo_activate, .btn_socialinfo_edit").hide();
	}

	function config_socialinfo_activate() {
		if($("#config_vk_id").val().trim() == "" || $("#config_vk_secret").val().trim() == "") { return false; }
		$.ajax({
			data: {
				act: "sn_vk_activate"
			},
			beforeSend: function() { startLoading(); },
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					$.fancybox({
						'afterClose':function () {
							closemw("socialinfo");
						},
						'content' : m_ok('Авторизация через соцсети активирована!')
					});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function config_socialinfo_unlink() {
		render_massage("Отключить авторизацию через соцсети?","<div class='render_massage'>Авторизационные данные пользователей сохранятся, но будут действовать при повторной активации отключаемого приложения.</div><div class='render_massage_buttons'><a class='btn1' href='' onclick='config_socialinfo_unlinkYES(); $.fancybox.close(); return false;' style='background:#f36b69;'>Отключить</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>");
	}

	function config_socialinfo_unlinkYES() {
		$.ajax({
			data: {
				act: "unlinksnparams"
			},
			beforeSend: function() { startLoading(); },
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					$.fancybox({
						'afterClose':function () {
							closemw("socialinfo");
						},
						'content' : m_ok('Авторизация через соцсети отключена!')
					});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function config_ratinginfo_window() {
		$.ajax({
			data: {
				act: "getratingparams"
			},
			beforeSend: function() { startLoading(); },
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					$("#config_rating_roles_u").val(data.rating_roles.u);
					$("#config_rating_roles_p").val(data.rating_roles.p);
					$("#config_rating_roles_w").val(data.rating_roles.w);
					$("#config_rating_roles_l").val(data.rating_roles.l);
					$("#config_rating_roles_m").val(data.rating_roles.m);
					$("#config_rating_roles_h").val(data.rating_roles.h);
					$("#config_rating_levels_f").val(data.rating_levels.f);
					$("#config_rating_levels_u").val(data.rating_levels.u);
					$("#config_rating_levels_c").val(data.rating_levels.c);
					$("#config_rating_levels_r").val(data.rating_levels.r);
					$("#config_rating_levels_v").val(data.rating_levels.v);
					$("#config_rating_levels_i").val(data.rating_levels.i);
					$("#config_rating_complex").val(data.rating_complex);
					$("#config_rating_muscle").val(data.rating_muscle);
					$("#config_rating_oneday").val(data.rating_oneday);
					$("html, body").animate({ scrollTop: 0 });
					$(".fillblack, .ratinginfo").fadeIn();
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function config_commoninfo_save() {
		if(
			$("#config_organization_form").val().trim() == ""
			|| $("#config_organization_fullname").val().trim() == ""
			|| $("#config_organization_shortname").val().trim() == ""
			|| $("#config_organization_department").val().trim() == ""
		) { return false; }
		var fd = new FormData();
		fd.append('act', 'setorginfo');
		fd.append('organization_form', $("#config_organization_form").val());
		fd.append('organization_fullname', $("#config_organization_fullname").val());
		fd.append('organization_shortname', $("#config_organization_shortname").val());
		fd.append('organization_department', $("#config_organization_department").val());
		fd.append('organization_logo', $('#config_organization_logo')[0].files[0]);
		fd.append('organization_favicon', $('#config_organization_favicon')[0].files[0]);
		$.ajax({
			method: "post",
			url:"operator2",
			data: fd,
			processData: false,
			contentType: false,
			beforeSend: function() { startLoading(); },
			success: function(answer) {
			  var data = (JSON.parse(answer));
			  if(data.error == "ok") {
					$.fancybox({
						'afterClose':function () {
						window.location.reload();
					},
					'content' : m_ok('Изменения успешно сохранены!')
				  });
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function config_smsinfo_save() {
		if($(".btn_sms_unlink").is(":visible") && $("#config_sms_name option:selected").val() == "") { $.fancybox({ 'content' : m_error("Выберите имя отправителя SMS.") }); return false; }
		$.ajax({
			data: {
				act: "setsmsparams",
				sms_login: $("#config_sms_login").val().trim(),
				sms_pw: MD5($("#config_sms_pw").val()),
				sms_name: $("#config_sms_name option:selected").val(),
				sms_channel: $("#config_sms_channel option:selected").val()
			},
			beforeSend: function() { startLoading(); },
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					closemw('smsinfo');
					$.fancybox({'content' : m_ok('Изменения успешно сохранены!')});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function config_smsinfo_auth() {
		if($("#config_sms_login").val().trim() == "" || $("#config_sms_pw").val() == "") { return false; }
		$.ajax({
			data: {
				act: "smsauth",
				sms_login: $("#config_sms_login").val().trim(),
				sms_pw: MD5($("#config_sms_pw").val())
			},
			beforeSend: function() { startLoading(); },
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					$(".config_sms_alert, .config_sms_auth_box, .config_sms_add_box, .btn_sms_auth, .btn_sms_unlink, .config_sms_user_label").hide();
					$(".sms_auth_error_alert, .sms_auth_sender_alert").remove();
					$(".btn_sms_unlink, .config_sms_add_box, .config_sms_user_label").show();
					$(".config_sms_user").html("<input class='span12' style='margin-bottom:5px;' type='text' value='"+data.sms_login+"' disabled />");
					$(".config_sms_balance").html(data.sms_balance);
					$("#config_sms_name").html('<option value="">Не выбрано</option>');
					for(var i = 0; i<(data.sms_senders).length; i++) {
						$("#config_sms_name").append('<option value="'+data.sms_senders[i]+'">'+data.sms_senders[i]+'</option>');
					}
					$("#config_sms_channel option[value=4]").prop("selected",true);
				} else if(data.error == "sms_auth_error") {
					$.fancybox({ 'content' : m_error("Неверный логин или пароль") });
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function config_ratinginfo_save() {
		$.ajax({
			data: {
				act: "setratingparams",
				rating_roles_u: $("#config_rating_roles_u").val(),
				rating_roles_p: $("#config_rating_roles_p").val(),
				rating_roles_w: $("#config_rating_roles_w").val(),
				rating_roles_l: $("#config_rating_roles_l").val(),
				rating_roles_m: $("#config_rating_roles_m").val(),
				rating_roles_h: $("#config_rating_roles_h").val(),
				rating_levels_f: $("#config_rating_levels_f").val(),
				rating_levels_u: $("#config_rating_levels_u").val(),
				rating_levels_c: $("#config_rating_levels_c").val(),
				rating_levels_r: $("#config_rating_levels_r").val(),
				rating_levels_v: $("#config_rating_levels_v").val(),
				rating_levels_i: $("#config_rating_levels_i").val(),
				rating_complex: $("#config_rating_complex").val(),
				rating_muscle: $("#config_rating_muscle").val(),
				rating_oneday: $("#config_rating_oneday").val()
			},
			beforeSend: function() { startLoading(); },
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					closemw('ratinginfo');
					$.fancybox({'content' : m_ok('Изменения успешно сохранены!')});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function config_depsinfo_window() {
		$(".mainmenu_box").hide('slide', {direction: 'left'}, function() {
			$(".depsinfo_box").show('slide', {direction: 'right'});
		});
	}

	function config_depslist_fac_windows() {
		$.ajax({
			data: {
				act: "getdeps_fac"
			},
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					$(".deplist").html("");
					if((data.fac).length == 0) { $(".textalert_depslist_fac").show(); }
					else { $(".textalert_depslist_fac").hide(); }
					for(var i = 0; i<(data.fac).length; i++) {
						newfac = '\
						<div class="pl_row" depid="'+data.fac[i].id+'">\
							<div class="pl_row_box">\
								<div class="row-fluid">\
									<div class="span1 dep_type">Факул.</div>\
									<div class="span3 dep_shortname"><div class="comment" contenteditable="true" spellcheck="false">'+data.fac[i].short+'</div></div>\
									<div class="span5 dep_fullname"><div class="comment" contenteditable="true" spellcheck="false">'+data.fac[i].full+'</div></div>\
									<div class="span2"><a class="btn btnadd" href="" onclick="config_depslist_groups_windows('+data.fac[i].id+'); return false;">Группы &gt;</a></div>\
									<div class="span1 funcside"><a href="" class="btn_edit" onclick="delfac('+data.fac[i].id+'); return false;"><i class="icon-remove"></i></a></div>\
								</div>\
							</div>\
						</div>';
						$(".deplist").append(newfac);
						$("div[depid="+data.fac[i].id+"] .comment").each(function() {
							$(this).keydown(function(e) {
								if(e.keyCode == 13 && !e.shiftKey) {
									e.preventDefault();
									config_depslist_dep_save($(this).parent().parent().parent().parent().attr("depid"));
								}
							});
							$(this).focus(function() { checkEditContent = $(this).html(); });
							$(this).blur(function() {
								if($(this).html() !== checkEditContent) {
									config_depslist_dep_save($(this).parent().parent().parent().parent().attr("depid"));
								}
							});
						});
					}
					if($(".grouplist_box").is(":visible")) {
						$(".grouplist_box").hide('slide', {direction: 'right'}, function() {
							$(".deplist_box").show('slide', {direction: 'left'});
						});
					} else {
						$(".depsinfo_box").hide('slide', {direction: 'left'}, function() {
							$(".deplist_box").show('slide', {direction: 'right'});
						});
					}
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function config_depslist_dep_save(facid) {
		if($("div[depid="+facid+"] .dep_shortname .comment").html() == "" || $("div[depid="+facid+"] .dep_fullname .comment").html() == "") { return false; }
		$.ajax({
			data: {
				act: "getdeps_dep_save",
				id: facid,
				short: $("div[depid="+facid+"] .dep_shortname .comment").html(),
				full: $("div[depid="+facid+"] .dep_fullname .comment").html()
			},
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					$.fancybox({'content' : m_ok('Изменения успешно сохранены!')});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	_facid = null;
	function config_depslist_groups_windows(facid) {
		$.ajax({
			data: {
				act: "getdeps_groups",
				id: facid
			},
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					_facid = facid;
					$(".grouplist_box #box_title").html($("div[depid="+facid+"] .dep_fullname .comment").html());
					$(".grouplist").html("");
					if((data.groups).length == 0) { $(".textalert_depslist_groups").show(); }
					else { $(".textalert_depslist_groups").hide(); }
					for(var i = 0; i<(data.groups).length; i++) {
						newfac = '\
						<div class="pl_row" depid="'+data.groups[i].id+'">\
							<div class="pl_row_box">\
								<div class="row-fluid">\
									<div class="span1 dep_type">Группа</div>\
									<div class="span3 dep_shortname"><div class="comment" contenteditable="true" spellcheck="false" tabindex="-1">'+HTML.decode(data.groups[i].short)+'</div></div>\
									<div class="span7 dep_fullname"><div class="comment" contenteditable="true" spellcheck="false" tabindex="-1">'+HTML.decode(data.groups[i].full)+'</div></div>\
									<div class="span1 funcside"><a href="" class="btn_edit" onclick="delgroup('+data.groups[i].id+'); return false;"><i class="icon-remove"></i></a></div>\
								</div>\
							</div>\
						</div>';
						$(".grouplist").append(newfac);
						$("div[depid="+data.groups[i].id+"] .comment").each(function() {
							$(this).keydown(function(e) {
								if(e.keyCode == 13 && !e.shiftKey) {
									e.preventDefault();
									config_depslist_dep_save($(this).parent().parent().parent().parent().attr("depid"));
								}
							});
							$(this).focus(function() { checkEditContent = $(this).html(); });
							$(this).blur(function() {
								if($(this).html() !== checkEditContent) {
									config_depslist_dep_save($(this).parent().parent().parent().parent().attr("depid"));
								}
							});
						});
					}
					$(".deplist_box").hide('slide', {direction: 'left'}, function() {
						$(".grouplist_box").show('slide', {direction: 'right'});
					});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function config_orglist_windows() {
		$.ajax({
			data: {
				act: "getdeps_org"
			},
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					$(".orglist").html("");

					if((data.order).length !== 0) {
						for(var i = 0; i<(data.order).length; i++) {
							for(var c = 0; c<(data.org).length; c++) {
								if(data.org[c].id == data.order[i]) {
									orginfo = data.org[c];
								}
							}
							newfac = '\
							<div class="pl_row pl_editable" depid="'+orginfo.id+'">\
								<div class="pl_row_box">\
									<div class="row-fluid">\
										<div class="span1 center dep_sort" style="cursor:move;"><div><img src="img/list.svg" width="31px" alt="" /></div></div>\
										<div class="span3 dep_shortname"><div class="comment" contenteditable="true" spellcheck="false">'+HTML.decode(orginfo.short)+'</div></div>\
										<div class="span7 dep_fullname"><div class="comment" contenteditable="true" spellcheck="false">'+HTML.decode(orginfo.full)+'</div></div>\
										<div class="span1 funcside"><a href="" class="btn_edit" onclick="delorg('+orginfo.id+'); return false;"><i class="icon-remove"></i></a></div>\
									</div>\
								</div>\
							</div>';
							$(".orglist").append(newfac);
							$("div[depid="+orginfo.id+"] .comment").each(function() {
								$(this).keydown(function(e) {
									if(e.keyCode == 13 && !e.shiftKey) {
										e.preventDefault();
										config_depslist_dep_save($(this).parent().parent().parent().parent().attr("depid"));
									}
								});
								$(this).focus(function() { checkEditContent = $(this).html(); });
								$(this).blur(function() {
									if($(this).html() !== checkEditContent) {
										config_depslist_dep_save($(this).parent().parent().parent().parent().attr("depid"));
									}
								});
							});
						}
						$('.textalert_orglist').hide();
						$(".orglist").sortable({
							handle: ".dep_sort",
							start: function(event, ui) { ui.item.addClass("highlight"); },
							stop: function(event, ui) { ui.item.removeClass("highlight"); config_orglist_sort(); },
							update: function(event, ui) { }
						});
					} else {
						$('.textalert_orglist').show();
					}

					$(".depsinfo_box").hide('slide', {direction: 'left'}, function() {
						$(".orglist_box").show('slide', {direction: 'right'});
					});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function config_orglist_sort() {
		neworder = [];
		curorder = $(".orglist").children(".pl_row[depid != '']");
		for(var i = 0; i < curorder.length; i++) {
			neworder.push(curorder.eq(i).attr("depid"));
		}
		$.ajax({
			data: {
				act: "config_orglist_sort",
				order: JSON.stringify(neworder)
			},
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error !== "ok") { $.fancybox({ 'content' : m_error(data.error) }); }
			}
		});
	}

	function delorg(orgid) {
		render_massage("Удалить подразделение?","<div class='render_massage_buttons'><a class='btn1' href='' onclick='delmembersYES("+orgid+"); $.fancybox.close(); return false;' style='background:#f36b69;'>Удалить</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>");
	}

	function delmembersYES(orgid) {
		$.ajax({
			data: {
				act: "deldep_org",
				id: orgid
			},
			beforeSend: function() {
				$(".orglist .pl_row[depid='"+orgid+"']").slideUp(100);
			},
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					$(".orglist .pl_row[depid='"+orgid+"']").slideUp(100, function() {
						$(".orglist .pl_row[depid='"+orgid+"']").remove();
						if($(".orglist .pl_row[depid != '']").length == 0) { $('.textalert_orglist').slideDown(); }
					});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
					$(".orglist .pl_row[depid='"+orgid+"']").slideDown(100);
				}
			}
		});
	}

	function delfac(facid) {
		render_massage("Удалить факультет и все входящие в него группы?","<div class='render_massage_buttons'><a class='btn1' href='' onclick='delfacYES("+facid+"); $.fancybox.close(); return false;' style='background:#f36b69;'>Удалить</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>");
	}

	function delfacYES(facid) {
		$.ajax({
			data: {
				act: "deldep_fac",
				id: facid
			},
			beforeSend: function() {
				$(".deplist .pl_row[depid='"+facid+"']").slideUp(100);
			},
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					$(".deplist .pl_row[depid='"+facid+"']").slideUp(100, function() {
						$(".deplist .pl_row[depid='"+facid+"']").remove();
						if($(".deplist .pl_row[depid != '']").length == 0) { $('.textalert_depslist_fac').slideDown(); }
					});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
					$(".deplist .pl_row[depid='"+facid+"']").slideDown(100);
				}
			}
		});
	}

	function delgroup(groupid) {
		render_massage("Удалить группу?","<div class='render_massage_buttons'><a class='btn1' href='' onclick='delgroupYES("+groupid+"); $.fancybox.close(); return false;' style='background:#f36b69;'>Удалить</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>");
	}

	function delgroupYES(groupid) {
		$.ajax({
			data: {
				act: "deldep_group",
				id: groupid
			},
			beforeSend: function() {
				$(".grouplist .pl_row[depid='"+groupid+"']").slideUp(100);
			},
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					$(".grouplist .pl_row[depid='"+groupid+"']").slideUp(100, function() {
						$(".grouplist .pl_row[depid='"+groupid+"']").remove();
						if($(".grouplist .pl_row[depid != '']").length == 0) { $('.textalert_depslist_groups').slideDown(); }
					});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
					$(".grouplist .pl_row[depid='"+groupid+"']").slideDown(100);
				}
			}
		});
	}

	function config_orglist_addwindow() {
		$("#config_orglist_shortname, #config_orglist_fullname").val("");
		$("html, body").animate({ scrollTop: 0 });
		$(".fillblack, .config_orglist_addwindow").fadeIn();
		$("#config_orglist_shortname").focus();
	}

	function config_depslist_fac_addwindow() {
		$("#config_depslist_fac_shortname, #config_depslist_fac_fullname").val("");
		$("html, body").animate({ scrollTop: 0 });
		$(".fillblack, .config_depslist_fac_addwindow").fadeIn();
		$("#config_depslist_fac_shortname").focus();
	}

	function config_depslist_groups_addwindow() {
		$("#config_depslist_groups_shortname, #config_depslist_groups_fullname").val("");
		$("html, body").animate({ scrollTop: 0 });
		$(".fillblack, .config_depslist_groups_addwindow").fadeIn();
		$("#config_depslist_groups_shortname").focus();
	}

	function config_orglist_add() {
		if($("#config_orglist_shortname").val().trim() == "" || $("#config_orglist_fullname").val().trim() == "") { return false; }
		$.ajax({
			data: {
				act: "deps_org_add",
				short: $("#config_orglist_shortname").val().trim(),
				full: $("#config_orglist_fullname").val().trim()
			},
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					$.fancybox({
						'afterClose':function () {
							config_orglist_windows();
							closemw("config_orglist_addwindow");
						},
						'content' : m_ok('Организация успешно зарегистрирована!')
					});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function config_depslist_fac_add() {
		if($("#config_depslist_fac_shortname").val().trim() == "" || $("#config_depslist_fac_fullname").val().trim() == "") { return false; }
		$.ajax({
			data: {
				act: "deps_fac_add",
				short: $("#config_depslist_fac_shortname").val().trim(),
				full: $("#config_depslist_fac_fullname").val().trim()
			},
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					$.fancybox({
						'afterClose':function () {
							config_depslist_fac_windows();
							closemw("config_depslist_fac_addwindow");
						},
						'content' : m_ok('Факультет успешно зарегистрирован!')
					});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function config_depslist_groups_add() {
		if($("#config_depslist_groups_shortname").val().trim() == "" || $("#config_depslist_groups_fullname").val().trim() == "") { return false; }
		$.ajax({
			data: {
				act: "deps_groups_add",
				facid: _facid,
				short: $("#config_depslist_groups_shortname").val().trim(),
				full: $("#config_depslist_groups_fullname").val().trim()
			},
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					$.fancybox({
						'afterClose':function () {
							config_depslist_groups_windows(_facid);
							closemw("config_depslist_groups_addwindow");
						},
						'content' : m_ok('Группа успешно зарегистрирована!')
					});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function config_studentsinfo_window() {
		$(".mainmenu_box").hide('slide', {direction: 'left'}, function() {
			$(".studentsinfo_box").show('slide', {direction: 'right'});
		});
	}

	function config_studentsupload_window() {
		$(".connectbox_list").html("");
		$(".studentsupload_confirmtext_inner").html("");
		$(".studentsupload_start").show();
		$(".studentsupload_settype").hide();
		$(".studentsupload_confirmbox").hide();
		$(".studentsupload_confirmtext").hide();
		$("#config_studentsupload_file").val("");
		$("html, body").animate({ scrollTop: 0 });
		$(".fillblack, .studentsupload").fadeIn();
	}

	function config_studentsupload_settype() {
		$("input[name=studentsupload_type][value=reload]").prop("checked", true);
		$(".studentsupload_start").slideUp(null,null,function() {
			$(".studentsupload_settype").slideDown();
		});
	}


	var connectboxRows = {
		nodata: "Не выбрано",
		id: "Идентификатор студента",
		sname: "Фамилия",
		fname: "Имя",
		pname: "Отчество",
		sex: "Пол",
		birthday: "Дата рождения",
		phone: "Номер телефона",
		edu_type: "Форма обучения",
		edu_level: "Ступень образования",
		department: "Факультет (аббревиатура)",
		course: "Номер курса",
		group_name: "Группа (аббревиатура)",
		group_num: "Номер группы",
		pay: "Форма оплаты",
	}

	var CSVid = null;

	function config_studentsupload_upload() {
		if($('#config_studentsupload_file')[0].files.length == 0) { return false; }

		if($('#config_studentsupload_file')[0].files[0].size > 3145728) {
			$.fancybox({ 'content' : m_error("Размер файла превышает 3мб.") });
			return false;
		}
		if(!(new RegExp('(' + ['.csv'].join('|').replace(/\./g, '\\.') + ')$', "i")).test($('#config_studentsupload_file')[0].files[0].name)) {
			$.fancybox({ 'content' : m_error("Поддерживается только формат \".csv\"") });
			return false;
		}

		var fd = new FormData();
		fd.append('act', 'studentsupload');
		fd.append('file', $('#config_studentsupload_file')[0].files[0]);
		$.ajax({
			type: "post",
			url:"operator2",
			data: fd,
			processData: false,
			contentType: false,
			cache:false,
			beforeSend: function() { startLoading(); },
			success: function(answer) {
			  var data = (JSON.parse(answer));
			  if(data.error == "ok") {
					// соотношение
					CSVid = data.CSVid;
					connectbox_list = $(".connectbox_list");
					for(var i = 0; i<(data.preCSV[0]).length; i++) {
						newColumn = $("<div/>").addClass("connectbox");
						newColumnSelect = $("<select/>").addClass("span12").attr("CSVRow",i);
						for (var connectboxRowsKey in connectboxRows) {
							newColumnSelectOption = $("<option/>").val(connectboxRowsKey).html(connectboxRows[connectboxRowsKey]);
							newColumnSelect.append(newColumnSelectOption);
						};
						newColumn.append(newColumnSelect);
						newArrowIcon = $("<div/>").addClass("arrow-down");
						newColumn.append(newArrowIcon);
						newTable = $("<table/>").addClass("table_withhead");
						newTableHeader = $("<tr/>"); // .addClass("table_head");
						newTableHeaderTd = $("<td/>").html("<b>"+data.preCSV[0][i]+"</b>");
						newTableHeader.append(newTableHeaderTd);
						newTable.append(newTableHeader);
						for(var c = 1; c<(data.preCSV).length; c++) {
							newTableTr = $("<tr/>");
							newTableTd = $("<td/>");
							if((data.preCSV[c][i]).trim() == "") { data.preCSV[c][i] = "-"; }
							newTableTd.html(data.preCSV[c][i]);
							newTableTr.append(newTableTd);
							newTable.append(newTableTr);
						}
						newColumn.append(newTable);
						connectbox_list.append(newColumn);
					}
					$(".connectbox select").each(function() {
						$(this).find("option[value='nodata']").prop("selected",true);
						$(this).change(function() {
							if($(this).find("option:selected").val() !== "nodata") {
								$(this).addClass("apply");
							} else {
								$(this).removeClass("apply");
							}
						});
					});

					$(".studentsupload_settype").slideUp(null,null,function() {
						$(".studentsupload_confirmbox").slideDown();
						$("html, body").animate({ scrollTop: 0 });
					});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	var su_newdata, su_out, su_newdeps, su_newgroups;
	function config_studentsupload_connect() {
		for (var connectboxRowsKey in connectboxRows) {
			if($(".connectbox select option[value='"+connectboxRowsKey+"']:selected").length > 1 && connectboxRowsKey !== "nodata") {
				$.fancybox({ 'content' : m_error("Для нескольких столбцов указано одно и то же поле") });
				return false;
			}
		}

		var necessoryRows = ["id", "sname", "fname", "pname", "edu_level", "department", "course", "group_name", "group_num"];
		for (var necessoryRowsKey in necessoryRows) {
			if($(".connectbox select option[value='"+necessoryRows[necessoryRowsKey]+"']:selected").length == 0) {
				$.fancybox({ 'content' : m_error("Не выбраны обязательные поля") });
				return false;
			}
		}

		sendRows = [];
		$(".connectbox select").each(function() {
			if($(this).find("option:selected").val() !== "nodata") {
				newElem = {};
				newElem.row = $(this).attr("CSVRow");
				newElem.column = $(this).find("option:selected").val();
				sendRows.push(newElem);
			}
		});

		$.ajax({
			data: {
				act: "studentsupload_connect",
				csvid: CSVid,
				connect: JSON.stringify(sendRows),
				type: $("input[name=studentsupload_type]:checked").val()
			},
			timeout:200000,
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					su_newdata = data.newdata;
					su_out = data.out;
					su_newdeps = data.newdeps;
					su_newgroups = data.newgroups;
					CSVid = data.CSVid;

					studentsupload_confirm_newdata = "";
					if(data.newdata.length !== 0) { studentsupload_confirm_newdata = ' <a class="btn" href="" onclick="studentsupload_confirm_newdata_show(); return false;">Просмотреть</a>'; }
					$(".studentsupload_confirmtext_inner").append('<b>Студенты</b><p>Кол-во загружаемых студентов: <b>'+data.datarows+'</b></p><p>Кол-во новых студентов: <b>'+data.newdata.length+'</b>'+studentsupload_confirm_newdata+'</p>');
					if(data.type == "reload") {
						studentsupload_confirm_out = "";
						if(data.out.length !== 0) { studentsupload_confirm_out = ' <a class="btn" href="" onclick="studentsupload_confirm_out_show(); return false;">Просмотреть</a>'; }
						$(".studentsupload_confirmtext_inner").append('<p>Кол-во студентов, которые перестанут отображаться в рейтинге: <b>'+data.out.length+'</b>'+studentsupload_confirm_out+'</p>');
					}
					studentsupload_confirm_newdeps = "";
					if(data.newdeps.length !== 0) { studentsupload_confirm_newdeps = ' <a class="btn" href="" onclick="studentsupload_confirm_newdeps_show(); return false;">Просмотреть</a>'; }
					studentsupload_confirm_newgroups = "";
					if(data.newgroups.length !== 0) { studentsupload_confirm_newgroups = ' <a class="btn" href="" onclick="studentsupload_confirm_newgroups_show(); return false;">Просмотреть</a>'; }
					$(".studentsupload_confirmtext_inner").append('<p><br></p><b>Факультеты и группы</b><p>Кол-во новых факультетов: <b>'+data.newdeps.length+'</b>'+studentsupload_confirm_newdeps+'</p><p>Кол-во новых групп: <b>'+data.newgroups.length+'</b>'+studentsupload_confirm_newgroups+'</p>');


					$(".studentsupload_confirmbox").slideUp(null,null,function() {
						$(".studentsupload_confirmtext").slideDown();
					});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}

	function studentsupload_confirm_newdata_show() {
		render_table = "";
		for(var i = 0; i<(su_newdata).length; i++) {
			render_table += "<tr><td>"+su_newdata[i][0]+"</td><td>"+su_newdata[i][1]+"</td></tr>";
		}
		render_massage("Новые студенты","<div class='render_massage'><table class='table_withhead'><tr class='table_head'><td width='20%'>ID</td><td>Студент</td></tr>"+render_table+"</table></div>");
	}

	function studentsupload_confirm_out_show() {
		render_table = "";
		for(var i = 0; i<(su_out).length; i++) {
			render_table += "<tr><td>"+su_out[i][0]+"</td><td>"+su_out[i][1]+"</td></tr>";
		}
		render_massage("Студенты, которые перестанут отображаться в рейтинге студентов","<div class='render_massage'><table class='table_withhead'><tr class='table_head'><td width='20%'>ID</td><td>Студент</td></tr>"+render_table+"</table></div>");
	}

	function studentsupload_confirm_newdeps_show() {
		render_table = "";
		for(var i = 0; i<(su_newdeps).length; i++) {
			render_table += "<tr><td>"+su_newdeps[i]+"</td></tr>";
		}
		render_massage("Новые факультеты","<div class='render_massage'><table class='table_withhead'><tr class='table_head'><td>Факультет</td></tr>"+render_table+"</table></div>");
	}

	function studentsupload_confirm_newgroups_show() {
		render_table = "";
		for(var i = 0; i<(su_newgroups).length; i++) {
			render_table += "<tr><td>"+su_newgroups[i]+"</td></tr>";
		}
		render_massage("Новые группы","<div class='render_massage'><table class='table_withhead'><tr class='table_head'><td>Группа</td></tr>"+render_table+"</table></div>");
	}

	function config_studentsupload_confirm() {
		$.ajax({
			data: {
				act: "studentsupload_confirm",
				csvid: CSVid
			},
			timeout:200000,
			success: function(answer) {
				var data = (JSON.parse(answer));
				if(data.error == "ok") {
					closemw('studentsupload');
					$.fancybox({'content' : m_ok('Изменения успешно внесены!')});
				} else {
					$.fancybox({ 'content' : m_error(data.error) });
				}
			}
		});
	}
	</script>
	<style>
	.comment {
		line-height:15px;
	}
	</style>
</head>

<body>
<div class="fillblack"></div>
<div class="mw commoninfo">
	<a class="closemw" href="javascript:closemw('commoninfo');"><i class="icon-remove"></i></a>
	<h1>Основная информация об организации</h1>
	<div class="row-fluid form-horizontal" style="width:500px;">

		<div class="control-group">
			<label class="control-label">Орг.форма:</label>
			<div class="controls">
				<input id="config_organization_form" class="span12" type="text" placeholder="Например, ФГБОУ ВО" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Полное наим.:</label>
			<div class="controls">
				<input id="config_organization_fullname" class="span12" type="text" placeholder="Например, Санкт-Петербургский государственный университет" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Сокращенное наим.:</label>
			<div class="controls">
				<input id="config_organization_shortname" class="span12" type="text" placeholder="Например, СПбГУ" />
			</div>
		</div>

		<hr>

		<div class="control-group">
			<label class="control-label">Логотип:</label>
			<div class="controls">
				<div class="alert" style="font-size:12px; line-height:12px;">
					<span>Отображается на странице входа и на всех других страницах данного сайта напротив надписи "КАРТА АКТИВИСТА".</span>
				</div>
				<span class="filepath"><input id="config_organization_logo_path" class="uploadFile" disabled="disabled" placeholder="Формат .SVG или .PNG" style="width:180px;" /><a class="btn_delicon" href="" onclick="$('#config_organization_logo_path, #config_organization_logo').val(''); return false;"><i class="icon-remove"></i></a></span>
				<div class="fileUpload btn">
					<span>Заменить <img class="inlinesvg config_organization_logo_image" src="" alt="" style="margin:0;"></span>
					<input id="config_organization_logo" type="file" />
				</div>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">FAVICON:</label>
			<div class="controls">
				<div class="alert" style="font-size:12px; line-height:12px;">
					<span><b>FAVICON</b> - значок, который отображается во вкладке браузера напротив названия веб-страницы. Размер значка должен быть одинаковым по высоте и ширине (32 пикс. или больше)</span>
				</div>
				<span class="filepath"><input id="config_organization_favicon_path" class="uploadFile" disabled="disabled" placeholder="Формат .ICO или .PNG" style="width:180px;" /><a class="btn_delicon" href="" onclick="$('#config_organization_favicon_path, #config_organization_favicon').val(''); return false;"><i class="icon-remove"></i></a></span>
				<div class="fileUpload btn">
					<span>Заменить <img class="inlinesvg config_organization_logo_favicon" src="" alt="" style="margin:0;"></span>
					<input id="config_organization_favicon" type="file" />
				</div>
			</div>
		</div>

		<hr>

		<div class="control-group">
			<label class="control-label">Координатор системы:</label>
			<div class="controls">
				<div class="alert" style="font-size:12px; line-height:12px;">
					<span><b>Координатор системы</b> - подразделение образовательной организации, координирующее работу системы Карта активиста. Например, Объединенный совет обучающихся или Управление по воспитательной работе.</span>
				</div>
				<input id="config_organization_department" class="span12" type="text" placeholder="Например, Студенческий совет СПбГУ" />
			</div>
		</div>

		<hr>

		<div class="control-group">
			<div class="controls">
				<a class="btn1" href="" onclick="config_commoninfo_save(); return false;">Сохранить</a>
			</div>
		</div>
	</div>
</div>
<div class="mw ratinginfo">
	<a class="closemw" href="javascript:closemw('ratinginfo');"><i class="icon-remove"></i></a>
	<h1>Рейтинговая система</h1>
	<div class="alert alert-success" style="font-size:12px; line-height:12px;">
		<span><b>Рассчет баллов (если задана роль):</b><br>ПервичныйБалл = (Роль + Уровень) * КоеффициентСложности<br>Итоговый балл = Округление (ПервичныйБалл + ПервичныйБалл * КолвоДней * КоеффициентДополнительныхДней + ПервичныйБалл * КоеффициентЗвездочка).<br><i>КоеффициентСложности = 0, если галочка "Тяжелое мероприятие" не установлена, иначе - КоеффициентСложности = "Тяжелое" (коеф.).</i></span>
	</div>
	<div class="row-fluid form-horizontal" style="width:500px;">

		<b>Баллы за роль в мероприятии<br></b>

		<div class="control-group">
			<label class="control-label">Участник:</label>
			<div class="controls">
				<input id="config_rating_roles_u" class="span12" type="text" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Призер:</label>
			<div class="controls">
				<input id="config_rating_roles_p" class="span12" type="text" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Победитель:</label>
			<div class="controls">
				<input id="config_rating_roles_w" class="span12" type="text" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Помощник орг.:</label>
			<div class="controls">
				<input id="config_rating_roles_l" class="span12" type="text" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Организатор:</label>
			<div class="controls">
				<input id="config_rating_roles_m" class="span12" type="text" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Главный орг.:</label>
			<div class="controls">
				<input id="config_rating_roles_h" class="span12" type="text" />
			</div>
		</div>

		<hr>

		<b>Баллы за уровень мероприятия<br></b>

		<div class="control-group">
			<label class="control-label">Факультетский:</label>
			<div class="controls">
				<input id="config_rating_levels_f" class="span12" type="text" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Университетский:</label>
			<div class="controls">
				<input id="config_rating_levels_u" class="span12" type="text" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Городской:</label>
			<div class="controls">
				<input id="config_rating_levels_c" class="span12" type="text" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Региональный:</label>
			<div class="controls">
				<input id="config_rating_levels_r" class="span12" type="text" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Всероссийский:</label>
			<div class="controls">
				<input id="config_rating_levels_v" class="span12" type="text" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Международный:</label>
			<div class="controls">
				<input id="config_rating_levels_i" class="span12" type="text" />
			</div>
		</div>

		<hr>

		<b>Коэффициенты<br></b>

		<div class="control-group">
			<label class="control-label">"Тяжелое" мероп.:</label>
			<div class="controls">
				<input id="config_rating_complex" class="span12" type="text" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">"Звездочка":</label>
			<div class="controls">
				<input id="config_rating_muscle" class="span12" type="text" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">За доп.день (коеф.):</label>
			<div class="controls">
				<input id="config_rating_oneday" class="span12" type="text" />
			</div>
		</div>

		<hr>

		<div class="control-group">
			<div class="controls">
				<a class="btn1" href="" onclick="config_ratinginfo_save(); return false;">Сохранить</a>
			</div>
		</div>
	</div>
</div>
<div class="mw smsinfo">
	<a class="closemw" href="javascript:closemw('smsinfo');"><i class="icon-remove"></i></a>
	<h1>SMS-уведомления</h1>
	<div class="alert alert-success config_sms_alert" style="font-size:12px; line-height:12px;">
		<span>Карта активиста использует сервис отправки SMS от компании <b><a href="http://smsaero.ru/" target="_blank">SMSAero</a></b>.<br>Для отправки SMS введите логин и пароль от Вашего аккаунта на сайте <a href="http://smsaero.ru/" target="_blank">SMSAero</a>.</span>
	</div>
	<div class="row-fluid form-horizontal" style="width:500px;">

		<div class="config_sms_auth_box">

			<div class="control-group">
				<label class="control-label">Логин:</label>
				<div class="controls">
					<input id="config_sms_login" class="span12" type="text" />
				</div>
			</div>

			<div class="control-group">
				<label class="control-label">Пароль:</label>
				<div class="controls">
					<input id="config_sms_pw" class="span12" type="password" />
				</div>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label config_sms_user_label">Аккаунт SMSAero:</label>
			<div class="controls">
				<div class="config_sms_user"></div>
				<a class="btn btn_sms_auth" href="" onclick="config_smsinfo_auth(); return false;"><i class="icon icon-lock"></i> Авторизоваться</a>
				<a class="btn btn_sms_unlink" href="" onclick="config_smsinfo_unlink(); return false;"><i class="icon icon-remove"></i> Отключить аккаунт</a>
			</div>

		</div>

		<div class="config_sms_add_box">

			<div class="control-group">
				<label class="control-label">Имя отправителя:</label>
				<div class="controls">
					<div class="alert" style="font-size:12px; line-height:12px;">
						<span>Список имен отправителя Вы можете настроить на сайте <a href="http://smsaero.ru/" target="_blank">SMSAero</a> в Вашем личном кабинете в разделе "Настройки" &gt; "Подписи отправителя".</span>
					</div>
					<select id="config_sms_name" class="span12">
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Канал отправки:</label>
				<div class="controls">
					<select id="config_sms_channel" class="span12">
						<option value="4">Инфоканал</option>
						<option value="7">Рекламный (с буквенной подписью)</option>
					</select>
				</div>
			</div>

			<hr>

			<div class="control-group">
				<label class="control-label"></label>
				<div class="controls">
					<div class="whitebox" style="margin:0; background:rgba(14,175,113,1); border:none; color:#fff;">
						<b>Баланс: <span class="config_sms_balance"></span>руб.</b>
					</div>
				</div>
			</div>

		</div>

		<hr>

		<div class="control-group">
			<div class="controls">
				<a class="btn1" href="" onclick="config_smsinfo_save(); return false;">Сохранить</a>
			</div>
		</div>
	</div>
</div>
<div class="mw socialinfo">
	<a class="closemw" href="javascript:closemw('socialinfo');"><i class="icon-remove"></i></a>
	<h1>Авторизация через соцсети</h1>
	<b>ВКонтакте</b>
	<div class="alert alert-success" style="font-size:12px; line-height:12px;">
		<span>Для возможности авторизовываться на сайте через ВКонтакте создайте веб-приложение ВКонтакте (<a href="https://vk.com/editapp?act=create" target="_blank">https://vk.com/editapp?act=create</a>). Тип приложения: веб-сайт. Название и описание приложения указываются на Ваше усмотрение, например, название: "Карта активиста СПбГУ", описание: "Ведение учета внеаудиторной деятельности студентов". После успешного создания в настройках приложения Вам будет доступен ID приложения и его защищенный ключ.</span>
	</div>
	<div class="alert" style="font-size:12px; line-height:12px;">
		<span>После сохранения введенных данных Вам будет предложено пройти авторизацию для проверки работы приложения (если раннее Вы еще этого не делали).</span>
	</div>
	<div class="row-fluid form-horizontal" style="width:500px;">

		<div class="control-group">
			<label class="control-label">ID приложения:</label>
			<div class="controls">
				<input id="config_vk_id" class="span12" type="text" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Защищенный ключ:</label>
			<div class="controls">
				<input id="config_vk_secret" class="span12" type="text" />
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<a class="btn btn_socialinfo_prepare" href="" onclick="config_socialinfo_prepare(); return false;">Предварительное сохранение</a>
				<a class="btn btn_socialinfo_edit" href="" onclick="config_socialinfo_edit(); return false;">Изменить настройки</a>
			</div>
		</div>

		<hr>

		<div class="control-group">
			<div class="controls">
				<a class="btn1 btn_socialinfo_activate" href="" onclick="javascript:void window.open('https://oauth.vk.com/authorize?client_id='+$('#config_vk_id').val().trim()+'&scope=offline&redirect_uri=<?php echo urlencode(PROTOCOL.$_SERVER['SERVER_NAME'].'/vktoken'); ?>&response_type=code&v=5.32&state=1','vkauthwindow','width=656,height=377,toolbar=0,menubar=0,location=0,status=1,scrollbars=0,resizable=1'); return false;">Активировать</a>
				<a class="btn1 btn_socialinfo_unlink" href="" onclick="config_socialinfo_unlink(); return false;" style="background:#f36b69;">Отключить</a>
			</div>
		</div>
	</div>
</div>
<div class="mw config_orglist_addwindow">
	<a class="closemw" href="javascript:closemw('config_orglist_addwindow');"><i class="icon-remove"></i></a>
	<h1>Регистрация подразделения</h1>
	<div class="row-fluid form-horizontal" style="width:500px;">

		<div class="control-group">
			<label class="control-label">Сокращенное наим.:</label>
			<div class="controls">
				<input id="config_orglist_shortname" class="span12" type="text" placeholder="Например, ССиА" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Полное наим.:</label>
			<div class="controls">
				<input id="config_orglist_fullname" class="span12" type="text" placeholder="Например, Совет студентов и аспирантов" />
			</div>
		</div>

		<hr>

		<div class="control-group">
			<div class="controls">
				<a class="btn1" href="" onclick="config_orglist_add(); return false;">Зарегистрировать</a>
			</div>
		</div>
	</div>
</div>
<div class="mw config_depslist_fac_addwindow">
	<a class="closemw" href="javascript:closemw('config_depslist_fac_addwindow');"><i class="icon-remove"></i></a>
	<h1>Регистрация факультета</h1>
	<div class="row-fluid form-horizontal" style="width:500px;">

		<div class="control-group">
			<label class="control-label">Сокращенное наим.:</label>
			<div class="controls">
				<input id="config_depslist_fac_shortname" class="span12" type="text" placeholder="Например, ЭФ" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Полное наим.:</label>
			<div class="controls">
				<input id="config_depslist_fac_fullname" class="span12" type="text" placeholder="Например, Экономический факультет" />
			</div>
		</div>

		<hr>

		<div class="control-group">
			<div class="controls">
				<a class="btn1" href="" onclick="config_depslist_fac_add(); return false;">Зарегистрировать</a>
			</div>
		</div>
	</div>
</div>
<div class="mw config_depslist_groups_addwindow">
	<a class="closemw" href="javascript:closemw('config_depslist_groups_addwindow');"><i class="icon-remove"></i></a>
	<h1>Регистрация группы</h1>
	<div class="row-fluid form-horizontal" style="width:500px;">

		<div class="control-group">
			<label class="control-label">Сокращенное наим.:</label>
			<div class="controls">
				<input id="config_depslist_groups_shortname" class="span12" type="text" placeholder="Например, МЭ" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Полное наим.:</label>
			<div class="controls">
				<input id="config_depslist_groups_fullname" class="span12" type="text" placeholder="Например, Мировая экономика" />
			</div>
		</div>

		<hr>

		<div class="control-group">
			<div class="controls">
				<a class="btn1" href="" onclick="config_depslist_groups_add(); return false;">Зарегистрировать</a>
			</div>
		</div>
	</div>
</div>
<div class="mw studentsupload">
	<a class="closemw" href="javascript:closemw('studentsupload');"><i class="icon-remove"></i></a>
	<h1>Загрузка списка студентов</h1>

	<div class="studentsupload_settype">
		<div class="alert alert-success" style="font-size:12px; line-height:12px;">
			<span>
				<b>Загрузить текущий список студентов</b>
				<p>Если в файле присутствует студент, которого еще нет в системе, то он будет добавлен в «Список студентов, отражающихся в рейтинге».</p>
				<p>Если в файле присутствует студент, который уже находится в системе (в «Списке студентов, отражающихся в рейтинге» или в «Архиве студентов»), то информация о нем заменяется на ту, что находится в загружаемом файле (кроме номера телефона). Если этот студент находится в «Архиве студентов», то он переведется в «Список студентов, отражающихся в рейтинге».</p>
				<p>Если в файле отсутствует студент, который есть в «Списке студентов, отражающихся в рейтинге», то этот студент будет переведен в «Архив студентов»</p>
				<b>Загрузить дополнительный список студентов (без перевода в «Архив студентов»)</b>
				<p>Если в файле присутствует студент, которого еще нет в системе, то он будет добавлен в «Список студентов, отражающихся в рейтинге».</p>
				<p>Студенты, которые не присутствуют в загружаемом файле, но присутствуют в «Списке студентов, отражающихся в рейтинге» остаются в этом же списке и не переводятся в «Архив студентов».</p>

			</span>
		</div>

		<div class="row-fluid form-horizontal" style="width:500px;">

			<div class="control-group">
				<div class="controls">
					<input type="radio" name="studentsupload_type" value="reload" /> Загрузить текущий список студентов<br />
					<input type="radio" name="studentsupload_type" value="add" /> Загрузить дополнительный список студентов<br />
				</div>
			</div>
		</div>

		<div class="row-fluid" style="text-align:center;">
			<a class="btn1" href="" onclick="config_studentsupload_upload(); return false;">Продолжить</a>
		</div>
	</div>

	<div class="studentsupload_confirmtext">
		<div class="whitebox">
			<div class="row-fluid whitebox_line">
				<div class="span12">
					<div class="row-fluid studentsupload_confirmtext_inner">
					</div>
				</div>
			</div>
		</div>

		<div class="row-fluid" style="text-align:center;">
			<a class="btn1" href="" onclick="config_studentsupload_confirm(); return false;" style="background:#f36b69;">Выполнить</a>
		</div>
	</div>

	<div class="studentsupload_confirmbox">
		<b>Соотнесите столбцы и соответствующие поля</b>
		<div class="alert" style="font-size:12px; line-height:12px;">
			<span>Обязательные поля: <b>Идентификатор студента</b>, <b>Фамилия</b>, <b>Имя</b>, <b>Отчество</b>, <b>Дата рождения</b>, <b>Ступень образования</b>, <b>Факультет (аббревиатура)</b>, <b>Группа (аббревиатура)</b>, <b>Номер группы</b>.<br><br>Если поле <b>Пол</b> не выбрано, то пол будет определен автоматически.<br>Если поле <b>Номер телефона</b> не выбрано, то номер телефона будет установлен как "неизвестно".<br>Если поле <b>Форма обучения</b> не выбрано, то форма обучения будет установлена как "очная".<br>Если поле <b>Форма оплаты</b> не выбрано, то форма оплаты будет установлена как "бюджет".</span>
		</div>
		<div class="whitebox">
			<div class="row-fluid whitebox_line">
				<div class="span12">
					<div class="row-fluid connectbox_list">
					</div>
				</div>
			</div>
		</div>

		<div class="row-fluid" style="text-align:center;">
			<a class="btn1" href="" onclick="config_studentsupload_connect(); return false;">Продолжить</a>
		</div>
	</div>

	<div class="studentsupload_start">
		<div class="whitebox">
			<div class="row-fluid whitebox_line">
				<div class="span12">
					<b>ТРЕБОВАНИЯ К ЗАГРУЖАЕМОЙ ИНФОРМАЦИИ</b><br>
					<p>Файл с таблицей в формате .CSV (в кодировке UTF-8).<br>
						Столбцы:
						<ul style="font-size:12px !important; line-height:12px !important;">
							<li>УНИКАЛЬНЫЙ НОМЕР СТУДЕНЧЕСКОГО БИЛЕТА или ИДЕНТИФИКАТОР СТУДЕНТА <span class="blowit">(который студент будет использовать как логин для входа на сайт)</span></li>
							<li>ФАМИЛИЯ</li>
							<li>ИМЯ</li>
							<li>ОТЧЕСТВО <span class="blowit">(если есть)</span></li>
							<li>ПОЛ <span class="blowit">(<b>«м</b>» - мужской, <b>«ж»</b> - женский; если не указано, то будет определен автоматически)</span></li>
							<li>ДАТА РОЖДЕНИЯ <span class="blowit">(<b>дд.мм.гггг</b> или <b>дд.мм.гг</b>)</span></li>
							<li>МОБИЛЬНЫЙ НОМЕР ТЕЛЕФОНА <span class="blowit">(если есть; в любом формате)</span></li>
							<li>ФОРМА ОБУЧЕНИЯ <span class="blowit">(Очная форма = <b>«1»</b>; Очно-заочная (вечерняя) = <b>«2»</b>; Заочная = <b>«3»</b>)</span></li>
							<li>СТУПЕНЬ ОБРАЗОВАНИЯ <span class="blowit">(СПО = <b>«1»</b>; Бакалавриат = <b>«2»</b>; Магистратура = <b>«3»</b>; Специалитет = <b>«4»</b>; Аспирантура = <b>«5»</b>)</span></li>
							<li>Сокращенное название факультета (аббревиатура)</li>
							<li>Сокращенное название группы (аббревиатура)</li>
							<li>НОМЕР КУРСА</li>
							<li>НОМЕР ГРУППЫ</li>
							<li>ФОРМА ОПЛАТЫ <span class="blowit">(Бюджет = <b>«б»</b>; Коммерция (пвз) = <b>«к»</b>; если не указано, то по умолчанию - бюджет)</span></li>
						</ul>
						Первая строка содержит произвольные заголовки столбцов и не читается системой.
					</p>
					<div><a href="img/csv-example.png" id="image"><img src="img/csv-example.png?2" alt="" width="100%" /></a></div>
				</div>
			</div>
		</div>
		<div class="alert alert-error" style="font-size:12px; line-height:12px;">
			<span>Идентификатор студента должен быть уникальным и не повторяться даже с течением лет. То есть идентификатор нового для системы студента, добавленного в новом учебном году, не может совпадать с идентификатором одного из уже добавленных когда-либо студентов, так как это приведет к замене данных раннее добавленного студента данными нового.
			</span>
		</div>
		<div class="alert alert-warning" style="font-size:12px; line-height:12px;">
			<span>После загрузки вам будет предложено соотнести столбцы файла и соответствующие поля системы.<br>Только после вашей проверки и соотношения столбцов данные будут внесены в систему.</span>
		</div>
		<!-- <br>Указанные факультеты и группы будут зарегистрированы в разделе "Подразделения" &gt; "Факультеты, группы". -->

		<hr>

		<div class="row-fluid form-horizontal" style="width:500px;">

			<div class="control-group">
				<label class="control-label">Файл (.CSV):</label>
				<div class="controls">
					<input id="config_studentsupload_file" class="span12" type="file" />
				</div>
			</div>

			<div class="control-group">
				<div class="controls">
					<a class="btn1" href="" onclick="config_studentsupload_settype(); return false;" style="background:#f36b69;">Загрузить</a>
				</div>
			</div>
		</div>
	</div>
</div>

	<?php render_header(); ?>
	<section class="page">
		<section class="content">
			<div class="row-fluid content_sides">
				<div class="row-fluid">
					<div class="span3">
						<?php menu();
						?>
					</div>
					<div class="span9">
						<div class="span12 titleline">
							<div class="row-fluid">
								<div class="span6"><h1>Конфигурация</h1></div>
								<div class="span6 userline"><div><span><?php echo LOGGED_FIRSTNAME.' '.LOGGED_SURNAME; ?></span> | <a class="logout" href="#">Выход</a></div></div>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12">

								<div class="mainmenu_box">
									<div class="whitebox">
										<div class="row-fluid whitebox_line">
											<div class="span6"><b>Основная информация об организации</b></div>
											<div class="span6"><a href="" class="btn btnadd" onclick="config_commoninfo_window(); return false;">Настроить</a></div>
										</div>
										<div class="row-fluid whitebox_line">
											<div class="span6"><b>Рейтинговая система</b></div>
											<div class="span6"><a href="" class="btn btnadd" onclick="config_ratinginfo_window(); return false;">Настроить</a></div>
										</div>
										<div class="row-fluid whitebox_line">
											<div class="span6"><b>SMS-уведомления</b></div>
											<div class="span6"><a href="" class="btn btnadd" onclick="config_smsinfo_window(); return false;">Настроить</a></div>
										</div>
										<div class="row-fluid whitebox_line">
											<div class="span6"><b>Авторизация через соцсети</b></div>
											<div class="span6"><a href="" class="btn btnadd" onclick="config_socialinfo_window(); return false;">Настроить</a></div>
										</div>
										<div class="row-fluid whitebox_line">
											<div class="span6"><b>Подразделения организации</b></div>
											<div class="span6"><a href="" class="btn btnadd" onclick="config_depsinfo_window(); return false;">Настроить</a></div>
										</div>
										<div class="row-fluid whitebox_line">
											<div class="span6"><b>Студенты</b></div>
											<div class="span6"><a href="" class="btn btnadd" onclick="config_studentsinfo_window(); return false;">Настроить</a></div>
										</div>
									</div>
								</div>

								<div class="depsinfo_box">
									<div class="whitebox">
										<div class="row-fluid whitebox_line">
											<div class="span6"><b>Факультеты, группы</b></div>
											<div class="span6"><a href="" class="btn btnadd" onclick="config_depslist_fac_windows(); return false;">Настроить</a></div>
										</div>
										<div class="row-fluid whitebox_line">
											<div class="span6"><b>Подразделения, организующие мероприятия</b></div>
											<div class="span6"><a href="" class="btn btnadd" onclick="config_orglist_windows(); return false;">Настроить</a></div>
										</div>
									</div>
									<a class='btn' href='' onclick='mainmenu_window("depsinfo_box"); return false;'>&lt; Меню конфигурации</a>
								</div>

								<div class="deplist_box">
									<div class="whitebox">
										<div class="row-fluid whitebox_line">
											<div class="span6"><b>Факультеты</b></div>
											<div class="span6" style="text-align:right;"><a href="" class="btn1 btnadd" style="display:inline-block;" onclick="config_depslist_fac_addwindow(); return false;">Добавить</a></div>
										</div>
									</div>
									<div class="table_head">
										<div>
											<div style="margin:0 10px;">
												<div class="row-fluid" style="height:30px;">
													<div class="span1" style="padding-top:5px;"><b>Тип</b></div>
													<div class="span3" style="padding-top:5px;"><b>Сокращенное название</b></div>
													<div class="span5" style="padding-top:5px;"><b>Полное название</b></div>
													<div class="span1"></div>
													<div class="span1"></div>
												</div>
											</div>
										</div>
									</div>
									<div class="deplist">
									</div>
									<div class="lowerbox">
										<div class="center textalert_depslist_fac">Ни одного факультета не зарегистрировано</div>
									</div>
									<hr>
									<a class='btn' href='' onclick='$(".deplist_box").hide("slide", {direction: "right"}, function() { $(".depsinfo_box").show("slide", {direction: "left"}); }); return false;'>&lt; Подразделения организации</a>
								</div>

								<div class="grouplist_box">
									<div class="whitebox">
										<div class="row-fluid whitebox_line">
											<div class="span6"><b id="box_title"></b></div>
											<div class="span6" style="text-align:right;"><a href="" class="btn1 btnadd" style="display:inline-block;" onclick="config_depslist_groups_addwindow(); return false;">Добавить</a></div>
										</div>
									</div>
									<div class="table_head">
										<div>
											<div style="margin:0 10px;">
												<div class="row-fluid" style="height:30px;">
													<div class="span1" style="padding-top:5px;"><b>Тип</b></div>
													<div class="span3" style="padding-top:5px;"><b>Сокращенное название</b></div>
													<div class="span5" style="padding-top:5px;"><b>Полное название</b></div>
													<div class="span1"></div>
													<div class="span1"></div>
												</div>
											</div>
										</div>
									</div>
									<div class="grouplist">
									</div>
									<div class="lowerbox">
										<div class="center textalert_depslist_groups">В выбранном факультете не зарегистрировано ни одной группы</div>
									</div>
									<hr>
									<a class='btn' href='' onclick='config_depslist_fac_windows(); return false;'>&lt; Факультеты</a>
								</div>

								<div class="orglist_box">
									<div class="whitebox">
										<div class="row-fluid whitebox_line">
											<div class="span6"><b>Подразделения, организующие мероприятия</b></div>
											<div class="span6" style="text-align:right;"><a href="" class="btn1 btnadd" style="display:inline-block;" onclick="config_orglist_addwindow(); return false;">Добавить</a></div>
										</div>
									</div>
									<div class="table_head">
										<div>
											<div style="margin:0 10px;">
												<div class="row-fluid" style="height:30px;">
													<div class="span1"></div>
													<div class="span3" style="padding-top:5px;"><b>Сокращенное название</b></div>
													<div class="span7" style="padding-top:5px;"><b>Полное название</b></div>
													<div class="span1"></div>
												</div>
											</div>
										</div>
									</div>
									<div class="orglist ui-sortable">
									</div>
									<div class="lowerbox">
										<div class="center textalert_orglist">Ни одного подразделения не зарегистрировано</div>
									</div>
									<hr>
									<a class='btn' href='' onclick='$(".orglist_box").hide("slide", {direction: "right"}, function() { $(".depsinfo_box").show("slide", {direction: "left"}); }); return false;'>&lt; Подразделения организации</a>
								</div>

								<div class="studentsinfo_box">
									<div class="whitebox">
										<div class="row-fluid whitebox_line">
											<div class="span6"><b>Загрузить список студентов</b></div>
											<div class="span6" style="text-align:right;"><a href="" class="btn1 btnadd" onclick="config_studentsupload_window(); return false;" style='background:#f36b69;'>Загрузить</a></div>
										</div>
									</div>
									<!--<div class="whitebox">
										<div class="row-fluid whitebox_line">
											<div class="span6"><b>Студенты, отображающиеся в рейтинге</b></div>
											<div class="span6"><a href="" class="btn btnadd" onclick="return false;">Настроить</a></div>
										</div>
										<div class="row-fluid whitebox_line">
											<div class="span6"><b>Архив студентов (отчисленные, академ.отпуск)</b></div>
											<div class="span6"><a href="" class="btn btnadd" onclick="return false;">Настроить</a></div>
										</div>
									</div>-->
									<a class='btn' href='' onclick='mainmenu_window("studentsinfo_box"); return false;'>&lt; Меню конфигурации</a>
								</div>
								<div class="students_rating">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</section>
	<?php render_footer(); ?>
</body>
</html>
