<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
%html xmlns="http://www.w3.org/1999/xhtml" xml:lang="#{l('lang_shortname')}" lang="#{l('lang_shortname')}"
	%head
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
		<meta name="author" content="Quick-and-Simple (http://github.com/Quick-and-Simple/)" />
		<meta content="[metaKeywords]" name="keywords" />
		<meta content="[metaDecription]" name="description" />
		%title
			=l('app_meta_title')
		%script type="text/javascript" src="js/jquery/jquery-1.7.1.min.js"
		%script type="text/javascript" src="js/bootstrap-modal.js"
		%script type="text/javascript" src="js/bootstrap-dropdown.js"		
		%script type="text/javascript" src="js/main.js"
			
		
		%link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" /		
		%link rel="stylesheet" type="text/css" href="css/main.css" /				
	%body
		.container
			.topbar-wrapper
				.topbar  data-dropdown="dropdown"
					.topbar-inner 
						.container
							%a.brand href=#{$ctx->gen_url('diff','logout',false)}
								Logout
							%ul
								- foreach (M_REPO::repository_list() as $f)
									%li
										%a href='#{$ctx->gen_url('diff','diff',$f[1])}'
											=$f[1]
								
								
			%center
				%br /
				- if ($ctx->has_flash())
					%hr /
					%br /
					.'alert-message block-message error'
						- foreach ($ctx->get_flash_and_clear('global') as $f)
							=$f
							%br /
				%hr /
				
		.container			
			[[YIELD]]