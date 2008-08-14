function add_author_edit_links( row ) {
	var reg = ameAjaxL10n.postType + "-([0-9]+)";
	var Ausdruck = new RegExp(reg, "i");
	var post_id = Ausdruck.exec(row.id)[1];
	jQuery("a[href^='edit.php?author='], a[href^='edit-pages.php?author=']", row).each(function() {
		jQuery(this).after(' <a href="javascript:void(0);" onclick="ame_author_edit(' + post_id + ', \'' + ameAjaxL10n.postType + '\');"><img src="' + ameAjaxL10n.imgUrl + 'edit_small.gif" border="0" alt="' + ameAjaxL10n.Edit + '" title="' + ameAjaxL10n.Edit + '" /></a>');
	});
}

function ame_roll_through_author_rows() {
	jQuery("tr[id^='" + ameAjaxL10n.postType + "-']").each(function() {
    	add_author_edit_links(this);
  	});
}


function add_title_edit_links( row ) {
	var reg = ameAjaxL10n.postType + "-([0-9]+)";
	var Ausdruck = new RegExp(reg, "i");
	var post_id = Ausdruck.exec(row.id)[1];
	jQuery("a[href^='post.php?action=edit&post='], a[href^='page.php?action=edit&post=']", row).each(function() {
		var title = jQuery(this).html();
		title = title.replace(/— /g, "");
		jQuery(this).after(' <a href="javascript:void(0);" onclick="ame_title_edit(' + post_id + ', \'' + title + '\', \'' + ameAjaxL10n.postType + '\');"><img src="' + ameAjaxL10n.imgUrl + 'edit_small.gif" border="0" alt="' + ameAjaxL10n.Edit + '" title="' + ameAjaxL10n.Edit + '" /></a>');
	});
}

function ame_roll_through_title_rows() {
	jQuery("tr[id^='" + ameAjaxL10n.postType + "-']").each(function() {
    	add_title_edit_links(this);
  	});
}