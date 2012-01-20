- if ($ctx->must_checkout)
	.'alert-message block-message info'	
		%center
			%a href='#{$ctx->self_id_url('checkout')}'
				=l('must_checkout')
%form action='#{$ctx->self_id_url('update')}' method='POST' 
	-if ($ctx->diff_result)
		.'alert-message block-message info'
			%center
				#{count($ctx->diff_result['files']) ? l('diff_modified_files') : l('diff_no_modified_files')}
		- if (count($ctx->diff_result['files']))
			%center
				%a href='#' id=checkall
					select
				%nbsp;
				%a href='#' id=uncheckall
					deselect
				%br
		- foreach($ctx->diff_result{'files'} as $f)
			%input type='checkbox' name='path[]' value='#{$f}'
			%code
				=$f
				%br /
		.row
			&nbsp;
		%center
			%input.'btn primary' type='submit' #{count($ctx->diff_result['files']) ? "" : "disabled" }  value=#{l('diff_update')}
	
		%script
			$('#checkall').click(function () {
				$.each($.find(':checkbox'), function() { 
					$(this).attr('checked','');
				})			
			});
			$('#uncheckall').click(function () {
				$.each($.find(':checkbox'), function() { 
					$(this).removeAttr('checked');
				})			
			});
