<style>
	.smiley{display:inline-block;}
	.smiley img{cursor:pointer;}
</style>
<div id="site_content">
	<h3 style="display: inline;">
		<a href="<?php echo $homeUrl; ?>forum">论坛</a>
		&raquo; <a href="<?php echo $homeUrl; ?>forum"><?php echo $forum->name; ?></a>
		&raquo; <a href="<?php echo $homeUrl; ?>forum/topic/<?php echo $topic->id; ?>"><?php echo $topic->topic_title; ?></a>
		&raquo; 发表回复
	</h3>
	<h1>发表回复</h1>
	<form id="post_form">
		<div class="form_settings">
			<p><span>用户名</span><?php echo $user->nickname; ?></p>
			<p><span>主题</span><a href="<?php echo $homeUrl; ?>forum/topic/<?php echo $topic->id; ?>"><?php echo $topic->topic_title; ?></a></p>
			<p>
				<span>内容
					<br />
					<br /><?php echo $form['optionsInput']; ?>
				</span>
				<?php echo $form['bbcodeControls']; ?><br />
				<textarea id="tags-txtarea" name="content" rows="15" cols="60"><?php echo $form['quotedPost']; ?></textarea>
			</p>
			<p>
				<span>&nbsp;</span>
				<em class="smiley" style="width:299px;" data-bak="width:329px"><?php echo $form['smileyControls']; ?></em>
			</p>
			<p style="padding-top: 15px">
				<span>&nbsp;</span>
				<input type="hidden" name="fid" value="<?php echo $topic->forum_id; ?>" />
				<input type="hidden" name="tid" value="<?php echo $topic->id; ?>" />
				<input type="submit" name="submit" value="提交" class="submit" />
				<input type="reset" name="reset" value="重置" class="submit" />
			</p>
		</div>
	</form>
	<table style="width:100%; border-spacing:0;">
		<tr>
			<td colspan="2">&raquo; 主题回顾</td>
		</tr>
		<tr>
			<th>作者</th>
			<th>帖子</th>
		</tr>
		<?php foreach ($posts as $node): ?>
			<tr class="tr<?php echo $node->colornum; ?>">
				<td>
					<?php echo $node->nickname; ?>
					<br />
					<?php echo $node->post_time; ?>
				</td>
				<td>
					<?php echo $node->post_content; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<p><?php echo $viewMorePosts; ?></p>
</div>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>';
	var topicId = '<?php echo $topic->id; ?>';
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
			$.post(homeUrl + 'forum/post_reply/', $(this).serialize(), function(r) {
				$('input[type="submit"]').prop('disabled', true);
				if (r.status === 1) {
					$('input[type="submit"]').prop('disabled', false);
					window.location.href = homeUrl + 'forum/topic/' + topicId + '/?page=-1#lastpost';
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