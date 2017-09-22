/**
 * 提示信息对话框
 */
var showMsg = function(msgTitle, msgText, withButton) {
	var button = withButton == false ? '' : '<span class="close-me">关闭</span>';
	var show = '<div id="alert_box">\
		<div class="filter"></div>\
		<div class="msg_box">\
			<h3>' + msgTitle + '</h3>\
			<p>' + msgText + '</p>'
		+ button +
		'</div>\
	</div>';
	$('#alert_box').remove();
	$('body').append(show);
	$('#alert_box').fadeIn();
};
$(function() {
	// 菜单显示与隐藏
	var showText = '显示';
	var hideText = '隐藏';
	$('.toggle').prev().append('<a href="javascript:void(0)" class="toggleLink">' + hideText + '</a>');
	$('.toggle').show();
	$('a.toggleLink').click(function() {
		if ($(this).text() == showText) {
			$(this).text(hideText);
			$(this).parent().next('.toggle').slideDown('slow');
		} else {
			$(this).text(showText);
			$(this).parent().next('.toggle').slideUp('slow');
		}
		return false;
	});
	// 关闭提示信息对话框
	$('body').on('click', '.close-me', function() {
		$('#alert_box').fadeOut();
	});
});
