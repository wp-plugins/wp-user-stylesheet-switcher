jQuery( document ).ready(function() {
	var cookie = Cookies.getJSON('wp_user_stylesheet_switcher_js');
	if(null == cookie) {
		//console.log ('Aucun cookie');
	} else {
		var switcher;
		for (switcher in cookie) {
			wp_user_stylesheet_switcher_changeCSS (switcher, cookie[switcher]);
			jQuery("select[name='user_stylesheet_switcher_choice_dropdown_"+switcher+"']").val(cookie[switcher]);
		}
	}
});

