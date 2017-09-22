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
}
$(function() {
	$('body').on('click', '.close-me', function() {
		$('#alert_box').fadeOut();
	});
});