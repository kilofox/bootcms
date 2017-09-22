<style>
	.smiley{display:inline-block;}
	.smiley img{cursor:pointer;}
</style>
<div id="site_content">
	<h3 style="display: inline;">
		<a href="<?php echo $homeUrl; ?>forum">论坛</a>
		&raquo; <a href="<?php echo $homeUrl; ?>forum"><?php echo $forum->name; ?></a>
		&raquo; 新主题
	</h3>
	<h1>新主题</h1>
	<form id="post_form">
		<div class="form_settings">
			<p><span>用户名</span><?php echo $user->nickname; ?></p>
			<p><span>主题</span><input type="text" name="subject" size="50" value="" /></p>
			<p>
				<span>内容
					<br />
					<br /><?php echo $form['optionsInput']; ?>
				</span>
				<?php echo $form['bbcodeControls']; ?>
				<br />
				<textarea id="tags-txtarea" name="content" rows="15" cols="60"></textarea>
			</p>
			<p>
				<span>&nbsp;</span>
				<em class="smiley" style="width:299px;"><?php echo $form['smileyControls']; ?></em>
			</p>
			<p style="padding-top: 15px">
				<span>&nbsp;</span>
				<input type="hidden" name="fid" value="<?php echo $forum->id; ?>" />
				<input type="submit" name="submit" value="提交" class="submit" />
			</p>
		</div>
	</form>
</div>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>';
	var insertTags = function(tagOpen, tagClose) {
		var txtarea = document.getElementById('tags-txtarea');
		var sampleText = '';
		// IE
		if (document.selection && !is_gecko) {
			var theSelection = document.selection.createRange().text;
			var replaced = true;
			if (!theSelection) {
				replaced = false;
				theSelection = sampleText;
			}
			txtarea.focus();
			text = theSelection;
			if (theSelection.charAt(theSelection.length - 1) == ' ') {
				theSelection = theSelection.substring(0, theSelection.length - 1);
				r = document.selection.createRange();
				r.text = tagOpen + theSelection + tagClose + ' ';
			} else {
				r = document.selection.createRange();
				r.text = tagOpen + theSelection + tagClose;
			}
			if (!replaced) {
				r.moveStart('character', -text.length - tagClose.length);
				r.moveEnd('character', -tagClose.length);
			}
			r.select();
		}
		// Firefox
		else if (txtarea.selectionStart || txtarea.selectionStart == '0') {
			var replaced = false;
			var startPos = txtarea.selectionStart;
			var endPos = txtarea.selectionEnd;
			if (endPos - startPos)
				replaced = true;
			var scrollTop = txtarea.scrollTop;
			var myText = (txtarea.value).substring(startPos, endPos);
			if (!myText) {
				myText = sampleText;
			}
			if (myText.charAt(myText.length - 1) == ' ') {
				subst = tagOpen + myText.substring(0, (myText.length - 1)) + tagClose + " ";
			} else {
				subst = tagOpen + myText + tagClose;
			}
			txtarea.value = txtarea.value.substring(0, startPos) + subst + txtarea.value.substring(endPos, txtarea.value.length);
			txtarea.focus();
			if (replaced) {
				var cPos = startPos + (tagOpen.length + myText.length + tagClose.length);
				txtarea.selectionStart = cPos;
				txtarea.selectionEnd = cPos;
			} else {
				txtarea.selectionStart = startPos + tagOpen.length;
				txtarea.selectionEnd = startPos + tagOpen.length + myText.length;
			}
			txtarea.scrollTop = scrollTop;

		}
		// Others
		else {
			var copy_alertText = alertText;
			var re1 = new RegExp("\\$1", "g");
			var re2 = new RegExp("\\$2", "g");
			copy_alertText = copy_alertText.replace(re1, sampleText);
			copy_alertText = copy_alertText.replace(re2, tagOpen + sampleText + tagClose);
			var text;
			if (sampleText) {
				text = prompt(copy_alertText);
			} else {
				text = "";
			}
			if (!text) {
				text = sampleText;
			}
			text = tagOpen + text + tagClose;
			txtarea.value += "\n" + text;
			if (!is_safari) {
				txtarea.focus();
			}
		}
		if (txtarea.createTextRange)
			txtarea.caretPos = document.selection.createRange().duplicate();
	};
	$(function() {
		$('#post_form').submit(function() {
			$.post(homeUrl + 'forum/post_topic/', $('#post_form').serialize(), function(r) {
				$('input[type="submit"]').prop('disabled', true);
				if (r.status == '1') {
					$('input[type="submit"]').prop('disabled', false);
					window.location.href = homeUrl + 'forum/topic/' + r.content + '/';
				} else {
					$('input[type="submit"]').prop('disabled', false);
					showMsg(r.title, r.content);
				}
			}, 'json');
			return false;
		});
		$('.smiley > img').click(function() {
			insertTags(' ' + $(this).attr('alt') + ' ', '');
		});
		$('a[data-open]').click(function() {
			insertTags($(this).attr('data-open'), $(this).attr('data-close'));
		});
	});
</script>